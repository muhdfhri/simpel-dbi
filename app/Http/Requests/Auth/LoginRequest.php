<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
            'cf_turnstile_response' => ['nullable', 'string'],
        ];
    }

    public function ensureIsNotRateLimited(): void
    {
        // Verifikasi Cloudflare Turnstile Token
        $turnstileSecret = config('services.turnstile.secret', env('TURNSTILE_SECRET_KEY'));
        $turnstileResponse = $this->input('cf_turnstile_response');

        if ($turnstileSecret) {
            if (!$turnstileResponse) {
                throw ValidationException::withMessages([
                    'email' => 'Silakan selesaikan verifikasi keamanan Cloudflare Turnstile terlebih dahulu.',
                ]);
            }

            try {
                $response = \Illuminate\Support\Facades\Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => $turnstileSecret,
                    'response' => $turnstileResponse,
                    'remoteip' => $this->ip(),
                ]);

                if (!$response->json('success')) {
                    throw ValidationException::withMessages([
                        'email' => 'Verifikasi keamanan gagal. Silakan muat ulang halaman dan coba lagi.',
                    ]);
                }
            } catch (\Exception $e) {
                if ($e instanceof ValidationException) throw $e;
                // Ignore network failure or log warning to prevent blocking user if CF API is unreachable
            }
        }

        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }
}
