<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KegiatanPembinaanExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithMapping
{
    private int $rowNumber = 0;

    public function __construct(protected $kegiatanList) {}

    public function collection(): \Illuminate\Support\Enumerable
    {
        return collect($this->kegiatanList);
    }

    public function headings(): array
    {
        return [
            'NO',
            'JUDUL KEGIATAN',
            'DESA BINAAN',
            'JENIS PEMBINAAN',
            'TANGGAL',
            'LOKASI',
            'JUMLAH PESERTA',
            'STATUS',
            'PETUGAS PIMPASA',
            'RINGKASAN MATERI',
        ];
    }

    public function map($item): array
    {
        $this->rowNumber++;
        $itemObj = (object) $item;

        return [
            $this->rowNumber,
            $itemObj->judul ?? '-',
            $itemObj->desa_nama ?? $itemObj->desa->nama ?? '-',
            $itemObj->jenis_pembinaan ?? '-',
            !empty($itemObj->tanggal) ? date('d/m/Y', strtotime($itemObj->tanggal)) : '-',
            $itemObj->lokasi ?? '-',
            ($itemObj->jumlah_peserta ?? 0) . ' Orang',
            strtoupper($itemObj->status ?? 'SELESAI'),
            $itemObj->petugas_nama ?? $itemObj->pimpasa->name ?? '-',
            $itemObj->ringkasan_materi ?? '-',
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
