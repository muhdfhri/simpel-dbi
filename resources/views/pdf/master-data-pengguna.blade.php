<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Data {{ $judulTipe }} - SIMPEL DBI</title>
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
            text-weight: 600;
            text-transform: uppercase;
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
            background-color: #e2e8f0;
            color: #1e293b;
        }
        .badge-success {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }
        .badge-danger {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
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
                <h4>DATA MASTER MANAJEMEN PENGGUNA - {{ $judulTipe }}</h4>
                <p>Sistem Informasi Manajemen Pelayanan Desa Binaan Imigrasi (SIMPEL DBI)</p>
            </td>
        </tr>
    </table>

    <div class="meta-info">
        <table>
            <tr>
                <td width="15%"><strong>Kategori Pengguna</strong></td>
                <td width="35%">: {{ $judulTipe }}</td>
                <td width="15%"><strong>Dicetak Pada</strong></td>
                <td width="35%">: {{ $tanggalCetak }}</td>
            </tr>
            <tr>
                <td><strong>Lingkup Wilayah</strong></td>
                <td>: Kanwil Kemenkumham Sumut / Ditjen Imigrasi</td>
                <td><strong>Dicetak Oleh</strong></td>
                <td>: {{ $pimpinanNama }} (Admin Kanwil)</td>
            </tr>
            <tr>
                <td><strong>Total Record Data</strong></td>
                <td colspan="3">: {{ count($userList) }} Pengguna Terdaftar</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="4%" style="text-align: center;">NO</th>
                @if($userType === 'admin-kanwil')
                    <th width="26%">NAMA LENGKAP</th>
                    <th width="24%">EMAIL</th>
                    <th width="18%">NIP</th>
                    <th width="16%">GOLONGAN / PANGKAT</th>
                    <th width="12%" style="text-align: center;">STATUS</th>
                @elseif($userType === 'pimpasa')
                    <th width="22%">NAMA PETUGAS</th>
                    <th width="22%">EMAIL</th>
                    <th width="16%">NIP / GOLONGAN</th>
                    <th width="28%">SATKER UPT IMIGRASI</th>
                    <th width="12%" style="text-align: center;">STATUS</th>
                @else
                    <th width="22%">NAMA PERANGKAT DESA</th>
                    <th width="22%">EMAIL</th>
                    <th width="24%">DESA BINAAN</th>
                    <th width="20%">SATKER UPT PEMBINA</th>
                    <th width="12%" style="text-align: center;">STATUS</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($userList as $index => $user)
                @php
                    $u = (object) $user;
                    $isActive = isset($u->is_active) ? (bool)$u->is_active : true;
                @endphp
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $index + 1 }}</td>
                    
                    @if($userType === 'admin-kanwil')
                        <td style="font-weight: bold;">{{ $u->name ?? '-' }}</td>
                        <td>{{ $u->email ?? '-' }}</td>
                        <td>{{ $u->nip ?? '-' }}</td>
                        <td>{{ $u->golongan ?? '-' }}</td>
                        <td style="text-align: center;">
                            <span class="badge {{ $isActive ? 'badge-success' : 'badge-danger' }}">
                                {{ $isActive ? 'Aktif' : 'Non-Aktif' }}
                            </span>
                        </td>
                    @elseif($userType === 'pimpasa')
                        <td style="font-weight: bold;">{{ $u->name ?? '-' }}</td>
                        <td>{{ $u->email ?? '-' }}</td>
                        <td>
                            <strong>{{ $u->nip ?? '-' }}</strong>
                            @if(!empty($u->golongan))
                                <br><span style="color: #64748b;">{{ $u->golongan }}</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $uptNama = is_array($u->upt ?? null) ? ($u->upt['nama'] ?? '-') : ($u->upt->nama ?? '-');
                            @endphp
                            {{ $uptNama }}
                        </td>
                        <td style="text-align: center;">
                            <span class="badge {{ $isActive ? 'badge-success' : 'badge-danger' }}">
                                {{ $isActive ? 'Aktif' : 'Non-Aktif' }}
                            </span>
                        </td>
                    @else
                        <td style="font-weight: bold;">{{ $u->name ?? '-' }}</td>
                        <td>{{ $u->email ?? '-' }}</td>
                        <td>
                            @php
                                $desaNama = is_array($u->desa ?? null) ? ($u->desa['nama'] ?? '-') : ($u->desa->nama ?? '-');
                            @endphp
                            <strong>{{ $desaNama }}</strong>
                        </td>
                        <td>
                            @php
                                $uptNama = '-';
                                if (isset($u->desa)) {
                                    $desaObj = is_array($u->desa) ? (object)$u->desa : $u->desa;
                                    if (isset($desaObj->upt)) {
                                        $uptNama = is_array($desaObj->upt) ? ($desaObj->upt['nama'] ?? '-') : ($desaObj->upt->nama ?? '-');
                                    }
                                }
                            @endphp
                            {{ $uptNama }}
                        </td>
                        <td style="text-align: center;">
                            <span class="badge {{ $isActive ? 'badge-success' : 'badge-danger' }}">
                                {{ $isActive ? 'Aktif' : 'Non-Aktif' }}
                            </span>
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #94a3b8; font-style: italic; padding: 15px;">
                        Tidak ada data pengguna terdaftar yang ditemukan.
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
                    <p style="margin-bottom: 45px;">Medan, {{ date('d F Y') }}<br><strong>Kantor Wilayah Ditjen Imigrasi Sumatera Utara</strong></p>
                    <p><strong><u>ADMINISTRATOR KANWIL</u></strong></p>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
