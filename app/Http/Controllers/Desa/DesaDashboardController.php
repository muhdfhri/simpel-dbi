<?php

namespace App\Http\Controllers\Desa;

use App\Enums\StatusLaporan;
use App\Http\Controllers\Controller;
use App\Models\Laporan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

use App\Models\User;

class DesaDashboardController extends Controller
{
    /**
     * Tampilkan Beranda Overview & Peta Sebaran khusus Perangkat Desa.
     */
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $desa = $user->desa?->load(['upt', 'pimpasa']);

        // Data Tim Petugas PIMPASA Pengampu Wilayah (Privasi Terjaga: id, name, avatar)
        $pimpasaList = [];
        if ($desa && $desa->upt_id) {
            $pimpasaList = User::where('role', 'pimpasa')
                ->where('upt_id', $desa->upt_id)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'avatar' => $p->name ? strtoupper(substr(trim($p->name), 0, 1)) : 'P',
                ])
                ->toArray();
        }

        // Fallback jika upt_id belum diset tetapi pimpasa_id diset di desa_binaan
        if (empty($pimpasaList) && $desa && $desa->pimpasa) {
            $pimpasaList[] = [
                'id' => $desa->pimpasa->id,
                'name' => $desa->pimpasa->name,
                'avatar' => $desa->pimpasa->name ? strtoupper(substr(trim($desa->pimpasa->name), 0, 1)) : 'P',
            ];
        }

        // Data UPT Pembina
        $uptData = null;
        if ($desa && $desa->upt) {
            $u = $desa->upt;
            $uptData = [
                'id' => $u->id,
                'nama' => $u->nama,
                'tipe' => $u->tipe ?? 'Kanim',
            ];
        }

        // Data Desa Binaan Spesifik (dengan status kerawanan terhitung real-time)
        $desaData = null;
        if ($desa) {
            $laporanAduanCount = Laporan::where('desa_id', $desa->id)
                ->whereIn('status', [StatusLaporan::DIAJUKAN, StatusLaporan::MINTA_PERBAIKAN])
                ->count();
            $totalLaporanCount = Laporan::where('desa_id', $desa->id)->count();

            $statusTerkini = 'aman';
            if ($laporanAduanCount > 0) {
                $statusTerkini = 'aduan';
            } elseif ($totalLaporanCount > 0) {
                $statusTerkini = 'pembinaan';
            }

            $desaData = [
                'id' => $desa->id,
                'nama' => $desa->nama,
                'status_terkini' => $statusTerkini,
                'lat' => $desa->lat ? (float) $desa->lat : 3.5952,
                'lng' => $desa->lng ? (float) $desa->lng : 98.6722,
                'upt_nama' => $desa->upt?->nama ?? 'Satker UPT Imigrasi',
            ];
        }

        // Laporan Kejadian di Wilayah Desa Ini (Markers & List)
        $laporanQuery = Laporan::query();
        if ($user->desa_id) {
            $laporanQuery->where('desa_id', $user->desa_id);
        }

        $recentLaporan = (clone $laporanQuery)
            ->latest()
            ->take(20)
            ->get()
            ->map(function ($lap) {
                return [
                    'id' => $lap->id,
                    'kode' => $lap->kode_tiket,
                    'judul' => $lap->judul,
                    'kategori' => $lap->kategoriRef?->nama_kategori ?? '-',
                    'status' => $lap->status,
                    'tanggal' => $lap->created_at ? $lap->created_at->diffForHumans() : 'Baru saja',
                ];
            });

        // Quick Stats Metrics
        $stats = [
            'total' => (clone $laporanQuery)->count(),
            'diajukan' => (clone $laporanQuery)->where('status', StatusLaporan::DIAJUKAN)->count(),
            'minta_perbaikan' => (clone $laporanQuery)->where('status', StatusLaporan::MINTA_PERBAIKAN)->count(),
            'diverifikasi' => (clone $laporanQuery)->where('status', StatusLaporan::DIVERIFIKASI)->count(),
            'selesai' => (clone $laporanQuery)->where('status', StatusLaporan::SELESAI)->count(),
        ];

        // 1. Dynamic Chart Tren Pelaporan (Scoped to Desa)
        $chartTrenData = [
            '1_minggu' => [
                'categories' => ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                'series' => [
                    [
                        'name' => 'Diajukan',
                        'data' => array_map(function ($daysAgo) use ($user) {
                            $date = now()->subDays($daysAgo)->toDateString();
                            return Laporan::where('desa_id', $user->desa_id)->whereDate('created_at', $date)->count();
                        }, range(6, 0))
                    ],
                    [
                        'name' => 'Selesai',
                        'data' => array_map(function ($daysAgo) use ($user) {
                            $date = now()->subDays($daysAgo)->toDateString();
                            return Laporan::where('desa_id', $user->desa_id)->where('status', StatusLaporan::SELESAI)->whereDate('updated_at', $date)->count();
                        }, range(6, 0))
                    ]
                ]
            ],
            '1_bulan' => [
                'categories' => ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
                'series' => [
                    [
                        'name' => 'Diajukan',
                        'data' => array_map(function ($week) use ($user) {
                            $start = now()->subWeeks(4 - $week)->startOfWeek();
                            $end = (clone $start)->endOfWeek();
                            return Laporan::where('desa_id', $user->desa_id)->whereBetween('created_at', [$start, $end])->count();
                        }, range(1, 4))
                    ],
                    [
                        'name' => 'Selesai',
                        'data' => array_map(function ($week) use ($user) {
                            $start = now()->subWeeks(4 - $week)->startOfWeek();
                            $end = (clone $start)->endOfWeek();
                            return Laporan::where('desa_id', $user->desa_id)->where('status', StatusLaporan::SELESAI)->whereBetween('updated_at', [$start, $end])->count();
                        }, range(1, 4))
                    ]
                ]
            ],
            '1_tahun' => [
                'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                'series' => [
                    [
                        'name' => 'Diajukan',
                        'data' => array_map(function ($month) use ($user) {
                            return Laporan::where('desa_id', $user->desa_id)->whereYear('created_at', now()->year)->whereMonth('created_at', $month)->count();
                        }, range(1, 12))
                    ],
                    [
                        'name' => 'Selesai',
                        'data' => array_map(function ($month) use ($user) {
                            return Laporan::where('desa_id', $user->desa_id)->where('status', StatusLaporan::SELESAI)->whereYear('updated_at', now()->year)->whereMonth('updated_at', $month)->count();
                        }, range(1, 12))
                    ]
                ]
            ]
        ];

        // 2. Dynamic Chart Distribusi Kategori (Scoped to Desa)
        $categoriesCounts = (clone $laporanQuery)
            ->select('kategori_id', \DB::raw('count(*) as count'))
            ->whereNotNull('kategori_id')
            ->groupBy('kategori_id')
            ->pluck('count', 'kategori_id')
            ->toArray();

        $fallbackPalette = ['#0E7490', '#4F46E5', '#BE185D', '#92400E'];
        $allCategories = \App\Models\KategoriLaporan::where('is_active', true)->get();
        $chartKategoriColors = $allCategories->map(function ($cat, $idx) use ($fallbackPalette) {
            return match ($cat->kode) {
                'kegiatan_dbi' => '#033566',
                'wna' => '#E8C070',
                'indikasi_tppo_pmi' => '#DC2626',
                'insidentil' => '#7C688C',
                default => $fallbackPalette[$idx % count($fallbackPalette)],
            };
        })->toArray();

        $chartKategoriData = [
            'labels' => $allCategories->pluck('nama_kategori')->toArray(),
            'series' => $allCategories->map(fn ($cat) => $categoriesCounts[$cat->id] ?? 0)->toArray(),
            'colors' => $chartKategoriColors,
        ];

        return Inertia::render('Welcome', [
            'role' => 'desa',
            'desa' => $desaData,
            'pimpasaList' => $pimpasaList,
            'upt' => $uptData,
            'stats' => $stats,
            'recentLaporan' => $recentLaporan,
            'chartTrenData' => $chartTrenData,
            'chartKategoriData' => $chartKategoriData,
        ]);
    }
}
