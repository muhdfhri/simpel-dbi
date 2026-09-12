<?php

namespace App\Http\Controllers\Pimpasa;

use App\Http\Controllers\Controller;
use App\Services\PimpasaWorkflowService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TindakLanjutIndexController extends Controller
{
    public function __construct(
        protected PimpasaWorkflowService $pimpasaService
    ) {}

    public function __invoke(Request $request): Response
    {
        $filters = $request->only(['search', 'status', 'kategori', 'desa_id', 'tanggal_mulai', 'tanggal_selesai']);
        $user = $request->user();
        $uptId = $user->upt_id;

        $laporanPaginator = $this->pimpasaService->getDisposisiTindakLanjut($filters, 50);

        // Calculate counts for Disposisi & Tindak Lanjut
        $disposisiQuery = \App\Models\Laporan::whereIn('status', ['diverifikasi', 'ditindaklanjuti', 'selesai']);
        if ($uptId) {
            $disposisiQuery->whereHas('desa', fn ($q) => $q->where('upt_id', $uptId));
        }

        $statusCounts = [
            'all' => (clone $disposisiQuery)->count(),
            'diverifikasi' => (clone $disposisiQuery)->where('status', 'diverifikasi')->count(),
            'ditindaklanjuti' => (clone $disposisiQuery)->where('status', 'ditindaklanjuti')->count(),
            'selesai' => (clone $disposisiQuery)->where('status', 'selesai')->count(),
        ];

        $laporanDisposisi = (clone $disposisiQuery)->get(['id', 'kategori_id', 'desa_id']);
        $kategoriOptions = \App\Models\KategoriLaporan::where('is_active', true)
            ->get(['id', 'nama_kategori', 'kode'])
            ->map(fn ($cat) => [
                'id' => $cat->id,
                'value' => (string) $cat->id,
                'label' => $cat->nama_kategori,
                'count' => $laporanDisposisi->where('kategori_id', $cat->id)->count(),
            ]);

        // Fetch Desa Binaan options
        $desaQuery = \App\Models\DesaBinaan::query();
        if ($uptId) {
            $desaQuery->where('upt_id', $uptId);
        }
        $desaOptions = $desaQuery->orderBy('nama')->get(['id', 'nama'])->map(fn ($d) => [
            'value' => (string) $d->id,
            'label' => $d->nama,
            'count' => $laporanDisposisi->where('desa_id', $d->id)->count(),
        ]);

        return Inertia::render('Pimpasa/TindakLanjut/Index', [
            'laporan' => $laporanPaginator,
            'filters' => $filters,
            'statusCounts' => $statusCounts,
            'kategoriOptions' => $kategoriOptions,
            'desaOptions' => $desaOptions,
        ]);
    }
}
