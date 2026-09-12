<?php

namespace App\Http\Controllers\Kanwil;

use App\Http\Controllers\Controller;
use App\Models\Upt;
use App\Services\MonitoringService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MonitoringController extends Controller
{
    /**
     * Sub-Menu 1: Pusat Kendali SLA & Eskalasi Insiden
     */
    public function slaControl(Request $request, MonitoringService $monitoringService): Response
    {
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');

        return Inertia::render('Kanwil/Monitoring/SlaControl', [
            'kpiData' => $monitoringService->getKpiMetrics(),
            'slaIncidents' => $monitoringService->getSlaIncidents($tanggalMulai, $tanggalSelesai),
            'uptListOptions' => Upt::select('id', 'nama')->get(),
            'filters' => $request->only(['search', 'upt_id', 'sla_status', 'tanggal_mulai', 'tanggal_selesai']),
        ]);
    }

    /**
     * Sub-Menu 3: Scorecard Kepatuhan Satker UPT Imigrasi
     */
    public function uptScorecard(Request $request, MonitoringService $monitoringService): Response
    {
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');

        return Inertia::render('Kanwil/Monitoring/UptScorecard', [
            'kpiData' => $monitoringService->getKpiMetrics(),
            'uptScorecard' => $monitoringService->getUptScorecards($tanggalMulai, $tanggalSelesai),
            'uptListOptions' => Upt::select('id', 'nama')->get(),
            'filters' => $request->only(['search', 'upt_id', 'status', 'tanggal_mulai', 'tanggal_selesai']),
        ]);
    }


    /**
     * Export Laporan Kendali SLA ke Excel (.xlsx)
     */
    public function exportSlaExcel(Request $request, MonitoringService $monitoringService)
    {
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalSelesai = $request->query('tanggal_selesai');
        $incidents = $monitoringService->getSlaIncidents($tanggalMulai, $tanggalSelesai);
        
        $uptId = $request->query('upt_id');
        $slaStatus = $request->query('sla_status');
        $search = $request->query('search');

        if ($uptId && $uptId !== 'all') {
            $targetUpt = Upt::find($uptId);
            if ($targetUpt) {
                $incidents = array_filter($incidents, fn($i) => str_contains(strtolower($i['upt_nama']), strtolower($targetUpt->nama)));
            }
        }

        if ($slaStatus && $slaStatus !== 'all') {
            $incidents = array_filter($incidents, fn($i) => $i['sla_status'] === $slaStatus);
        }

        if ($search) {
            $q = strtolower($search);
            $incidents = array_filter($incidents, fn($i) => 
                str_contains(strtolower($i['nomor_tiket']), $q) ||
                str_contains(strtolower($i['judul']), $q) ||
                str_contains(strtolower($i['desa_nama']), $q) ||
                str_contains(strtolower($i['upt_nama']), $q) ||
                str_contains(strtolower($i['kategori']), $q)
            );
        }

        $filename = 'laporan-kendali-sla-' . date('Y-m-d-His') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SlaControlExport($incidents),
            $filename,
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    /**
     * Export Laporan Kendali SLA ke PDF (.pdf)
     */
    public function exportSlaPdf(Request $request, MonitoringService $monitoringService)
    {
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalSelesai = $request->query('tanggal_selesai');
        $incidents = $monitoringService->getSlaIncidents($tanggalMulai, $tanggalSelesai);
        
        $uptId = $request->query('upt_id');
        $slaStatus = $request->query('sla_status');
        $search = $request->query('search');

        $filterUptNama = 'Semua Satker UPT Imigrasi';
        if ($uptId && $uptId !== 'all') {
            $targetUpt = Upt::find($uptId);
            if ($targetUpt) {
                $filterUptNama = $targetUpt->nama;
                $incidents = array_filter($incidents, fn($i) => str_contains(strtolower($i['upt_nama']), strtolower($targetUpt->nama)));
            }
        }

        $filterSlaStatusNama = 'Semua Status SLA';
        if ($slaStatus && $slaStatus !== 'all') {
            $filterSlaStatusNama = match($slaStatus) {
                'terlambat' => 'TERLAMBAT (> 24 Jam)',
                'peringatan' => 'PERINGATAN (Sisa < 6 Jam)',
                'tepat_waktu' => 'TEPAT WAKTU',
                default => $slaStatus
            };
            $incidents = array_filter($incidents, fn($i) => $i['sla_status'] === $slaStatus);
        }

        if ($search) {
            $q = strtolower($search);
            $incidents = array_filter($incidents, fn($i) => 
                str_contains(strtolower($i['nomor_tiket']), $q) ||
                str_contains(strtolower($i['judul']), $q) ||
                str_contains(strtolower($i['desa_nama']), $q) ||
                str_contains(strtolower($i['upt_nama']), $q) ||
                str_contains(strtolower($i['kategori']), $q)
            );
        }

        $pimpinanNama = $request->user()?->name ?? 'Administrator Kanwil';

        $periodeText = 'Semua Periode';
        if ($tanggalMulai && $tanggalSelesai) {
            $periodeText = \Carbon\Carbon::parse($tanggalMulai)->locale('id')->isoFormat('D MMM YYYY') . ' s.d. ' . \Carbon\Carbon::parse($tanggalSelesai)->locale('id')->isoFormat('D MMM YYYY');
        } elseif ($tanggalMulai) {
            $periodeText = \Carbon\Carbon::parse($tanggalMulai)->locale('id')->isoFormat('D MMM YYYY') . ' s.d. Selesai';
        } elseif ($tanggalSelesai) {
            $periodeText = 's.d. ' . \Carbon\Carbon::parse($tanggalSelesai)->locale('id')->isoFormat('D MMM YYYY');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.sla-control', [
            'incidents' => array_values($incidents),
            'filterUptNama' => $filterUptNama,
            'filterSlaStatusNama' => $filterSlaStatusNama,
            'pimpinanNama' => $pimpinanNama,
            'tanggalCetak' => date('d F Y, H:i'),
            'periodeText' => $periodeText,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-kendali-sla-' . date('Y-m-d-His') . '.pdf');
    }

    /**
     * Sub-Menu Executive Monitoring: Monitoring Pembinaan Desa
     */
    public function kegiatanPembinaan(Request $request, MonitoringService $monitoringService): Response
    {
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalSelesai = $request->query('tanggal_selesai');

        return Inertia::render('Kanwil/Monitoring/KegiatanPembinaan', [
            'kpiMetrics' => $monitoringService->getKegiatanKpiMetrics(),
            'kegiatanList' => $monitoringService->getKegiatanPembinaanData($tanggalMulai, $tanggalSelesai),
            'uptListOptions' => Upt::select('id', 'nama')->get(),
            'filters' => [
                'search' => $request->query('search'),
                'upt_id' => $request->query('upt_id'),
                'jenis' => $request->query('jenis'),
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_selesai' => $tanggalSelesai,
            ],
        ]);
    }

    /**
     * Export Laporan Monitoring Pembinaan Desa ke Excel (.xlsx)
     */
    public function exportKegiatanExcel(Request $request, MonitoringService $monitoringService)
    {
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalSelesai = $request->query('tanggal_selesai');

        $kegiatan = $monitoringService->getKegiatanPembinaanData($tanggalMulai, $tanggalSelesai);

        $uptId = $request->query('upt_id');
        $jenis = $request->query('jenis');
        $search = $request->query('search');

        if ($uptId && $uptId !== 'all') {
            $targetUpt = Upt::find($uptId);
            if ($targetUpt) {
                $kegiatan = array_filter($kegiatan, fn($k) => str_contains(strtolower($k['upt_nama']), strtolower($targetUpt->nama)));
            }
        }

        if ($jenis && $jenis !== 'all') {
            $kegiatan = array_filter($kegiatan, fn($k) => str_contains(strtolower($k['jenis_pembinaan']), strtolower($jenis)));
        }

        if ($search) {
            $q = strtolower($search);
            $kegiatan = array_filter($kegiatan, fn($k) => 
                str_contains(strtolower($k['judul']), $q) ||
                str_contains(strtolower($k['desa_nama']), $q) ||
                str_contains(strtolower($k['upt_nama']), $q) ||
                str_contains(strtolower($k['pimpasa_nama']), $q) ||
                str_contains(strtolower($k['lokasi']), $q)
            );
        }

        $filename = 'rekapitulasi-pembinaan-desa-' . date('Y-m-d-His') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\KegiatanKanwilExport(array_values($kegiatan)),
            $filename,
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    /**
     * Export Laporan Monitoring Pembinaan Desa ke PDF (.pdf)
     */
    public function exportKegiatanPdf(Request $request, MonitoringService $monitoringService)
    {
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalSelesai = $request->query('tanggal_selesai');

        $kegiatan = $monitoringService->getKegiatanPembinaanData($tanggalMulai, $tanggalSelesai);

        $uptId = $request->query('upt_id');
        $jenis = $request->query('jenis');
        $search = $request->query('search');

        $filterUptNama = 'Semua Satker UPT Imigrasi';
        if ($uptId && $uptId !== 'all') {
            $targetUpt = Upt::find($uptId);
            if ($targetUpt) {
                $filterUptNama = $targetUpt->nama;
                $kegiatan = array_filter($kegiatan, fn($k) => str_contains(strtolower($k['upt_nama']), strtolower($targetUpt->nama)));
            }
        }

        if ($jenis && $jenis !== 'all') {
            $kegiatan = array_filter($kegiatan, fn($k) => str_contains(strtolower($k['jenis_pembinaan']), strtolower($jenis)));
        }

        if ($search) {
            $q = strtolower($search);
            $kegiatan = array_filter($kegiatan, fn($k) => 
                str_contains(strtolower($k['judul']), $q) ||
                str_contains(strtolower($k['desa_nama']), $q) ||
                str_contains(strtolower($k['upt_nama']), $q) ||
                str_contains(strtolower($k['pimpasa_nama']), $q) ||
                str_contains(strtolower($k['lokasi']), $q)
            );
        }

        $pimpinanNama = $request->user()?->name ?? 'Administrator Kanwil';

        $periodeText = 'Semua Periode Data';
        if ($tanggalMulai && $tanggalSelesai) {
            $periodeText = date('d/m/Y', strtotime($tanggalMulai)) . ' s/d ' . date('d/m/Y', strtotime($tanggalSelesai));
        } elseif ($tanggalMulai) {
            $periodeText = 'Mulai ' . date('d/m/Y', strtotime($tanggalMulai));
        } elseif ($tanggalSelesai) {
            $periodeText = 'Sampai ' . date('d/m/Y', strtotime($tanggalSelesai));
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.kegiatan-kanwil', [
            'kegiatanList' => array_values($kegiatan),
            'filterUptNama' => $filterUptNama,
            'pimpinanNama' => $pimpinanNama,
            'tanggalCetak' => date('d F Y, H:i'),
            'periodeText' => $periodeText,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('rekapitulasi-pembinaan-desa-' . date('Y-m-d-His') . '.pdf');
    }

    /**
     * Export Scorecard Kepatuhan UPT ke Excel (.xlsx)
     */
    public function exportScorecardExcel(Request $request, MonitoringService $monitoringService)
    {
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalSelesai = $request->query('tanggal_selesai');
        $scorecard = $monitoringService->getUptScorecards($tanggalMulai, $tanggalSelesai);

        $status = $request->query('status');
        $tipe = $request->query('tipe');
        $search = $request->query('search');

        if ($status && $status !== 'all') {
            $scorecard = array_filter($scorecard, fn($u) => $u['status_kepatuhan'] === $status);
        }

        if ($tipe && $tipe !== 'all') {
            $scorecard = array_filter($scorecard, fn($u) => str_contains(strtolower($u['tipe']), strtolower($tipe)));
        }

        if ($search) {
            $q = strtolower($search);
            $scorecard = array_filter($scorecard, fn($u) => 
                str_contains(strtolower($u['nama']), $q) ||
                str_contains(strtolower($u['tipe']), $q) ||
                str_contains(strtolower($u['status_kepatuhan']), $q)
            );
        }

        $filename = 'scorecard-kepatuhan-upt-' . date('Y-m-d-His') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\UptScorecardExport(array_values($scorecard)),
            $filename,
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    /**
     * Export Scorecard Kepatuhan UPT ke PDF (.pdf)
     */
    public function exportScorecardPdf(Request $request, MonitoringService $monitoringService)
    {
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalSelesai = $request->query('tanggal_selesai');
        $scorecard = $monitoringService->getUptScorecards($tanggalMulai, $tanggalSelesai);

        $status = $request->query('status');
        $tipe = $request->query('tipe');
        $search = $request->query('search');

        $filterStatusNama = 'Semua Status Kepatuhan';
        if ($status && $status !== 'all') {
            $filterStatusNama = $status;
            $scorecard = array_filter($scorecard, fn($u) => $u['status_kepatuhan'] === $status);
        }

        if ($tipe && $tipe !== 'all') {
            $scorecard = array_filter($scorecard, fn($u) => str_contains(strtolower($u['tipe']), strtolower($tipe)));
        }

        if ($search) {
            $q = strtolower($search);
            $scorecard = array_filter($scorecard, fn($u) => 
                str_contains(strtolower($u['nama']), $q) ||
                str_contains(strtolower($u['tipe']), $q) ||
                str_contains(strtolower($u['status_kepatuhan']), $q)
            );
        }

        $pimpinanNama = $request->user()?->name ?? 'Administrator Kanwil';

        $periodeText = 'Semua Periode';
        if ($tanggalMulai && $tanggalSelesai) {
            $periodeText = \Carbon\Carbon::parse($tanggalMulai)->locale('id')->isoFormat('D MMM YYYY') . ' s.d. ' . \Carbon\Carbon::parse($tanggalSelesai)->locale('id')->isoFormat('D MMM YYYY');
        } elseif ($tanggalMulai) {
            $periodeText = \Carbon\Carbon::parse($tanggalMulai)->locale('id')->isoFormat('D MMM YYYY') . ' s.d. Selesai';
        } elseif ($tanggalSelesai) {
            $periodeText = 's.d. ' . \Carbon\Carbon::parse($tanggalSelesai)->locale('id')->isoFormat('D MMM YYYY');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.upt-scorecard', [
            'scorecards' => array_values($scorecard),
            'filterStatusNama' => $filterStatusNama,
            'pimpinanNama' => $pimpinanNama,
            'user' => $request->user(),
            'generated_at' => date('d F Y, H:i'),
            'tanggalCetak' => date('d F Y, H:i'),
            'periodeText' => $periodeText,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('scorecard-kepatuhan-upt-' . date('Y-m-d-His') . '.pdf');
    }

    /**
     * Kirim Teguran High-Priority SLA dari Kanwil ke Petugas PIMPASA Desa Binaan
     */
    public function tegurSla(Request $request, \App\Models\Laporan $laporan)
    {
        $desa = $laporan->desa;
        $targetUptId = $desa?->upt_id;

        // Target Notifikasi: Seluruh Petugas PIMPASA di bawah UPT Pembina Desa tersebut (kolektif/sama rata)
        $pimpasaTargets = \App\Models\User::where('role', 'pimpasa')
            ->when($targetUptId, function ($query) use ($targetUptId) {
                $query->where(function ($q) use ($targetUptId) {
                    $q->where('upt_id', $targetUptId)->orWhereNull('upt_id');
                });
            })
            ->get();

        // Fallback jika belum ada yang terikat UPT: Ambil seluruh Petugas PIMPASA di sistem
        if ($pimpasaTargets->isEmpty()) {
            $pimpasaTargets = \App\Models\User::where('role', 'pimpasa')->get();
        }

        if ($pimpasaTargets->isEmpty()) {
            return back()->with('error', 'Petugas PIMPASA penanggung jawab UPT ini belum terdaftar di dalam sistem.');
        }

        $title = "Teguran SLA Kanwil — Tiket {$laporan->kode_tiket}";
        $message = "PERINGATAN SLA KANWIL: Laporan {$laporan->kode_tiket} di Desa " . ($desa?->nama ?? 'Binaan') . " telah diajukan > 24 Jam dan belum diverifikasi. Segera tindak lanjuti!";

        $laporan->increment('jumlah_teguran');

        \Illuminate\Support\Facades\Notification::send(
            $pimpasaTargets->unique('id'),
            new \App\Notifications\LaporanNotification(
                title: $title,
                message: $message,
                type: 'urgent',
                url: "/pimpasa/verifikasi/{$laporan->id}",
                laporanId: $laporan->id,
                kodeTiket: $laporan->kode_tiket
            )
        );

        return back()->with('success', "Teguran SLA untuk tiket {$laporan->kode_tiket} berhasil dikirimkan via In-App, Email & Push Notification ke seluruh Petugas PIMPASA " . ($desa?->upt?->nama ?? 'UPT') . ".");
    }
}
