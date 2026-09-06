<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Kegiatan Pembinaan Desa Binaan Imigrasi</title>
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
        .badge-selesai { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge-terjadwal { background-color: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
        .badge-dibatalkan { background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

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
                <td width="18%"><strong>Satker UPT</strong></td>
                <td width="32%">: {{ $uptNama }}</td>
                <td width="18%"><strong>Tanggal Cetak</strong></td>
                <td width="32%">: {{ $tanggalCetak }} WIB</td>
            </tr>
            <tr>
                <td><strong>Total Agenda</strong></td>
                <td>: {{ $stats['total_kegiatan'] ?? count($kegiatanList) }} Kegiatan</td>
                <td><strong>Total Peserta Terbina</strong></td>
                <td>: {{ $stats['total_peserta'] ?? 0 }} Orang</td>
            </tr>
            <tr>
                <td><strong>Cakupan Desa</strong></td>
                <td>: {{ $stats['total_desa'] ?? 0 }} Desa Binaan</td>
                <td><strong>Petugas Pencetak</strong></td>
                <td>: {{ $pimpasaNama }}</td>
            </tr>
        </table>
    </div>

    <div class="doc-title">
        LAPORAN REKAPITULASI KEGIATAN PEMBINAAN DESA BINAAN IMIGRASI
    </div>

    <!-- Tabel Data Rekapitulasi Kegiatan Pembinaan -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="4%" style="text-align: center;">No</th>
                <th width="24%">Judul &amp; Jenis Pembinaan</th>
                <th width="16%">Desa Binaan &amp; Lokasi</th>
                <th width="12%">Tanggal</th>
                <th width="10%" style="text-align: center;">Peserta</th>
                <th width="10%" style="text-align: center;">Status</th>
                <th width="24%">Ringkasan Materi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kegiatanList as $index => $item)
                @php
                    $itemObj = (object) $item;
                    $statusVal = strtolower($itemObj->status ?? 'selesai');
                    $statusBadgeClass = match($statusVal) {
                        'terjadwal' => 'badge-terjadwal',
                        'dibatalkan' => 'badge-dibatalkan',
                        default => 'badge-selesai',
                    };
                @endphp
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $itemObj->judul ?? '-' }}</strong><br>
                        <span style="color: #047857; font-size: 8.5px; font-weight: 600;">[{{ $itemObj->jenis_pembinaan ?? '-' }}]</span>
                    </td>
                    <td>
                        <strong>{{ $itemObj->desa_nama ?? $itemObj->desa->nama ?? '-' }}</strong><br>
                        <span style="color: #64748b; font-size: 8.5px;">{{ $itemObj->lokasi ?? '-' }}</span>
                    </td>
                    <td>
                        {{ !empty($itemObj->tanggal) ? date('d/m/Y', strtotime($itemObj->tanggal)) : '-' }}
                    </td>
                    <td style="text-align: center; font-weight: bold;">
                        {{ $itemObj->jumlah_peserta ?? 0 }} Org
                    </td>
                    <td style="text-align: center;">
                        <span class="badge {{ $statusBadgeClass }}">
                            {{ strtoupper($statusVal) }}
                        </span>
                    </td>
                    <td>
                        {{ $itemObj->ringkasan_materi ?? '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #94a3b8; padding: 20px;">
                        Tidak ada data kegiatan pembinaan yang ditemukan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Kolom Tanda Tangan Pengesahan Kedinasan -->
    <table class="signature-table">
        <tr>
            <td>
                <p style="margin-bottom: 2px;">Mengetahui,</p>
                <p style="margin-top: 0; font-weight: bold; text-transform: uppercase;">Kepala Kantor / Satker UPT</p>
                <br><br><br><br>
                <p style="margin-bottom: 2px;"><strong>( Kepala Satker UPT )</strong></p>
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
        Dokumen ini dihasilkan secara otomatis oleh Sistem Informasi SIMPEL DBI &bull; Ketenagakerjaan &amp; Keimigrasian RI
    </div>

</body>
</html>
