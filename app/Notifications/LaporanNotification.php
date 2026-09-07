<?php

namespace App\Notifications;

use App\Models\User;
use App\Services\NtfyNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LaporanNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public string $type, // 'warning' | 'info' | 'success' | 'error' | 'purple' | 'urgent'
        public string $url,
        public ?int $laporanId = null,
        public ?string $kodeTiket = null
    ) {}

    public function via(object $notifiable): array
    {
        // Dispatch ntfy HTTP Push Notification
        if ($notifiable instanceof User) {
            NtfyNotificationService::sendToUser(
                user: $notifiable,
                title: $this->title,
                message: $this->message,
                url: $this->url,
                type: $this->type,
                laporanId: $this->laporanId,
                kodeTiket: $this->kodeTiket
            );
        }

        $channels = ['database'];

        // Kirim email jika user memiliki alamat email yang valid
        if ($notifiable instanceof User && ! empty($notifiable->email)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appUrl = config('app.url', 'http://localhost:8000');
        $fullUrl = str_starts_with($this->url, 'http') ? $this->url : rtrim($appUrl, '/').'/'.ltrim($this->url, '/');

        $statusLabel = match ($this->type) {
            'success' => 'Selesai / Diverifikasi',
            'warning' => 'Minta Perbaikan Data',
            'error'   => 'Ditolak',
            'purple'  => 'Ditindaklanjuti UPT',
            default   => 'Diajukan',
        };

        $subject = "[SIMPEL DBI] {$this->title}";
        if ($this->kodeTiket && ! str_contains($this->title, $this->kodeTiket)) {
            $subject .= " — {$this->kodeTiket}";
        }

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Yth. {$notifiable->name},")
            ->line("Bersama surat elektronik ini disampaikan pemberitahuan resmi dari Sistem Pelaporan Desa Binaan Imigrasi (SIMPEL DBI) Kanwil Ditjenim Sumatera Utara mengenai pembaruan status laporan sebagai berikut:")
            ->line("---")
            ->line("**Nomor Registrasi Tiket:** " . ($this->kodeTiket ?? '-'))
            ->line("**Status Terbaru:** {$statusLabel}")
            ->line("**Catatan / Keterangan:** {$this->message}")
            ->line("---")
            ->action('Lihat Detail Laporan', $fullUrl)
            ->line('Demikian pemberitahuan ini disampaikan. Atas perhatian dan kerja sama Anda, kami ucapkan terima kasih.')
            ->salutation("Hormat kami,\n\n**Kanwil Direktorat Jenderal Imigrasi Sumatera Utara**");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'url' => $this->url,
            'laporan_id' => $this->laporanId,
            'kode_tiket' => $this->kodeTiket,
        ];
    }
}

