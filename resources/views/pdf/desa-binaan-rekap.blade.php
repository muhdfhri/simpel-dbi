<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Direktori & Profil Desa Binaan - SIMPEL DBI</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        @page {
            margin: 25px 30px;
        }
        body {
            font-family: 'Inter', sans-serif, Helvetica, Arial;
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
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 6px;
            border: 1px solid #033566;
            text-align: left;
        }
        .data-table td {
            padding: 6px 6px;
            border: 1px solid #cbd5e1;
            font-size: 9px;
            vertical-align: top;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-rendah { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge-sedang { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-tinggi { background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

        .signature-table {
            width: 100%;
            margin-top: 30px;
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
            margin-top: 20px;
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
                <td width="15%"><strong>Petugas Pembina</strong></td>
                <td width="35%">: {{ $pimpasaNama }}</td>
                <td width="15%"><strong>Tanggal Cetak</strong></td>
                <td width="35%">: {{ $tanggalCetak }} WIB</td>
            </tr>
            <tr>
                <td><strong>Satker UPT</strong></td>
                <td>: {{ $uptNama }}</td>
                <td><strong>Total Wilayah</strong></td>
                <td>: {{ count($desaList) }} Desa Binaan</td>
            </tr>
        </table>
    </div>

    <div class="doc-title">
        DIREKTORI &amp; REKAPITULASI PROFIL DESA BINAAN UPT
    </div>

    <!-- Tabel Data Desa Binaan -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="4%" style="text-align: center;">No</th>
                <th width="12%">Kode Desa</th>
                <th width="22%">Nama Desa Binaan</th>
                <th width="18%">Wilayah Administratif</th>
                <th width="22%">Perangkat Desa / Kontak</th>
                <th width="10%" style="text-align: center;">Total Laporan</th>
                <th width="12%">Status Kerawanan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($desaList as $index => $item)
                @php
                    $itemObj = (object) $item;
                    $kerawananVal = $itemObj->indeks_kerawanan ?? 'rendah';
                    $kerawananLabel = match($kerawananVal) {
                        'tinggi' => 'Rentan / Aduan',
                        'sedang' => 'Pembinaan Aktif',
                        default => 'Kondusif / Aman'
                    };
                @endphp
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $index + 1 }}</td>
                    <td style="font-weight: bold; font-family: monospace;">{{ $itemObj->kode_desa ?? '-' }}</td>
                    <td><strong>{{ $itemObj->nama ?? '-' }}</strong></td>
                    <td>
                        Kec. {{ $itemObj->kecamatan ?? '-' }}<br>
                        <span style="color: #64748b; font-size: 8.5px;">{{ $itemObj->kabupaten ?? '-' }}</span>
                    </td>
                    <td>
                        <strong>{{ $itemObj->kepala_desa ?? '-' }}</strong><br>
                        <span style="color: #64748b; font-size: 8.5px;">{{ $itemObj->kontak ?? '-' }}</span>
                    </td>
                    <td style="text-align: center; font-weight: bold;">
                        {{ $itemObj->total_laporan ?? 0 }} Tiket
                    </td>
                    <td>
                        <span class="badge badge-{{ strtolower($kerawananVal) }}">
                            {{ $kerawananLabel }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #94a3b8; padding: 20px;">
                        Tidak ada data desa binaan yang ditemukan.
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
                <p style="margin-top: 0; font-weight: bold; text-transform: uppercase;">Kepala Seksi / Atasan UPT</p>
                <br><br><br><br>
                <p style="margin-bottom: 2px;"><strong>( Pejabat Struktural UPT )</strong></p>
                <p style="margin-top: 0; color: #64748b; font-size: 8.5px;">Satker {{ $uptNama }}</p>
            </td>
            <td>
                <p style="margin-bottom: 2px;">Dicetak Oleh,</p>
                <p style="margin-top: 0; font-weight: bold; text-transform: uppercase;">Petugas Pembina PIMPASA</p>
                <br><br><br><br>
                <p style="margin-bottom: 2px;"><strong>( {{ $pimpasaNama }} )</strong></p>
                <p style="margin-top: 0; color: #64748b; font-size: 8.5px;">Petugas Pembina PIMPASA</p>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Dokumen ini dihasilkan secara otomatis oleh Sistem Pemetaan Geospasial SIMPEL DBI &bull; Ketenagakerjaan &amp; Keimigrasian RI
    </div>

</body>
</html>
