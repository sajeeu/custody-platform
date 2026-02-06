<?php

namespace App\Enums;

final class UserRole
{
    public const ADMIN = 'ADMIN';
    public const RETAIL = 'RETAIL';
    public const INSTITUTIONAL = 'INSTITUTIONAL';

    public static function all(): array
    {
        return [self::ADMIN, self::RETAIL, self::INSTITUTIONAL];
    }
}
