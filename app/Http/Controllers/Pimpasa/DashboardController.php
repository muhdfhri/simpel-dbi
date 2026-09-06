<?php

namespace App\Http\Controllers\Pimpasa;

use App\Http\Controllers\Controller;
use App\Services\PimpasaWorkflowService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected PimpasaWorkflowService $pimpasaService
    ) {}

    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $uptId = $user->upt_id;
        $stats = $this->pimpasaService->getDashboardStats($uptId);
        $recentAntrean = $this->pimpasaService->getAntreanVerifikasi([], 20);

        $desaBinaanList = $this->pimpasaService->getDesaBinaanList($uptId);
        $perangkatDesaList = $this->pimpasaService->getPerangkatDesaList($uptId);

        $upt = null;
        if ($user->upt) {
            $upt = [
                'id' => $user->upt->id,
                'nama' => $user->upt->nama,
                'tipe' => $user->upt->tipe ?? 'Kanim',
            ];
        }

        return Inertia::render('Pimpasa/Dashboard', [
            'stats' => $stats,
            'recentAntrean' => $recentAntrean,
            'desaBinaanList' => $desaBinaanList,
            'perangkatDesaList' => $perangkatDesaList,
            'upt' => $upt,
        ]);
    }
}
