<?php

namespace App\Http\Controllers\Kanwil;

use App\Http\Controllers\Controller;
use App\Services\MonitoringService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Beranda Utama / Executive Visual Analytics Dashboard (Role Kanwil)
     */
    public function index(Request $request, MonitoringService $monitoringService): Response
    {
        return Inertia::render('Kanwil/Dashboard', [
            'kpiData' => $monitoringService->getKpiMetrics(),
            'chartAnalytics' => $monitoringService->getChartAnalyticsData(),
        ]);
    }

    /**
     * Export Laporan Ringkasan Eksekutif & Visual Analytics ke PDF (.pdf)
     */
    public function exportExecutivePdf(Request $request, MonitoringService $monitoringService)
    {
        $kpiData = $monitoringService->getKpiMetrics();
        $chartAnalytics = $monitoringService->getChartAnalyticsData();
        $uptScorecard = $monitoringService->getUptScorecards();
        $pimpinanNama = $request->user()?->name ?? 'Administrator Kanwil';

        $trendImg = $request->input('trendImg');
        $donutImg = $request->input('donutImg');
        $barImg = $request->input('barImg');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.executive-summary', [
            'kpiData' => $kpiData,
            'chartAnalytics' => $chartAnalytics,
            'uptScorecard' => $uptScorecard,
            'pimpinanNama' => $pimpinanNama,
            'tanggalCetak' => date('d F Y, H:i'),
            'trendImg' => $trendImg,
            'donutImg' => $donutImg,
            'barImg' => $barImg,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('ringkasan-eksekutif-kanwil-' . date('Y-m-d-His') . '.pdf');
    }
}
