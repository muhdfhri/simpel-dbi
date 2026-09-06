<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KegiatanKanwilExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithMapping, WithCustomCsvSettings
{
    private int $rowNumber = 0;

    public function __construct(protected array $kegiatanList) {}

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
        return collect($this->kegiatanList);
    }

    public function headings(): array
    {
        return [
            'NO',
            'TANGGAL EXECUTION',
            'JUDUL KEGIATAN',
            'JENIS PEMBINAAN',
            'SATKER UPT PEMBINA',
            'DESA BINAAN',
            'PETUGAS PIMPASA',
            'LOKASI',
            'JUMLAH PESERTA',
            'BERKAS FOTO',
        ];
    }

    public function map($item): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $item['tanggal_formatted'] ?? '-',
            preg_replace('/\s+/', ' ', trim($item['judul'] ?? '')),
            $item['jenis_pembinaan'] ?? 'Penyuluhan Hukum',
            $item['upt_nama'] ?? '-',
            $item['desa_nama'] ?? '-',
            $item['pimpasa_nama'] ?? '-',
            $item['lokasi'] ?? '-',
            ($item['jumlah_peserta'] ?? 0) . ' Peserta',
            ($item['lampiran_count'] ?? 0) > 0 ? "{$item['lampiran_count']} Berkas Foto" : '0 Berkas',
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
