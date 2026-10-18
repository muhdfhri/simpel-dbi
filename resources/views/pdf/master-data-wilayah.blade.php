<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Master Data {{ $judulTipe }} - SIMPEL DBI</title>
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
        .badge-info {
            background-color: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
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
                <h4>DATA MASTER WILAYAH & PEMBINAAN - {{ $judulTipe }}</h4>
                <p>Sistem Informasi Manajemen Pelayanan Desa Binaan Imigrasi (SIMPEL DBI)</p>
            </td>
        </tr>
    </table>

    <div class="meta-info">
        <table>
            <tr>
                <td width="18%"><strong>Kategori Master Data</strong></td>
                <td width="32%">: {{ $judulTipe }}</td>
                <td width="15%"><strong>Dicetak Pada</strong></td>
                <td width="35%">: {{ $tanggalCetak }}</td>
            </tr>
            <tr>
                <td><strong>Lingkup Wilayah</strong></td>
                <td>: Provinsi Sumatera Utara</td>
                <td><strong>Dicetak Oleh</strong></td>
                <td>: {{ $pimpinanNama }} (Admin Kanwil)</td>
            </tr>
            <tr>
                <td><strong>Total Record Data</strong></td>
                <td colspan="3">: {{ count($itemList) }} {{ $subType === 'desa' ? 'Desa Binaan' : 'Satker UPT' }} Terdaftar</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="4%" style="text-align: center;">NO</th>
                @if($subType === 'desa')
                    <th width="28%">NAMA DESA BINAAN</th>
                    <th width="26%">SATKER UPT PEMBINA</th>
                    <th width="24%">PETUGAS PIMPASA PENANGGUNG JAWAB</th>
                    <th width="18%" style="text-align: center;">STATUS TERKINI</th>
                @else
                    <th width="40%">NAMA SATKER UPT IMIGRASI</th>
                    <th width="22%">TIPE SATKER</th>
                    <th width="17%" style="text-align: center;">DESA BINAAN</th>
                    <th width="17%" style="text-align: center;">PERSONEL UPT</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($itemList as $index => $item)
                @php
                    $row = (object) $item;
                @endphp
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $index + 1 }}</td>
                    
                    @if($subType === 'desa')
                        @php
                            $uptNama = is_array($row->upt ?? null) ? ($row->upt['nama'] ?? '-') : ($row->upt->nama ?? '-');
                            $pimpasaNama = is_array($row->pimpasa ?? null) ? ($row->pimpasa['name'] ?? '-') : ($row->pimpasa->name ?? '-');
                        @endphp
                        <td style="font-weight: bold;">{{ $row->nama ?? '-' }}</td>
                        <td>{{ $uptNama }}</td>
                        <td>{{ $pimpasaNama }}</td>
                        <td style="text-align: center;">
                            <span class="badge badge-success">
                                {{ $row->status_terkini ?? 'Binaan Aktif' }}
                            </span>
                        </td>
                    @else
                        @php
                            $desaCount = $row->desa_binaan_list_count ?? (is_array($row->desa_binaan_list ?? null) ? count($row->desa_binaan_list) : 0);
                            $userCount = $row->users_count ?? (is_array($row->users ?? null) ? count($row->users) : 0);
                        @endphp
                        <td style="font-weight: bold;">{{ $row->nama ?? '-' }}</td>
                        <td><span class="badge badge-info">{{ $row->tipe ?? 'Kantor Imigrasi' }}</span></td>
                        <td style="text-align: center; font-weight: bold;">{{ $desaCount }} Desa</td>
                        <td style="text-align: center; font-weight: bold;">{{ $userCount }} Personel</td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $subType === 'desa' ? 5 : 5 }}" style="text-align: center; color: #94a3b8; font-style: italic; padding: 15px;">
                        Tidak ada data yang ditemukan.
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
