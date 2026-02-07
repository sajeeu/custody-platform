<?php

namespace App\Enums;

final class BarStatus
{
    public const AVAILABLE = 'AVAILABLE';
    public const RESERVED = 'RESERVED';
    public const WITHDRAWN = 'WITHDRAWN';

    public static function all(): array
    {
        return [self::AVAILABLE, self::RESERVED, self::WITHDRAWN];
    }
}
