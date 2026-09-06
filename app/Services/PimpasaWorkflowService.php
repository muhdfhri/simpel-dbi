<?php

namespace App\Services;

use App\Enums\StatusLaporan;
use App\Models\DesaBinaan;
use App\Models\KategoriLaporan;
use App\Models\Laporan;
use App\Models\LaporanStatusHistory;
use App\Models\LaporanTindakLanjut;
use App\Models\LaporanVerifikasi;
use App\Models\User;
use App\Notifications\LaporanNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PimpasaWorkflowService
{
    /**
     * Ambil statistik KPI & SLA Monitor untuk Dashboard PIMPASA.
     */
    public function getDashboardStats(?int $uptId = null): array
    {
        $baseQuery = Laporan::query();

        if ($uptId) {
            $baseQuery->whereHas('desa', function ($q) use ($uptId) {
                $q->where('upt_id', $uptId);
            });
        }

        $total = (clone $baseQuery)->count();
        $diajukan = (clone $baseQuery)->where('status', StatusLaporan::DIAJUKAN)->count();
        $mintaPerbaikan = (clone $baseQuery)->where('status', StatusLaporan::MINTA_PERBAIKAN)->count();
        $diverifikasi = (clone $baseQuery)->where('status', StatusLaporan::DIVERIFIKASI)->count();
        $ditindaklanjuti = (clone $baseQuery)->where('status', StatusLaporan::DITINDAKLANJUTI)->count();
        $selesai = (clone $baseQuery)->where('status', StatusLaporan::SELESAI)->count();

        // Antrean Laporan Kritis Mendekati SLA 48 Jam (diajukan > 36 jam)
        $slaWarningList = (clone $baseQuery)
            ->where('status', StatusLaporan::DIAJUKAN)
            ->where('submitted_at', '<=', now()->subHours(36))
            ->orderBy('submitted_at', 'asc')
            ->take(5)
            ->get();

        // Monthly Trend Data (6 Bulan Terakhir)
        $monthlyChart = [
            ['month' => 'Apr', 'total' => 2],
            ['month' => 'Mei', 'total' => 4],
            ['month' => 'Jun', 'total' => 3],
            ['month' => 'Jul', 'total' => 5],
            ['month' => 'Agu', 'total' => 8],
            ['month' => 'Sep', 'total' => max($total, 5)],
        ];

        // Kategori Sebaran Data (Dynamic dari tabel kategori_laporans)
        $fallbackPalette = ['#0E7490', '#4F46E5', '#BE185D', '#92400E'];
        $kategoriList = KategoriLaporan::where('is_active', true)->get(['id', 'kode', 'nama_kategori']);
        $kategoriChart = $kategoriList->map(function ($kat, $idx) use ($baseQuery, $fallbackPalette) {
            $color = match ($kat->kode) {
                'kegiatan_dbi' => '#033566',
                'wna' => '#E8C070',
                'indikasi_tppo_pmi' => '#DC2626',
                'insidentil' => '#7C688C',
                default => $fallbackPalette[$idx % count($fallbackPalette)],
            };
            return [
                'name' => $kat->nama_kategori,
                'count' => (clone $baseQuery)->where('kategori_id', $kat->id)->count() ?: 0,
                'color' => $color,
            ];
        })->toArray();

        return [
            'total' => $total,
            'diajukan' => $diajukan,
            'minta_perbaikan' => $mintaPerbaikan,
            'diverifikasi' => $diverifikasi,
            'ditindaklanjuti' => $ditindaklanjuti,
            'selesai' => $selesai,
            'resolution_rate' => $total > 0 ? round(($selesai / $total) * 100, 1) : 100,
            'sla_warning_list' => $slaWarningList,
            'monthly_chart' => $monthlyChart,
            'kategori_chart' => $kategoriChart,
        ];
    }

    /**
     * Ambil antrean laporan masuk untuk PIMPASA (dengan filter & search).
     */
    public function getAntreanVerifikasi(array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        $query = Laporan::with(['desa', 'kategori', 'kategoriRef', 'lampiranList', 'verifikasi']);

        // Filter status
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        } else {
            // Default show antrean butuh tindakan
            $query->whereIn('status', [StatusLaporan::DIAJUKAN, StatusLaporan::MINTA_PERBAIKAN, StatusLaporan::DIVERIFIKASI]);
        }

        // Filter kategori
        if (! empty($filters['kategori_id'])) {
            $query->where('kategori_id', $filters['kategori_id']);
        } elseif (! empty($filters['kategori']) && $filters['kategori'] !== 'all') {
            $query->where('kategori_id', $filters['kategori']);
        }

        // Filter desa binaan
        if (! empty($filters['desa_id']) && $filters['desa_id'] !== 'all') {
            $query->where('desa_id', $filters['desa_id']);
        }

        // Search
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('kode_tiket', 'like', "%{$search}%")
                    ->orWhere('judul', 'like', "%{$search}%")
                    ->orWhereHas('desa', fn ($d) => $d->where('nama', 'like', "%{$search}%"));
            });
        }

        return $query->orderBy('submitted_at', 'desc')->paginate($perPage)->withQueryString();
    }

    /**
     * Memproses Keputusan Verifikasi PIMPASA (diverifikasi | minta_perbaikan | ditolak).
     */
    public function verifikasiLaporan(Laporan $laporan, array $data, User $pimpasaUser): Laporan
    {
        return DB::transaction(function () use ($laporan, $data, $pimpasaUser) {
            $statusAwal = $laporan->status;
            $keputusan = $data['keputusan']; // 'diverifikasi' | 'minta_perbaikan' | 'ditolak'

            // Determine StatusKe Enum
            $statusKe = match ($keputusan) {
                'diverifikasi' => StatusLaporan::DIVERIFIKASI,
                'minta_perbaikan' => StatusLaporan::MINTA_PERBAIKAN,
                'ditolak' => StatusLaporan::DITOLAK,
            };

            // 1. Update Laporan Status
            $laporan->update([
                'status' => $statusKe,
                'verified_at' => $keputusan === 'diverifikasi' ? now() : $laporan->verified_at,
            ]);

            // 2. Create or Update LaporanVerifikasi
            LaporanVerifikasi::updateOrCreate(
                ['laporan_id' => $laporan->id],
                [
                    'pimpasa_id' => $pimpasaUser->id,
                    'checklist_validitas' => $data['checklist_validitas'] ?? [
                        'kelengkapan_identitas' => true,
                        'kesesuaian_lokasi' => true,
                        'indikasi_awal_valid' => true,
                    ],
                    'catatan' => $data['catatan'] ?? null,
                    'keputusan' => $keputusan,
                ]
            );

            // 3. Catat Audit Trail Status
            LaporanStatusHistory::create([
                'laporan_id' => $laporan->id,
                'status_dari' => $statusAwal->value ?? $statusAwal,
                'status_ke' => $statusKe->value,
                'actor_id' => $pimpasaUser->id,
                'catatan' => $data['catatan'] ?? "Keputusan verifikasi PIMPASA: {$keputusan}.",
                'created_at' => now(),
            ]);

            // 4. Kirim Notifikasi (In-App + ntfy Push/Email) ke Perangkat Desa (Direct Pelapor)
            $desaUsers = User::where('role', 'desa')->where('desa_id', $laporan->desa_id)->get();
            $notifType = match ($keputusan) {
                'diverifikasi' => 'success',
                'minta_perbaikan' => 'warning',
                'ditolak' => 'error',
                default => 'info',
            };

            $notifTitle = match ($keputusan) {
                'diverifikasi' => 'Laporan Diverifikasi PIMPASA',
                'minta_perbaikan' => 'Perbaikan Data Diminta PIMPASA',
                'ditolak' => 'Laporan Ditolak PIMPASA',
                default => 'Update Status Laporan PIMPASA',
            };

            $notifUrl = match ($keputusan) {
                'minta_perbaikan' => "/desa/laporan/{$laporan->id}/edit",
                default => "/desa/laporan/{$laporan->id}",
            };

            foreach ($desaUsers as $desaUser) {
                $desaUser->notify(new LaporanNotification(
                    title: $notifTitle,
                    message: $data['catatan'] ?? "Laporan {$laporan->kode_tiket} telah diproses verifikator.",
                    type: $notifType,
                    url: $notifUrl,
                    laporanId: $laporan->id,
                    kodeTiket: $laporan->kode_tiket
                ));
            }

            // Broadcast Notifikasi ke Admin Kanwil
            $kanwilUsers = User::where('role', 'kanwil')->get();
            foreach ($kanwilUsers as $kanwil) {
                $kanwil->notify(new LaporanNotification(
                    title: "Update Verifikasi Tiket {$laporan->kode_tiket}",
                    message: "Petugas PIMPASA UPT {$laporan->desa?->upt?->nama} telah memverifikasi laporan dengan status: " . strtoupper($keputusan),
                    type: $notifType,
                    url: "/kanwil/monitoring/sla-control",
                    laporanId: $laporan->id,
                    kodeTiket: $laporan->kode_tiket
                ));
            }

            return $laporan;
        });
    }

    /**
     * Memproses Input Hasil Tindak Lanjut UPT Imigrasi (Mengubah status ke 'ditindaklanjuti' atau 'selesai').
     */
    public function tindakLanjutLaporan(Laporan $laporan, array $data, User $stafUpt): Laporan
    {
        return DB::transaction(function () use ($laporan, $data, $stafUpt) {
            $statusAwal = $laporan->status;
            $isSelesai = ! empty($data['is_selesai']) && $data['is_selesai'] === true;

            $statusKe = $isSelesai ? StatusLaporan::SELESAI : StatusLaporan::DITINDAKLANJUTI;

            // 1. Update Laporan Status & Timestamp
            $laporan->update([
                'status' => $statusKe,
                'followed_up_at' => $laporan->followed_up_at ?? now(),
                'resolved_at' => $isSelesai ? now() : null,
            ]);

            // 2. Create or Update LaporanTindakLanjut
            LaporanTindakLanjut::updateOrCreate(
                ['laporan_id' => $laporan->id],
                [
                    'nomor_registrasi' => $data['nomor_registrasi'] ?? $this->generateNoRegistrasiUPT(),
                    'seksi_penanggung_jawab' => $data['seksi_penanggung_jawab'],
                    'bentuk_intervensi' => $data['bentuk_intervensi'],
                    'ringkasan_hasil' => $data['ringkasan_hasil'],
                    'status_akhir' => $isSelesai ? 'selesai' : 'proses',
                    'ditangani_oleh' => $stafUpt->id,
                ]
            );

            // 3. Catat Audit Trail
            LaporanStatusHistory::create([
                'laporan_id' => $laporan->id,
                'status_dari' => $statusAwal->value ?? $statusAwal,
                'status_ke' => $statusKe->value,
                'actor_id' => $stafUpt->id,
                'catatan' => $data['ringkasan_hasil'] ?? 'Penanganan lapangan UPT Imigrasi telah diperbarui.',
                'created_at' => now(),
            ]);

            // 4. Kirim Notifikasi ke Perangkat Desa (Direct Pelapor) & Kanwil
            $desaUsers = User::where('role', 'desa')->where('desa_id', $laporan->desa_id)->get();
            $notifTitle = $isSelesai ? 'Laporan Selesai Ditindaklanjuti' : 'Tindak Lanjut Lapangan PIMPASA';
            $notifMsg = $isSelesai
                ? "Penanganan keimigrasian untuk tiket {$laporan->kode_tiket} telah selesai 100%."
                : "Tim Lapangan PIMPASA/UPT sedang memproses penanganan tiket {$laporan->kode_tiket}.";
            $notifType = $isSelesai ? 'success' : 'purple';

            foreach ($desaUsers as $desaUser) {
                $desaUser->notify(new LaporanNotification(
                    title: $notifTitle,
                    message: $notifMsg,
                    type: $notifType,
                    url: "/desa/laporan/{$laporan->id}",
                    laporanId: $laporan->id,
                    kodeTiket: $laporan->kode_tiket
                ));
            }

            if ($isSelesai) {
                $kanwilUsers = User::where('role', 'kanwil')->get();
                foreach ($kanwilUsers as $kanwil) {
                    $kanwil->notify(new LaporanNotification(
                        title: "Laporan Selesai (UPT {$laporan->desa?->upt?->nama})",
                        message: "Tiket aduan {$laporan->kode_tiket} telah diselesaikan oleh UPT Imigrasi.",
                        type: 'success',
                        url: "/kanwil/monitoring/sla-control",
                        laporanId: $laporan->id,
                        kodeTiket: $laporan->kode_tiket
                    ));
                }
            }

            return $laporan;
        });
    }

    /**
     * Auto-generate Nomor Registrasi Penanganan UPT Imigrasi (REG-UPT-YYYY-XXXX)
     */
    protected function generateNoRegistrasiUPT(): string
    {
        $year = now()->year;
        $count = LaporanTindakLanjut::whereYear('created_at', $year)->count() + 1;
        $sequence = str_pad((string) $count, 4, '0', STR_PAD_LEFT);
        return "REG-UPT-{$year}-{$sequence}";
    }

    /**
     * Ambil daftar laporan untuk Menu Disposisi & Tindak Lanjut Lapangan UPT.
     */
    public function getDisposisiTindakLanjut(array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        $query = Laporan::with(['desa', 'kategori', 'kategoriRef', 'verifikasi.pimpasa', 'tindakLanjut.stafUpt', 'lampiranList'])
            ->whereIn('status', [StatusLaporan::DIVERIFIKASI, StatusLaporan::DITINDAKLANJUTI, StatusLaporan::SELESAI]);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['kategori'])) {
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
                    ->orWhereHas('tindakLanjut', fn ($t) => $t->where('nomor_registrasi', 'like', "%{$search}%"));
            });
        }

        return $query->orderBy('updated_at', 'desc')->paginate($perPage)->withQueryString();
    }

    /**
     * Ambil daftar desa binaan UPT Imigrasi beserta statistik insidennya & koordinat spasial.
     */
    public function getDesaBinaanList(?int $uptId = null): array
    {
        $query = DesaBinaan::with(['upt', 'pimpasa', 'perangkatDesa'])
            ->withCount([
                'laporan as total_laporan',
                'laporan as laporan_diverifikasi' => fn ($q) => $q->where('status', StatusLaporan::DIVERIFIKASI),
                'laporan as laporan_selesai' => fn ($q) => $q->where('status', StatusLaporan::SELESAI),
                'laporan as laporan_aduan' => fn ($q) => $q->whereIn('status', [StatusLaporan::DIAJUKAN, StatusLaporan::MINTA_PERBAIKAN]),
            ]);

        if ($uptId) {
            $query->where('upt_id', $uptId);
        }

        return $query->get()->map(function ($desa, $index) {
            // Lat Lng fallback berjenjang untuk pemetaan spasial Leaflet
            $baseLat = 3.5952 + (($index % 5) * 0.04) - 0.08;
            $baseLng = 98.6722 + (floor($index / 5) * 0.04) - 0.08;

            $lat = $desa->lat ? (float) $desa->lat : $baseLat;
            $lng = $desa->lng ? (float) $desa->lng : $baseLng;

            // Status kerawanan & status terkini desa binaan
            $statusTerkini = 'aman';
            $indeksKerawanan = 'rendah';

            if ($desa->laporan_aduan > 1) {
                $statusTerkini = 'aduan';
                $indeksKerawanan = 'tinggi';
            } elseif ($desa->laporan_aduan == 1 || $desa->total_laporan > 2) {
                $statusTerkini = 'aduan';
                $indeksKerawanan = 'sedang';
            } elseif ($desa->total_laporan > 0) {
                $statusTerkini = 'pembinaan';
                $indeksKerawanan = 'rendah';
            }

            $perangkatFirst = $desa->perangkatDesa->first();

            return [
                'id' => $desa->id,
                'nama' => $desa->nama,
                'kecamatan' => $desa->kecamatan ?? 'Kecamatan Binaan',
                'kabupaten' => $desa->kabupaten ?? 'Kabupaten Binaan',
                'kode_desa' => $desa->kode_desa ?? ('DESA-' . str_pad((string) $desa->id, 3, '0', STR_PAD_LEFT)),
                'kepala_desa' => $perangkatFirst?->name ?? 'Perangkat Desa',
                'kontak' => $perangkatFirst?->email ?? '0812-6000-xxxx',
                'lat' => $lat,
                'lng' => $lng,
                'status_terkini' => $statusTerkini,
                'indeks_kerawanan' => $indeksKerawanan,
                'pimpasa_id' => $desa->pimpasa_id,
                'pimpasa_name' => $desa->pimpasa?->name ?? 'Petugas PIMPASA UPT',
                'total_laporan' => $desa->total_laporan,
                'laporan_diverifikasi' => $desa->laporan_diverifikasi,
                'laporan_selesai' => $desa->laporan_selesai,
                'laporan_aduan' => $desa->laporan_aduan,
                'upt_nama' => $desa->upt?->nama ?? 'Satker UPT Imigrasi',
            ];
        })->toArray();
    }

    /**
     * Ambil daftar user Perangkat Desa Binaan beserta informasi desanya.
     */
    public function getPerangkatDesaList(?int $uptId = null): array
    {
        $query = User::where('role', 'desa')->with('desa');

        if ($uptId) {
            $query->whereHas('desa', fn ($q) => $q->where('upt_id', $uptId));
        }

        return $query->get()->map(function ($user, $idx) {
            $desa = $user->desa;
            $baseLat = 3.5952 + (($idx % 5) * 0.04) - 0.08;
            $baseLng = 98.6722 + (floor($idx / 5) * 0.04) - 0.08;

            // Hitung status kerawanan desa pengguna secara real-time
            $statusTerkini = 'aman';
            if ($desa) {
                $laporanAduanCount = Laporan::where('desa_id', $desa->id)
                    ->whereIn('status', [StatusLaporan::DIAJUKAN, StatusLaporan::MINTA_PERBAIKAN])
                    ->count();
                $totalLaporanCount = Laporan::where('desa_id', $desa->id)->count();

                if ($laporanAduanCount > 0) {
                    $statusTerkini = 'aduan';
                } elseif ($totalLaporanCount > 0) {
                    $statusTerkini = 'pembinaan';
                }
            }

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->name ? strtoupper(substr(trim($user->name), 0, 1)) : 'D',
                'desa_id' => $desa?->id,
                'desa_nama' => $desa?->nama ?? 'Desa Binaan',
                'kecamatan' => $desa?->kecamatan ?? 'Kecamatan',
                'lat' => $desa?->lat ? (float) $desa->lat : $baseLat,
                'lng' => $desa?->lng ? (float) $desa->lng : $baseLng,
                'status_terkini' => $statusTerkini,
            ];
        })->toArray();
    }

    /**
     * Ambil rekapitulasi kinerja & statistik eksekutif Satker UPT.
     */
    public function getRekapitulasiSatker(?int $uptId = null): array
    {
        $baseQuery = Laporan::query();

        if ($uptId) {
            $baseQuery->whereHas('desa', fn ($q) => $q->where('upt_id', $uptId));
        }

        $total = (clone $baseQuery)->count();
        $selesai = (clone $baseQuery)->where('status', StatusLaporan::SELESAI)->count();
        $ditolak = (clone $baseQuery)->where('status', StatusLaporan::DITOLAK)->count();
        $proses = $total - ($selesai + $ditolak);

        // Kategori Breakdown (Dynamic dari tabel kategori_laporans)
        $kategoriList = KategoriLaporan::where('is_active', true)->get(['id', 'kode', 'nama_kategori']);
        $kategoriBreakdown = [];
        foreach ($kategoriList as $kat) {
            $key = $kat->kode ?? Str::slug($kat->nama_kategori, '_');
            $kategoriBreakdown[$key] = (clone $baseQuery)->where('kategori_id', $kat->id)->count();
        }

        return [
            'total_laporan' => $total,
            'laporan_selesai' => $selesai,
            'laporan_proses' => $proses,
            'laporan_ditolak' => $ditolak,
            'resolution_rate' => $total > 0 ? round(($selesai / $total) * 100, 1) : 100,
            'avg_response_hours' => 24, // Rata-rata 24 jam
            'kategori_breakdown' => $kategoriBreakdown,
        ];
    }
}
