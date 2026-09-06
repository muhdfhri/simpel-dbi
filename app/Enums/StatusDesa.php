<?php

namespace App\Enums;

enum StatusDesa: string
{
    case AMAN = 'aman';
    case PERLU_PEMBINAAN = 'perlu_pembinaan';
    case ADA_ADUAN = 'ada_aduan';

    public function label(): string
    {
        return match ($this) {
            self::AMAN => 'Aman / Kondusif',
            self::PERLU_PEMBINAAN => 'Perlu Pembinaan',
            self::ADA_ADUAN => 'Ada Aduan Aktif',
        };
    }
}
