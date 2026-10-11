<?php

namespace App\Services;

use App\Models\Author;
use App\Models\PayoutAllocation;
use App\Models\PayoutAudit;
use App\Models\PayoutRequest;
use App\Models\RoyaltyLedger;
use App\Models\User;
use App\Support\Money;
use DomainException;
use Illuminate\Support\Facades\DB;

class PayoutService
{
    public function requestPayout(Author $author, int|float|string $amount, string $idempotencyKey): PayoutRequest
    {
        $this->ensureEnabled();
        $minor = Money::minor($amount);
        if ($minor < 10000000) {
            throw new DomainException('Batas minimum penarikan adalah Rp 100.000.');
        }
        if (! preg_match('/\A[a-zA-Z0-9_-]{16,100}\z/', $idempotencyKey)) {
            throw new DomainException('Kunci permohonan payout tidak valid.');
        }

        return DB::transaction(function () use ($author, $minor, $idempotencyKey) {
            // All payout operations lock the author first, then payout/ledgers.
            // This also serializes requests when an author has no ledger rows.
            $lockedAuthor = Author::whereKey($author->id)->lockForUpdate()->firstOrFail();
            $existing = PayoutRequest::where('author_id', $author->id)->where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                if (Money::minor($existing->amount) !== $minor) {
                    throw new DomainException('Kunci permohonan sudah digunakan untuk nominal berbeda.');
                }

                return $existing;
            }
            if (! $lockedAuthor->isVerified() || ! $lockedAuthor->user->is_active) {
                throw new DomainException('Akun belum terverifikasi KYC atau tidak aktif.');
            }
            foreach (['bank_name', 'bank_account', 'bank_holder_name'] as $field) {
                if (blank($lockedAuthor->$field)) {
                    throw new DomainException('Data rekening bank tidak lengkap.');
                }
            }

            $ledgers = RoyaltyLedger::where('author_id', $author->id)->orderBy('created_at')->orderBy('id')->lockForUpdate()->get();
            $remaining = [];
            $total = 0;
            foreach ($ledgers as $ledger) {
                $consumed = PayoutAllocation::where('royalty_ledger_id', $ledger->id)
                    ->whereHas('payoutRequest', fn ($q) => $q->whereIn('status', ['requested', 'processing', 'completed']))
                    ->get()->sum(fn ($allocation) => Money::minor($allocation->amount));
                $available = Money::minor($ledger->author_earning) - $consumed;
                if ($available < 0) {
                    throw new DomainException('Rekonsiliasi ledger gagal; proses payout dihentikan.');
                }
                $remaining[$ledger->id] = $available;
                $total += $available;
            }
            if ($minor > $total) {
                throw new DomainException('Saldo tersedia tidak mencukupi.');
            }

            $payout = PayoutRequest::create([
                'author_id' => $author->id,
                'idempotency_key' => $idempotencyKey,
                'amount' => Money::decimal($minor),
                'bank_name_snapshot' => $lockedAuthor->bank_name,
                'bank_account_snapshot' => $lockedAuthor->bank_account,
                'bank_holder_name_snapshot' => $lockedAuthor->bank_holder_name,
                'status' => 'requested',
            ]);
            $toAllocate = $minor;
            foreach ($ledgers as $ledger) {
                $take = min($toAllocate, $remaining[$ledger->id]);
                if ($take > 0) {
                    $payout->allocations()->create(['royalty_ledger_id' => $ledger->id, 'amount' => Money::decimal($take)]);
                    $toAllocate -= $take;
                }
                if ($toAllocate === 0) {
                    break;
                }
            }
            PayoutAudit::create(['payout_request_id' => $payout->id, 'action' => 'requested', 'actor_id' => $lockedAuthor->user_id, 'occurred_at' => now()]);

            return $payout;
        }, 3);
    }

    public function rejectPayout(PayoutRequest $payout, string $reason, int $adminId): void
    {
        if (strlen(trim($reason)) < 5 || mb_strlen($reason) > 1000) {
            throw new DomainException('Alasan penolakan harus 5–1000 karakter.');
        }
        $this->transition($payout, 'rejected', $adminId, ['admin_notes' => $reason]);
    }

    public function completePayout(PayoutRequest $payout, string $refNumber, ?string $proofPath, int $adminId): void
    {
        if (blank($refNumber) || mb_strlen($refNumber) > 100) {
            throw new DomainException('Nomor referensi transfer tidak valid.');
        }
        $this->transition($payout, 'completed', $adminId, ['reference_number' => $refNumber, 'transfer_proof_path' => $proofPath]);
    }

    private function transition(PayoutRequest $payout, string $target, int $adminId, array $details): void
    {
        $this->ensureEnabled();
        DB::transaction(function () use ($payout, $target, $adminId, $details) {
            Author::whereKey($payout->author_id)->lockForUpdate()->firstOrFail();
            $admin = User::whereKey($adminId)->firstOrFail();
            if (! $admin->is_admin || ! $admin->is_active) {
                throw new DomainException('Pemrosesan payout hanya untuk administrator aktif.');
            }
            $locked = PayoutRequest::whereKey($payout->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === $target) {
                foreach ($details as $field => $value) {
                    if ($value !== $locked->$field) {
                        throw new DomainException('Payout telah diproses dengan data berbeda.');
                    }
                }

                return;
            }
            if (! in_array($locked->status, ['requested', 'processing'], true)) {
                throw new DomainException('Transisi status payout tidak valid.');
            }
            $allocations = $locked->allocations()->lockForUpdate()->get();
            if ($allocations->sum(fn ($a) => Money::minor($a->amount)) !== Money::minor($locked->amount)) {
                throw new DomainException('Rekonsiliasi alokasi payout gagal.');
            }
            foreach ($allocations as $allocation) {
                $ledger = RoyaltyLedger::whereKey($allocation->royalty_ledger_id)->lockForUpdate()->firstOrFail();
                $consumed = $ledger->allocations()->whereHas('payoutRequest', fn ($q) => $q->whereIn('status', ['requested', 'processing', 'completed']))
                    ->get()->sum(fn ($a) => Money::minor($a->amount));
                if ($ledger->author_id !== $locked->author_id || $consumed > Money::minor($ledger->author_earning)) {
                    throw new DomainException('Integritas ledger payout gagal.');
                }
            }
            $locked->update($details + ['status' => $target, 'processed_by' => $adminId, 'processed_at' => now()]);
            PayoutAudit::create(['payout_request_id' => $locked->id, 'action' => $target, 'actor_id' => $adminId, 'occurred_at' => now()]);
        }, 3);
    }

    private function ensureEnabled(): void
    {
        if (! config('features.payout')) {
            throw new DomainException('Fitur payout tidak aktif.');
        }
    }
}
