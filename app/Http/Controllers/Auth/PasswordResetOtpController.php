<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordOtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PasswordResetOtpController extends Controller
{
    /**
     * Tampilkan halaman Forgot Password.
     */
    public function showForgotForm()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    /**
     * Kirim email berisi link + kode OTP 6-digit.
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.exists' => 'Alamat email ini tidak terdaftar di sistem kami.',
        ]);

        $email = $request->email;
        $otpCode = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $token = Str::random(40);
        $expiresAt = now()->addMinutes(10);

        // Hapus token lama untuk email ini
        DB::table('password_reset_otps')->where('email', $email)->delete();

        // Simpan token & kode OTP baru
        DB::table('password_reset_otps')->insert([
            'email' => $email,
            'token' => $token,
            'otp_code' => $otpCode,
            'expires_at' => $expiresAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $verifyUrl = url("/verify-otp?token={$token}&email=" . urlencode($email));

        // Kirim email via SMTP Gmail
        Mail::to($email)->send(new ResetPasswordOtpMail($email, $otpCode, $verifyUrl));

        return redirect()->route('verify-otp.show', [
            'token' => $token,
            'email' => $email,
        ])->with('status', 'Kode OTP 6 digit telah dikirimkan ke email Anda.');
    }

    /**
     * Tampilkan halaman Verifikasi Kode OTP (6 Box).
     */
    public function showVerifyForm(Request $request)
    {
        return Inertia::render('Auth/VerifyOtp', [
            'email' => $request->query('email', ''),
            'token' => $request->query('token', ''),
            'status' => session('status'),
        ]);
    }

    /**
     * Verifikasi kode OTP 6-digit.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'otp' => 'required|string|size:6',
        ], [
            'otp.required' => 'Kode OTP 6 digit wajib diisi.',
            'otp.size' => 'Kode OTP harus terdiri dari 6 digit angka.',
        ]);

        $record = DB::table('password_reset_otps')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (! $record) {
            return back()->withErrors(['otp' => 'Sesi verifikasi tidak valid atau tidak ditemukan.']);
        }

        if (now()->gt($record->expires_at)) {
            return back()->withErrors(['otp' => 'Kode OTP sudah kedaluwarsa. Silakan minta kode baru.']);
        }

        if ($record->otp_code !== $request->otp) {
            return back()->withErrors(['otp' => 'Kode OTP yang Anda masukkan salah. Periksa kembali email Anda.']);
        }

        // Simpan flag verifikasi di session
        session([
            'otp_verified_email' => $request->email,
            'otp_verified_token' => $request->token,
        ]);

        return redirect()->route('reset-password.show', [
            'token' => $request->token,
            'email' => $request->email,
        ]);
    }

    /**
     * Tampilkan halaman Reset Password Baru.
     */
    public function showResetForm(Request $request)
    {
        $email = $request->query('email');
        $token = $request->query('token');

        // Pastikan pengguna sudah lolos verifikasi OTP
        if (session('otp_verified_email') !== $email || session('otp_verified_token') !== $token) {
            return redirect()->route('forgot-password.show')->withErrors([
                'email' => 'Silakan verifikasi kode OTP terlebih dahulu.',
            ]);
        }

        return Inertia::render('Auth/ResetPassword', [
            'email' => $email,
            'token' => $token,
        ]);
    }

    /**
     * Proses reset password baru pengguna.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers()->symbols(), 'confirmed'],
        ], [
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        // Verifikasi token OTP sekali lagi
        $record = DB::table('password_reset_otps')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (! $record) {
            return back()->withErrors(['password' => 'Token reset kata sandi tidak valid.']);
        }

        // Update password pengguna
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        // Hapus token OTP yang sudah digunakan
        DB::table('password_reset_otps')->where('email', $request->email)->delete();
        session()->forget(['otp_verified_email', 'otp_verified_token']);

        return redirect()->route('login')->with('status', 'Kata sandi Anda berhasil diperbarui. Silakan masuk kembali.');
    }
}
