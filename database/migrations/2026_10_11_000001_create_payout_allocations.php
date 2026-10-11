<?php

use App\Support\Money;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fail before DDL if the legacy state cannot be reconciled. Never guess
        // which historical sale funded a payout or silently repair old amounts.
        foreach (DB::table('royalty_ledgers')->get() as $ledger) {
            $item = DB::table('order_items')->where('id', $ledger->order_item_id)->first();
            if (! $item || Money::minor($ledger->gross_sale) !== Money::minor($item->price)
                || Money::minor($ledger->author_earning) + Money::minor($ledger->platform_earning) !== Money::minor($ledger->gross_sale)
                || Money::minor($ledger->author_earning) !== intdiv(Money::minor($ledger->gross_sale) * Money::minor($ledger->author_percentage) + 5000, 10000)) {
                throw new RuntimeException('Legacy ledger reconciliation failed; review anonymized historical evidence before migration.');
            }
            if (in_array($ledger->status, ['pending', 'withdrawn'], true) && ! $ledger->payout_request_id) {
                throw new RuntimeException('Legacy allocated ledger has no payout link; manual reconciliation required.');
            }
            if ($ledger->payout_request_id) {
                if (Money::minor($ledger->author_earning) <= 0) {
                    throw new RuntimeException('Legacy allocation is nonpositive; manual reconciliation required.');
                }
                $payout = DB::table('payout_requests')->where('id', $ledger->payout_request_id)->first();
                $expected = match ($payout?->status) {
                    'requested', 'processing' => 'pending',
                    'completed' => 'withdrawn',
                    'rejected' => 'available',
                    default => null,
                };
                if (! $payout || $payout->author_id !== $ledger->author_id || $expected !== $ledger->status) {
                    throw new RuntimeException('Legacy payout/ledger state mismatch; manual reconciliation required.');
                }
            }
        }
        foreach (DB::table('payout_requests')->whereIn('status', ['requested', 'processing', 'completed'])->get() as $payout) {
            $sum = DB::table('royalty_ledgers')->where('payout_request_id', $payout->id)->get()
                ->sum(fn ($row) => Money::minor($row->author_earning));
            if ($sum !== Money::minor($payout->amount)) {
                throw new RuntimeException('Legacy payout amount differs from linked ledger amounts; manual reconciliation required.');
            }
        }

        if (! Schema::hasIndex('payout_requests', 'payout_author_lookup')) {
            Schema::table('payout_requests', fn (Blueprint $table) => $table->index('author_id', 'payout_author_lookup'));
        }
        Schema::table('payout_requests', function (Blueprint $table) {
            $table->string('idempotency_key', 100)->nullable();
            $table->unique(['author_id', 'idempotency_key'], 'payout_author_idempotency_unique');
        });
        Schema::create('payout_allocations', function (Blueprint $table) {
            $table->id();
            $table->string('payout_request_id', 26);
            $table->foreign('payout_request_id')->references('id')->on('payout_requests')->restrictOnDelete();
            $table->foreignId('royalty_ledger_id')->constrained('royalty_ledgers')->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->timestamps();
            $table->unique(['payout_request_id', 'royalty_ledger_id'], 'payout_ledger_unique');
        });
        Schema::create('payout_audits', function (Blueprint $table) {
            $table->id();
            $table->string('payout_request_id', 26);
            $table->foreign('payout_request_id')->references('id')->on('payout_requests')->restrictOnDelete();
            $table->string('action', 20);
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('occurred_at');
            $table->unique(['payout_request_id', 'action']);
        });
        if (DB::getDriverName() === 'sqlite') {
            DB::statement("CREATE TRIGGER payout_positive_insert BEFORE INSERT ON payout_allocations WHEN NEW.amount <= 0 BEGIN SELECT RAISE(ABORT, 'allocation amount must be positive'); END");
            DB::statement("CREATE TRIGGER payout_positive_update BEFORE UPDATE ON payout_allocations WHEN NEW.amount <= 0 BEGIN SELECT RAISE(ABORT, 'allocation amount must be positive'); END");
        } else {
            DB::statement('ALTER TABLE payout_allocations ADD CONSTRAINT payout_allocation_positive CHECK (amount > 0)');
        }
        foreach (['royalty_ledgers' => 'royalty', 'payout_allocations' => 'allocation', 'payout_audits' => 'audit'] as $table => $prefix) {
            foreach (['UPDATE', 'DELETE'] as $operation) {
                $trigger = $prefix.'_immutable_'.strtolower($operation);
                if (DB::getDriverName() === 'sqlite') {
                    DB::statement("CREATE TRIGGER {$trigger} BEFORE {$operation} ON {$table} BEGIN SELECT RAISE(ABORT, 'Financial record is immutable'); END");
                } else {
                    DB::statement("CREATE TRIGGER {$trigger} BEFORE {$operation} ON {$table} FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Financial record is immutable'");
                }
            }
        }
        DB::transaction(function () {
            foreach (DB::table('royalty_ledgers')->whereNotNull('payout_request_id')->get() as $ledger) {
                DB::table('payout_allocations')->insert([
                    'payout_request_id' => $ledger->payout_request_id,
                    'royalty_ledger_id' => $ledger->id,
                    'amount' => $ledger->author_earning,
                    'created_at' => $ledger->created_at,
                    'updated_at' => $ledger->updated_at,
                ]);
            }
        });
    }

    public function down(): void
    {
        // New allocations cannot be represented by the old one-ledger/one-payout
        // schema. Refuse a destructive downgrade once real new writes exist.
        if (DB::table('payout_requests')->whereNotNull('idempotency_key')->exists() || DB::table('payout_audits')->exists()) {
            throw new RuntimeException('Payout writes exist; use an approved backup/forward-fix plan instead of dropping financial allocations.');
        }
        foreach (['royalty', 'allocation', 'audit'] as $prefix) {
            foreach (['update', 'delete'] as $operation) {
                DB::statement('DROP TRIGGER IF EXISTS '.$prefix.'_immutable_'.$operation);
            }
        }
        Schema::dropIfExists('payout_audits');
        Schema::dropIfExists('payout_allocations');
        Schema::table('payout_requests', function (Blueprint $table) {
            $table->dropUnique('payout_author_idempotency_unique');
            $table->dropColumn('idempotency_key');
        });
    }
};
