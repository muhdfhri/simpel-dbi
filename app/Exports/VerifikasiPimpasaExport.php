<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VerifikasiPimpasaExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithMapping, WithCustomCsvSettings
{
    private int $rowNumber = 0;

    public function __construct(protected $laporanList) {}

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
        return collect($this->laporanList);
    }

    public function headings(): array
    {
        return [
            'NO',
            'KODE TIKET',
            'JUDUL LAPORAN',
            'DESA BINAAN',
            'KECAMATAN',
            'KATEGORI LAPORAN',
            'JUMLAH BERKAS',
            'STATUS SIKLUS',
            'WAKTU PENGAJUAN',
        ];
    }

    public function map($item): array
    {
        $this->rowNumber++;

        $statusVal = is_object($item->status) ? $item->status->value : $item->status;
        $statusLabel = match($statusVal) {
            'diajukan' => 'Diajukan',
            'minta_perbaikan' => 'Minta Perbaikan',
            'diverifikasi' => 'Diverifikasi PIMPASA',
            'ditindaklanjuti' => 'Ditindaklanjuti PIMPASA',
            'selesai' => 'Selesai 100%',
            'ditolak' => 'Ditolak',
            default => ucwords(str_replace('_', ' ', (string) $statusVal))
        };
        $lampiranCount = $item->lampiranList ? count($item->lampiranList) : 0;

        return [
            $this->rowNumber,
            $item->kode_tiket,
            preg_replace('/\s+/', ' ', trim($item->judul ?? '')),
            $item->desa?->nama ?? '-',
            $item->desa?->kecamatan ?? '-',
            $item->kategoriRef?->nama_kategori ?? $item->kategori ?? '-',
            $lampiranCount > 0 ? "{$lampiranCount} Berkas" : '0 Berkas',
            $statusLabel,
            $item->submitted_at ? $item->submitted_at->format('d/m/Y H:i') : '-',
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
