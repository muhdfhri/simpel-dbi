<?php

namespace Database\Seeders;

use App\Models\DesaBinaan;
use App\Models\Upt;
use App\Models\WilayahAdministratif;
use Illuminate\Database\Seeder;

class WilayahAndUptSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Provinsi Sumatera Utara
        $sumut = WilayahAdministratif::create([
            'nama' => 'SUMATERA UTARA',
            'level' => 'provinsi',
            'kode_kemendagri' => '12',
        ]);

        // 2. Kota Medan
        $medan = WilayahAdministratif::create([
            'parent_id' => $sumut->id,
            'nama' => 'KOTA MEDAN',
            'level' => 'kabupaten_kota',
            'kode_kemendagri' => '12.71',
        ]);

        // 3. Kecamatan Medan Tuntungan
        $tuntungan = WilayahAdministratif::create([
            'parent_id' => $medan->id,
            'nama' => 'MEDAN TUNTUNGAN',
            'level' => 'kecamatan',
            'kode_kemendagri' => '12.71.01',
        ]);

        // 4. Desa/Kelurahan Mangga
        $desaSampel = WilayahAdministratif::create([
            'parent_id' => $tuntungan->id,
            'nama' => 'KELURAHAN MANGGA',
            'level' => 'desa',
            'kode_kemendagri' => '12.71.01.1001',
        ]);

        // 5. UPT Kanim Kelas I TPI Medan
        $kanimMedan = Upt::create([
            'nama' => 'Kanim Kelas I TPI Medan',
            'tipe' => 'kantor_imigrasi',
            'wilayah_id' => $medan->id,
        ]);

        // 6. Desa Binaan Sampel
        DesaBinaan::create([
            'nama' => 'Desa Binaan Mangga Tuntungan',
            'wilayah_id' => $desaSampel->id,
            'upt_id' => $kanimMedan->id,
            'lat' => 3.5241000,
            'lng' => 98.6214000,
            'status_terkini' => 'aman',
        ]);
    }
}
