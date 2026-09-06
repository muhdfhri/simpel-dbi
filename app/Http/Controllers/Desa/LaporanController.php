<?php

namespace App\Http\Controllers\Desa;

use App\Enums\StatusLaporan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Laporan\StoreLaporanRequest;
use App\Models\Laporan;
use App\Services\LaporanWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LaporanController extends Controller
{
    public function __construct(
        protected LaporanWorkflowService $laporanService
    ) {}

    /**
     * Tampilkan daftar laporan desa dengan TanStack Table & Quick Metrics.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $filters = $request->only(['search', 'status', 'kategori']);

        $desaId = $user->desa_id;
        $laporanPaginator = $this->laporanService->getLaporanByDesa(
            $desaId,
            $filters,
            50
        );

        // Quick Stats Metrics & Status Counts (Dynamic Backend Calculation)
        $baseQuery = Laporan::query();
        if ($desaId) {
            $baseQuery->where('desa_id', $desaId);
        }

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'diajukan' => (clone $baseQuery)->where('status', StatusLaporan::DIAJUKAN)->count(),
            'minta_perbaikan' => (clone $baseQuery)->where('status', StatusLaporan::MINTA_PERBAIKAN)->count(),
            'diverifikasi' => (clone $baseQuery)->whereIn('status', [StatusLaporan::DIVERIFIKASI, StatusLaporan::DITINDAKLANJUTI])->count(),
            'selesai' => (clone $baseQuery)->where('status', StatusLaporan::SELESAI)->count(),
        ];

        $statusCounts = [
            'all' => (clone $baseQuery)->count(),
            'diajukan' => (clone $baseQuery)->where('status', StatusLaporan::DIAJUKAN)->count(),
            'minta_perbaikan' => (clone $baseQuery)->where('status', StatusLaporan::MINTA_PERBAIKAN)->count(),
            'diverifikasi' => (clone $baseQuery)->where('status', StatusLaporan::DIVERIFIKASI)->count(),
            'ditindaklanjuti' => (clone $baseQuery)->where('status', StatusLaporan::DITINDAKLANJUTI)->count(),
            'selesai' => (clone $baseQuery)->where('status', StatusLaporan::SELESAI)->count(),
            'ditolak' => (clone $baseQuery)->where('status', StatusLaporan::DITOLAK)->count(),
        ];

        $allDesaLaporan = (clone $baseQuery)->get(['id', 'kategori_id']);

        $kategoriOptions = \App\Models\KategoriLaporan::where('is_active', true)
            ->get(['id', 'nama_kategori', 'kode', 'deskripsi'])
            ->map(fn ($cat) => [
                'id' => $cat->id,
                'value' => (string) $cat->id,
                'kode' => $cat->kode,
                'label' => $cat->nama_kategori,
                'deskripsi' => $cat->deskripsi,
                'count' => $allDesaLaporan->where('kategori_id', $cat->id)->count(),
            ]);

        return Inertia::render('Desa/Laporan/Index', [
            'laporan' => $laporanPaginator,
            'stats' => $stats,
            'statusCounts' => $statusCounts,
            'filters' => $filters,
            'kategoriOptions' => $kategoriOptions,
        ]);
    }

    /**
     * Tampilkan form pengajuan laporan baru.
     */
    public function create(): Response
    {
        $kategoriOptions = \App\Models\KategoriLaporan::where('is_active', true)
            ->get(['id', 'nama_kategori', 'kode', 'deskripsi'])
            ->map(fn ($cat) => [
                'id' => $cat->id,
                'value' => $cat->id,
                'kode' => $cat->kode,
                'label' => $cat->nama_kategori,
                'deskripsi' => $cat->deskripsi,
            ]);

        return Inertia::render('Desa/Laporan/Create', [
            'kategoriOptions' => $kategoriOptions,
        ]);
    }

    /**
     * Simpan pengajuan laporan baru dari Perangkat Desa.
     */
    public function store(StoreLaporanRequest $request): RedirectResponse
    {
        $laporan = $this->laporanService->submitLaporan(
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route('desa.laporan.index')
            ->with('success', "Laporan dengan Kode Tiket {$laporan->kode_tiket} berhasil diajukan.");
    }

    /**
     * Tampilkan detail & tracking timeline tiket laporan.
     */
    public function show(Request $request, Laporan $laporan): Response
    {
        if ($laporan->desa_id !== $request->user()->desa_id) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat laporan ini.');
        }

        $laporan->load(['desa', 'verifikasi.pimpasa', 'tindakLanjut.stafUpt', 'statusHistories.actor', 'lampiranList.uploader']);

        return Inertia::render('Desa/Laporan/Show', [
            'laporan' => $laporan,
        ]);
    }

    /**
     * Tampilkan form perbaikan/pengubahan data (hanya jika status 'diajukan' atau 'minta_perbaikan').
     */
    public function edit(Request $request, Laporan $laporan): Response
    {
        if ($laporan->desa_id !== $request->user()->desa_id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah laporan ini.');
        }

        if (!in_array($laporan->status, [StatusLaporan::DIAJUKAN, StatusLaporan::MINTA_PERBAIKAN])) {
            abort(403, 'Laporan yang telah diverifikasi atau ditindaklanjuti tidak dapat diubah lagi.');
        }

        $laporan->load(['verifikasi.pimpasa', 'lampiranList']);

        $kategoriOptions = \App\Models\KategoriLaporan::where('is_active', true)
            ->get(['id', 'nama_kategori', 'kode', 'deskripsi'])
            ->map(fn ($cat) => [
                'id' => $cat->id,
                'value' => $cat->id,
                'kode' => $cat->kode,
                'label' => $cat->nama_kategori,
                'deskripsi' => $cat->deskripsi,
            ]);

        return Inertia::render('Desa/Laporan/Edit', [
            'laporan' => $laporan,
            'kategoriOptions' => $kategoriOptions,
        ]);
    }

    /**
     * Simpan perubahan/perbaikan data laporan.
     */
    public function update(StoreLaporanRequest $request, Laporan $laporan): RedirectResponse
    {
        if ($laporan->desa_id !== $request->user()->desa_id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah laporan ini.');
        }

        if (!in_array($laporan->status, [StatusLaporan::DIAJUKAN, StatusLaporan::MINTA_PERBAIKAN])) {
            abort(403, 'Laporan yang telah diverifikasi atau ditindaklanjuti tidak dapat diubah lagi.');
        }

        $this->laporanService->resubmitLaporan(
            $laporan,
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route('desa.laporan.show', $laporan->id)
            ->with('success', "Perubahan data laporan Kode Tiket {$laporan->kode_tiket} berhasil disimpan.");
    }

    /**
     * Export daftar laporan desa ke format CSV via Maatwebsite Excel.
     */
    public function exportExcel(Request $request)
    {
        $user = $request->user();
        $desaId = $user->desa_id;
        $filters = $request->only(['search', 'status', 'kategori']);

        $query = Laporan::query()->where('desa_id', $desaId);

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('kode_tiket', 'like', "%{$s}%")
                  ->orWhere('judul', 'like', "%{$s}%");
            });
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['kategori_id']) && $filters['kategori_id'] !== 'all') {
            $query->where('kategori_id', $filters['kategori_id']);
        } elseif (!empty($filters['kategori']) && $filters['kategori'] !== 'all') {
            $query->where('kategori_id', $filters['kategori']);
        }

        $laporanList = $query->with('kategoriRef')->latest('submitted_at')->get();
        $filename = 'rekap-laporan-desa-' . date('Y-m-d-His') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\LaporanDesaExport($laporanList),
            $filename,
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    /**
     * Export rekapitulasi laporan desa ke PDF kedinasan.
     */
    public function exportPdf(Request $request)
    {
        $user = $request->user()->load(['desa.upt', 'desa.pimpasa']);
        $desa = $user->desa;
        $desaId = $user->desa_id;
        $filters = $request->only(['search', 'status', 'kategori']);

        $query = Laporan::query()->where('desa_id', $desaId);

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('kode_tiket', 'like', "%{$s}%")
                  ->orWhere('judul', 'like', "%{$s}%");
            });
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['kategori_id']) && $filters['kategori_id'] !== 'all') {
            $query->where('kategori_id', $filters['kategori_id']);
        } elseif (!empty($filters['kategori']) && $filters['kategori'] !== 'all') {
            $query->where('kategori_id', $filters['kategori']);
        }

        $laporanRaw = $query->with('kategoriRef')->latest('submitted_at')->get();

        $laporanList = $laporanRaw->map(function ($item) {
            $st = is_object($item->status) ? $item->status->value : $item->status;
            $kategoriNama = $item->kategoriRef?->nama_kategori ?? (is_string($item->kategori) ? $item->kategori : ($item->kategori?->nama_kategori ?? '-'));
            return [
                'kode_tiket' => $item->kode_tiket,
                'judul' => $item->judul,
                'kategori' => $kategoriNama,
                'status' => $st,
                'status_label' => ucwords(str_replace('_', ' ', $st)),
                'submitted_at_formatted' => $item->submitted_at ? $item->submitted_at->format('d M Y, H:i') : '-',
            ];
        })->toArray();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.laporan-rekap', [
            'laporanList' => $laporanList,
            'desaNama' => $desa?->nama ?? 'Wilayah Desa',
            'uptNama' => $desa?->upt?->nama ?? 'Kanim UPT Imigrasi Pembina',
            'pimpasaNama' => $desa?->pimpasa?->name ?? 'Tim Pembina PIMPASA',
            'userName' => $user->name,
            'tanggalCetak' => date('d F Y, H:i'),
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('rekapitulasi-laporan-desa-' . date('Ymd-His') . '.pdf');
    }

    /**
     * Export lembar bukti registrasi tiket laporan individual ke PDF.
     */
    public function exportDetailPdf(Request $request, Laporan $laporan)
    {
        if ($laporan->desa_id !== $request->user()->desa_id) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat laporan ini.');
        }

        $laporan->load(['desa.upt', 'kategoriRef', 'verifikasi.pimpasa', 'tindakLanjut', 'statusHistories.actor', 'lampiranList']);
        $user = $request->user();

        $st = is_object($laporan->status) ? $laporan->status->value : $laporan->status;
        $statusLabel = ucwords(str_replace('_', ' ', $st));
        $kategoriNama = $laporan->kategoriRef?->nama_kategori ?? (is_string($laporan->kategori) ? $laporan->kategori : ($laporan->kategori?->nama_kategori ?? '-'));

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.laporan-detail', [
            'laporan' => $laporan,
            'statusStr' => $st,
            'statusLabel' => $statusLabel,
            'kategoriNama' => $kategoriNama,
            'userName' => $user->name,
            'submittedAtFormatted' => $laporan->submitted_at ? $laporan->submitted_at->format('d F Y, H:i') . ' WIB' : '-',
            'tanggalKejadianFormatted' => $laporan->tanggal_kejadian ? $laporan->tanggal_kejadian->format('d F Y, H:i') . ' WIB' : '-',
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('bukti-tiket-' . $laporan->kode_tiket . '.pdf');
    }
}
