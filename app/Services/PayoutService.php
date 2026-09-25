<?php

namespace App\Services;

use App\Models\Author;
use App\Models\PayoutRequest;
use App\Models\RoyaltyLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use DomainException;

class PayoutService
{
    /**
     * Request a new payout for an author.
     * Uses pessimistic locking to prevent double-spending.
     *
     * @param Author $author
     * @param float $amount
     * @return PayoutRequest
     * @throws DomainException
     */
    public function requestPayout(Author $author, float $amount): PayoutRequest
    {
        if (! config('features.payout')) throw new DomainException('Fitur payout tidak aktif.');
        if (!$author->isVerified()) {
            throw new DomainException("Akun belum terverifikasi KYC.");
        }

        if ($amount < 100000) {
            throw new DomainException("Batas minimum penarikan adalah Rp 100.000.");
        }

        if (empty($author->bank_name) || empty($author->bank_account) || empty($author->bank_holder_name)) {
            throw new DomainException("Data rekening bank tidak lengkap. Harap lengkapi profil Anda.");
        }

        return DB::transaction(function () use ($author, $amount) {
            // Lock the rows to prevent race conditions (double spending)
            $availableLedgers = RoyaltyLedger::where('author_id', $author->id)
                ->where('status', 'available')
                ->orderBy('created_at', 'asc')
                ->lockForUpdate()
                ->get();

            $totalAvailable = $availableLedgers->sum('author_earning');

            if ($amount > $totalAvailable) {
                throw new DomainException("Saldo tersedia tidak mencukupi.");
            }

            $payout = PayoutRequest::create([
                'id' => (string) Str::ulid(),
                'author_id' => $author->id,
                'amount' => $amount,
                'bank_name_snapshot' => $author->bank_name,
                'bank_account_snapshot' => $author->bank_account,
                'bank_holder_name_snapshot' => $author->bank_holder_name,
                'status' => 'requested',
            ]);

            $remainingAmountToDeduct = $amount;

            // FIFO allocation
            foreach ($availableLedgers as $ledger) {
                if ($remainingAmountToDeduct <= 0) {
                    break;
                }

                $ledgerAmount = $ledger->author_earning;

                if ($remainingAmountToDeduct >= $ledgerAmount) {
                    // Fully allocate this ledger
                    $ledger->update([
                        'status' => 'pending',
                        'payout_request_id' => $payout->id,
                    ]);
                    $remainingAmountToDeduct -= $ledgerAmount;
                } else {
                    // Partially allocate this ledger? Wait, ledgers are atomic per sale.
                    // The requirements say "Kurangi nilai akumulasi hingga $amount terpenuhi."
                    // If we can't split a ledger, we just mark it as pending anyway, which means
                    // the withdrawn amount covers this ledger. But wait, if $amount doesn't exactly match the sum of ledgers,
                    // we'll have a problem because ledgers represent a specific earning. 
                    // To handle exact amounts, maybe we should just mark ledgers as pending until we cover the amount.
                    // Wait, if remainingAmountToDeduct < ledgerAmount, and we mark it as pending, it means we are withdrawing MORE than requested?
                    // Usually in such systems, the user withdraws EXACTLY the sum of some ledgers, or we split the ledger.
                    // But splitting ledger is complex. Let's look at the instruction:
                    // "Kurangi nilai akumulasi hingga `$amount` terpenuhi."
                    // I will just mark the ledger as pending. The remaining ledger value will be part of this payout.
                    // Wait! If they request 100,000, and ledgers are 75,000 and 50,000.
                    // If we mark both as pending, the total in payout is 100,000, but ledgers marked pending is 125,000.
                    // That would mean 25,000 is lost!
                    // Let's implement ledger splitting or just take the exact amount if possible.
                    // Actually, a simpler way is: split the ledger into two: one for the requested amount, one for the remainder.
                    $ledger->update([
                        'status' => 'pending',
                        'payout_request_id' => $payout->id,
                        'author_earning' => $remainingAmountToDeduct,
                        'gross_sale' => $ledger->gross_sale * ($remainingAmountToDeduct / $ledgerAmount), // Approximation
                        'platform_earning' => $ledger->platform_earning * ($remainingAmountToDeduct / $ledgerAmount),
                    ]);

                    $remainder = $ledgerAmount - $remainingAmountToDeduct;
                    
                    // Create a new ledger for the remainder
                    $newLedger = $ledger->replicate();
                    $newLedger->status = 'available';
                    $newLedger->payout_request_id = null;
                    $newLedger->author_earning = $remainder;
                    $newLedger->gross_sale = $ledger->gross_sale - $ledger->gross_sale * ($remainingAmountToDeduct / $ledgerAmount);
                    $newLedger->platform_earning = $ledger->platform_earning - $ledger->platform_earning * ($remainingAmountToDeduct / $ledgerAmount);
                    $newLedger->save();

                    $remainingAmountToDeduct = 0;
                }
            }

            return $payout;
        });
    }

    /**
     * Reject a payout request.
     *
     * @param PayoutRequest $payout
     * @param string $reason
     * @param int $adminId
     * @return void
     */
    public function rejectPayout(PayoutRequest $payout, string $reason, int $adminId): void
    {
        if (! config('features.payout')) throw new DomainException('Fitur payout tidak aktif.');
        DB::transaction(function () use ($payout, $reason, $adminId) {
            RoyaltyLedger::where('payout_request_id', $payout->id)->update([
                'status' => 'available',
                'payout_request_id' => null,
            ]);

            $payout->update([
                'status' => 'rejected',
                'admin_notes' => $reason,
                'processed_by' => $adminId,
                'processed_at' => now(),
            ]);
        });
    }

    /**
     * Complete a payout request.
     *
     * @param PayoutRequest $payout
     * @param string $refNumber
     * @param string|null $proofPath
     * @param int $adminId
     * @return void
     */
    public function completePayout(PayoutRequest $payout, string $refNumber, ?string $proofPath, int $adminId): void
    {
        if (! config('features.payout')) throw new DomainException('Fitur payout tidak aktif.');
        DB::transaction(function () use ($payout, $refNumber, $proofPath, $adminId) {
            RoyaltyLedger::where('payout_request_id', $payout->id)->update([
                'status' => 'withdrawn',
            ]);

            $payout->update([
                'status' => 'completed',
                'reference_number' => $refNumber,
                'transfer_proof_path' => $proofPath,
                'processed_by' => $adminId,
                'processed_at' => now(),
            ]);
        });
    }
}
