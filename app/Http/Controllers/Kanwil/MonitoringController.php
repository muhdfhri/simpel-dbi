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
        return Inertia::render('Kanwil/Monitoring/SlaControl', [
            'kpiData' => $monitoringService->getKpiMetrics(),
            'slaIncidents' => $monitoringService->getSlaIncidents(),
            'uptListOptions' => Upt::select('id', 'nama')->get(),
        ]);
    }

    /**
     * Sub-Menu 3: Scorecard Kepatuhan Satker UPT Imigrasi
     */
    public function uptScorecard(Request $request, MonitoringService $monitoringService): Response
    {
        return Inertia::render('Kanwil/Monitoring/UptScorecard', [
            'kpiData' => $monitoringService->getKpiMetrics(),
            'uptScorecard' => $monitoringService->getUptScorecards(),
            'uptListOptions' => Upt::select('id', 'nama')->get(),
        ]);
    }


    /**
     * Export Laporan Kendali SLA ke Excel (.xlsx)
     */
    public function exportSlaExcel(Request $request, MonitoringService $monitoringService)
    {
        $incidents = $monitoringService->getSlaIncidents();
        
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
        $incidents = $monitoringService->getSlaIncidents();
        
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

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.sla-control', [
            'incidents' => array_values($incidents),
            'filterUptNama' => $filterUptNama,
            'filterSlaStatusNama' => $filterSlaStatusNama,
            'pimpinanNama' => $pimpinanNama,
            'tanggalCetak' => date('d F Y, H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-kendali-sla-' . date('Y-m-d-His') . '.pdf');
    }

    /**
     * Sub-Menu Executive Monitoring: Monitoring Pembinaan Desa
     */
    public function kegiatanPembinaan(Request $request, MonitoringService $monitoringService): Response
    {
        return Inertia::render('Kanwil/Monitoring/KegiatanPembinaan', [
            'kpiMetrics' => $monitoringService->getKegiatanKpiMetrics(),
            'kegiatanList' => $monitoringService->getKegiatanPembinaanData(),
            'uptListOptions' => Upt::select('id', 'nama')->get(),
        ]);
    }

    /**
     * Export Laporan Monitoring Pembinaan Desa ke Excel (.xlsx)
     */
    public function exportKegiatanExcel(Request $request, MonitoringService $monitoringService)
    {
        $kegiatan = $monitoringService->getKegiatanPembinaanData();

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
        $kegiatan = $monitoringService->getKegiatanPembinaanData();

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

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.kegiatan-kanwil', [
            'kegiatanList' => array_values($kegiatan),
            'filterUptNama' => $filterUptNama,
            'pimpinanNama' => $pimpinanNama,
            'tanggalCetak' => date('d F Y, H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('rekapitulasi-pembinaan-desa-' . date('Y-m-d-His') . '.pdf');
    }

    /**
     * Export Scorecard Kepatuhan UPT ke Excel (.xlsx)
     */
    public function exportScorecardExcel(Request $request, MonitoringService $monitoringService)
    {
        $scorecard = $monitoringService->getUptScorecards();

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
        $scorecard = $monitoringService->getUptScorecards();

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

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.upt-scorecard', [
            'scorecards' => array_values($scorecard),
            'filterStatusNama' => $filterStatusNama,
            'pimpinanNama' => $pimpinanNama,
            'user' => $request->user(),
            'generated_at' => date('d F Y, H:i'),
            'tanggalCetak' => date('d F Y, H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('scorecard-kepatuhan-upt-' . date('Y-m-d-His') . '.pdf');
    }

    /**
     * Kirim Teguran High-Priority SLA dari Kanwil ke Petugas PIMPASA Desa Binaan
     */
    public function tegurSla(Request $request, \App\Models\Laporan $laporan)
    {
        $desa = $laporan->desa;
        $pimpasaTargets = collect();

        if ($desa?->pimpasa_id) {
            $pimpasaUser = \App\Models\User::find($desa->pimpasa_id);
            if ($pimpasaUser) $pimpasaTargets->push($pimpasaUser);
        }

        if ($pimpasaTargets->isEmpty() && $desa?->upt_id) {
            $uptPimpasa = \App\Models\User::where('role', 'pimpasa')->where('upt_id', $desa->upt_id)->get();
            $pimpasaTargets = $pimpasaTargets->merge($uptPimpasa);
        }

        if ($pimpasaTargets->isEmpty()) {
            return back()->with('error', 'Petugas PIMPASA penanggung jawab desa ini belum terdaftar.');
        }

        $title = "⚠️ Teguran SLA Kanwil — Tiket {$laporan->kode_tiket}";
        $message = "PERINGATAN SLA KANWIL: Laporan {$laporan->kode_tiket} di Desa " . ($desa?->nama ?? 'Binaan') . " telah diajukan > 24 Jam dan belum diverifikasi. Segera tindak lanjuti!";

        foreach ($pimpasaTargets->unique('id') as $pimpasa) {
            $pimpasa->notify(new \App\Notifications\LaporanNotification(
                title: $title,
                message: $message,
                type: 'urgent',
                url: "/pimpasa/verifikasi/{$laporan->id}",
                laporanId: $laporan->id,
                kodeTiket: $laporan->kode_tiket
            ));
        }

        return back()->with('success', "Teguran SLA untuk tiket {$laporan->kode_tiket} berhasil dikirimkan ke Petugas PIMPASA.");
    }
}
