<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MasterDataUserExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithMapping
{
    private int $rowNumber = 0;

    public function __construct(
        protected array|\Illuminate\Support\Collection $userList,
        protected string $userType // 'admin-kanwil' | 'pimpasa' | 'user-desa'
    ) {}

    public function collection(): \Illuminate\Support\Enumerable
    {
        return collect($this->userList);
    }

    public function headings(): array
    {
        if ($this->userType === 'admin-kanwil') {
            return [
                'NO',
                'NAMA ADMINISTRATOR',
                'EMAIL',
                'NIP',
                'GOLONGAN / PANGKAT',
                'TELEPON / KONTAK',
                'STATUS AKUN',
            ];
        } elseif ($this->userType === 'pimpasa') {
            return [
                'NO',
                'NAMA PETUGAS PIMPASA',
                'EMAIL',
                'NIP',
                'GOLONGAN / PANGKAT',
                'SATKER UPT IMIGRASI',
                'STATUS AKUN',
            ];
        } else {
            return [
                'NO',
                'NAMA PERANGKAT DESA',
                'EMAIL',
                'DESA BINAAN',
                'SATKER UPT PEMBINA',
                'TELEPON / KONTAK',
                'STATUS AKUN',
            ];
        }
    }

    public function map($item): array
    {
        $this->rowNumber++;
        
        $isActive = true;
        if (is_object($item) && isset($item->is_active)) {
            $isActive = (bool) $item->is_active;
        } elseif (is_array($item) && isset($item['is_active'])) {
            $isActive = (bool) $item['is_active'];
        }
        $statusStr = $isActive ? 'Aktif' : 'Non-Aktif';

        if ($this->userType === 'admin-kanwil') {
            $name = is_object($item) ? ($item->name ?? '-') : ($item['name'] ?? '-');
            $email = is_object($item) ? ($item->email ?? '-') : ($item['email'] ?? '-');
            $nip = is_object($item) ? ($item->nip ?? '-') : ($item['nip'] ?? '-');
            $golongan = is_object($item) ? ($item->golongan ?? '-') : ($item['golongan'] ?? '-');
            $kontak = is_object($item) ? ($item->kontak ?? '-') : ($item['kontak'] ?? '-');

            return [
                (string) $this->rowNumber,
                (string) $name,
                (string) $email,
                (string) $nip,
                (string) $golongan,
                (string) $kontak,
                (string) $statusStr,
            ];
        } elseif ($this->userType === 'pimpasa') {
            $name = is_object($item) ? ($item->name ?? '-') : ($item['name'] ?? '-');
            $email = is_object($item) ? ($item->email ?? '-') : ($item['email'] ?? '-');
            $nip = is_object($item) ? ($item->nip ?? '-') : ($item['nip'] ?? '-');
            $golongan = is_object($item) ? ($item->golongan ?? '-') : ($item['golongan'] ?? '-');
            
            $uptNama = '-';
            if (is_object($item)) {
                $uptNama = $item->upt?->nama ?? '-';
            } elseif (is_array($item) && isset($item['upt'])) {
                $uptNama = is_array($item['upt']) ? ($item['upt']['nama'] ?? '-') : ($item['upt']->nama ?? '-');
            }

            return [
                (string) $this->rowNumber,
                (string) $name,
                (string) $email,
                (string) $nip,
                (string) $golongan,
                (string) $uptNama,
                (string) $statusStr,
            ];
        } else {
            $name = is_object($item) ? ($item->name ?? '-') : ($item['name'] ?? '-');
            $email = is_object($item) ? ($item->email ?? '-') : ($item['email'] ?? '-');
            $kontak = is_object($item) ? ($item->kontak ?? '-') : ($item['kontak'] ?? '-');

            $desaNama = '-';
            $uptNama = '-';
            if (is_object($item)) {
                $desaNama = $item->desa?->nama ?? '-';
                $uptNama = $item->desa?->upt?->nama ?? '-';
            } elseif (is_array($item) && isset($item['desa'])) {
                $desaArr = $item['desa'];
                $desaNama = is_array($desaArr) ? ($desaArr['nama'] ?? '-') : ($desaArr->nama ?? '-');
                if (is_array($desaArr) && isset($desaArr['upt'])) {
                    $uptNama = is_array($desaArr['upt']) ? ($desaArr['upt']['nama'] ?? '-') : ($desaArr['upt']->nama ?? '-');
                }
            }

            return [
                (string) $this->rowNumber,
                (string) $name,
                (string) $email,
                (string) $desaNama,
                (string) $uptNama,
                (string) $kontak,
                (string) $statusStr,
            ];
        }
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '033566']
                ],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
