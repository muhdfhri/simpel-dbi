<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Tampilkan halaman login.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'status' => session('status'),
        ]);
    }

    /**
     * Memproses permintaan login.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->ensureIsNotRateLimited();

        $user = $this->authService->login(
            $request->only('email', 'password'),
            $request->boolean('remember')
        );

        RateLimiter::clear($request->throttleKey());

        $redirectUrl = $this->authService->getRedirectUrlForUser($user);

        return redirect()->intended($redirectUrl);
    }

    /**
     * Memproses logout session pengguna.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $this->authService->logout($request);

        return redirect('/');
    }
}
