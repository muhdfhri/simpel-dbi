<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * Template root yang dirender.
     * Blade view yang menjadi "shell" HTML — berisi @inertia directive.
     */
    protected $rootView = 'app';

    /**
     * Versi aset untuk cache-busting otomatis.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Data global yang di-share ke semua halaman Vue via usePage().props
     * Sesuai ARCHITECTURE.md: auth user + role untuk RBAC di sisi frontend.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),

            // Auth user — null jika belum login
            'auth' => [
                'user' => $request->user() ? [
                    'id'       => $request->user()->id,
                    'name'     => $request->user()->name,
                    'email'    => $request->user()->email,
                    'role'     => $request->user()->role->value ?? (string) $request->user()->role,   // enum: desa|pimpasa|upt|kanwil
                    'is_active'=> $request->user()->is_active,
                    'upt_nama' => $request->user()->upt?->nama,
                    'desa_nama' => $request->user()->desa?->nama,
                    'permissions' => $request->user()->getAllPermissions()->pluck('name')->toArray(),
                    'unreadNotificationsCount' => $request->user()->unreadNotifications()->count(),
                    'recentNotifications' => $request->user()->notifications()
                        ->latest()
                        ->take(8)
                        ->get()
                        ->map(fn ($n) => [
                            'id' => $n->id,
                            'title' => $n->data['title'] ?? 'Pemberitahuan Sistem',
                            'message' => $n->data['message'] ?? '',
                            'type' => $n->data['type'] ?? 'info',
                            'url' => $n->data['url'] ?? '#',
                            'read_at' => $n->read_at,
                            'created_at' => $n->created_at->toISOString(),
                        ]),
                ] : null,
            ],

            // Flash messages untuk toast notifications
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
                'info'    => fn () => $request->session()->get('info'),
            ],
        ];
    }
}
