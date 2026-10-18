<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MasterDataWilayahExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithMapping
{
    private int $rowNumber = 0;

    public function __construct(
        protected array|\Illuminate\Support\Collection $itemList,
        protected string $subType // 'desa' | 'upt'
    ) {}

    public function collection(): \Illuminate\Support\Enumerable
    {
        return collect($this->itemList);
    }

    public function headings(): array
    {
        if ($this->subType === 'desa') {
            return [
                'NO',
                'NAMA DESA BINAAN',
                'SATKER UPT PEMBINA',
                'PETUGAS PIMPASA PENANGGUNG JAWAB',
                'KOORDINAT (LAT, LNG)',
                'STATUS TERKINI',
            ];
        } else {
            return [
                'NO',
                'NAMA SATKER UPT IMIGRASI',
                'TIPE SATKER',
                'JUMLAH DESA BINAAN',
                'JUMLAH PETUGAS PIMPASA',
            ];
        }
    }

    public function map($item): array
    {
        $this->rowNumber++;

        if ($this->subType === 'desa') {
            /** @var \App\Models\DesaBinaan $item */
            $namaDesa = is_object($item) ? ($item->nama ?? '-') : ($item['nama'] ?? '-');
            
            $uptNama = '-';
            if (is_object($item)) {
                $uptNama = $item->upt?->nama ?? '-';
            } elseif (isset($item['upt']) && is_array($item['upt'])) {
                $uptNama = $item['upt']['nama'] ?? '-';
            }

            $pimpasaNama = '-';
            if (is_object($item)) {
                $pimpasaNama = $item->pimpasa?->name ?? '-';
            } elseif (isset($item['pimpasa']) && is_array($item['pimpasa'])) {
                $pimpasaNama = $item['pimpasa']['name'] ?? '-';
            }

            $lat = is_object($item) ? $item->lat : ($item['lat'] ?? null);
            $lng = is_object($item) ? $item->lng : ($item['lng'] ?? null);
            $koordinat = ($lat && $lng) ? "{$lat}, {$lng}" : 'Belum diatur';

            $statusTerkini = is_object($item) ? ($item->status_terkini ?? 'Binaan Aktif') : ($item['status_terkini'] ?? 'Binaan Aktif');
            if (is_object($statusTerkini) && enum_exists(get_class($statusTerkini))) {
                $statusTerkini = $statusTerkini->value ?? (string)$statusTerkini;
            } elseif (is_object($statusTerkini)) {
                $statusTerkini = (string)$statusTerkini;
            }

            return [
                (string) $this->rowNumber,
                (string) $namaDesa,
                (string) $uptNama,
                (string) $pimpasaNama,
                (string) $koordinat,
                (string) $statusTerkini,
            ];
        } else {
            /** @var \App\Models\Upt $item */
            $namaUpt = is_object($item) ? ($item->nama ?? '-') : ($item['nama'] ?? '-');
            
            $tipeSatker = is_object($item) ? ($item->tipe ?? 'Kantor Imigrasi') : ($item['tipe'] ?? 'Kantor Imigrasi');
            if (is_object($tipeSatker) && enum_exists(get_class($tipeSatker))) {
                $tipeSatker = $tipeSatker->value ?? (string)$tipeSatker;
            } elseif (is_object($tipeSatker)) {
                $tipeSatker = (string)$tipeSatker;
            }

            $desaCount = 0;
            if (is_object($item)) {
                $desaCount = $item->desa_binaan_list_count ?? ($item->desaBinaanList ? $item->desaBinaanList->count() : 0);
            } elseif (is_array($item)) {
                $desaCount = $item['desa_binaan_list_count'] ?? (isset($item['desa_binaan_list']) ? count($item['desa_binaan_list']) : 0);
            }

            $userCount = 0;
            if (is_object($item)) {
                $userCount = $item->users_count ?? ($item->users ? $item->users->count() : 0);
            } elseif (is_array($item)) {
                $userCount = $item['users_count'] ?? (isset($item['users']) ? count($item['users']) : 0);
            }

            return [
                (string) $this->rowNumber,
                (string) $namaUpt,
                (string) $tipeSatker,
                (string) ($desaCount . ' Desa'),
                (string) ($userCount . ' Personel'),
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
