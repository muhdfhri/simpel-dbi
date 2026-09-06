<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SlaControlExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithMapping, WithCustomCsvSettings
{
    private int $rowNumber = 0;

    public function __construct(protected $incidentsList) {}

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ',',
            'enclosure' => '"',
            'line_ending' => "\r\n",
            'use_bom' => true,
        ];
    }

    public function collection(): \Illuminate\Support\Enumerable
    {
        return collect($this->incidentsList);
    }

    public function headings(): array
    {
        return [
            'NO',
            'NOMOR TIKET',
            'JUDUL ADUAN',
            'KATEGORI LAPORAN',
            'SATKER UPT PEMBINA',
            'DESA BINAAN',
            'DURASI PENANGANAN',
            'STATUS SLA (24 JAM)',
            'WAKTU PENGAJUAN',
        ];
    }

    public function map($item): array
    {
        $this->rowNumber++;

        $slaStatusLabel = match($item['sla_status'] ?? '') {
            'terlambat' => '🔴 TERLAMBAT (> 24 Jam)',
            'peringatan' => '🟡 PERINGATAN (Sisa < 6 Jam)',
            'tepat_waktu' => '🟢 TEPAT WAKTU',
            default => '🟢 TEPAT WAKTU'
        };

        return [
            $this->rowNumber,
            $item['nomor_tiket'] ?? '-',
            preg_replace('/\s+/', ' ', trim($item['judul'] ?? '')),
            $item['kategori'] ?? '-',
            $item['upt_nama'] ?? '-',
            $item['desa_nama'] ?? '-',
            ($item['hours_elapsed'] ?? 0) . ' Jam',
            $slaStatusLabel,
            $item['created_at_formatted'] ?? '-',
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
