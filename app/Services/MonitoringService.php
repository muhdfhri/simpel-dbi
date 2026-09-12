<?php

namespace App\Services;

use App\Models\DesaBinaan;
use App\Models\Laporan;
use App\Models\Upt;

class MonitoringService
{
    /**
     * Dapatkan Executive Top KPI Metrics
     */
    public function getKpiMetrics(): array
    {
        $totalDesa = DesaBinaan::count();
        $totalLaporan = Laporan::count();
        $laporanSelesai = Laporan::where('status', 'selesai')->count();
        $laporanProses = Laporan::whereIn('status', ['diajukan', 'minta_perbaikan', 'diverifikasi', 'ditindaklanjuti'])->count();

        $slaResolutionRate = $totalLaporan > 0 ? round(($laporanSelesai / $totalLaporan) * 100, 1) : 0;

        // Count SLA Breached Laporan (> 24 jam tanpa status selesai atau red_flag = true)
        $slaBreachedCount = Laporan::whereIn('status', ['diajukan', 'minta_perbaikan', 'diverifikasi', 'ditindaklanjuti'])
            ->where(function ($q) {
                $q->where('created_at', '<', now()->subHours(24))
                  ->orWhere('red_flag', true);
            })
            ->count();

        $totalKegiatan = \App\Models\KegiatanPembinaan::count();
        $totalPeserta = \App\Models\KegiatanPembinaan::sum('jumlah_peserta');

        return [
            'total_desa' => $totalDesa,
            'total_laporan' => $totalLaporan,
            'laporan_proses' => $laporanProses,
            'laporan_selesai' => $laporanSelesai,
            'resolution_rate' => $slaResolutionRate,
            'sla_breached_count' => $slaBreachedCount,
            'total_kegiatan_pembinaan' => $totalKegiatan,
            'total_peserta_pembinaan' => $totalPeserta,
        ];
    }

    /**
     * Dapatkan UPT Compliance & Performance Scorecard Data (6 Satker UPT se-Sumut)
     */
    public function getUptScorecards(?string $tanggalMulai = null, ?string $tanggalSelesai = null): array
    {
        $dateFilter = function ($q) use ($tanggalMulai, $tanggalSelesai) {
            if ($tanggalMulai) {
                $q->whereDate('created_at', '>=', $tanggalMulai);
            }
            if ($tanggalSelesai) {
                $q->whereDate('created_at', '<=', $tanggalSelesai);
            }
        };

        return Upt::withCount(['desaBinaanList', 'users'])
            ->get()
            ->map(function ($upt) use ($dateFilter) {
                $desaIds = DesaBinaan::where('upt_id', $upt->id)->pluck('id');
                $totalUptLaporan = Laporan::whereIn('desa_id', $desaIds)->tap($dateFilter)->count();
                $selesaiUptLaporan = Laporan::whereIn('desa_id', $desaIds)->where('status', 'selesai')->tap($dateFilter)->count();
                
                $breachedCount = Laporan::whereIn('desa_id', $desaIds)
                    ->whereIn('status', ['diajukan', 'minta_perbaikan', 'diverifikasi', 'ditindaklanjuti'])
                    ->where(function ($q) {
                        $q->where('created_at', '<', now()->subHours(24))
                          ->orWhere('red_flag', true);
                    })
                    ->tap($dateFilter)
                    ->count();

                // Hitung Rata-rata jam penyelesaian (SLA) dari created_at s/d resolved_at
                $selesaiLaporans = Laporan::whereIn('desa_id', $desaIds)
                    ->where('status', 'selesai')
                    ->whereNotNull('resolved_at')
                    ->tap($dateFilter)
                    ->get();

                if ($selesaiLaporans->count() > 0) {
                    $totalHours = $selesaiLaporans->sum(function ($lap) {
                        return abs($lap->created_at->diffInHours($lap->resolved_at));
                    });
                    $avgSlaHours = (int) round($totalHours / $selesaiLaporans->count());
                } else {
                    $avgSlaHours = 0;
                }

                // Logic Presisi Status Kepatuhan
                if ($totalUptLaporan === 0) {
                    $rate = 0;
                    $statusKepatuhan = 'SANGAT BAIK';
                } else {
                    $rate = round(($selesaiUptLaporan / $totalUptLaporan) * 100, 1);

                    if ($breachedCount > 2 || $rate < 70) {
                        $statusKepatuhan = 'PERLU EVALUASI';
                    } elseif ($breachedCount > 0 || $rate < 85) {
                        $statusKepatuhan = 'CUKUP';
                    } else {
                        $statusKepatuhan = 'SANGAT BAIK';
                    }
                }

                // Ambil daftar desa binaan beserta petugas pimpasa & statistik tiket dari database
                $desaList = DesaBinaan::where('upt_id', $upt->id)
                    ->get()
                    ->map(function ($desa) {
                        $pimpasaOfficer = \App\Models\User::where('upt_id', $desa->upt_id)
                            ->where('role', 'pimpasa')
                            ->first();

                        $laporanSelesaiDesa = Laporan::where('desa_id', $desa->id)
                            ->where('status', 'selesai')
                            ->count();

                        return [
                            'id' => $desa->id,
                            'nama' => $desa->nama,
                            'pimpasa' => $pimpasaOfficer ? $pimpasaOfficer->name : 'Petugas PIMPASA UPT',
                            'status' => 'Aktif',
                            'laporan' => $laporanSelesaiDesa,
                        ];
                    })
                    ->toArray();

                return [
                    'id' => $upt->id,
                    'nama' => $upt->nama,
                    'tipe' => $upt->tipe,
                    'desa_count' => $upt->desa_binaan_list_count,
                    'pimpasa_count' => $upt->users_count,
                    'total_laporan' => $totalUptLaporan,
                    'laporan_selesai' => $selesaiUptLaporan,
                    'breached_count' => $breachedCount,
                    'completion_rate' => $rate,
                    'avg_sla_hours' => $avgSlaHours,
                    'status_kepatuhan' => $statusKepatuhan,
                    'desa_list' => $desaList,
                ];
            })
            ->toArray();
    }

    /**
     * Dapatkan Live SLA Incident Control Center Data
     */
    public function getSlaIncidents(?string $tanggalMulai = null, ?string $tanggalSelesai = null): array
    {
        $query = Laporan::with(['desa.upt', 'kategoriRef'])
            ->whereIn('status', ['diajukan', 'minta_perbaikan', 'diverifikasi', 'ditindaklanjuti']);

        if ($tanggalMulai) {
            $query->whereDate('created_at', '>=', $tanggalMulai);
        }
        if ($tanggalSelesai) {
            $query->whereDate('created_at', '<=', $tanggalSelesai);
        }

        return $query->orderBy('created_at', 'asc')
            ->limit(30)
            ->get()
            ->map(function ($lap) {
                $created = $lap->created_at ?? now();
                $hoursElapsed = (int) abs($created->diffInHours(now()));
                $hoursRemaining = max(0, 24 - $hoursElapsed);

                $slaStatus = 'tepat_waktu';
                if ($hoursElapsed >= 24 || $lap->red_flag) {
                    $slaStatus = 'terlambat';
                } elseif ($hoursElapsed >= 18) {
                    $slaStatus = 'peringatan';
                }

                $kategoriVal = $lap->kategoriRef?->nama_kategori ?? '-';
                $statusVal = is_object($lap->status) ? ($lap->status->value ?? (string)$lap->status) : (string)$lap->status;

                return [
                    'id' => $lap->id,
                    'nomor_tiket' => $lap->kode_tiket ?? ('TKT-' . str_pad($lap->id, 5, '0', STR_PAD_LEFT)),
                    'judul' => $lap->judul,
                    'kategori' => $kategoriVal,
                    'desa_nama' => $lap->desa->nama ?? 'Desa Binaan',
                    'upt_nama' => $lap->desa->upt->nama ?? 'Kanim Pembina',
                    'pelapor_nama' => 'Perangkat Desa',
                    'status' => $statusVal,
                    'created_at_formatted' => $lap->created_at ? $lap->created_at->format('d M Y, H:i') : '-',
                    'hours_elapsed' => $hoursElapsed,
                    'hours_remaining' => $hoursRemaining,
                    'sla_status' => $slaStatus,
                    'jumlah_teguran' => $lap->jumlah_teguran ?? 0,
                ];
            })
            ->toArray();
    }

    /**
     * Dapatkan Live Activity Feed Data
     */
    public function getActivityFeed(): array
    {
        return Laporan::with(['desa.upt', 'kategoriRef'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($lap) {
                $kategoriVal = $lap->kategoriRef?->nama_kategori ?? '-';
                $statusVal = is_object($lap->status) ? ($lap->status->value ?? (string)$lap->status) : (string)$lap->status;

                return [
                    'id' => $lap->id,
                    'nomor_tiket' => $lap->kode_tiket ?? ('TKT-' . str_pad($lap->id, 5, '0', STR_PAD_LEFT)),
                    'judul' => $lap->judul,
                    'kategori' => $kategoriVal,
                    'desa_nama' => $lap->desa->nama ?? 'Desa Binaan',
                    'upt_nama' => $lap->desa->upt->nama ?? 'Kanim Pembina',
                    'status' => $statusVal,
                    'updated_at_relative' => $lap->updated_at ? $lap->updated_at->diffForHumans() : '-',
                ];
            })
            ->toArray();
    }

    /**
     * Dapatkan Data Analytics Chart Eksekutif untuk ApexCharts (100% Real-Time DB Query)
     */
    public function getChartAnalyticsData(): array
    {
        // 1. Dynamic Trend Data by Database Query (1 Minggu, 1 Bulan, 1 Tahun)
        $now = now();

        // 1.A: 1 Minggu (7 Hari Terakhir)
        $weekCategories = [];
        $weekMasuk = [];
        $weekSelesai = [];
        $weekBreached = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $dayName = $date->locale('id')->isoFormat('dddd');
            $weekCategories[] = ucfirst($dayName);

            $masuk = Laporan::whereDate('created_at', $date->toDateString())->count();
            $selesai = Laporan::whereDate('created_at', $date->toDateString())->where('status', 'selesai')->count();
            $breached = Laporan::whereDate('created_at', $date->toDateString())
                ->where(function ($q) {
                    $q->where('created_at', '<', now()->subHours(24))->where('status', '!=', 'selesai')->orWhere('red_flag', true);
                })->count();

            $weekMasuk[] = $masuk;
            $weekSelesai[] = $selesai;
            $weekBreached[] = $breached;
        }

        // 1.B: 1 Bulan (4 Minggu Terakhir)
        $monthCategories = ['Mgg 1', 'Mgg 2', 'Mgg 3', 'Mgg 4'];
        $monthMasuk = [];
        $monthSelesai = [];
        $monthBreached = [];

        for ($w = 3; $w >= 0; $w--) {
            $startWeek = $now->copy()->subWeeks($w)->startOfWeek();
            $endWeek = $now->copy()->subWeeks($w)->endOfWeek();

            $masuk = Laporan::whereBetween('created_at', [$startWeek, $endWeek])->count();
            $selesai = Laporan::whereBetween('created_at', [$startWeek, $endWeek])->where('status', 'selesai')->count();
            $breached = Laporan::whereBetween('created_at', [$startWeek, $endWeek])
                ->where(function ($q) {
                    $q->where('created_at', '<', now()->subHours(24))->where('status', '!=', 'selesai')->orWhere('red_flag', true);
                })->count();

            $monthMasuk[] = $masuk;
            $monthSelesai[] = $selesai;
            $monthBreached[] = $breached;
        }

        // 1.C: 1 Tahun (12 Bulan dalam Tahun Ini)
        $yearCategories = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $yearMasuk = [];
        $yearSelesai = [];
        $yearBreached = [];

        $currentYear = $now->year;
        for ($m = 1; $m <= 12; $m++) {
            $masuk = Laporan::whereYear('created_at', $currentYear)->whereMonth('created_at', $m)->count();
            $selesai = Laporan::whereYear('created_at', $currentYear)->whereMonth('created_at', $m)->where('status', 'selesai')->count();
            $breached = Laporan::whereYear('created_at', $currentYear)->whereMonth('created_at', $m)
                ->where(function ($q) {
                    $q->where('created_at', '<', now()->subHours(24))->where('status', '!=', 'selesai')->orWhere('red_flag', true);
                })->count();

            $yearMasuk[] = $masuk;
            $yearSelesai[] = $selesai;
            $yearBreached[] = $breached;
        }

        // 2. Dynamic Sebaran Status Desa Binaan (Real-Time DB Query)
        $allDesaIds = DesaBinaan::pluck('id');
        $desaAduanCount = Laporan::whereIn('status', ['diajukan', 'minta_perbaikan'])
            ->pluck('desa_id')
            ->unique()
            ->count();

        $desaPembinaanCount = \App\Models\KegiatanPembinaan::pluck('desa_id')
            ->merge(Laporan::whereIn('status', ['diverifikasi', 'ditindaklanjuti', 'selesai'])->pluck('desa_id'))
            ->unique()
            ->filter(fn($id) => !Laporan::where('desa_id', $id)->whereIn('status', ['diajukan', 'minta_perbaikan'])->exists())
            ->count();

        $totalDesaCount = DesaBinaan::count();
        $desaAmanCount = max(0, $totalDesaCount - ($desaAduanCount + $desaPembinaanCount));

        $desaSebaran = [
            'total_desa' => $totalDesaCount,
            'labels' => ['Desa Aman / Aktif', 'Perlu Pembinaan', 'Ada Aduan Aktif'],
            'series' => [$desaAmanCount, $desaPembinaanCount, $desaAduanCount],
            'colors' => ['#16A34A', '#E8C070', '#DC2626'],
        ];

        // 3. Rangking Kepatuhan UPT Imigrasi
        $uptScorecards = $this->getUptScorecards();
        $uptCategories = [];
        $uptCompletionRates = [];
        $uptAvgHours = [];

        foreach ($uptScorecards as $upt) {
            $shortNama = preg_replace('/Kantor Imigrasi Kelas (I|II|III|I Khusus)( TPI| Non TPI)?\s*/i', 'Kanim ', $upt['nama']);
            $uptCategories[] = trim($shortNama);
            $uptCompletionRates[] = (float) $upt['completion_rate'];
            $uptAvgHours[] = (int) $upt['avg_sla_hours'];
        }

        return [
            'trendData' => [
                '1_minggu' => [
                    'categories' => $weekCategories,
                    'series' => [
                        ['name' => 'Laporan Masuk', 'data' => $weekMasuk],
                        ['name' => 'Selesai Tepat SLA', 'data' => $weekSelesai],
                        ['name' => 'Red-Flag SLA Breached', 'data' => $weekBreached],
                    ],
                ],
                '1_bulan' => [
                    'categories' => $monthCategories,
                    'series' => [
                        ['name' => 'Laporan Masuk', 'data' => $monthMasuk],
                        ['name' => 'Selesai Tepat SLA', 'data' => $monthSelesai],
                        ['name' => 'Red-Flag SLA Breached', 'data' => $monthBreached],
                    ],
                ],
                '1_tahun' => [
                    'categories' => $yearCategories,
                    'series' => [
                        ['name' => 'Laporan Masuk', 'data' => $yearMasuk],
                        ['name' => 'Selesai Tepat SLA', 'data' => $yearSelesai],
                        ['name' => 'Red-Flag SLA Breached', 'data' => $yearBreached],
                    ],
                ],
            ],
            'monthlyTrend' => [
                'categories' => $yearCategories,
                'series' => [
                    ['name' => 'Laporan Masuk', 'data' => $yearMasuk],
                    ['name' => 'Selesai Tepat SLA', 'data' => $yearSelesai],
                    ['name' => 'Red-Flag SLA Breached', 'data' => $yearBreached],
                ],
            ],
            'desaStatusDistribution' => $desaSebaran,
            'uptComplianceRanking' => [
                'categories' => $uptCategories,
                'completionRates' => $uptCompletionRates,
                'avgHours' => $uptAvgHours,
            ],
        ];
    }

    /**
     * Dapatkan Data Kegiatan Pembinaan Desa Lintas UPT untuk Kanwil Executive Monitoring
     */
    public function getKegiatanPembinaanData(?string $tanggalMulai = null, ?string $tanggalSelesai = null): array
    {
        $query = \App\Models\KegiatanPembinaan::with(['desa.upt', 'pimpasa', 'lampiranList']);

        if ($tanggalMulai) {
            $query->whereDate('tanggal', '>=', $tanggalMulai);
        }

        if ($tanggalSelesai) {
            $query->whereDate('tanggal', '<=', $tanggalSelesai);
        }

        return $query->orderBy('tanggal', 'desc')
            ->get()
            ->map(function ($keg) {
                return [
                    'id' => $keg->id,
                    'judul' => $keg->judul,
                    'jenis_pembinaan' => $keg->jenis_pembinaan ?? 'Penyuluhan Hukum',
                    'tanggal' => $keg->tanggal ? $keg->tanggal->format('Y-m-d') : null,
                    'tanggal_formatted' => $keg->tanggal ? $keg->tanggal->format('d M Y') : '-',
                    'jumlah_peserta' => $keg->jumlah_peserta ?? 0,
                    'status' => $keg->status ?? 'selesai',
                    'lokasi' => $keg->lokasi ?? 'Aula Desa',
                    'ringkasan_materi' => $keg->ringkasan_materi ?? '-',
                    'desa_nama' => $keg->desa->nama ?? 'Desa Binaan',
                    'upt_nama' => $keg->desa->upt->nama ?? 'Kanim Pembina',
                    'pimpasa_nama' => $keg->pimpasa->name ?? 'Petugas PIMPASA',
                    'lampiran_count' => $keg->lampiranList ? count($keg->lampiranList) : 0,
                    'lampiran_list' => $keg->lampiranList ? $keg->lampiranList->map(fn($l) => [
                        'id' => $l->id,
                        'nama_file_asli' => $l->nama_file_asli,
                        'path' => $l->path,
                        'tipe_file' => $l->tipe_file,
                        'ukuran_bytes' => $l->ukuran_bytes,
                    ]) : [],
                ];
            })
            ->toArray();
    }

    /**
     * Dapatkan KPI Metrics Kegiatan Pembinaan Desa se-Sumut
     */
    public function getKegiatanKpiMetrics(): array
    {
        $allKegiatan = \App\Models\KegiatanPembinaan::with('desa.upt')->get();

        $totalKegiatan = $allKegiatan->count();
        $totalPeserta = $allKegiatan->sum('jumlah_peserta');
        $desaCount = $allKegiatan->pluck('desa_id')->unique()->count();

        // UPT terbanyak kegiatan
        $uptCounts = [];
        foreach ($allKegiatan as $keg) {
            $uptNama = $keg->desa->upt->nama ?? null;
            if ($uptNama) {
                $uptCounts[$uptNama] = ($uptCounts[$uptNama] ?? 0) + 1;
            }
        }

        arsort($uptCounts);
        if (! empty($uptCounts)) {
            $topUptNama = array_key_first($uptCounts);
            $topUptShort = preg_replace('/Kantor Imigrasi Kelas (I|II|III|I Khusus)( TPI| Non TPI)?\s*/i', 'Kanim ', $topUptNama);
        } else {
            $topUptShort = '-';
        }

        return [
            'total_kegiatan' => $totalKegiatan,
            'total_peserta' => $totalPeserta,
            'total_desa_terjangkau' => $desaCount,
            'top_upt' => trim($topUptShort),
        ];
    }
}
