<?php

namespace App\Http\Controllers\Desa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PanduanController extends Controller
{
    /**
     * Tampilkan halaman Manual Book / Panduan Penggunaan untuk Perangkat Desa.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Desa/Panduan/Index', [
            'appName' => config('app.name', 'SIMPEL DBI'),
        ]);
    }
}
