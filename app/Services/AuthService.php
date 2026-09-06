<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Memproses otentikasi login pengguna.
     *
     * @throws ValidationException
     */
    public function login(array $credentials, bool $remember = false): User
    {
        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => __('Email atau kata sandi yang Anda masukkan salah.'),
            ]);
        }

        $user = Auth::user();

        // Cek status keaktifan akun
        if (! $user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => __('Akun Anda telah dinonaktifkan. Silakan hubungi Administrator Kanwil.'),
            ]);
        }

        session()->regenerate();

        return $user;
    }

    /**
     * Memproses logout pengguna dan menginvalidasikan session.
     */
    public function logout(Request $request): void
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * Mengirimkan link reset password ke email pengguna.
     */
    public function sendResetLink(string $email): string
    {
        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }

        return __($status);
    }

    /**
     * Memproses permohonan reset password dengan token.
     */
    public function resetPassword(array $credentials): string
    {
        $status = Password::reset($credentials, function (User $user, string $password) {
            $user->password = $password;
            $user->save();
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }

        return __($status);
    }

    /**
     * Mendapatkan URL pengalihan default berdasarkan role pengguna setelah login.
     */
    public function getRedirectUrlForUser(User $user): string
    {
        return match ($user->role->value ?? $user->role) {
            'desa' => route('desa.dashboard', [], false),
            'pimpasa' => route('pimpasa.dashboard', [], false),
            'kanwil' => route('kanwil.dashboard', [], false),
            default => route('dashboard', [], false),
        };
    }
}
