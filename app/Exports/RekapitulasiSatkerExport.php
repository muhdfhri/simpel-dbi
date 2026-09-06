<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapitulasiSatkerExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithMapping
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
            'TOTAL LAPORAN',
            'LAPORAN SELESAI',
            'PROSES LAPANGAN',
            'RESOLUTION RATE',
            'STATUS KERAWANAN',
        ];
    }

    public function map($item): array
    {
        $this->rowNumber++;

        $itemObj = (object) $item;
        $total = (int) ($itemObj->total_laporan ?? 0);
        $selesai = (int) ($itemObj->laporan_selesai ?? 0);
        $proses = max(0, $total - $selesai);
        $rate = $total > 0 ? round(($selesai / $total) * 100, 1) . '%' : '0%';

        $kerawananVal = $itemObj->indeks_kerawanan ?? 'rendah';
        $kerawananLabel = match($kerawananVal) {
            'tinggi' => 'Rentan / High Risk',
            'sedang' => 'Pembinaan Aktif',
            default => 'Kondusif / Aman'
        };

        return [
            $this->rowNumber,
            $itemObj->kode_desa ?? '-',
            $itemObj->nama ?? '-',
            $itemObj->kecamatan ?? '-',
            $itemObj->kabupaten ?? '-',
            $total . ' Tiket',
            $selesai . ' Selesai',
            $proses . ' Proses',
            $rate,
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
