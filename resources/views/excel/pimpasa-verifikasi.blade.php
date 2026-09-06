<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Worklist Verifikasi Laporan Desa</title>
    <style>
        body {
            font-family: 'Calibri', 'Segoe UI', Arial, sans-serif;
            font-size: 11pt;
            color: #0f172a;
        }
        .header-title {
            font-size: 14pt;
            font-weight: bold;
            color: #033566;
            text-align: center;
        }
        .header-subtitle {
            font-size: 11pt;
            font-weight: bold;
            color: #334155;
            text-align: center;
        }
        .meta-info {
            font-size: 10pt;
            color: #475569;
            text-align: center;
        }
        .table-data {
            border-collapse: collapse;
            width: 100%;
            margin-top: 15px;
        }
        .table-data th {
            background-color: #033566;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            border: 1px solid #022a52;
            padding: 8px 12px;
            font-size: 10.5pt;
        }
        .table-data td {
            border: 1px solid #cbd5e1;
            padding: 7px 10px;
            font-size: 10pt;
            vertical-align: middle;
        }
        .bg-even {
            background-color: #f8fafc;
        }
        .text-center {
            text-align: center;
        }
        .text-left {
            text-align: left;
        }
        .font-bold {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <table style="width: 100%;">
        <tr>
            <td colspan="8" class="header-title">SISTEM INFORMASI PELAPORAN DESA BINAAN IMIGRASI (SIMPEL DBI)</td>
        </tr>
        <tr>
            <td colspan="8" class="header-subtitle">WORKLIST VERIFIKASI LAPORAN DESA BINAAN UPT IMIGRASI</td>
        </tr>
        <tr>
            <td colspan="8" class="meta-info">
                Petugas Verifikator: <b>{{ $pimpasaNama }}</b> | Satker: <b>{{ $uptNama }}</b> | Tanggal Ekspor: <b>{{ date('d F Y, H:i') }} WIB</b> | Total: <b>{{ count($laporanList) }} Data</b>
            </td>
        </tr>
        <tr><td colspan="8">&nbsp;</td></tr>
    </table>

    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 50px;">No</th>
                <th style="width: 140px;">Kode Tiket</th>
                <th style="width: 250px;">Judul Laporan</th>
                <th style="width: 200px;">Desa Binaan & Kecamatan</th>
                <th style="width: 180px;">Kategori Laporan</th>
                <th style="width: 150px;">Waktu Pengajuan</th>
                <th style="width: 120px;">Berkas Lampiran</th>
                <th style="width: 160px;">Status Siklus</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporanList as $idx => $item)
                @php
                    $statusVal = is_object($item->status) ? $item->status->value : $item->status;
                    $statusLabel = match($statusVal) {
                        'diajukan' => 'Diajukan',
                        'minta_perbaikan' => 'Minta Perbaikan',
                        'diverifikasi' => 'Diverifikasi PIMPASA',
                        'ditindaklanjuti' => 'Ditindaklanjuti PIMPASA',
                        'selesai' => 'Selesai 100%',
                        'ditolak' => 'Ditolak',
                        default => ucwords(str_replace('_', ' ', $statusVal))
                    };
                    $lampiranCount = $item->lampiranList ? count($item->lampiranList) : 0;
                @endphp
                <tr class="{{ $idx % 2 === 1 ? 'bg-even' : '' }}">
                    <td class="text-center font-bold">{{ $idx + 1 }}</td>
                    <td class="text-center font-bold" style="mso-number-format:'\@';">{{ $item->kode_tiket }}</td>
                    <td class="text-left">{{ $item->judul }}</td>
                    <td class="text-left">{{ $item->desa?->nama ?? '-' }} (Kec. {{ $item->desa?->kecamatan ?? '-' }})</td>
                    <td class="text-left">{{ $item->kategoriRef?->nama_kategori ?? $item->kategori ?? '-' }}</td>
                    <td class="text-center">{{ $item->submitted_at ? $item->submitted_at->format('d/m/Y H:i') : '-' }}</td>
                    <td class="text-center font-bold">{{ $lampiranCount > 0 ? $lampiranCount . ' File' : '-' }}</td>
                    <td class="text-center font-bold">{{ $statusLabel }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px; color: #94a3b8;">
                        Tidak ada antrean laporan yang tersedia
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
