<?php

namespace App\Http\Controllers\Desa;

use App\Enums\StatusLaporan;
use App\Http\Controllers\Controller;
use App\Models\KategoriLaporan;
use App\Models\Laporan;
use App\Services\LaporanWorkflowService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RiwayatController extends Controller
{
    public function __construct(
        protected LaporanWorkflowService $laporanService
    ) {}

    /**
     * Tampilkan pusat rekapitulasi, histori, dan audit trail laporan desa.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $filters = $request->only(['search', 'status', 'kategori', 'periode']);

        $query = Laporan::with([
            'verifikasi.pimpasa',
            'tindakLanjut.stafUpt',
            'statusHistories.actor',
            'lampiranList',
        ]);

        if ($user->desa_id) {
            $query->where('desa_id', $user->desa_id);
        }

        // Filter status
        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        // Filter kategori
        if (! empty($filters['kategori']) && $filters['kategori'] !== 'all') {
            $query->where('kategori_id', $filters['kategori']);
        }

        // Filter periode (bulan ini, triwulan, tahun ini)
        if (! empty($filters['periode'])) {
            switch ($filters['periode']) {
                case 'this_month':
                    $query->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year);
                    break;
                case 'this_quarter':
                    $query->whereBetween('created_at', [now()->startOfQuarter(), now()->endOfQuarter()]);
                    break;
                case 'this_year':
                    $query->whereYear('created_at', now()->year);
                    break;
            }
        }

        // Search kata kunci
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('kode_tiket', 'like', "%{$search}%")
                    ->orWhere('judul', 'like', "%{$search}%")
                    ->orWhere('lokasi_detail', 'like', "%{$search}%");
            });
        }

        $laporanPaginator = $query->orderBy('created_at', 'desc')
            ->paginate(50)
            ->withQueryString();

        // High Level Executive KPI Metrics
        $baseQuery = Laporan::query();
        if ($user->desa_id) {
            $baseQuery->where('desa_id', $user->desa_id);
        }

        $totalLaporan = (clone $baseQuery)->count();
        $totalSelesai = (clone $baseQuery)->where('status', StatusLaporan::SELESAI)->count();
        $totalDitolak = (clone $baseQuery)->where('status', StatusLaporan::DITOLAK)->count();
        $totalDiproses = (clone $baseQuery)->whereIn('status', [
            StatusLaporan::DIAJUKAN,
            StatusLaporan::MINTA_PERBAIKAN,
            StatusLaporan::DIVERIFIKASI,
            StatusLaporan::DITINDAKLANJUTI,
        ])->count();

        $resolutionRate = $totalLaporan > 0 ? round(($totalSelesai / $totalLaporan) * 100, 1) : 0;

        $stats = [
            'total' => $totalLaporan,
            'selesai' => $totalSelesai,
            'ditolak' => $totalDitolak,
            'diproses' => $totalDiproses,
            'resolution_rate' => $resolutionRate,
        ];

        return Inertia::render('Desa/Riwayat/Index', [
            'laporan' => $laporanPaginator,
            'stats' => $stats,
            'filters' => $filters,
            'kategoriOptions' => KategoriLaporan::where('is_active', true)
                ->get(['id', 'nama_kategori', 'kode'])
                ->map(fn ($cat) => [
                    'value' => (string) $cat->id,
                    'label' => $cat->nama_kategori,
                ])
                ->toArray(),
        ]);
    }
}
