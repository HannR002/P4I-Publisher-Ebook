<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/** Transitional read compatibility; all new writes are encrypted. */
class EncryptedFinancialString implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        return str_starts_with($value, 'enc:v1:') ? Crypt::decryptString(substr($value, 7)) : $value;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $value === null ? null : 'enc:v1:'.Crypt::encryptString((string) $value);
    }
}
