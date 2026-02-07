<?php

namespace App\Enums;

final class DepositStatus
{
    public const PENDING = 'PENDING';
    public const COMPLETED = 'COMPLETED';
    public const REJECTED = 'REJECTED';

    public static function all(): array
    {
        return [self::PENDING, self::COMPLETED, self::REJECTED];
    }
}
