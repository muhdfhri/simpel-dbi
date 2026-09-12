<?php

namespace App\Http\Controllers\Kanwil;

use App\Http\Controllers\Controller;
use App\Models\DesaBinaan;
use App\Models\Upt;
use App\Models\User;
use App\Services\MasterDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class MasterDataController extends Controller
{
    public function __construct(
        protected MasterDataService $masterDataService
    ) {}

    private function getPasswordRule(bool $required = true): array
    {
        $rule = Password::min(8)->mixedCase()->numbers()->symbols();
        return $required ? ['required', $rule] : ['nullable', $rule];
    }

    public function index(Request $request): Response
    {
        $tab = $request->query('tab', 'desa');

        $desaList = DesaBinaan::with(['upt', 'pimpasa'])
            ->orderBy('nama', 'asc')
            ->get();

        $pimpasaList = User::where('role', 'pimpasa')
            ->with(['upt'])
            ->orderBy('name', 'asc')
            ->get();

        // Eager load relasi desaBinaanList dan users untuk Modal Detail UPT
        $uptList = Upt::with(['desaBinaanList.pimpasa', 'users'])
            ->withCount(['desaBinaanList', 'users'])
            ->orderBy('nama', 'asc')
            ->get();

        // List Akun User Perangkat Desa (role = desa)
        $desaUserList = User::where('role', 'desa')
            ->with(['desa.upt'])
            ->orderBy('name', 'asc')
            ->get();

        // List Akun Administrator Kanwil (role = kanwil)
        $kanwilUserList = User::where('role', 'kanwil')
            ->orderBy('name', 'asc')
            ->get();

        // List Kategori Laporan Master Data
        $kategoriLaporanList = \App\Models\KategoriLaporan::orderBy('nama_kategori', 'asc')->get();

        // Matriks Hak Akses Spatie Role Permission
        $permissionsMatrix = $this->masterDataService->getPermissionsMatrix();

        return Inertia::render('Kanwil/MasterData/Index', [
            'tab' => $tab,
            'desaList' => $desaList,
            'pimpasaList' => $pimpasaList,
            'uptList' => $uptList,
            'desaUserList' => $desaUserList,
            'kanwilUserList' => $kanwilUserList,
            'kategoriLaporanList' => $kategoriLaporanList,
            'permissionsMatrix' => $permissionsMatrix,
        ]);
    }

    // --- CRUD ADMIN KANWIL ---
    public function storeKanwilUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'nip' => 'nullable|string|max:30|unique:users,nip',
            'golongan' => 'nullable|string|max:50',
            'kontak' => 'nullable|string|max:30',
            'password' => $this->getPasswordRule(true),
        ]);

        $this->masterDataService->createKanwilUser($validated);

        return redirect()->back()->with('success', 'Akun Administrator Kanwil berhasil ditambahkan.');
    }

    public function updateKanwilUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'nip' => 'nullable|string|max:30|unique:users,nip,' . $user->id,
            'golongan' => 'nullable|string|max:50',
            'kontak' => 'nullable|string|max:30',
            'password' => $this->getPasswordRule(false),
        ]);

        $this->masterDataService->updateKanwilUser($user, $validated);

        return redirect()->back()->with('success', 'Data Administrator Kanwil berhasil diperbarui.');
    }

    public function destroyKanwilUser(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.');
        }

        $this->masterDataService->deleteKanwilUser($user);
        return redirect()->back()->with('success', 'Akun Administrator Kanwil berhasil dihapus.');
    }

    // --- CRUD DESA BINAAN ---
    public function storeDesa(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:150',
            'upt_id' => 'required|exists:upt,id',
            'pimpasa_id' => 'nullable',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        $this->masterDataService->createDesaBinaan($validated);

        return redirect()->back()->with('success', 'Desa Binaan berhasil ditambahkan.');
    }

    public function updateDesa(Request $request, DesaBinaan $desa): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:150',
            'upt_id' => 'required|exists:upt,id',
            'pimpasa_id' => 'nullable',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'status_terkini' => 'required|string',
        ]);

        $this->masterDataService->updateDesaBinaan($desa, $validated);

        return redirect()->back()->with('success', 'Data Desa Binaan berhasil diperbarui.');
    }

    public function destroyDesa(DesaBinaan $desa): RedirectResponse
    {
        $this->masterDataService->deleteDesaBinaan($desa);
        return redirect()->back()->with('success', 'Desa Binaan berhasil dihapus.');
    }

    // --- CRUD PETUGAS PIMPASA ---
    public function storePimpasa(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'nip' => 'nullable|string|max:30|unique:users,nip',
            'golongan' => 'nullable|string|max:50',
            'upt_id' => 'required|exists:upt,id',
            'password' => $this->getPasswordRule(true),
        ]);

        $this->masterDataService->createPimpasaUser($validated);

        return redirect()->back()->with('success', 'Akun Petugas PIMPASA berhasil ditambahkan.');
    }

    public function updatePimpasa(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'nip' => 'nullable|string|max:30|unique:users,nip,' . $user->id,
            'golongan' => 'nullable|string|max:50',
            'upt_id' => 'required|exists:upt,id',
            'password' => $this->getPasswordRule(false),
        ]);

        $this->masterDataService->updatePimpasaUser($user, $validated);

        return redirect()->back()->with('success', 'Data Petugas PIMPASA berhasil diperbarui.');
    }

    public function destroyPimpasa(User $user): RedirectResponse
    {
        $this->masterDataService->deletePimpasaUser($user);
        return redirect()->back()->with('success', 'Akun Petugas PIMPASA berhasil dihapus.');
    }

    // --- CRUD SATKER UPT ---
    public function storeUpt(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:150|unique:upt,nama',
            'tipe' => 'required|string|max:50',
        ]);

        $this->masterDataService->createUpt($validated);

        return redirect()->back()->with('success', 'Satker UPT Imigrasi berhasil ditambahkan.');
    }

    public function updateUpt(Request $request, Upt $upt): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:150|unique:upt,nama,' . $upt->id,
            'tipe' => 'required|string|max:50',
        ]);

        $this->masterDataService->updateUpt($upt, $validated);

        return redirect()->back()->with('success', 'Data Satker UPT Imigrasi berhasil diperbarui.');
    }

    public function destroyUpt(Upt $upt): RedirectResponse
    {
        $this->masterDataService->deleteUpt($upt);
        return redirect()->back()->with('success', 'Satker UPT Imigrasi berhasil dihapus.');
    }

    // --- CRUD USER PERANGKAT DESA ---
    public function storeDesaUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'desa_id' => 'required|exists:desa_binaan,id',
            'kontak' => 'nullable|string|max:30',
            'password' => $this->getPasswordRule(true),
            'is_active' => 'nullable|boolean',
        ]);

        $this->masterDataService->createDesaUser($validated);

        return redirect()->back()->with('success', 'Akun User Perangkat Desa berhasil ditambahkan.');
    }

    public function updateDesaUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'desa_id' => 'required|exists:desa_binaan,id',
            'kontak' => 'nullable|string|max:30',
            'password' => $this->getPasswordRule(false),
            'is_active' => 'nullable|boolean',
        ]);

        $this->masterDataService->updateDesaUser($user, $validated);

        return redirect()->back()->with('success', 'Data User Perangkat Desa berhasil diperbarui.');
    }

    public function destroyDesaUser(User $user): RedirectResponse
    {
        $this->masterDataService->deleteDesaUser($user);
        return redirect()->back()->with('success', 'Akun User Perangkat Desa berhasil dihapus.');
    }

    // --- TOGGLE ROLE PERMISSION ---
    public function togglePermission(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role' => 'required|string|in:pimpasa,desa',
            'permission' => 'required|string',
            'enable' => 'required|boolean',
        ]);

        $this->masterDataService->toggleRolePermission(
            $validated['role'],
            $validated['permission'],
            $validated['enable']
        );

        $statusText = $validated['enable'] ? 'diaktifkan' : 'dinonaktifkan';
        $roleLabel = $validated['role'] === 'pimpasa' ? 'Petugas PIMPASA' : 'Perangkat Desa';

        return redirect()->back()->with('success', "Hak akses '{$validated['permission']}' untuk role {$roleLabel} berhasil {$statusText}.");
    }

    // --- CRUD KATEGORI LAPORAN ---
    public function storeKategori(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:50|unique:kategori_laporans,kode',
            'nama_kategori' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $this->masterDataService->createKategoriLaporan($validated);

        return redirect()->back()->with('success', 'Kategori Laporan baru berhasil ditambahkan.');
    }

    public function updateKategori(Request $request, \App\Models\KategoriLaporan $kategori): RedirectResponse
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:50|unique:kategori_laporans,kode,' . $kategori->id,
            'nama_kategori' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $this->masterDataService->updateKategoriLaporan($kategori, $validated);

        return redirect()->back()->with('success', 'Data Kategori Laporan berhasil diperbarui.');
    }

    public function toggleKategoriStatus(\App\Models\KategoriLaporan $kategori): RedirectResponse
    {
        $this->masterDataService->toggleKategoriLaporanStatus($kategori);
        $statusText = $kategori->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Kategori Laporan '{$kategori->nama_kategori}' berhasil {$statusText}.");
    }

    public function destroyKategori(\App\Models\KategoriLaporan $kategori): RedirectResponse
    {
        $this->masterDataService->deleteKategoriLaporan($kategori);
        return redirect()->back()->with('success', 'Kategori Laporan berhasil dihapus.');
    }
}
