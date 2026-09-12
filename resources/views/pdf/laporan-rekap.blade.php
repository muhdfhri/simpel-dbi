<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Laporan Desa - SIMPEL DBI</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        @page {
            margin: 25px 30px;
        }
        body {
            font-family: 'Inter', sans-serif, Helvetica, Arial;
            font-size: 10.5px;
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
            font-size: 9.5px;
            padding: 2px 4px;
            color: #334155;
        }
        .doc-title {
            text-align: center;
            margin: 12px 0 10px 0;
            font-size: 12px;
            font-weight: bold;
            color: #033566;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .data-table th {
            background-color: #033566;
            color: #ffffff;
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 8px;
            border: 1px solid #033566;
            text-align: left;
        }
        .data-table td {
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            font-size: 9.5px;
            vertical-align: top;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-diajukan { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-minta_perbaikan { background-color: #ffedd5; color: #9a3412; border: 1px solid #fed7aa; }
        .badge-diverifikasi { background-color: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
        .badge-ditindaklanjuti { background-color: #f3e8ff; color: #6b21a8; border: 1px solid #e9d5ff; }
        .badge-selesai { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }

        .signature-table {
            width: 100%;
            margin-top: 35px;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 9.5px;
            color: #1e293b;
        }
        .footer-note {
            margin-top: 25px;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
            text-align: right;
        }
    </style>
</head>
<body>

    <!-- Header / Kop Kedinasan Corporate -->
    <table class="header-table">
        <tr>
            <td class="header-title">
                <h2>PEMERINTAH REPUBLIK INDONESIA</h2>
                <h4>SISTEM INFORMASI MANAJEMEN PELAPORAN DESA BINAAN (SIMPEL DBI)</h4>
                <p>Kementerian Ketenagakerjaan &amp; Direktorat Jenderal Imigrasi &bull; Wilayah Sumatera Utara</p>
            </td>
        </tr>
    </table>

    <!-- Ringkasan Metadata PDF -->
    <div class="meta-info">
        <table>
            <tr>
                <td width="15%"><strong>Desa Binaan</strong></td>
                <td width="35%">: {{ $desaNama }}</td>
                <td width="15%"><strong>Tanggal Cetak</strong></td>
                <td width="35%">: {{ $tanggalCetak }} WIB</td>
            </tr>
            <tr>
                <td><strong>Satker UPT</strong></td>
                <td>: {{ $uptNama }}</td>
                <td><strong>Periode Filter</strong></td>
                <td>: {{ $periodeText ?? 'Semua Periode' }}</td>
            </tr>
            <tr>
                <td><strong>Total Laporan</strong></td>
                <td colspan="3">: {{ count($laporanList) }} Tiket Terdaftar</td>
            </tr>
        </table>
    </div>

    <div class="doc-title">
        REKAPITULASI DOKUMEN LAPORAN KEJADIAN DESA
    </div>

    <!-- Tabel Data Laporan -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%" style="text-align: center;">No</th>
                <th width="18%">Kode Tiket</th>
                <th width="32%">Judul Laporan</th>
                <th width="18%">Kategori</th>
                <th width="14%">Status</th>
                <th width="13%">Waktu Pengajuan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporanList as $index => $item)
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $index + 1 }}</td>
                    <td style="font-weight: bold; font-family: monospace;">{{ $item['kode_tiket'] }}</td>
                    <td>{{ $item['judul'] }}</td>
                    <td>{{ $item['kategori'] }}</td>
                    <td>
                        <span class="badge badge-{{ strtolower(is_object($item['status']) ? $item['status']->value : $item['status']) }}">
                            {{ $item['status_label'] }}
                        </span>
                    </td>
                    <td>{{ $item['submitted_at_formatted'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #94a3b8; padding: 20px;">
                        Tidak ada data laporan yang sesuai dengan filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Kolom Tanda Tangan Pengesahan Kedinasan Clean -->
    <table class="signature-table">
        <tr>
            <td>
                <p style="margin-bottom: 2px;">Mengetahui,</p>
                <p style="margin-top: 0; font-weight: bold; text-transform: uppercase;">Tim Pembina PIMPASA UPT</p>
                <br><br><br><br>
                <p style="margin-bottom: 2px;"><strong>( Tim Petugas PIMPASA )</strong></p>
                <p style="margin-top: 0; color: #64748b; font-size: 8.5px;">Pembina Wilayah {{ $uptNama }}</p>
            </td>
            <td>
                <p style="margin-bottom: 2px;">Dicetak Oleh,</p>
                <p style="margin-top: 0; font-weight: bold; text-transform: uppercase;">Perangkat Desa Binaan</p>
                <br><br><br><br>
                <p style="margin-bottom: 2px;"><strong>( Perangkat Desa {{ $desaNama }} )</strong></p>
                <p style="margin-top: 0; color: #64748b; font-size: 8.5px;">Operator Pelapor Desa</p>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Dokumen ini dihasilkan secara otomatis oleh Sistem Pemetaan Geospasial SIMPEL DBI &bull; Ketenagakerjaan &amp; Keimigrasian RI
    </div>

</body>
</html>
