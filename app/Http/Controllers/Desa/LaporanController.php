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
        $filters = $request->only(['search', 'status', 'kategori', 'mode_periode', 'bulan', 'tahun', 'tanggal_mulai', 'tanggal_selesai']);

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

        $bulanOptions = [
            ['value' => 'all', 'label' => 'Semua Bulan'],
            ['value' => '01', 'label' => 'Januari'],
            ['value' => '02', 'label' => 'Februari'],
            ['value' => '03', 'label' => 'Maret'],
            ['value' => '04', 'label' => 'April'],
            ['value' => '05', 'label' => 'Mei'],
            ['value' => '06', 'label' => 'Juni'],
            ['value' => '07', 'label' => 'Juli'],
            ['value' => '08', 'label' => 'Agustus'],
            ['value' => '09', 'label' => 'September'],
            ['value' => '10', 'label' => 'Oktober'],
            ['value' => '11', 'label' => 'November'],
            ['value' => '12', 'label' => 'Desember'],
        ];

        $dbYears = Laporan::when($desaId, fn ($q) => $q->where('desa_id', $desaId))
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->pluck('year')
            ->filter()
            ->toArray();

        $currentYear = (int) date('Y');
        $yearsList = array_unique(array_merge($dbYears, [$currentYear, $currentYear - 1, $currentYear - 2]));
        rsort($yearsList);

        $tahunOptions = array_merge(
            [['value' => 'all', 'label' => 'Semua Tahun']],
            array_map(fn ($y) => ['value' => (string) $y, 'label' => (string) $y], $yearsList)
        );

        return Inertia::render('Desa/Laporan/Index', [
            'laporan' => $laporanPaginator,
            'stats' => $stats,
            'statusCounts' => $statusCounts,
            'filters' => $filters,
            'kategoriOptions' => $kategoriOptions,
            'bulanOptions' => $bulanOptions,
            'tahunOptions' => $tahunOptions,
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
     * Hapus berkas lampiran tertentu milik laporan desa.
     */
    public function destroyLampiran(Request $request, \App\Models\Lampiran $lampiran): RedirectResponse
    {
        $user = $request->user();
        if ($lampiran->lampiranable_type === 'App\Models\Laporan') {
            $laporan = \App\Models\Laporan::find($lampiran->lampiranable_id);
            if ($laporan && $laporan->desa_id !== $user->desa_id) {
                abort(403, 'Anda tidak memiliki hak akses untuk menghapus lampiran laporan ini.');
            }
            if ($laporan && !in_array($laporan->status, [StatusLaporan::DIAJUKAN, StatusLaporan::MINTA_PERBAIKAN])) {
                abort(403, 'Laporan yang telah diverifikasi atau ditindaklanjuti tidak dapat diubah lampirannya.');
            }
        }

        if ($lampiran->path && \Illuminate\Support\Facades\Storage::disk('public')->exists($lampiran->path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($lampiran->path);
        }
        $lampiran->delete();

        return redirect()->back()->with('success', 'File lampiran berhasil dihapus.');
    }

    /**
     * Export daftar laporan desa ke format CSV via Maatwebsite Excel.
     */
    public function exportExcel(Request $request)
    {
        $user = $request->user();
        $desaId = $user->desa_id;
        $filters = $request->only(['search', 'status', 'kategori', 'mode_periode', 'bulan', 'tahun', 'tanggal_mulai', 'tanggal_selesai']);

        $query = Laporan::query()->where('desa_id', $desaId);

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('kode_tiket', 'like', "%{$s}%")
                  ->orWhere('judul', 'like', "%{$s}%")
                  ->orWhere('lokasi_detail', 'like', "%{$s}%")
                  ->orWhereHas('kategoriRef', function ($catQuery) use ($s) {
                      $catQuery->where('nama_kategori', 'like', "%{$s}%")
                               ->orWhere('kode', 'like', "%{$s}%");
                  });
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

        $modePeriode = $filters['mode_periode'] ?? null;
        if ($modePeriode === 'hari_ini') {
            $query->whereDate('created_at', now()->today());
        } elseif ($modePeriode === '7_hari') {
            $query->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()]);
        } elseif ($modePeriode === '30_hari') {
            $query->whereBetween('created_at', [now()->subDays(29)->startOfDay(), now()->endOfDay()]);
        } elseif ($modePeriode === 'bulan_ini') {
            $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
        } elseif ($modePeriode === 'tahun_ini') {
            $query->whereYear('created_at', now()->year);
        } elseif ($modePeriode === 'rentang_tanggal' || !empty($filters['tanggal_mulai']) || !empty($filters['tanggal_selesai'])) {
            if (!empty($filters['tanggal_mulai'])) {
                $query->whereDate('created_at', '>=', $filters['tanggal_mulai']);
            }
            if (!empty($filters['tanggal_selesai'])) {
                $query->whereDate('created_at', '<=', $filters['tanggal_selesai']);
            }
        } else {
            if (!empty($filters['bulan']) && $filters['bulan'] !== 'all') {
                $query->whereMonth('created_at', $filters['bulan']);
            }
            if (!empty($filters['tahun']) && $filters['tahun'] !== 'all') {
                $query->whereYear('created_at', $filters['tahun']);
            }
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
        $filters = $request->only(['search', 'status', 'kategori', 'mode_periode', 'bulan', 'tahun', 'tanggal_mulai', 'tanggal_selesai']);

        $query = Laporan::query()->where('desa_id', $desaId);

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('kode_tiket', 'like', "%{$s}%")
                  ->orWhere('judul', 'like', "%{$s}%")
                  ->orWhere('lokasi_detail', 'like', "%{$s}%")
                  ->orWhereHas('kategoriRef', function ($catQuery) use ($s) {
                      $catQuery->where('nama_kategori', 'like', "%{$s}%")
                               ->orWhere('kode', 'like', "%{$s}%");
                  });
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

        $modePeriode = $filters['mode_periode'] ?? null;
        if ($modePeriode === 'hari_ini') {
            $query->whereDate('created_at', now()->today());
        } elseif ($modePeriode === '7_hari') {
            $query->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()]);
        } elseif ($modePeriode === '30_hari') {
            $query->whereBetween('created_at', [now()->subDays(29)->startOfDay(), now()->endOfDay()]);
        } elseif ($modePeriode === 'bulan_ini') {
            $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
        } elseif ($modePeriode === 'tahun_ini') {
            $query->whereYear('created_at', now()->year);
        } elseif ($modePeriode === 'rentang_tanggal' || !empty($filters['tanggal_mulai']) || !empty($filters['tanggal_selesai'])) {
            if (!empty($filters['tanggal_mulai'])) {
                $query->whereDate('created_at', '>=', $filters['tanggal_mulai']);
            }
            if (!empty($filters['tanggal_selesai'])) {
                $query->whereDate('created_at', '<=', $filters['tanggal_selesai']);
            }
        } else {
            if (!empty($filters['bulan']) && $filters['bulan'] !== 'all') {
                $query->whereMonth('created_at', $filters['bulan']);
            }
            if (!empty($filters['tahun']) && $filters['tahun'] !== 'all') {
                $query->whereYear('created_at', $filters['tahun']);
            }
        }

        $laporanRaw = $query->with('kategoriRef')->latest('submitted_at')->get();

        $bulanNamaMap = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];

        $periodeText = 'Semua Periode';
        if ($modePeriode === 'hari_ini') {
            $periodeText = 'Hari Ini (' . date('d M Y') . ')';
        } elseif ($modePeriode === '7_hari') {
            $periodeText = '7 Hari Terakhir (' . now()->subDays(6)->format('d M') . ' - ' . date('d M Y') . ')';
        } elseif ($modePeriode === '30_hari') {
            $periodeText = '30 Hari Terakhir (' . now()->subDays(29)->format('d M') . ' - ' . date('d M Y') . ')';
        } elseif ($modePeriode === 'bulan_ini') {
            $periodeText = 'Bulan Ini (' . date('F Y') . ')';
        } elseif ($modePeriode === 'tahun_ini') {
            $periodeText = 'Tahun Ini (' . date('Y') . ')';
        } elseif ($modePeriode === 'rentang_tanggal' || !empty($filters['tanggal_mulai']) || !empty($filters['tanggal_selesai'])) {
            $f = !empty($filters['tanggal_mulai']) ? date('d M Y', strtotime($filters['tanggal_mulai'])) : 'Awal';
            $t = !empty($filters['tanggal_selesai']) ? date('d M Y', strtotime($filters['tanggal_selesai'])) : 'Sekarang';
            $periodeText = "{$f} s.d. {$t}";
        } elseif (!empty($filters['bulan']) && $filters['bulan'] !== 'all' && !empty($filters['tahun']) && $filters['tahun'] !== 'all') {
            $b = $bulanNamaMap[$filters['bulan']] ?? $filters['bulan'];
            $periodeText = "{$b} {$filters['tahun']}";
        } elseif (!empty($filters['bulan']) && $filters['bulan'] !== 'all') {
            $b = $bulanNamaMap[$filters['bulan']] ?? $filters['bulan'];
            $periodeText = "Bulan {$b}";
        } elseif (!empty($filters['tahun']) && $filters['tahun'] !== 'all') {
            $periodeText = "Tahun {$filters['tahun']}";
        }

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
            'periodeText' => $periodeText,
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
