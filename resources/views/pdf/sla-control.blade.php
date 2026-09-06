<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kendali SLA & Eskalasi Insiden - SIMPEL DBI</title>
    <style>
        @page {
            margin: 25px 30px;
        }
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #0f172a;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #033566;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header-title {
            text-align: center;
        }
        .header-title h2 {
            margin: 0;
            font-size: 14px;
            color: #033566;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 700;
        }
        .header-title h4 {
            margin: 3px 0 0 0;
            font-size: 10.5px;
            color: #334155;
            font-weight: 600;
        }
        .header-title p {
            margin: 2px 0 0 0;
            font-size: 9px;
            color: #64748b;
        }
        .meta-info {
            width: 100%;
            margin-bottom: 14px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 6px 10px;
        }
        .meta-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-info td {
            font-size: 9px;
            color: #475569;
            padding: 2px 4px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        .data-table th {
            background-color: #033566;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8.5px;
            padding: 6px 8px;
            border: 1px solid #033566;
            text-align: left;
        }
        .data-table td {
            padding: 5.5px 8px;
            border: 1px solid #cbd5e1;
            font-size: 9px;
            vertical-align: middle;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
        }
        .badge-terlambat {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .badge-peringatan {
            background-color: #fffbe6;
            color: #b45309;
            border: 1px solid #fef08a;
        }
        .badge-tepat_waktu {
            background-color: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .footer-sig {
            margin-top: 25px;
            width: 100%;
        }
        .footer-sig table {
            width: 100%;
        }
        .footer-sig td {
            text-align: right;
            font-size: 9px;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td class="header-title">
                <h2>KANTOR WILAYAH DIREKTORAT JENDERAL IMIGRASI SUMATERA UTARA</h2>
                <h4>REKAPITULASI KENDALI SLA & ESKALASI INSIDEN (STANDAR 24 JAM)</h4>
                <p>Sistem Informasi Manajemen Pelayanan Desa Binaan Imigrasi (SIMPEL DBI)</p>
            </td>
        </tr>
    </table>

    <div class="meta-info">
        <table>
            <tr>
                <td width="15%"><strong>Filter Satker UPT</strong></td>
                <td width="35%">: {{ $filterUptNama }}</td>
                <td width="15%"><strong>Dicetak Pada</strong></td>
                <td width="35%">: {{ $tanggalCetak }}</td>
            </tr>
            <tr>
                <td><strong>Filter Status SLA</strong></td>
                <td>: {{ $filterSlaStatusNama }}</td>
                <td><strong>Dicetak Oleh</strong></td>
                <td>: {{ $pimpinanNama }} (Admin Kanwil)</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="4%" style="text-align: center;">NO</th>
                <th width="14%">NOMOR TIKET</th>
                <th width="24%">JUDUL ADUAN</th>
                <th width="15%">KATEGORI LAPORAN</th>
                <th width="16%">SATKER UPT PEMBINA</th>
                <th width="12%">DESA BINAAN</th>
                <th width="7%" style="text-align: center;">DURASI</th>
                <th width="8%" style="text-align: center;">STATUS SLA</th>
            </tr>
        </thead>
        <tbody>
            @forelse($incidents as $index => $inc)
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $index + 1 }}</td>
                    <td style="font-weight: bold;">{{ $inc['nomor_tiket'] }}</td>
                    <td>{{ $inc['judul'] }}</td>
                    <td>{{ $inc['kategori'] }}</td>
                    <td>{{ $inc['upt_nama'] }}</td>
                    <td>{{ $inc['desa_nama'] }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $inc['hours_elapsed'] }} Jam</td>
                    <td style="text-align: center;">
                        @if($inc['sla_status'] === 'terlambat')
                            <span class="badge badge-terlambat">TERLAMBAT</span>
                        @elseif($inc['sla_status'] === 'peringatan')
                            <span class="badge badge-peringatan">PERINGATAN</span>
                        @else
                            <span class="badge badge-tepat_waktu">TEPAT WAKTU</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #94a3b8; font-style: italic; padding: 15px;">
                        Tidak ada data insiden SLA yang ditemukan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-sig">
        <table>
            <tr>
                <td width="70%"></td>
                <td width="30%" style="text-align: center;">
                    <p style="margin-bottom: 45px;">Medan, {{ date('d F Y') }}<br><strong>Kantor Wilayah Direktorat Jenderal Imigrasi Sumatera Utara</strong></p>
                    <p><strong><u>ADMINISTRATOR KANWIL</u></strong></p>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
