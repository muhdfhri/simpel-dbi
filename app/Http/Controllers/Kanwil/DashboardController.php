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
}
