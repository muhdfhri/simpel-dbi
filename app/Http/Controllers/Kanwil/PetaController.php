<?php

namespace App\Http\Controllers\Kanwil;

use App\Http\Controllers\Controller;
use App\Models\DesaBinaan;
use App\Models\Upt;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PetaController extends Controller
{
    public function index(Request $request): Response
    {
        $desaMarkers = DesaBinaan::with([
                'upt',
                'pimpasa',
                'laporanList' => fn ($q) => $q->with('kategoriRef')->latest('submitted_at'),
            ])
            ->withMax('laporanList as latest_laporan_at', 'submitted_at')
            ->withCount([
                'laporanList as total_laporan',
                'laporanList as laporan_diajukan' => fn ($q) => $q->where('status', 'diajukan'),
                'laporanList as laporan_diverifikasi' => fn ($q) => $q->where('status', 'diverifikasi'),
                'laporanList as laporan_selesai' => fn ($q) => $q->where('status', 'selesai'),
            ])
            ->get()
            ->sortByDesc(fn ($desa) => $desa->latest_laporan_at ?? '1970-01-01 00:00:00')
            ->values()
            ->map(function ($desa) {
                $latestLaporanItem = $desa->laporanList->first();
                $latestLaporanData = null;
                if ($latestLaporanItem) {
                    $st = is_object($latestLaporanItem->status) ? $latestLaporanItem->status->value : $latestLaporanItem->status;
                    $kategoriNama = $latestLaporanItem->kategoriRef?->nama_kategori
                        ?? (is_string($latestLaporanItem->kategori) ? $latestLaporanItem->kategori : ($latestLaporanItem->kategori?->nama_kategori ?? '-'));

                    $latestLaporanData = [
                        'id' => $latestLaporanItem->id,
                        'kode_tiket' => $latestLaporanItem->kode_tiket,
                        'judul' => $latestLaporanItem->judul,
                        'kategori' => $kategoriNama,
                        'status' => $st,
                        'status_label' => ucwords(str_replace('_', ' ', $st)),
                        'submitted_at' => $latestLaporanItem->submitted_at ? $latestLaporanItem->submitted_at->format('Y-m-d H:i:s') : null,
                        'submitted_at_formatted' => $latestLaporanItem->submitted_at ? $latestLaporanItem->submitted_at->format('d M Y, H:i') . ' WIB' : '-',
                    ];
                }

                return [
                    'id' => $desa->id,
                    'nama' => $desa->nama,
                    'upt_id' => $desa->upt_id,
                    'upt_nama' => $desa->upt?->nama ?? 'UPT Imigrasi',
                    'pimpasa_name' => $desa->pimpasa?->name ?? 'Petugas PIMPASA UPT',
                    'lat' => (float) ($desa->lat ?? 3.5952),
                    'lng' => (float) ($desa->lng ?? 98.6722),
                    'status_terkini' => $desa->status_terkini->value ?? $desa->status_terkini,
                    'total_laporan' => $desa->total_laporan,
                    'laporan_diajukan' => $desa->laporan_diajukan,
                    'laporan_diverifikasi' => $desa->laporan_diverifikasi,
                    'laporan_selesai' => $desa->laporan_selesai,
                    'latest_laporan_at' => $desa->latest_laporan_at,
                    'latest_laporan' => $latestLaporanData,
                ];
            });

        $uptList = Upt::orderBy('nama', 'asc')->get(['id', 'nama', 'tipe']);

        return Inertia::render('Kanwil/Peta/Index', [
            'desaMarkers' => $desaMarkers,
            'uptList' => $uptList,
        ]);
    }
}
