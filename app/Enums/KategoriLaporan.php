<?php

namespace App\Enums;

enum KategoriLaporan: string
{
    case KEGIATAN_DBI = 'kegiatan_dbi';
    case WNA = 'wna';
    case INDIKASI_TPPO_PMI = 'indikasi_tppo_pmi';
    case INSIDENTIL = 'insidentil';

    public function label(): string
    {
        return match ($this) {
            self::KEGIATAN_DBI => 'Kegiatan Desa Binaan Imigrasi',
            self::WNA => 'Laporan Terkait WNA',
            self::INDIKASI_TPPO_PMI => 'Indikasi TPPO / PMI Non-Prosedural',
            self::INSIDENTIL => 'Kejadian Insidentil',
        };
    }
}
