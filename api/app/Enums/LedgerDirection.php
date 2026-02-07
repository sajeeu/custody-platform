<?php

namespace App\Enums;

final class LedgerDirection
{
    public const CREDIT = 'CREDIT';
    public const DEBIT = 'DEBIT';

    public static function all(): array
    {
        return [self::CREDIT, self::DEBIT];
    }
}
