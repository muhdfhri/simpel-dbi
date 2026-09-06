<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class UptScorecardExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected array $scorecards;

    public function __construct(array $scorecards)
    {
        $this->scorecards = $scorecards;
    }

    public function title(): string
    {
        return 'Scorecard Kepatuhan UPT';
    }

    public function array(): array
    {
        $data = [];
        foreach ($this->scorecards as $index => $item) {
            $data[] = [
                'no' => $index + 1,
                'nama' => $item['nama'] ?? '-',
                'desa_count' => $item['desa_count'] ?? 0,
                'pimpasa_count' => $item['pimpasa_count'] ?? 0,
                'total_laporan' => $item['total_laporan'] ?? 0,
                'laporan_selesai' => $item['laporan_selesai'] ?? 0,
                'completion_rate' => ($item['completion_rate'] ?? 0) . '%',
                'avg_sla_hours' => ($item['avg_sla_hours'] ?? 0) . ' Jam',
                'status_kepatuhan' => $item['status_kepatuhan'] ?? '-',
            ];
        }
        return $data;
    }

    public function headings(): array
    {
        return [
            'NO',
            'SATKER UPT IMIGRASI',
            'JUMLAH DESA',
            'JUMLAH PIMPASA',
            'TOTAL TIKET LAPORAN',
            'SELESAI',
            'RATE PENYELESAIAN (%)',
            'RERATA SLA',
            'STATUS KEPATUHAN',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 38,
            'C' => 16,
            'D' => 18,
            'E' => 22,
            'F' => 14,
            'G' => 24,
            'H' => 16,
            'I' => 22,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = count($this->scorecards) + 1;

        // Header style
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '033566'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(30);

        // Data rows style
        $sheet->getStyle("A2:I{$lastRow}")->applyFromArray([
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E2E8F0'],
                ],
            ],
        ]);

        // Specific column alignments
        $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("C2:G{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("H2:H{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("I2:I{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }
}
