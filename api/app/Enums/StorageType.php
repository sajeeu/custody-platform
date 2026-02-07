<?php

namespace App\Enums;

final class StorageType
{
    public const UNALLOCATED = 'UNALLOCATED';
    public const ALLOCATED = 'ALLOCATED'; // used later

    public static function all(): array
    {
        return [self::UNALLOCATED, self::ALLOCATED];
    }
}
