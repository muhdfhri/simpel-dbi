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
        $rekap = $this->pimpasaService->getRekapitulasiSatker($request->user()->upt_id);
        $desaList = $this->pimpasaService->getDesaBinaanList($request->user()->upt_id);

        return Inertia::render('Pimpasa/Rekapitulasi/Index', [
            'rekap' => $rekap,
            'desaList' => $desaList,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $user = $request->user();
        $desaList = $this->pimpasaService->getDesaBinaanList($user->upt_id);

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
        $rekap = $this->pimpasaService->getRekapitulasiSatker($user->upt_id);
        $desaList = $this->pimpasaService->getDesaBinaanList($user->upt_id);

        $pimpasaNama = $user->name ?? 'Petugas PIMPASA';
        $uptNama = $user->upt?->nama_upt ?? 'Kanwil / UPT Imigrasi';

        $pdf = Pdf::loadView('pdf.rekapitulasi-satker', [
            'rekap' => $rekap,
            'desaList' => $desaList,
            'pimpasaNama' => $pimpasaNama,
            'uptNama' => $uptNama,
            'tanggalCetak' => date('d F Y, H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('rekapitulasi-satker-upt-' . date('Y-m-d-His') . '.pdf');
    }
}
