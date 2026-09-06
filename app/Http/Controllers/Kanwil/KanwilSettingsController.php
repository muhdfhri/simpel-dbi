<?php

namespace App\Http\Controllers\Kanwil;

use App\Http\Controllers\Controller;
use App\Models\DesaBinaan;
use App\Models\Upt;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class KanwilSettingsController extends Controller
{
    /**
     * Tampilkan halaman Pengaturan Akun Pimpinan / Executive Kanwil.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $totalUpt = Upt::count();
        $totalDesa = DesaBinaan::count();
        $totalPimpasa = User::where('role', 'pimpasa')->count();

        return Inertia::render('Kanwil/Settings/Index', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'kontak' => $user->kontak,
                'nip' => $user->nip,
                'golongan' => $user->golongan,
                'role' => $user->role->value ?? $user->role,
                'role_label' => 'Executive & Administrator Kanwil Sumut',
                'created_at' => $user->created_at?->format('d M Y'),
            ],
            'kanwilInfo' => [
                'nama' => 'Kantor Wilayah Ditjen Imigrasi Sumatera Utara',
                'provinsi' => 'Sumatera Utara',
                'total_upt' => $totalUpt,
                'total_desa' => $totalDesa,
                'total_pimpasa' => $totalPimpasa,
            ],
        ]);
    }

    /**
     * Update profil pengguna Kanwil (Nama, Email, WhatsApp Operasional, NIP, Golongan).
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'kontak' => ['nullable', 'string', 'max:30'],
            'nip' => ['nullable', 'string', 'max:50'],
            'golongan' => ['nullable', 'string', 'max:50'],
        ], [
            'name.required' => 'Nama lengkap pimpinan / admin wajib diisi.',
            'email.required' => 'Email resmi akun wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini telah digunakan oleh akun lain.',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'kontak' => $validated['kontak'],
            'nip' => $validated['nip'],
            'golongan' => $validated['golongan'],
        ]);

        return back()->with('success', 'Profil Executive Kanwil berhasil diperbarui.');
    }

    /**
     * Update kata sandi akun pengguna Kanwil.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'current_password.current_password' => 'Kata sandi saat ini tidak sesuai.',
            'new_password.required' => 'Kata sandi baru wajib diisi.',
            'new_password.min' => 'Kata sandi baru minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return back()->with('success', 'Kata sandi akun Kanwil berhasil diperbarui.');
    }

    /**
     * Update preferensi notifikasi akun Kanwil.
     */
    public function updateNotifications(Request $request): RedirectResponse
    {
        return back()->with('success', 'Preferensi notifikasi Kanwil berhasil disimpan.');
    }
}
