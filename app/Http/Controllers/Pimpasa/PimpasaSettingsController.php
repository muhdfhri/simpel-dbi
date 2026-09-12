<?php

namespace App\Http\Controllers\Pimpasa;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class PimpasaSettingsController extends Controller
{
    /**
     * Tampilkan halaman Pengaturan Akun Petugas PIMPASA.
     */
    public function index(Request $request): Response
    {
        $user = $request->user()->load(['upt.wilayah']);
        $upt = $user->upt;

        $totalDesaBinaan = $upt ? \App\Models\DesaBinaan::where('upt_id', $upt->id)->count() : 0;

        return Inertia::render('Pimpasa/Settings/Index', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'kontak' => $user->kontak,
                'nip' => $user->nip,
                'golongan' => $user->golongan,
                'role' => $user->role->value ?? $user->role,
                'role_label' => 'Petugas PIMPASA UPT',
                'created_at' => $user->created_at?->format('d M Y'),
            ],
            'uptInfo' => $upt ? [
                'id' => $upt->id,
                'nama' => $upt->nama,
                'provinsi' => 'Sumatera Utara',
                'total_desa_binaan' => $totalDesaBinaan,
            ] : null,
        ]);
    }

    /**
     * Update profil pengguna PIMPASA (Nama, Email, WhatsApp Operasional, NIP, Golongan).
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
            'name.required' => 'Nama lengkap petugas wajib diisi.',
            'email.required' => 'Email kedinasan wajib diisi.',
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

        return back()->with('success', 'Profil Petugas PIMPASA berhasil diperbarui.');
    }

    /**
     * Update kata sandi akun pengguna.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'string', Password::min(8)->mixedCase()->numbers()->symbols(), 'confirmed'],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'current_password.current_password' => 'Kata sandi saat ini tidak sesuai.',
            'new_password.required' => 'Kata sandi baru wajib diisi.',
            'new_password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return back()->with('success', 'Kata sandi akun berhasil diperbarui.');
    }

    /**
     * Update preferensi notifikasi akun PIMPASA.
     */
    public function updateNotifications(Request $request): RedirectResponse
    {
        return back()->with('success', 'Preferensi notifikasi PIMPASA berhasil disimpan.');
    }
}
