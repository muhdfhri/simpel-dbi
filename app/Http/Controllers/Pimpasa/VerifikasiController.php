<?php

namespace App\Http\Controllers\Pimpasa;

use App\Enums\KategoriLaporan;
use App\Enums\StatusLaporan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pimpasa\StoreVerifikasiRequest;
use App\Models\Laporan;
use App\Services\PimpasaWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

use Barryvdh\DomPDF\Facade\Pdf;

class VerifikasiController extends Controller
{
    public function __construct(
        protected PimpasaWorkflowService $pimpasaService
    ) {}

    /**
     * Tampilkan antrean laporan masuk untuk PIMPASA.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status', 'kategori', 'desa_id']);
        $user = $request->user();
        $uptId = $user->upt_id;

        $laporanPaginator = $this->pimpasaService->getAntreanVerifikasi($filters, 50);

        // Compute counts for all status states
        $allLaporanQuery = Laporan::query();
        if ($uptId) {
            $allLaporanQuery->whereHas('desa', fn ($q) => $q->where('upt_id', $uptId));
        }

        $statusCounts = [
            'all' => (clone $allLaporanQuery)->count(),
            'diajukan' => (clone $allLaporanQuery)->where('status', 'diajukan')->count(),
            'minta_perbaikan' => (clone $allLaporanQuery)->where('status', 'minta_perbaikan')->count(),
            'diverifikasi' => (clone $allLaporanQuery)->where('status', 'diverifikasi')->count(),
            'ditindaklanjuti' => (clone $allLaporanQuery)->where('status', 'ditindaklanjuti')->count(),
            'selesai' => (clone $allLaporanQuery)->where('status', 'selesai')->count(),
            'ditolak' => (clone $allLaporanQuery)->where('status', 'ditolak')->count(),
        ];

        // Fetch categories and desa with counts
        $allLaporanForCounts = Laporan::get(['id', 'kategori_id', 'desa_id']);
        $kategoriOptions = \App\Models\KategoriLaporan::where('is_active', true)
            ->get(['id', 'nama_kategori', 'kode'])
            ->map(fn ($cat) => [
                'id' => $cat->id,
                'value' => (string) $cat->id,
                'label' => $cat->nama_kategori,
                'count' => $allLaporanForCounts->where('kategori_id', $cat->id)->count(),
            ]);

        // Fetch Desa Binaan options
        $desaQuery = \App\Models\DesaBinaan::query();
        if ($uptId) {
            $desaQuery->where('upt_id', $uptId);
        }
        $desaOptions = $desaQuery->orderBy('nama')->get(['id', 'nama'])->map(fn ($d) => [
            'value' => (string) $d->id,
            'label' => $d->nama,
            'count' => $allLaporanForCounts->where('desa_id', $d->id)->count(),
        ]);

        return Inertia::render('Pimpasa/Verifikasi/Index', [
            'laporan' => $laporanPaginator,
            'filters' => $filters,
            'statusCounts' => $statusCounts,
            'kategoriOptions' => $kategoriOptions,
            'desaOptions' => $desaOptions,
            'statusOptions' => array_map(fn ($st) => [
                'value' => $st->value,
                'label' => strtoupper($st->value),
            ], StatusLaporan::cases()),
        ]);
    }

    /**
     * Export worklist verifikasi ke format CSV via Maatwebsite Excel.
     */
    public function exportExcel(Request $request)
    {
        $user = $request->user();
        $filters = $request->only(['search', 'status', 'kategori', 'desa_id']);

        $query = Laporan::with(['desa', 'kategoriRef', 'lampiranList', 'verifikasi']);

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        } else {
            $query->whereIn('status', [StatusLaporan::DIAJUKAN, StatusLaporan::MINTA_PERBAIKAN, StatusLaporan::DIVERIFIKASI]);
        }

        if (! empty($filters['kategori_id'])) {
            $query->where('kategori_id', $filters['kategori_id']);
        } elseif (! empty($filters['kategori']) && $filters['kategori'] !== 'all') {
            $query->where('kategori_id', $filters['kategori']);
        }

        if (! empty($filters['desa_id']) && $filters['desa_id'] !== 'all') {
            $query->where('desa_id', $filters['desa_id']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('kode_tiket', 'like', "%{$search}%")
                    ->orWhere('judul', 'like', "%{$search}%")
                    ->orWhereHas('desa', fn ($d) => $d->where('nama', 'like', "%{$search}%"));
            });
        }

        $laporanList = $query->orderBy('submitted_at', 'desc')->get();
        $filename = 'worklist-verifikasi-pimpasa-' . date('Y-m-d-His') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\VerifikasiPimpasaExport($laporanList),
            $filename,
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    /**
     * Export worklist verifikasi ke PDF kedinasan.
     */
    public function exportPdf(Request $request)
    {
        $user = $request->user();
        $filters = $request->only(['search', 'status', 'kategori', 'desa_id']);

        $query = Laporan::with(['desa', 'kategoriRef', 'lampiranList', 'verifikasi']);

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        } else {
            $query->whereIn('status', [StatusLaporan::DIAJUKAN, StatusLaporan::MINTA_PERBAIKAN, StatusLaporan::DIVERIFIKASI]);
        }

        if (! empty($filters['kategori_id'])) {
            $query->where('kategori_id', $filters['kategori_id']);
        } elseif (! empty($filters['kategori']) && $filters['kategori'] !== 'all') {
            $query->where('kategori_id', $filters['kategori']);
        }

        if (! empty($filters['desa_id']) && $filters['desa_id'] !== 'all') {
            $query->where('desa_id', $filters['desa_id']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('kode_tiket', 'like', "%{$search}%")
                    ->orWhere('judul', 'like', "%{$search}%")
                    ->orWhereHas('desa', fn ($d) => $d->where('nama', 'like', "%{$search}%"));
            });
        }

        $laporanList = $query->orderBy('submitted_at', 'desc')->get();
        $pimpasaNama = $user->name ?? 'Petugas PIMPASA';
        $uptNama = $user->upt?->nama_upt ?? 'Kanwil / UPT Imigrasi';

        $pdf = Pdf::loadView('pdf.pimpasa-verifikasi', [
            'laporanList' => $laporanList,
            'pimpasaNama' => $pimpasaNama,
            'uptNama' => $uptNama,
            'tanggalCetak' => date('d F Y, H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('worklist-verifikasi-pimpasa-' . date('Y-m-d-His') . '.pdf');
    }

    /**
     * Tampilkan detail & form verifikasi laporan PIMPASA.
     */
    public function show(Laporan $laporan): Response
    {
        $laporan->load(['desa', 'kategoriRef', 'verifikasi.pimpasa', 'tindakLanjut.stafUpt', 'statusHistories.actor', 'lampiranList.uploader']);

        return Inertia::render('Pimpasa/Verifikasi/Show', [
            'laporan' => $laporan,
        ]);
    }

    /**
     * Simpan keputusan verifikasi PIMPASA (diverifikasi | minta_perbaikan | ditolak).
     */
    public function store(StoreVerifikasiRequest $request, Laporan $laporan): RedirectResponse
    {
        $this->pimpasaService->verifikasiLaporan(
            $laporan,
            $request->validated(),
            $request->user()
        );

        $keputusan = $request->validated()['keputusan'];

        if ($keputusan === 'diverifikasi') {
            return redirect()
                ->route('pimpasa.tindaklanjut.form', $laporan->id)
                ->with('success', "Laporan Kode Tiket {$laporan->kode_tiket} berhasil diverifikasi. Silakan lengkapi berita acara tindak lanjut UPT.");
        }

        $msg = match ($keputusan) {
            'minta_perbaikan' => "Laporan Kode Tiket {$laporan->kode_tiket} dikembalikan ke Perangkat Desa untuk perbaikan data.",
            'ditolak' => "Laporan Kode Tiket {$laporan->kode_tiket} telah ditolak.",
            default => "Keputusan verifikasi berhasil disimpan.",
        };

        return redirect()
            ->route('pimpasa.verifikasi.index')
            ->with('success', $msg);
    }
}
