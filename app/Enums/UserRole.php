<?php

namespace App\Enums;

enum UserRole: string
{
    case DESA = 'desa';
    case PIMPASA = 'pimpasa';
    case KANWIL = 'kanwil';

    public function label(): string
    {
        return match ($this) {
            self::DESA => 'Perangkat Desa',
            self::PIMPASA => 'Petugas PIMPASA / UPT',
            self::KANWIL => 'Admin / Pimpinan Kanwil',
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::DESA => 'secondary',
            self::PIMPASA => 'default',
            self::KANWIL => 'destructive',
        };
    }

    public function isInternal(): bool
    {
        return match ($this) {
            self::DESA => false,
            self::PIMPASA, self::KANWIL => true,
        };
    }
}
