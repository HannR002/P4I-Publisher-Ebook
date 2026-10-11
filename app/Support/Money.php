<?php

namespace App\Support;

use DomainException;

/** Fixed-point amounts for the existing DECIMAL(12,2) IDR schema. */
final class Money
{
    public static function minor(int|float|string $amount): int
    {
        $value = (string) $amount;
        if (! preg_match('/\A(0|[1-9][0-9]{0,9})(?:\.([0-9]{1,2}))?\z/', $value, $matches)) {
            throw new DomainException('Nominal harus positif, maksimal dua desimal dan sesuai batas database.');
        }

        return ((int) $matches[1] * 100) + (int) str_pad($matches[2] ?? '', 2, '0');
    }

    public static function decimal(int $minor): string
    {
        return ($minor < 0 ? '-' : '').intdiv(abs($minor), 100).'.'.str_pad((string) (abs($minor) % 100), 2, '0', STR_PAD_LEFT);
    }
}
