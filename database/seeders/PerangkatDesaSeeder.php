<?php

namespace Database\Seeders;

use App\Enums\KategoriLaporan;
use App\Enums\StatusLaporan;
use App\Models\DesaBinaan;
use App\Models\Laporan;
use App\Models\LaporanStatusHistory;
use App\Models\User;
use Illuminate\Database\Seeder;

class PerangkatDesaSeeder extends Seeder
{
    public function run(): void
    {
        $userDesa = User::where('email', 'desa@simpeldbi.go.id')->first();
        $userPimpasa = User::where('email', 'pimpasa@simpeldbi.go.id')->first();
        $desa = DesaBinaan::first();

        if (! $userDesa || ! $desa) {
            return;
        }

        // Data 5 Sample Laporan Perangkat Desa
        $sampleLaporan = [
            [
                'kode_tiket' => 'LP-2026-000001',
                'judul' => 'Pengawasan Terhadap 3 WNA Asing Tanpa Paspor di Dusun Mangga I',
                'kategori' => KategoriLaporan::WNA->value,
                'status' => StatusLaporan::DIAJUKAN->value,
                'tanggal_kejadian' => now()->subDays(1)->format('Y-m-d H:i:s'),
                'lokasi_detail' => 'Dusun I, RT 02/RW 01, Desa Mangga Besar',
                'kronologi' => 'Masyarakat melaporkan adanya 3 orang warga negara asing yang tinggal di rumah sewa warga tanpa melapor ke perangkat RT setempat selama lebih dari 3 hari.',
                'estimasi_jumlah_orang' => 3,
                'submitted_at' => now()->subDays(1),
            ],
            [
                'kode_tiket' => 'LP-2026-000002',
                'judul' => 'Dugaan Penampungan Pekerja Migran Non-Prosedural di Rumah Sewa RT 02',
                'kategori' => KategoriLaporan::INDIKASI_TPPO_PMI->value,
                'status' => StatusLaporan::MINTA_PERBAIKAN->value,
                'tanggal_kejadian' => now()->subDays(3)->format('Y-m-d H:i:s'),
                'lokasi_detail' => 'Perumahan Asri Blok C No. 12, RT 02/RW 04',
                'kronologi' => 'Terlihat adanya aktivitas pengumpulan pemudi asal luar desa yang dijanjikan bekerja ke luar negeri tanpa dokumen resmi keimigrasian.',
                'estimasi_jumlah_orang' => 8,
                'submitted_at' => now()->subDays(3),
            ],
            [
                'kode_tiket' => 'LP-2026-000003',
                'judul' => 'Permohonan Sosialisasi Bahaya TPPO bagi Pemuda & Karang Taruna Desa',
                'kategori' => KategoriLaporan::KEGIATAN_DBI->value,
                'status' => StatusLaporan::DIVERIFIKASI->value,
                'tanggal_kejadian' => now()->subDays(5)->format('Y-m-d H:i:s'),
                'lokasi_detail' => 'Aula Kantor Desa Mangga Besar',
                'kronologi' => 'Permohonan dari Perangkat Desa agar Petugas PIMPASA memberikan penyuluhan pencegahan TPPO dan pendaftaran paspor resmi bagi warga desa.',
                'estimasi_jumlah_orang' => 50,
                'submitted_at' => now()->subDays(5),
            ],
            [
                'kode_tiket' => 'LP-2026-000004',
                'judul' => 'Temuan Dokumen Paspor Palsu Milik Calon PMI di Dusun III',
                'kategori' => KategoriLaporan::INDIKASI_TPPO_PMI->value,
                'status' => StatusLaporan::DITINDAKLANJUTI->value,
                'tanggal_kejadian' => now()->subDays(7)->format('Y-m-d H:i:s'),
                'lokasi_detail' => 'Dusun III, RT 05/RW 02',
                'kronologi' => 'Warga mendatangi kantor desa membawa berkas permohonan paspor yang terindikasi menggunakan stempel palsu dari oknum calo.',
                'estimasi_jumlah_orang' => 2,
                'submitted_at' => now()->subDays(7),
            ],
            [
                'kode_tiket' => 'LP-2026-000005',
                'judul' => 'Inspeksi Lapangan Bersama Tim PIMPASA Terhadap Perusahaan Penyalur Tenaga Kerja',
                'kategori' => KategoriLaporan::WNA->value,
                'status' => StatusLaporan::SELESAI->value,
                'tanggal_kejadian' => now()->subDays(10)->format('Y-m-d H:i:s'),
                'lokasi_detail' => 'Kawasan Industri Desa, RT 01/RW 01',
                'kronologi' => 'Pemeriksaan rutin kelengkapan izin tinggal terbatas (ITAS) tenaga kerja asing yang bekerja di pabrik pengolahan karet.',
                'estimasi_jumlah_orang' => 12,
                'submitted_at' => now()->subDays(10),
            ],
        ];

        $kategoriMap = \App\Models\KategoriLaporan::pluck('id', 'kode')->toArray();

        foreach ($sampleLaporan as $data) {
            $kategoriKode = $data['kategori'];
            unset($data['kategori']);
            $data['kategori_id'] = $kategoriMap[$kategoriKode] ?? null;

            $laporan = Laporan::updateOrCreate(
                ['kode_tiket' => $data['kode_tiket']],
                array_merge($data, ['desa_id' => $desa->id])
            );

            // Audit Trail Status History Initial
            LaporanStatusHistory::firstOrCreate(
                [
                    'laporan_id' => $laporan->id,
                    'status_ke' => StatusLaporan::DIAJUKAN->value,
                ],
                [
                    'status_dari' => null,
                    'actor_id' => $userDesa->id,
                    'catatan' => 'Laporan diajukan oleh Perangkat Desa.',
                    'created_at' => $data['submitted_at'],
                ]
            );

            // Audit Trail lanjutan jika status bukan DIAJUKAN
            if ($data['status'] === StatusLaporan::MINTA_PERBAIKAN->value) {
                LaporanStatusHistory::firstOrCreate(
                    [
                        'laporan_id' => $laporan->id,
                        'status_ke' => StatusLaporan::MINTA_PERBAIKAN->value,
                    ],
                    [
                        'status_dari' => StatusLaporan::DIAJUKAN->value,
                        'actor_id' => $userPimpasa?->id ?? $userDesa->id,
                        'catatan' => 'Mohon lengkapi foto lokasi penampungan dan bukti percakapan.',
                        'created_at' => now()->subDays(2),
                    ]
                );

                $laporan->verifikasi()->updateOrCreate(
                    ['laporan_id' => $laporan->id],
                    [
                        'pimpasa_id' => $userPimpasa?->id ?? $userDesa->id,
                        'checklist_validitas' => [
                            'kelengkapan_identitas' => true,
                            'kesesuaian_lokasi' => false,
                            'indikasi_awal_valid' => true,
                        ],
                        'keputusan' => 'minta_perbaikan',
                        'catatan' => 'Mohon lengkapi foto lokasi penampungan dan bukti percakapan.',
                        'created_at' => now()->subDays(2),
                    ]
                );
            } elseif ($data['status'] === StatusLaporan::SELESAI->value) {
                LaporanStatusHistory::firstOrCreate(
                    [
                        'laporan_id' => $laporan->id,
                        'status_ke' => StatusLaporan::SELESAI->value,
                    ],
                    [
                        'status_dari' => StatusLaporan::DITINDAKLANJUTI->value,
                        'actor_id' => $userPimpasa?->id ?? $userDesa->id,
                        'catatan' => 'Pemeriksaan izin tinggal selesai. Seluruh WNA memiliki paspor & ITAS sah.',
                        'created_at' => now()->subDays(8),
                    ]
                );
            }
        }
    }
}
