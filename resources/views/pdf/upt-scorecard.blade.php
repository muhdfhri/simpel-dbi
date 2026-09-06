<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Scorecard Kepatuhan UPT</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1.5cm 1.5cm 1.5cm 1.5cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10pt;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #033566;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            font-size: 14pt;
            font-weight: 800;
            color: #033566;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header h3 {
            margin: 4px 0 0 0;
            font-size: 12pt;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
        }
        .header p {
            margin: 2px 0 0 0;
            font-size: 9pt;
            color: #64748b;
        }
        .meta-info {
            margin-bottom: 15px;
            font-size: 9pt;
            color: #475569;
        }
        .meta-info table {
            width: 100%;
            border: none;
        }
        .meta-info td {
            padding: 2px 0;
            border: none;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data-table th {
            background-color: #033566;
            color: #ffffff;
            font-weight: 700;
            font-size: 8.5pt;
            text-transform: uppercase;
            padding: 8px 6px;
            border: 1px solid #022446;
            text-align: center;
        }
        table.data-table td {
            padding: 7px 6px;
            font-size: 9pt;
            border: 1px solid #cbd5e1;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-semibold { font-weight: 600; }
        
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: 700;
            text-align: center;
        }
        .badge-success { background-color: #dcfce7; color: #15803d; }
        .badge-warning { background-color: #fef9c3; color: #a16207; }
        .badge-danger { background-color: #ffe4e6; color: #be123c; }

        .signature-container {
            margin-top: 30px;
            width: 100%;
            page-break-inside: avoid;
        }
        .signature-box {
            float: right;
            width: 280px;
            text-align: center;
            font-size: 9.5pt;
        }
        .signature-space {
            height: 60px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>KANTOR WILAYAH DIREKTORAT JENDERAL IMIGRASI SUMATERA UTARA</h2>
        <h3>SCORECARD KEPATUHAN & KINERJA UPT IMIGRASI</h3>
        <p>Sistem Informasi Monitoring Pimpasa & Desa Binaan Imigrasi (SIMPEL DESI)</p>
    </div>

    <div class="meta-info">
        <table>
            <tr>
                <td style="width: 15%;"><strong>Tanggal Cetak:</strong></td>
                <td style="width: 35%;">{{ $generated_at }}</td>
                <td style="width: 15%;"><strong>Dicetak Oleh:</strong></td>
                <td style="width: 35%;">{{ $user->name ?? 'Administrator Kanwil' }}</td>
            </tr>
            <tr>
                <td><strong>Total UPT Imigrasi:</strong></td>
                <td>{{ count($scorecards) }} Satker</td>
                <td><strong>Wilayah Kerja:</strong></td>
                <td>Sumatera Utara</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 30%;">Satker UPT Imigrasi</th>
                <th style="width: 10%;">Jumlah Desa</th>
                <th style="width: 11%;">Jumlah Pimpasa</th>
                <th style="width: 11%;">Total Tiket</th>
                <th style="width: 10%;">Selesai</th>
                <th style="width: 11%;">Rate Selesai</th>
                <th style="width: 10%;">Rerata SLA</th>
                <th style="width: 12%;">Status Kepatuhan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($scorecards as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-semibold">{{ $item['nama'] ?? '-' }}</td>
                    <td class="text-center">{{ number_format($item['desa_count'] ?? 0) }}</td>
                    <td class="text-center">{{ number_format($item['pimpasa_count'] ?? 0) }}</td>
                    <td class="text-center font-semibold">{{ number_format($item['total_laporan'] ?? 0) }}</td>
                    <td class="text-center font-semibold">{{ number_format($item['laporan_selesai'] ?? 0) }}</td>
                    <td class="text-center font-semibold">{{ $item['completion_rate'] ?? 0 }}%</td>
                    <td class="text-right">{{ $item['avg_sla_hours'] ?? 0 }} Jam</td>
                    <td class="text-center">
                        @if(($item['status_kepatuhan'] ?? '') === 'SANGAT BAIK')
                            <span class="badge badge-success">SANGAT BAIK</span>
                        @elseif(($item['status_kepatuhan'] ?? '') === 'CUKUP')
                            <span class="badge badge-warning">CUKUP</span>
                        @else
                            <span class="badge badge-danger">PERLU EVALUASI</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px; color: #64748b;">
                        Data Scorecard Kepatuhan UPT Tidak Tersedia.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature-container">
        <div class="signature-box">
            <p>Medan, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
            <p><strong>Kantor Wilayah Direktorat Jenderal Imigrasi Sumatera Utara</strong></p>
            <div class="signature-space"></div>
            <p style="text-decoration: underline; font-weight: bold;">( ___________________________ )</p>
            <p style="color: #64748b; font-size: 8.5pt;">Penanggung Jawab Executive Monitoring</p>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>
