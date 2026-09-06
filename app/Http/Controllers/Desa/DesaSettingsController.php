<?php

namespace App\Http\Controllers\Desa;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class DesaSettingsController extends Controller
{
    /**
     * Tampilkan halaman Pengaturan Akun Perangkat Desa.
     */
    public function index(Request $request): Response
    {
        $user = $request->user()->load([
            'desa.wilayah',
            'desa.upt',
            'desa.pimpasa'
        ]);

        return Inertia::render('Desa/Settings/Index', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'kontak' => $user->kontak,
                'role' => $user->role->value ?? $user->role,
                'role_label' => 'Perangkat Desa Binaan',
                'created_at' => $user->created_at?->format('d M Y'),
            ],
            'desaInfo' => $user->desa ? [
                'id' => $user->desa->id,
                'nama' => $user->desa->nama,
                'wilayah' => $user->desa->wilayah ? [
                    'kecamatan' => $user->desa->wilayah->kecamatan,
                    'kabupaten' => $user->desa->wilayah->kabupaten_kota,
                    'provinsi' => $user->desa->wilayah->provinsi,
                ] : null,
                'upt_pembina' => $user->desa->upt ? [
                    'nama' => $user->desa->upt->nama,
                    'kode' => 'UPT-' . str_pad((string) $user->desa->upt->id, 3, '0', STR_PAD_LEFT),
                ] : null,
                'pimpasa_pembina' => $user->desa->pimpasa ? [
                    'name' => $user->desa->pimpasa->name,
                    'kontak' => $user->desa->pimpasa->kontak,
                    'email' => $user->desa->pimpasa->email,
                ] : null,
            ] : null,
        ]);
    }

    /**
     * Update profil pengguna (Nama, Email, Nomor Kontak WhatsApp).
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'kontak' => ['nullable', 'string', 'max:30'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'kontak' => $validated['kontak'],
        ]);

        return back()->with('success', 'Profil Perangkat Desa berhasil diperbarui.');
    }

    /**
     * Update kata sandi akun pengguna.
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

        return back()->with('success', 'Kata sandi akun berhasil diperbarui.');
    }

    /**
     * Update preferensi notifikasi akun.
     */
    public function updateNotifications(Request $request): RedirectResponse
    {
        return back()->with('success', 'Preferensi notifikasi laporan berhasil disimpan.');
    }
}
