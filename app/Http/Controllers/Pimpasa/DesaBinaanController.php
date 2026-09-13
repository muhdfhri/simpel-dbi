<?php

namespace App\Http\Controllers\Pimpasa;

use App\Http\Controllers\Controller;
use App\Models\DesaBinaan;
use App\Models\User;
use App\Services\MasterDataService;
use App\Services\PimpasaWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DesaBinaanExport;

class DesaBinaanController extends Controller
{
    public function __construct(
        protected PimpasaWorkflowService $pimpasaService,
        protected MasterDataService $masterDataService
    ) {}

    private function getPasswordRule(bool $required = true): array
    {
        $rule = Password::min(8)->mixedCase()->numbers()->symbols();
        return $required ? ['required', $rule] : ['nullable', $rule];
    }

    public function index(Request $request): Response
    {
        $uptId = $request->user()->upt_id;
        $tab = $request->query('tab', 'desa');

        $desaList = $this->pimpasaService->getDesaBinaanList($uptId);

        // Ambil daftar user Perangkat Desa (role = desa) yang desanya milik UPT PIMPASA yang login
        $perangkatDesaList = User::where('role', 'desa')
            ->whereHas('desa', function ($q) use ($uptId) {
                if ($uptId) {
                    $q->where('upt_id', $uptId);
                }
            })
            ->with(['desa.upt'])
            ->orderBy('name', 'asc')
            ->get();

        // Ambil daftar Desa Binaan milik UPT PIMPASA ini untuk opsi dropdown form modal
        $desaOptions = DesaBinaan::when($uptId, fn ($q) => $q->where('upt_id', $uptId))
            ->orderBy('nama', 'asc')
            ->get(['id', 'nama', 'upt_id']);

        return Inertia::render('Pimpasa/DesaBinaan/Index', [
            'tab' => $tab,
            'desaList' => $desaList,
            'perangkatDesaList' => $perangkatDesaList,
            'desaOptions' => $desaOptions,
        ]);
    }

    public function storePerangkatDesa(Request $request): RedirectResponse
    {
        $uptId = $request->user()->upt_id;

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'desa_id' => [
                'required',
                'exists:desa_binaan,id',
                function ($attribute, $value, $fail) use ($uptId) {
                    if ($uptId) {
                        $isBelongs = DesaBinaan::where('id', $value)->where('upt_id', $uptId)->exists();
                        if (!$isBelongs) {
                            $fail('Desa binaan yang dipilih tidak berada di dalam wilayah UPT Anda.');
                        }
                    }
                }
            ],
            'kontak' => 'nullable|string|max:30',
            'password' => $this->getPasswordRule(true),
            'is_active' => 'nullable|boolean',
        ]);

        $this->masterDataService->createDesaUser($validated);

        return redirect()->back()->with('success', 'Akun User Perangkat Desa berhasil ditambahkan.');
    }

    public function updatePerangkatDesa(Request $request, User $user): RedirectResponse
    {
        $uptId = $request->user()->upt_id;

        // Pastikan user yang diedit memang ber-role 'desa' dan desanya berada di UPT PIMPASA yang login
        if ($user->role !== 'desa' && $user->role !== \App\Enums\UserRole::DESA) {
            return redirect()->back()->with('error', 'Hanya dapat memperbarui akun Perangkat Desa.');
        }

        if ($uptId && $user->desa && $user->desa->upt_id !== $uptId) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses mengedit akun Perangkat Desa di luar UPT Anda.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'desa_id' => [
                'required',
                'exists:desa_binaan,id',
                function ($attribute, $value, $fail) use ($uptId) {
                    if ($uptId) {
                        $isBelongs = DesaBinaan::where('id', $value)->where('upt_id', $uptId)->exists();
                        if (!$isBelongs) {
                            $fail('Desa binaan yang dipilih tidak berada di dalam wilayah UPT Anda.');
                        }
                    }
                }
            ],
            'kontak' => 'nullable|string|max:30',
            'password' => $this->getPasswordRule(false),
            'is_active' => 'nullable|boolean',
        ]);

        $this->masterDataService->updateDesaUser($user, $validated);

        return redirect()->back()->with('success', 'Data User Perangkat Desa berhasil diperbarui.');
    }

    public function destroyPerangkatDesa(Request $request, User $user): RedirectResponse
    {
        $uptId = $request->user()->upt_id;

        if ($user->role !== 'desa' && $user->role !== \App\Enums\UserRole::DESA) {
            return redirect()->back()->with('error', 'Hanya dapat menghapus akun Perangkat Desa.');
        }

        if ($uptId && $user->desa && $user->desa->upt_id !== $uptId) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses menghapus akun Perangkat Desa di luar UPT Anda.');
        }

        $this->masterDataService->deleteDesaUser($user);

        return redirect()->back()->with('success', 'Akun User Perangkat Desa berhasil dihapus.');
    }

    public function exportExcel(Request $request)
    {
        $user = $request->user();
        $desaList = $this->pimpasaService->getDesaBinaanList($user->upt_id);

        $filename = 'direktori-desa-binaan-' . date('Y-m-d-His') . '.xlsx';

        return Excel::download(
            new DesaBinaanExport($desaList),
            $filename,
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    public function exportPdf(Request $request)
    {
        $user = $request->user();
        $desaList = $this->pimpasaService->getDesaBinaanList($user->upt_id);

        $pimpasaNama = $user->name ?? 'Petugas PIMPASA';
        $uptNama = $user->upt?->nama_upt ?? 'Kanwil / UPT Imigrasi';

        $pdf = Pdf::loadView('pdf.desa-binaan-rekap', [
            'desaList' => $desaList,
            'pimpasaNama' => $pimpasaNama,
            'uptNama' => $uptNama,
            'tanggalCetak' => date('d F Y, H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('direktori-desa-binaan-' . date('Y-m-d-His') . '.pdf');
    }
}
