<?php

namespace App\Http\Controllers\Pimpasa;

use App\Exports\KegiatanPembinaanExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\KegiatanPembinaanRequest;
use App\Models\DesaBinaan;
use App\Models\KegiatanPembinaan;
use App\Models\Lampiran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class KegiatanPembinaanController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $query = KegiatanPembinaan::with(['desa', 'pimpasa', 'lampiranList'])
            ->orderBy('tanggal', 'desc');

        // PIMPASA Scope: Filter by PIMPASA user if applicable
        if ($user->hasRole('PIMPASA')) {
            $query->where('pimpasa_id', $user->id);
        }

        // Search Filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('jenis_pembinaan', 'like', "%{$search}%")
                  ->orWhere('ringkasan_materi', 'like', "%{$search}%")
                  ->orWhereHas('desa', function ($dq) use ($search) {
                      $dq->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        // Desa Filter
        if ($desaId = $request->input('desa_id')) {
            $query->where('desa_id', $desaId);
        }

        // Jenis Filter
        if ($jenis = $request->input('jenis_pembinaan')) {
            $query->where('jenis_pembinaan', $jenis);
        }

        // Status Filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $kegiatanList = $query->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'pimpasa_id' => $item->pimpasa_id,
                'desa_id' => $item->desa_id,
                'judul' => $item->judul,
                'jenis_pembinaan' => $item->jenis_pembinaan,
                'tanggal' => $item->tanggal ? $item->tanggal->format('Y-m-d') : null,
                'jumlah_peserta' => $item->jumlah_peserta,
                'status' => $item->status,
                'lokasi' => $item->lokasi,
                'ringkasan_materi' => $item->ringkasan_materi,
                'desa_nama' => $item->desa?->nama ?? '-',
                'petugas_nama' => $item->pimpasa?->name ?? 'Petugas PIMPASA',
                'lampiran' => $item->lampiranList->map(fn($l) => [
                    'id' => $l->id,
                    'file_name' => $l->file_name,
                    'file_path' => Storage::url($l->file_path),
                    'mime_type' => $l->mime_type,
                ]),
            ];
        });

        // Compute Stats & Counts
        $allKegiatan = KegiatanPembinaan::when($user->hasRole('PIMPASA'), fn($q) => $q->where('pimpasa_id', $user->id))->get();
        $stats = [
            'total_kegiatan' => $allKegiatan->count(),
            'total_peserta' => (int) $allKegiatan->sum('jumlah_peserta'),
            'total_desa' => $allKegiatan->pluck('desa_id')->unique()->count(),
            'count_selesai' => $allKegiatan->where('status', 'selesai')->count(),
            'count_terjadwal' => $allKegiatan->where('status', 'terjadwal')->count(),
            'count_dibatalkan' => $allKegiatan->where('status', 'dibatalkan')->count(),
            'count_penyuluhan' => $allKegiatan->where('jenis_pembinaan', 'Penyuluhan Hukum')->count(),
            'count_simpatik' => $allKegiatan->where('jenis_pembinaan', 'Layanan Simpatik')->count(),
            'count_pemuda' => $allKegiatan->where('jenis_pembinaan', 'Pembinaan Pemuda')->count(),
            'count_tppo' => $allKegiatan->where('jenis_pembinaan', 'Sosialisasi TPPO')->count(),
            'count_inspeksi' => $allKegiatan->where('jenis_pembinaan', 'Inspeksi Lapangan')->count(),
        ];

        // Fetch List of Desa Binaan under PIMPASA UPT with activity counts
        $desaQuery = DesaBinaan::query();
        if ($user->hasRole('PIMPASA')) {
            if ($user->upt_id) {
                $desaQuery->where('upt_id', $user->upt_id);
            } else {
                $desaQuery->where('pimpasa_id', $user->id);
            }
        }

        $desaList = $desaQuery->withCount(['kegiatanPembinaanList' => function ($q) use ($user) {
            if ($user->hasRole('PIMPASA')) {
                $q->where('pimpasa_id', $user->id);
            }
        }])->orderBy('nama', 'asc')->get(['id', 'nama']);

        return Inertia::render('Pimpasa/Kegiatan/Index', [
            'kegiatanList' => $kegiatanList,
            'desaList' => $desaList,
            'stats' => $stats,
            'filters' => $request->only(['search', 'desa_id', 'jenis_pembinaan', 'status']),
        ]);
    }

    public function store(KegiatanPembinaanRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        $validated['pimpasa_id'] = $user->id;

        $kegiatan = KegiatanPembinaan::create($validated);

        // Process attachments if any
        if ($request->hasFile('lampiran_files')) {
            foreach ($request->file('lampiran_files') as $file) {
                $path = $file->store('kegiatan-pembinaan', 'public');
                $kegiatan->lampiranList()->create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getClientMimeType(),
                ]);
            }
        }

        return redirect()->back()->with('success', 'Agenda kegiatan pembinaan berhasil ditambahkan.');
    }

    public function update(KegiatanPembinaanRequest $request, KegiatanPembinaan $kegiatan): RedirectResponse
    {
        $validated = $request->validated();
        $kegiatan->update($validated);

        // Process new attachments if any
        if ($request->hasFile('lampiran_files')) {
            foreach ($request->file('lampiran_files') as $file) {
                $path = $file->store('kegiatan-pembinaan', 'public');
                $kegiatan->lampiranList()->create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getClientMimeType(),
                ]);
            }
        }

        return redirect()->back()->with('success', 'Data kegiatan pembinaan berhasil diperbarui.');
    }

    public function destroy(KegiatanPembinaan $kegiatan): RedirectResponse
    {
        $kegiatan->delete();

        return redirect()->back()->with('success', 'Data kegiatan pembinaan berhasil dihapus.');
    }

    public function exportPdf(Request $request)
    {
        $user = $request->user();
        $query = KegiatanPembinaan::with(['desa', 'pimpasa'])
            ->orderBy('tanggal', 'desc');

        if ($user->hasRole('PIMPASA')) {
            $query->where('pimpasa_id', $user->id);
        }

        if ($desaId = $request->input('desa_id')) {
            $query->where('desa_id', $desaId);
        }

        $kegiatanList = $query->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'judul' => $item->judul,
                'jenis_pembinaan' => $item->jenis_pembinaan,
                'tanggal' => $item->tanggal ? $item->tanggal->format('Y-m-d') : null,
                'jumlah_peserta' => $item->jumlah_peserta,
                'status' => $item->status,
                'lokasi' => $item->lokasi,
                'ringkasan_materi' => $item->ringkasan_materi,
                'desa_nama' => $item->desa?->nama ?? '-',
            ];
        });

        $stats = [
            'total_kegiatan' => count($kegiatanList),
            'total_peserta' => array_sum(array_column($kegiatanList->toArray(), 'jumlah_peserta')),
            'total_desa' => count(array_unique(array_column($kegiatanList->toArray(), 'desa_nama'))),
        ];

        $uptNama = $user->upt?->nama_upt ?? 'Kanwil Kemenkumham Sumatera Utara';
        $pimpasaNama = $user->name ?? 'Petugas PIMPASA';
        $tanggalCetak = now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm');

        $pdf = Pdf::loadView('pdf.kegiatan-pembinaan', compact(
            'kegiatanList',
            'stats',
            'uptNama',
            'pimpasaNama',
            'tanggalCetak'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('Rekapitulasi_Kegiatan_Pembinaan_' . date('Ymd_His') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $user = $request->user();
        $query = KegiatanPembinaan::with(['desa', 'pimpasa'])
            ->orderBy('tanggal', 'desc');

        if ($user->hasRole('PIMPASA')) {
            $query->where('pimpasa_id', $user->id);
        }

        if ($desaId = $request->input('desa_id')) {
            $query->where('desa_id', $desaId);
        }

        $kegiatanList = $query->get();

        return Excel::download(
            new KegiatanPembinaanExport($kegiatanList),
            'Rekapitulasi_Kegiatan_Pembinaan_' . date('Ymd_His') . '.xlsx'
        );
    }
}
