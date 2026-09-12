<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NtfyNotificationService
{
    /**
     * Kirim HTTP Push Notification + Email via ntfy.sh (atau self-hosted server).
     */
    public static function sendToUser(
        User $user,
        string $title,
        string $message,
        string $url = '',
        string $type = 'info', // 'info' | 'success' | 'warning' | 'error' | 'urgent'
        ?int $laporanId = null,
        ?string $kodeTiket = null
    ): bool {
        $userRole = is_object($user->role) ? $user->role->value : $user->role;

        $topic = match ($userRole) {
            'pimpasa' => "simpel_dbi_pimpasa_{$user->id}",
            'desa' => "simpel_dbi_desa_" . ($user->desa_id ?? $user->id),
            'kanwil' => "simpel_dbi_kanwil_monitoring",
            default => "simpel_dbi_user_{$user->id}",
        };

        $tags = match ($type) {
            'urgent', 'error' => 'rotating_light,warning,exclamation',
            'warning' => 'warning,bell',
            'success' => 'white_check_mark,tada',
            'purple' => 'file_folder,clipboard',
            default => 'information_source,incoming_envelope',
        };

        $priority = match ($type) {
            'urgent', 'error' => 'max',
            'warning' => 'high',
            'success' => 'default',
            default => 'default',
        };

        $headers = [
            'Title' => $title,
            'Priority' => $priority,
            'Tags' => $tags,
        ];

        if ($url) {
            $fullUrl = str_starts_with($url, 'http') ? $url : url($url);
            $headers['Actions'] = "view, Buka Tiket, {$fullUrl}";
        }

        try {
            $ntfyServer = config('services.ntfy.server', 'https://ntfy.sh');
            
            // 1. Push ke topic spesifik user
            Http::timeout(1.5)
                ->withHeaders($headers)
                ->withBody($message, 'text/plain')
                ->post("{$ntfyServer}/{$topic}");

            // 2. Jika role pimpasa atau kanwil, push juga ke topic role publik (agar HP / App tertutup tetap dapat notif)
            if (in_array($userRole, ['pimpasa', 'kanwil'])) {
                $roleTopic = "simpel_dbi_{$userRole}";
                Http::timeout(1.5)
                    ->withHeaders($headers)
                    ->withBody($message, 'text/plain')
                    ->post("{$ntfyServer}/{$roleTopic}");
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning("Gagal mengirim ntfy push ke topic {$topic}: " . $e->getMessage());
            return false;
        }
    }
}
