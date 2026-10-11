<?php

namespace App\Console\Commands;

use App\Services\OrderPaidOutbox;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DispatchPaidOrderEvents extends Command
{
    protected $signature = 'payments:dispatch-events';

    protected $description = 'Retry durable paid-order event publication (does not contact the payment gateway).';

    public function handle(OrderPaidOutbox $outbox): int
    {
        $failed = false;
        DB::table('order_paid_outbox')->whereNull('dispatched_at')->orderBy('order_id')->chunkById(100, function ($rows) use ($outbox, &$failed) {
            foreach ($rows as $row) {
                if (! $outbox->dispatch($row->order_id)) {
                    $failed = true;
                }
            }
        }, 'order_id');

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
