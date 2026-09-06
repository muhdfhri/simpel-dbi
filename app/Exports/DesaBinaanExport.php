<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DesaBinaanExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithMapping
{
    private int $rowNumber = 0;

    public function __construct(protected $desaList) {}

    public function collection(): \Illuminate\Support\Enumerable
    {
        return collect($this->desaList);
    }

    public function headings(): array
    {
        return [
            'NO',
            'KODE DESA',
            'NAMA DESA BINAAN',
            'KECAMATAN',
            'KABUPATEN / KOTA',
            'PERANGKAT DESA / KONTAK',
            'TOTAL LAPORAN',
            'LAPORAN SELESAI',
            'STATUS KERAWANAN',
        ];
    }

    public function map($item): array
    {
        $this->rowNumber++;

        $itemObj = (object) $item;
        $kerawananVal = $itemObj->indeks_kerawanan ?? 'rendah';
        $kerawananLabel = match($kerawananVal) {
            'tinggi' => 'Rentan / Aduan Aktif',
            'sedang' => 'Pembinaan Aktif',
            default => 'Kondusif / Aman'
        };

        $kontakStr = !empty($itemObj->kontak) ? " ({$itemObj->kontak})" : '';

        return [
            $this->rowNumber,
            $itemObj->kode_desa ?? '-',
            $itemObj->nama ?? '-',
            $itemObj->kecamatan ?? '-',
            $itemObj->kabupaten ?? '-',
            ($itemObj->kepala_desa ?? '-') . $kontakStr,
            ($itemObj->total_laporan ?? 0) . ' Tiket',
            ($itemObj->laporan_selesai ?? 0) . ' Selesai',
            $kerawananLabel,
        ];
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
