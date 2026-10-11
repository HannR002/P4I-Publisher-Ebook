<?php

namespace App\Console\Commands;

use App\Models\Author;
use App\Models\PayoutRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EncryptLegacyFinancialData extends Command
{
    protected $signature = 'security:encrypt-financial-data {--approved-production : Only after owner approval and verified backups}';

    protected $description = 'Encrypt legacy bank columns without printing personal data (requires migrated schema).';

    public function handle(): int
    {
        if (app()->isProduction() && ! $this->option('approved-production')) {
            $this->error('Production writes require explicit approval, backups, and --approved-production.');

            return self::FAILURE;
        }
        $count = 0;
        foreach ([Author::class => ['bank_name', 'bank_account', 'bank_holder_name'], PayoutRequest::class => ['bank_name_snapshot', 'bank_account_snapshot', 'bank_holder_name_snapshot']] as $class => $fields) {
            $class::query()->select('id')->chunkById(100, function ($rows) use ($class, $fields, &$count) {
                foreach ($rows as $row) {
                    DB::transaction(function () use ($class, $row, $fields, &$count) {
                        $model = $class::whereKey($row->id)->lockForUpdate()->firstOrFail();
                        $changed = false;
                        foreach ($fields as $field) {
                            $raw = $model->getRawOriginal($field);
                            if ($raw !== null && ! str_starts_with($raw, 'enc:v1:')) {
                                $model->$field = $raw;
                                $changed = true;
                            }
                        }
                        if ($changed) {
                            $model->save();
                            $count++;
                        }
                    });
                }
            });
        }
        $this->info('Records encrypted: '.$count);

        return self::SUCCESS;
    }
}
