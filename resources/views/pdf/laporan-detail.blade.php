<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Tiket {{ $laporan->kode_tiket }} - SIMPEL DBI</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

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
            border-bottom: 2.5px solid #033566;
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
            margin: 2px 0 0 0;
            font-size: 10px;
            color: #334155;
            font-weight: 600;
        }
        .header-title p {
            margin: 2px 0 0 0;
            font-size: 8.5px;
            color: #64748b;
        }
        .ticket-banner {
            width: 100%;
            background: #f8fafc;
            border: 1.5px dashed #033566;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 12px;
            box-sizing: border-box;
        }
        .ticket-code {
            font-family: 'Inter', monospace;
            font-size: 15px;
            font-weight: bold;
            color: #033566;
            letter-spacing: 1px;
        }
        .section-title {
            font-size: 10px;
            font-weight: bold;
            color: #033566;
            text-transform: uppercase;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 3px;
            margin-top: 10px;
            margin-bottom: 6px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .info-table td {
            padding: 4px 6px;
            font-size: 9.5px;
            vertical-align: top;
        }
        .info-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .kronologi-box {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 8px 10px;
            font-size: 9.5px;
            line-height: 1.4;
            white-space: pre-line;
            margin-bottom: 8px;
        }
        .badge {
            display: inline-block;
            padding: 2.5px 7px;
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
        .badge-ditolak { background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

        .audit-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 10px;
        }
        .audit-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 5px 6px;
            border: 1px solid #cbd5e1;
            text-align: left;
        }
        .audit-table td {
            padding: 4px 6px;
            border: 1px solid #e2e8f0;
            font-size: 8.5px;
            vertical-align: top;
        }

        .signature-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 9px;
            color: #1e293b;
        }
        .stamp-box {
            width: 100px;
            height: 45px;
            border: 1.5px dashed #94a3b8;
            margin: 6px auto;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 8px;
            font-weight: bold;
        }
        .footer-note {
            margin-top: 15px;
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
                <h2>LEMBAR BUKTI REGISTRASI TIKET LAPORAN</h2>
                <h4>SISTEM INFORMASI MANAJEMEN PELAPORAN DESA BINAAN (SIMPEL DBI)</h4>
                <p>Kementerian Ketenagakerjaan &amp; Direktorat Jenderal Imigrasi &bull; UPT Satker {{ $laporan->desa?->upt?->nama ?? 'Imigrasi Pembina' }}</p>
            </td>
        </tr>
    </table>

    <!-- Banner Tiket Kode -->
    <div class="ticket-banner">
        <table width="100%">
            <tr>
                <td>
                    <span style="font-size: 8px; color: #64748b; text-transform: uppercase;">Kode Tiket Registrasi:</span><br>
                    <span class="ticket-code">{{ $laporan->kode_tiket }}</span>
                </td>
                <td style="text-align: right;">
                    <span style="font-size: 8px; color: #64748b; text-transform: uppercase; display: block; margin-bottom: 2px;">Status Tiket:</span>
                    <span class="badge badge-{{ strtolower($statusStr) }}">
                        {{ $statusLabel }}
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <!-- Section 1: Informasi Desa & Pengaju -->
    <div class="section-title">1. Informasi Wilayah Desa &amp; Pengaju</div>
    <table class="info-table">
        <tr>
            <td width="20%"><strong>Desa Binaan</strong></td>
            <td width="30%">: {{ $laporan->desa?->nama ?? '-' }}</td>
            <td width="20%"><strong>Waktu Pengajuan</strong></td>
            <td width="30%">: {{ $submittedAtFormatted }}</td>
        </tr>
        <tr>
            <td><strong>Satker UPT Pembina</strong></td>
            <td>: {{ $laporan->desa?->upt?->nama ?? '-' }}</td>
            <td><strong>Diajukan Oleh</strong></td>
            <td>: Perangkat Desa ({{ $userName }})</td>
        </tr>
    </table>

    <!-- Section 2: Detail Kejadian Laporan -->
    <div class="section-title">2. Detail Kejadian Laporan</div>
    <table class="info-table">
        <tr>
            <td width="20%"><strong>Judul Laporan</strong></td>
            <td colspan="3">: <strong>{{ $laporan->judul }}</strong></td>
        </tr>
        <tr>
            <td><strong>Kategori Isu</strong></td>
            <td>: {{ $kategoriNama }}</td>
            <td><strong>Waktu Kejadian</strong></td>
            <td>: {{ $tanggalKejadianFormatted }}</td>
        </tr>
        <tr>
            <td><strong>Lokasi Detail</strong></td>
            <td>: {{ $laporan->lokasi_detail }}</td>
            <td><strong>Estimasi Terlibat</strong></td>
            <td>: {{ $laporan->estimasi_jumlah_orang ? $laporan->estimasi_jumlah_orang . ' Orang' : '-' }}</td>
        </tr>
    </table>

    <!-- Kronologi -->
    <div style="font-size: 9px; font-weight: bold; color: #334155; margin-top: 5px; margin-bottom: 2px;">Kronologi Kejadian:</div>
    <div class="kronologi-box">
        {{ $laporan->kronologi }}
    </div>

    <!-- Section 3: Catatan Verifikasi & Tindak Lanjut -->
    @if($laporan->verifikasi || $laporan->tindakLanjut)
        <div class="section-title">3. Catatan Verifikasi &amp; Tindak Lanjut</div>
        <table class="info-table">
            @if($laporan->verifikasi)
                <tr>
                    <td width="20%"><strong>PIMPASA Verifikator</strong></td>
                    <td width="30%">: {{ $laporan->verifikasi->pimpasa?->name ?? 'Tim Petugas PIMPASA' }}</td>
                    <td width="20%"><strong>Keputusan</strong></td>
                    <td width="30%">: {{ strtoupper(is_object($laporan->verifikasi->keputusan) ? $laporan->verifikasi->keputusan->value : $laporan->verifikasi->keputusan) }}</td>
                </tr>
                @if($laporan->verifikasi->catatan)
                    <tr>
                        <td><strong>Catatan PIMPASA</strong></td>
                        <td colspan="3">: "{{ $laporan->verifikasi->catatan }}"</td>
                    </tr>
                @endif
            @endif

            @if($laporan->tindakLanjut)
                <tr>
                    <td><strong>No. Registrasi UPT</strong></td>
                    <td>: {{ $laporan->tindakLanjut->nomor_registrasi }}</td>
                    <td><strong>Bentuk Intervensi</strong></td>
                    <td>: {{ $laporan->tindakLanjut->bentuk_intervensi }}</td>
                </tr>
                <tr>
                    <td><strong>Ringkasan Hasil</strong></td>
                    <td colspan="3">: {{ $laporan->tindakLanjut->ringkasan_hasil }}</td>
                </tr>
            @endif
        </table>
    @endif

    <!-- Section 4: Histori Audit Trail -->
    @if($laporan->statusHistories && count($laporan->statusHistories) > 0)
        <div class="section-title">4. Rekam Jejak Audit Trail ({{ count($laporan->statusHistories) }} Catatan)</div>
        <table class="audit-table">
            <thead>
                <tr>
                    <th width="4%" style="text-align: center;">No</th>
                    <th width="24%">Waktu</th>
                    <th width="22%">Aktor</th>
                    <th width="25%">Perubahan Status</th>
                    <th width="25%">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($laporan->statusHistories as $idx => $hist)
                    @php
                        $roleVal = is_object($hist->actor?->role) ? $hist->actor->role->value : ($hist->actor?->role ?? 'User');
                        $roleName = ucwords(str_replace('_', ' ', (string) $roleVal));
                        $statusDariVal = is_object($hist->status_dari) ? $hist->status_dari->value : ($hist->status_dari ?? '-');
                        $statusKeVal = is_object($hist->status_ke) ? $hist->status_ke->value : $hist->status_ke;
                    @endphp
                    <tr>
                        <td style="text-align: center; font-weight: bold;">{{ $idx + 1 }}</td>
                        <td>{{ $hist->created_at ? $hist->created_at->format('d M Y, H:i') : '-' }} WIB</td>
                        <td><strong>{{ $hist->actor?->name ?? 'Sistem' }}</strong> ({{ $roleName }})</td>
                        <td>
                            {{ ucwords(str_replace('_', ' ', (string) $statusDariVal)) }} &rarr; 
                            <strong>{{ ucwords(str_replace('_', ' ', (string) $statusKeVal)) }}</strong>
                        </td>
                        <td>{{ $hist->catatan ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Section 5: Pengesahan Kedinasan Clean -->
    <table class="signature-table">
        <tr>
            <td>
                <p style="margin-bottom: 2px;">Tim Pembina PIMPASA UPT,</p>
                <div class="stamp-box">[ STEMPEL PIMPASA ]</div>
                <p style="margin-top: 0; font-weight: bold;">( {{ $laporan->verifikasi->pimpasa?->name ?? 'Tim Petugas PIMPASA' }} )</p>
            </td>
            <td>
                <p style="margin-bottom: 2px;">Pelapor Perangkat Desa,</p>
                <div class="stamp-box">[ STEMPEL DESA ]</div>
                <p style="margin-top: 0; font-weight: bold;">( Perangkat Desa {{ $laporan->desa?->nama }} )</p>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Dokumen Bukti Registrasi resmi SIMPEL DBI &bull; Validasi Sistem Geospasial &bull; Printed: {{ date('d M Y, H:i') }} WIB
    </div>

</body>
</html>

