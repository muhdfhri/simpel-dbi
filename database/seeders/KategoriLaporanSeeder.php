<?php

namespace Database\Seeders;

use App\Models\KategoriLaporan;
use Illuminate\Database\Seeder;

class KategoriLaporanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'kode' => 'kegiatan_dbi',
                'nama_kategori' => 'Kegiatan Desa Binaan Imigrasi',
                'deskripsi' => 'Laporan terkait pelaksanaan program sosialisasi, pembinaan, dan literasi keimigrasian di desa.',
                'is_active' => true,
            ],
            [
                'kode' => 'wna',
                'nama_kategori' => 'Laporan Terkait WNA',
                'deskripsi' => 'Laporan keberadaan, aktivitas, atau dugaan pelanggaran izin tinggal Warga Negara Asing.',
                'is_active' => true,
            ],
            [
                'kode' => 'indikasi_tppo_pmi',
                'nama_kategori' => 'Indikasi TPPO / PMI Non-Prosedural',
                'deskripsi' => 'Laporan indikasi Tindak Pidana Perdagangan Orang atau pengerahan Pekerja Migran Ilegal.',
                'is_active' => true,
            ],
            [
                'kode' => 'insidentil',
                'nama_kategori' => 'Kejadian Insidentil',
                'deskripsi' => 'Laporan kejadian darurat atau pengaduan khusus keimigrasian lainnya di lapangan.',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $cat) {
            KategoriLaporan::updateOrCreate(
                ['kode' => $cat['kode']],
                $cat
            );
        }
    }
}
