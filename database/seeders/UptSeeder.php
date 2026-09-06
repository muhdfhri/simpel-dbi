<?php

namespace Database\Seeders;

use App\Models\Upt;
use App\Models\WilayahAdministratif;
use Illuminate\Database\Seeder;

class UptSeeder extends Seeder
{
    public function run(): void
    {
        // Wilayah Default
        $wilayahDefault = WilayahAdministratif::firstOrCreate(
            ['kode_kemendagri' => '12.01.01.2001'],
            ['nama' => 'Pantai Labu Pekan', 'level' => 'desa']
        );

        // 10 Satker Kantor Imigrasi (Wilayah 1 - 10)
        $uptList = [
            1 => ['nama' => 'Kantor Imigrasi Kelas I Khusus TPI Medan', 'tipe' => 'Kelas I Khusus TPI'],
            2 => ['nama' => 'Kantor Imigrasi Kelas I TPI Polonia', 'tipe' => 'Kelas I TPI'],
            3 => ['nama' => 'Kantor Imigrasi Kelas II TPI Belawan', 'tipe' => 'Kelas II TPI'],
            4 => ['nama' => 'Kantor Imigrasi Kelas II TPI Pematang Siantar', 'tipe' => 'Kelas II TPI'],
            5 => ['nama' => 'Kantor Imigrasi Kelas II TPI Tanjung Balai Asahan', 'tipe' => 'Kelas II TPI'],
            6 => ['nama' => 'Kantor Imigrasi Kelas II TPI Sibolga', 'tipe' => 'Kelas II TPI'],
            7 => ['nama' => 'Kantor Imigrasi Kelas III Non TPI Mandailing Natal', 'tipe' => 'Kelas III Non TPI'],
            8 => ['nama' => 'Kantor Imigrasi Kelas III TPI Nias', 'tipe' => 'Kelas III TPI'],
            9 => ['nama' => 'Kantor Imigrasi Kelas III Non TPI Tapanuli Utara', 'tipe' => 'Kelas III Non TPI'],
            10 => ['nama' => 'Kantor Imigrasi Kelas III Non TPI Padangsidimpuan', 'tipe' => 'Kelas III Non TPI'],
        ];

        foreach ($uptList as $id => $u) {
            Upt::firstOrCreate(
                ['id' => $id],
                [
                    'nama' => $u['nama'],
                    'tipe' => $u['tipe'],
                    'wilayah_id' => $wilayahDefault->id,
                ]
            );
        }
    }
}
