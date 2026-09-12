<?php

namespace App\Services;

use App\Enums\StatusLaporan;
use App\Models\Lampiran;
use App\Models\Laporan;
use App\Models\LaporanStatusHistory;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LaporanWorkflowService
{
    /**
     * Memproses pengajuan laporan baru dari Perangkat Desa.
     * Sesuai ARCHITECTURE.md & MECHANISME.md Bab IV.
     */
    public function submitLaporan(array $data, User $user): Laporan
    {
        return DB::transaction(function () use ($data, $user) {
            // 1. Auto-generate Kode Tiket Unik (LP-YYYY-XXXXXX)
            $kodeTiket = $this->generateKodeTiket();

            // 2. Buat Record Laporan
            $kategoriId = $data['kategori_id'] ?? null;

            $laporan = Laporan::create([
                'kode_tiket' => $kodeTiket,
                'desa_id' => $user->desa_id,
                'kategori_id' => $kategoriId,
                'judul' => $data['judul'],
                'tanggal_kejadian' => $data['tanggal_kejadian'],
                'lokasi_detail' => $data['lokasi_detail'],
                'kronologi' => $data['kronologi'],
                'estimasi_jumlah_orang' => $data['estimasi_jumlah_orang'] ?? null,
                'status' => StatusLaporan::DIAJUKAN,
                'submitted_at' => now(),
            ]);

            // 3. Upload & Simpan Lampiran Polymorphic (jika ada)
            if (! empty($data['lampiran'])) {
                foreach ($data['lampiran'] as $file) {
                    if ($file instanceof UploadedFile) {
                        $this->storeLampiran($laporan, $file, $user);
                    }
                }
            }

            // 4. Catat Audit Trail Status (Append-Only)
            LaporanStatusHistory::create([
                'laporan_id' => $laporan->id,
                'status_dari' => null,
                'status_ke' => StatusLaporan::DIAJUKAN->value,
                'actor_id' => $user->id,
                'catatan' => 'Laporan berhasil diajukan oleh Perangkat Desa.',
                'created_at' => now(),
            ]);

            // 5. Kirim Notifikasi (In-App + ntfy + Email) ke SELURUH Petugas PIMPASA UPT/Desa Terkait
            $desaBinaan = \App\Models\DesaBinaan::with(['pimpasa', 'upt'])->find($user->desa_id);
            $targetUptId = $desaBinaan?->upt_id ?? $user->upt_id;
            $pimpasaTargets = collect();

            // A. Jika ada PIMPASA spesifik yang di-assign ke desa
            if ($desaBinaan?->pimpasa) {
                $pimpasaTargets->push($desaBinaan->pimpasa);
            }

            // B. Ambil SELURUH PIMPASA di UPT pembina desa tersebut
            if ($targetUptId) {
                $uptPimpasaList = User::where('role', 'pimpasa')->where('upt_id', $targetUptId)->get();
                $pimpasaTargets = $pimpasaTargets->merge($uptPimpasaList);
            }

            // C. Jika UPT belum terset, ambil seluruh PIMPASA di database
            if ($pimpasaTargets->isEmpty()) {
                $pimpasaTargets = User::where('role', 'pimpasa')->get();
            }

            foreach ($pimpasaTargets->unique('id') as $pimpasaTarget) {
                $pimpasaTarget->notify(
                    new \App\Notifications\LaporanNotification(
                        title: 'Pengajuan Laporan Baru',
                        message: "Desa " . ($desaBinaan?->nama ?? 'Binaan') . " mengajukan laporan baru: {$laporan->kode_tiket}.",
                        type: 'info',
                        url: "/pimpasa/verifikasi/{$laporan->id}",
                        laporanId: $laporan->id,
                        kodeTiket: $laporan->kode_tiket
                    )
                );
            }

            // Broadcast Notifikasi Realtime ke Admin Kanwil
            $kanwilUsers = User::where('role', 'kanwil')->get();
            foreach ($kanwilUsers as $kanwilUser) {
                $kanwilUser->notify(
                    new \App\Notifications\LaporanNotification(
                        title: 'Aduan Masuk Baru (Kanwil)',
                        message: "Desa " . ($desaBinaan?->nama ?? 'Binaan') . " (UPT " . ($desaBinaan?->upt?->nama ?? '-') . ") mengajukan aduan baru {$laporan->kode_tiket}.",
                        type: 'info',
                        url: "/kanwil/monitoring/sla-control",
                        laporanId: $laporan->id,
                        kodeTiket: $laporan->kode_tiket
                    )
                );
            }

            return $laporan;
        });
    }

    /**
     * Memproses perbaikan data (re-submit) dari Perangkat Desa ketika status 'minta_perbaikan'.
     */
    public function resubmitLaporan(Laporan $laporan, array $data, User $user): Laporan
    {
        return DB::transaction(function () use ($laporan, $data, $user) {
            $statusAwal = $laporan->status;

            $kategoriId = $data['kategori_id'] ?? $laporan->kategori_id;

            // 1. Update Data Laporan
            $laporan->update([
                'kategori_id' => $kategoriId,
                'judul' => $data['judul'],
                'tanggal_kejadian' => $data['tanggal_kejadian'],
                'lokasi_detail' => $data['lokasi_detail'],
                'kronologi' => $data['kronologi'],
                'estimasi_jumlah_orang' => $data['estimasi_jumlah_orang'] ?? null,
                'status' => StatusLaporan::DIAJUKAN,
            ]);

            // 2. Upload & Simpan Lampiran Baru (jika ada)
            if (! empty($data['lampiran'])) {
                foreach ($data['lampiran'] as $file) {
                    if ($file instanceof UploadedFile) {
                        $this->storeLampiran($laporan, $file, $user);
                    }
                }
            }

            // 3. Catat Audit Trail Re-Submission
            LaporanStatusHistory::create([
                'laporan_id' => $laporan->id,
                'status_dari' => $statusAwal->value ?? $statusAwal,
                'status_ke' => StatusLaporan::DIAJUKAN->value,
                'actor_id' => $user->id,
                'catatan' => 'Perangkat Desa telah melengkapi dan mengirimkan perbaikan data laporan.',
                'created_at' => now(),
            ]);

            // 4. Kirim Notifikasi (In-App + ntfy + Email) ke Petugas PIMPASA
            $desaBinaan = \App\Models\DesaBinaan::with(['pimpasa', 'upt'])->find($user->desa_id);
            $targetUptId = $desaBinaan?->upt_id ?? $user->upt_id;
            $pimpasaTargets = collect();

            if ($desaBinaan?->pimpasa) {
                $pimpasaTargets->push($desaBinaan->pimpasa);
            }

            if ($targetUptId) {
                $uptPimpasaUsers = User::where('role', 'pimpasa')->where('upt_id', $targetUptId)->get();
                $pimpasaTargets = $pimpasaTargets->merge($uptPimpasaUsers);
            }

            if ($pimpasaTargets->isEmpty()) {
                $pimpasaTargets = User::where('role', 'pimpasa')->get();
            }

            foreach ($pimpasaTargets->unique('id') as $pimpasaUser) {
                $pimpasaUser->notify(new \App\Notifications\LaporanNotification(
                    title: 'Perbaikan Data Dikirim Kembali',
                    message: "Perangkat Desa telah melengkapi data perbaikan pada tiket {$laporan->kode_tiket}.",
                    type: 'info',
                    url: "/pimpasa/verifikasi/{$laporan->id}",
                    laporanId: $laporan->id,
                    kodeTiket: $laporan->kode_tiket
                ));
            }

            return $laporan;
        });
    }

    /**
     * Mengambil daftar laporan khusus untuk Desa tertentu dengan filter & paginasi.
     */
    public function getLaporanByDesa(?int $desaId = null, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Laporan::with(['kategori', 'kategoriRef', 'verifikasi', 'tindakLanjut', 'lampiranList']);

        if ($desaId) {
            $query->where('desa_id', $desaId);
        }

        // Filter status
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter kategori
        if (! empty($filters['kategori_id'])) {
            $query->where('kategori_id', $filters['kategori_id']);
        } elseif (! empty($filters['kategori'])) {
            $query->where('kategori_id', $filters['kategori']);
        }

        // Filter Periode Waktu (Bulanan/Tahunan, Rentang Tanggal, Quick Presets)
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
            // Filter bulan
            if (! empty($filters['bulan']) && $filters['bulan'] !== 'all') {
                $query->whereMonth('created_at', $filters['bulan']);
            }
            // Filter tahun
            if (! empty($filters['tahun']) && $filters['tahun'] !== 'all') {
                $query->whereYear('created_at', $filters['tahun']);
            }
        }

        // Filter pencarian kata kunci (search)
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('kode_tiket', 'like', "%{$search}%")
                    ->orWhere('judul', 'like', "%{$search}%")
                    ->orWhere('lokasi_detail', 'like', "%{$search}%")
                    ->orWhereHas('kategoriRef', function ($catQuery) use ($search) {
                        $catQuery->where('nama_kategori', 'like', "%{$search}%")
                            ->orWhere('kode', 'like', "%{$search}%");
                    });
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
    }

    /**
     * Helper untuk meng-generate Kode Tiket unik format LP-YYYY-XXXXXX
     */
    protected function generateKodeTiket(): string
    {
        $year = now()->year;
        $count = Laporan::whereYear('created_at', $year)->withTrashed()->count() + 1;
        
        do {
            $sequence = str_pad((string) $count, 6, '0', STR_PAD_LEFT);
            $kode = "LP-{$year}-{$sequence}";
            $exists = Laporan::where('kode_tiket', $kode)->withTrashed()->exists();
            if ($exists) {
                $count++;
            }
        } while ($exists);

        return $kode;
    }

    /**
     * Helper untuk mengunggah file lampiran polymorphic.
     */
    protected function storeLampiran(Laporan $laporan, UploadedFile $file, User $user): Lampiran
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = Str::uuid().'.'.$extension;
        $path = $file->storeAs('lampiran/laporan/'.$laporan->id, $filename, 'public');

        return $laporan->lampiranList()->create([
            'path' => $path,
            'nama_file_asli' => $file->getClientOriginalName(),
            'tipe_file' => $extension,
            'ukuran_bytes' => $file->getSize(),
            'uploaded_by' => $user->id,
            'created_at' => now(),
        ]);
    }
}
