<?php

namespace Database\Seeders;

use App\Notifications\LaporanNotification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $userDesa = User::where('email', 'desa@simpeldbi.go.id')->first();
        $userPimpasa = User::where('email', 'pimpasa@simpeldbi.go.id')->first();

        // 1. Sampel Notifikasi untuk Perangkat Desa
        if ($userDesa) {
            $userDesa->notify(new LaporanNotification(
                title: 'Perbaikan Data Diminta',
                message: 'Petugas PIMPASA meminta perbaikan dokumen lokasi pada tiket LP-2026-000002.',
                type: 'warning',
                url: '/desa/laporan/2/edit',
                laporanId: 2,
                kodeTiket: 'LP-2026-000002'
            ));

            $userDesa->notify(new LaporanNotification(
                title: 'Laporan Diverifikasi PIMPASA',
                message: 'Laporan LP-2026-000003 tentang sosialisasi TPPO telah diverifikasi dan diteruskan ke Tim UPT.',
                type: 'success',
                url: '/desa/laporan/3',
                laporanId: 3,
                kodeTiket: 'LP-2026-000003'
            ));
        }

        // 2. Sampel Notifikasi untuk Petugas PIMPASA
        if ($userPimpasa) {
            $userPimpasa->notify(new LaporanNotification(
                title: 'Pengajuan Laporan Baru',
                message: 'Desa Mangga Besar mengajukan laporan baru: LP-2026-000001.',
                type: 'info',
                url: '/pimpasa/verifikasi/1',
                laporanId: 1,
                kodeTiket: 'LP-2026-000001'
            ));

            $userPimpasa->notify(new LaporanNotification(
                title: 'Peringatan SLA Verifikasi',
                message: 'Tiket LP-2026-000001 tersisa 6 jam sebelum melampaui SLA 48 jam.',
                type: 'error',
                url: '/pimpasa/verifikasi/1',
                laporanId: 1,
                kodeTiket: 'LP-2026-000001'
            ));
        }
    }
}
