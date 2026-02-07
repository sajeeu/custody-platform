<?php

namespace App\Enums;

final class WithdrawalStatus
{
    public const PENDING = 'PENDING';
    public const APPROVED = 'APPROVED';
    public const COMPLETED = 'COMPLETED';
    public const REJECTED = 'REJECTED';

    public static function all(): array
    {
        return [self::PENDING, self::APPROVED, self::COMPLETED, self::REJECTED];
    }
}
