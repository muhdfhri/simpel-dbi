<?php

namespace Database\Seeders;

use App\Models\DesaBinaan;
use App\Models\KegiatanPembinaan;
use App\Models\User;
use Illuminate\Database\Seeder;

class KegiatanPembinaanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pimpasaUser = User::role('PIMPASA')->first() ?? User::where('email', 'like', '%pimpasa%')->first();
        $desaList = DesaBinaan::all();

        if (!$pimpasaUser || $desaList->isEmpty()) {
            return;
        }

        $desa1 = $desaList->first();
        $desa2 = $desaList->skip(1)->first() ?? $desa1;
        $desa3 = $desaList->skip(2)->first() ?? $desa1;

        $items = [
            [
                'pimpasa_id' => $pimpasaUser->id,
                'desa_id' => $desa1->id,
                'judul' => 'Sosialisasi Pencegahan TPPO & Paspor Resmi bagi Pemuda Desa',
                'jenis_pembinaan' => 'Penyuluhan Hukum',
                'tanggal' => now()->subDays(3)->format('Y-m-d'),
                'jumlah_peserta' => 45,
                'status' => 'selesai',
                'lokasi' => 'Balai Desa ' . $desa1->nama,
                'ringkasan_materi' => 'Penyampaian materi bahaya Pekerja Migran Indonesia (PMI) Non-Prosedural, modus kejahatan TPPO, serta prosedur pembuatan paspor yang sah.',
            ],
            [
                'pimpasa_id' => $pimpasaUser->id,
                'desa_id' => $desa2->id,
                'judul' => 'Layanan Paspor Masuk Desa (Paspor Simpatik)',
                'jenis_pembinaan' => 'Layanan Simpatik',
                'tanggal' => now()->addDays(4)->format('Y-m-d'),
                'jumlah_peserta' => 80,
                'status' => 'terjadwal',
                'lokasi' => 'Aula Kecamatan / Posko Imigrasi Desa ' . $desa2->nama,
                'ringkasan_materi' => 'Pelaksanaan program jemput bola pengurusan perpanjangan dan penerbitan paspor simpatik warga desa binaan imigrasi.',
            ],
            [
                'pimpasa_id' => $pimpasaUser->id,
                'desa_id' => $desa3->id,
                'judul' => 'Pembinaan Karang Taruna & Pelaporan Keberadaan WNA',
                'jenis_pembinaan' => 'Pembinaan Pemuda',
                'tanggal' => now()->subDays(10)->format('Y-m-d'),
                'jumlah_peserta' => 30,
                'status' => 'selesai',
                'lokasi' => 'Gedung Pemuda Desa ' . $desa3->nama,
                'ringkasan_materi' => 'Edukasi dan pelatihan bagi pemuda desa dalam memanfaatkan platform SIMPEL DESI untuk pelaporan aktivitas atau keberadaan WNA di wilayah desa.',
            ],
        ];

        foreach ($items as $item) {
            KegiatanPembinaan::updateOrCreate(
                [
                    'pimpasa_id' => $item['pimpasa_id'],
                    'judul' => $item['judul'],
                ],
                $item
            );
        }
    }
}
