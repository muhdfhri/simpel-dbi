<?php

namespace Database\Seeders;

use App\Models\DesaBinaan;
use App\Models\Upt;
use App\Models\WilayahAdministratif;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class WilayahAdministratifFromMdSeeder extends Seeder
{
    public function run(): void
    {
        $mdPath = base_path('docs/daftar_desa_binaan_imigrasi_sumatera_utara.md');

        if (!File::exists($mdPath)) {
            $this->command->error("File docs/daftar_desa_binaan_imigrasi_sumatera_utara.md tidak ditemukan!");
            return;
        }

        $lines = file($mdPath, FILE_IGNORE_NEW_LINES);

        // 1. Root Provinsi Sumatera Utara
        $provinsi = WilayahAdministratif::firstOrCreate(
            ['kode_kemendagri' => '12'],
            [
                'nama' => 'SUMATERA UTARA',
                'level' => 'provinsi',
                'parent_id' => null,
            ]
        );

        $kabupatenCache = [];
        $desaCount = 0;

        foreach ($lines as $line) {
            $trimmedLine = trim($line);

            if (!str_starts_with($trimmedLine, '|')) {
                continue;
            }

            // Split kolom berdasarkan karakter pipe '|'
            $rawCols = explode('|', $line);
            $cols = array_values(array_filter(array_map('trim', $rawCols), fn($v, $k) => $k > 0 && $k < count($rawCols) - 1, ARRAY_FILTER_USE_BOTH));

            if (count($cols) < 4) {
                continue;
            }

            // Abaikan header tabel & pembatas
            if ($cols[0] === 'No' || str_starts_with($cols[0], '---') || str_starts_with($cols[0], ':---')) {
                continue;
            }

            $uptNama = trim($cols[1]);
            $desaNama = trim($cols[2]);
            $kabupatenNamaRaw = trim($cols[3]);

            if (empty($desaNama) || empty($kabupatenNamaRaw)) {
                continue;
            }

            // Clean & Normalisasi Nama Kabupaten/Kota
            $cleanKab = strtoupper(trim($kabupatenNamaRaw));
            if (!str_starts_with($cleanKab, 'KABUPATEN') && !str_starts_with($cleanKab, 'KOTA')) {
                $kabupatenNama = 'KABUPATEN ' . $cleanKab;
            } else {
                $kabupatenNama = $cleanKab;
            }

            // Clean Nama Desa untuk pencarian di tabel desa_binaan
            $cleanDesaNama = preg_replace('/^(Desa|Kelurahan|Kecamatan)\s+/i', '', $desaNama);
            $cleanDesaNama = trim($cleanDesaNama);

            // A. Create / Get Kabupaten/Kota Level
            if (!isset($kabupatenCache[$kabupatenNama])) {
                $kabKode = '12.' . str_pad((string) (count($kabupatenCache) + 1), 2, '0', STR_PAD_LEFT);
                $kabRecord = WilayahAdministratif::firstOrCreate(
                    [
                        'nama' => $kabupatenNama,
                        'level' => 'kabupaten_kota',
                    ],
                    [
                        'kode_kemendagri' => $kabKode,
                        'parent_id' => $provinsi->id,
                    ]
                );
                $kabupatenCache[$kabupatenNama] = $kabRecord;
            }
            $kabRecord = $kabupatenCache[$kabupatenNama];

            // Update UPT wilayah_id to point to Kab/Kota
            $uptRecord = Upt::where('nama', 'like', "%{$uptNama}%")->first();
            if ($uptRecord) {
                $uptRecord->update(['wilayah_id' => $kabRecord->id]);
            }

            // B. Create Desa / Kelurahan Level langsung di bawah Kabupaten/Kota
            $desaCount++;
            $desaKode = $kabRecord->kode_kemendagri . '.' . str_pad((string) $desaCount, 4, '0', STR_PAD_LEFT);

            $wilayahDesa = WilayahAdministratif::firstOrCreate(
                [
                    'nama' => strtoupper($desaNama),
                    'level' => 'desa',
                    'parent_id' => $kabRecord->id,
                ],
                [
                    'kode_kemendagri' => $desaKode,
                ]
            );

            // C. Hubungkan wilayah_id ke tabel desa_binaan
            $desaBinaan = DesaBinaan::where('nama', 'like', "%{$cleanDesaNama}%")
                ->orWhere('nama', 'like', "%{$desaNama}%")
                ->first();

            if ($desaBinaan) {
                $desaBinaan->update([
                    'wilayah_id' => $wilayahDesa->id,
                ]);
            }
        }
    }
}
