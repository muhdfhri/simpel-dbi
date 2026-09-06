<?php

namespace App\Enums;

enum StatusLaporan: string
{
    case DIAJUKAN = 'diajukan';
    case MINTA_PERBAIKAN = 'minta_perbaikan';
    case DIVERIFIKASI = 'diverifikasi';
    case DITINDAKLANJUTI = 'ditindaklanjuti';
    case SELESAI = 'selesai';
    case DITOLAK = 'ditolak';

    public function label(): string
    {
        return match ($this) {
            self::DIAJUKAN => 'Diajukan',
            self::MINTA_PERBAIKAN => 'Minta Perbaikan',
            self::DIVERIFIKASI => 'Diverifikasi',
            self::DITINDAKLANJUTI => 'Ditindaklanjuti PIMPASA',
            self::SELESAI => 'Selesai',
            self::DITOLAK => 'Ditolak',
        };
    }
}
