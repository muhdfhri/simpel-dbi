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

        if (! empty($user->email)) {
            $headers['Email'] = $user->email;
        }

        try {
            $ntfyServer = config('services.ntfy.server', 'https://ntfy.sh');
            $response = Http::timeout(5)
                ->withHeaders($headers)
                ->withBody($message, 'text/plain')
                ->post("{$ntfyServer}/{$topic}");

            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning("Gagal mengirim ntfy push ke topic {$topic}: " . $e->getMessage());
            return false;
        }
    }
}
