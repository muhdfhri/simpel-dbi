<?php

namespace App\Http\Controllers\Pimpasa;

use App\Http\Controllers\Controller;
use App\Services\PimpasaWorkflowService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RekapitulasiSatkerExport;

class RekapitulasiSatkerController extends Controller
{
    public function __construct(
        protected PimpasaWorkflowService $pimpasaService
    ) {}

    public function index(Request $request): Response
    {
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');

        $rekap = $this->pimpasaService->getRekapitulasiSatker($request->user()->upt_id, $tanggalMulai, $tanggalSelesai);
        $desaList = $this->pimpasaService->getDesaBinaanList($request->user()->upt_id, $tanggalMulai, $tanggalSelesai);

        return Inertia::render('Pimpasa/Rekapitulasi/Index', [
            'rekap' => $rekap,
            'desaList' => $desaList,
            'filters' => $request->only(['search', 'desa_id', 'indeks_kerawanan', 'tanggal_mulai', 'tanggal_selesai']),
        ]);
    }

    public function exportExcel(Request $request)
    {
        $user = $request->user();
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');

        $desaList = $this->pimpasaService->getDesaBinaanList($user->upt_id, $tanggalMulai, $tanggalSelesai);

        $filename = 'rekapitulasi-satker-upt-' . date('Y-m-d-His') . '.xlsx';

        return Excel::download(
            new RekapitulasiSatkerExport($desaList),
            $filename,
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    public function exportPdf(Request $request)
    {
        $user = $request->user();
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');

        $rekap = $this->pimpasaService->getRekapitulasiSatker($user->upt_id, $tanggalMulai, $tanggalSelesai);
        $desaList = $this->pimpasaService->getDesaBinaanList($user->upt_id, $tanggalMulai, $tanggalSelesai);

        $pimpasaNama = $user->name ?? 'Petugas PIMPASA';
        $uptNama = $user->upt?->nama_upt ?? 'Kanwil / UPT Imigrasi';

        $periodeText = 'Semua Periode';
        if ($tanggalMulai && $tanggalSelesai) {
            $periodeText = \Carbon\Carbon::parse($tanggalMulai)->locale('id')->isoFormat('D MMM YYYY') . ' s.d. ' . \Carbon\Carbon::parse($tanggalSelesai)->locale('id')->isoFormat('D MMM YYYY');
        } elseif ($tanggalMulai) {
            $periodeText = \Carbon\Carbon::parse($tanggalMulai)->locale('id')->isoFormat('D MMM YYYY') . ' s.d. Selesai';
        } elseif ($tanggalSelesai) {
            $periodeText = 's.d. ' . \Carbon\Carbon::parse($tanggalSelesai)->locale('id')->isoFormat('D MMM YYYY');
        }

        $pdf = Pdf::loadView('pdf.rekapitulasi-satker', [
            'rekap' => $rekap,
            'desaList' => $desaList,
            'pimpasaNama' => $pimpasaNama,
            'uptNama' => $uptNama,
            'tanggalCetak' => date('d F Y, H:i'),
            'periodeText' => $periodeText,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('rekapitulasi-satker-upt-' . date('Y-m-d-His') . '.pdf');
    }
}
