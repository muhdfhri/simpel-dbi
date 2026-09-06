<?php

namespace App\Http\Controllers\Pimpasa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pimpasa\StoreTindakLanjutRequest;
use App\Models\Laporan;
use App\Services\PimpasaWorkflowService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TindakLanjutController extends Controller
{
    public function __construct(
        protected PimpasaWorkflowService $pimpasaService
    ) {}

    /**
     * Tampilkan form tindak lanjut penanganan lapangan UPT.
     */
    public function form(Laporan $laporan): Response
    {
        $laporan->load(['desa', 'verifikasi.pimpasa', 'tindakLanjut', 'lampiranList']);

        return Inertia::render('Pimpasa/TindakLanjut/Form', [
            'laporan' => $laporan,
        ]);
    }

    /**
     * Simpan hasil tindak lanjut penanganan lapangan UPT Imigrasi.
     */
    public function store(StoreTindakLanjutRequest $request, Laporan $laporan): RedirectResponse
    {
        $this->pimpasaService->tindakLanjutLaporan(
            $laporan,
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route('pimpasa.tindaklanjut.index')
            ->with('success', "Hasil tindak lanjut penanganan UPT untuk Kode Tiket {$laporan->kode_tiket} berhasil disimpan.");
    }
}
