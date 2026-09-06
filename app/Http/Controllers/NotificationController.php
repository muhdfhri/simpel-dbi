<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Tandai 1 notifikasi spesifik sebagai dibaca (read_at).
     */
    public function markAsRead(Request $request, string $id): RedirectResponse|JsonResponse
    {
        $notification = $request->user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
            $targetUrl = $notification->data['url'] ?? null;

            if ($targetUrl && $request->wantsJson()) {
                return response()->json(['success' => true, 'redirect' => $targetUrl]);
            }

            if ($targetUrl) {
                return redirect($targetUrl);
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }

    /**
     * Tandai SELURUH notifikasi milik user sebagai dibaca.
     */
    public function markAllAsRead(Request $request): RedirectResponse|JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }
}
