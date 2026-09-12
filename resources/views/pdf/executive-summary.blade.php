<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Ringkasan Eksekutif SIMPEL DBI - Kanwil Ditjenim Sumut</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0.8cm 1.2cm 0.8cm 1.2cm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8.5pt;
            color: #1e293b;
            line-height: 1.3;
            background-color: #ffffff;
        }

        .page-break {
            page-break-before: always;
        }

        /* Kop Surat Dinas Header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            border-bottom: 2px solid #033566;
            padding-bottom: 4px;
        }

        .header-table td {
            vertical-align: middle;
        }

        .logo-title {
            font-size: 12pt;
            font-weight: bold;
            color: #033566;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .logo-subtitle {
            font-size: 8.5pt;
            font-weight: bold;
            color: #475569;
            margin-top: 1px;
        }

        .doc-meta {
            text-align: right;
            font-size: 7.5pt;
            color: #64748b;
        }

        .doc-meta strong {
            color: #0f172a;
        }

        /* Section Heading */
        .section-title {
            font-size: 9.5pt;
            font-weight: bold;
            color: #033566;
            background-color: #f1f5f9;
            padding: 4px 6px;
            border-left: 4px solid #033566;
            margin-top: 8px;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* Top 4 KPI Cards Grid Table */
        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
            margin-bottom: 8px;
        }

        .kpi-card {
            width: 25%;
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 6px 8px;
            vertical-align: top;
        }

        .kpi-card.red-flag {
            background-color: #fef2f2;
            border-color: #fca5a5;
        }

        .kpi-label {
            font-size: 7pt;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
        }

        .kpi-card.red-flag .kpi-label {
            color: #991b1b;
        }

        .kpi-value {
            font-size: 15pt;
            font-weight: bold;
            color: #0f172a;
            margin: 2px 0 1px 0;
            font-family: 'Courier', monospace;
        }

        .kpi-card.red-flag .kpi-value {
            color: #dc2626;
        }

        .kpi-sub {
            font-size: 7pt;
            color: #475569;
        }

        /* Data Tables General */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            font-size: 8pt;
        }

        .data-table th {
            background-color: #033566;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 4px 6px;
            border: 1px solid #022446;
            font-size: 7.5pt;
            text-transform: uppercase;
        }

        .data-table td {
            padding: 3.5px 6px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }

        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-mono {
            font-family: 'Courier', monospace;
            font-weight: bold;
        }

        /* Status Badges */
        .badge {
            display: inline-block;
            padding: 1.5px 5px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-success {
            background-color: #dcfce7;
            color: #166534;
        }

        .badge-warning {
            background-color: #fef9c3;
            color: #854d0e;
        }

        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* Tanda Tangan Footer Table */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            page-break-inside: avoid;
        }

        .footer-table td {
            vertical-align: top;
        }

        .signature-box {
            width: 220px;
            float: right;
            text-align: center;
        }

        .signature-space {
            height: 40px;
        }
    </style>
</head>

<body>

    <!-- ================= HALAMAN 1 ================= -->

    <!-- Header / Kop Resmi -->
    <table class="header-table">
        <tr>
            <td style="width: 70%;">
                <div class="logo-title">SIMPEL DBI — KANWIL DITJEN IMIGRASI SUMATERA UTARA</div>
                <div class="logo-subtitle">Laporan Ringkasan Eksekutif & Visual Analytics Performa Desa Binaan Imigrasi
                </div>
            </td>
            <td class="doc-meta" style="width: 30%;">
                Tanggal Cetak: <strong>{{ $tanggalCetak }}</strong><br>
                Sifat: <strong>RAHASIA / DOKUMEN DINAS</strong><br>
                Wilayah Kerja: <strong>Provinsi Sumatera Utara</strong>
            </td>
        </tr>
    </table>

    <!-- Section I: 4 Card KPI Metrics -->
    <div class="section-title">I. Matriks Indikator Kinerja Utama (Top KPI Metrics)</div>

    <table class="kpi-table">
        <tr>
            <td class="kpi-card">
                <div class="kpi-label">Total Desa Binaan (DBI)</div>
                <div class="kpi-value">{{ $kpiData['total_desa'] ?? 0 }}</div>
                <div class="kpi-sub">Terdaftar se-Sumut</div>
            </td>
            <td class="kpi-card">
                <div class="kpi-label">Total Laporan Masuk</div>
                <div class="kpi-value">{{ $kpiData['total_laporan'] ?? 0 }}</div>
                <div class="kpi-sub">Proses: <strong>{{ $kpiData['laporan_proses'] ?? 0 }}</strong> | Selesai:
                    <strong>{{ $kpiData['laporan_selesai'] ?? 0 }}</strong></div>
            </td>
            <td class="kpi-card">
                <div class="kpi-label">Kegiatan Pembinaan Desa</div>
                <div class="kpi-value">{{ $kpiData['total_kegiatan_pembinaan'] ?? 0 }}</div>
                <div class="kpi-sub">Total Peserta: <strong>{{ $kpiData['total_peserta_pembinaan'] ?? 0 }}</strong>
                    Orang</div>
            </td>
            <td class="kpi-card {{ ($kpiData['sla_breached_count'] ?? 0) > 0 ? 'red-flag' : '' }}">
                <div class="kpi-label">Red Flag SLA Alerts</div>
                <div class="kpi-value">{{ $kpiData['sla_breached_count'] ?? 0 }}</div>
                <div class="kpi-sub">Melewati SLA 1x24 Jam</div>
            </td>
        </tr>
    </table>

    <!-- Section II & III Side by Side (Grafik & Tabel Matriks) -->
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <!-- Left Side (60%): Tren Volume Laporan 12 Bulan -->
            <td style="width: 60%; vertical-align: top; padding-right: 8px;">
                <div class="section-title" style="margin-top: 0;">II. Tren Volume Laporan & SLA</div>
                @if(!empty($trendImg))
                    <div
                        style="text-align: center; border: 1px solid #cbd5e1; border-radius: 4px; padding: 6px; background-color: #ffffff; margin-bottom: 6px;">
                        <img src="{{ $trendImg }}"
                            style="max-width: 100%; max-height: 180px; width: auto; height: auto; display: inline-block;">
                    </div>
                @endif
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 15%;">Bulan</th>
                            <th class="text-center" style="width: 28%;">Laporan Masuk</th>
                            <th class="text-center" style="width: 28.5%;">Selesai SLA</th>
                            <th class="text-center" style="width: 28.5%;">Red Flag Breached</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $months = $chartAnalytics['trendData']['1_tahun']['categories'] ?? [];
                            $masukData = $chartAnalytics['trendData']['1_tahun']['series'][0]['data'] ?? [];
                            $selesaiData = $chartAnalytics['trendData']['1_tahun']['series'][1]['data'] ?? [];
                            $breachedData = $chartAnalytics['trendData']['1_tahun']['series'][2]['data'] ?? [];
                        @endphp
                        @foreach($months as $idx => $m)
                            <tr>
                                <td class="text-center font-mono">{{ $m }}</td>
                                <td class="text-center font-mono">{{ $masukData[$idx] ?? 0 }}</td>
                                <td class="text-center font-mono" style="color: #166534;">{{ $selesaiData[$idx] ?? 0 }}</td>
                                <td class="text-center font-mono"
                                    style="color: {{ ($breachedData[$idx] ?? 0) > 0 ? '#dc2626' : '#64748b' }};">
                                    {{ $breachedData[$idx] ?? 0 }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>

            <!-- Right Side (40%): Donut Sebaran Status Desa Binaan -->
            <td style="width: 40%; vertical-align: top;">
                <div class="section-title" style="margin-top: 0;">III. Sebaran Status Desa Binaan</div>
                @if(!empty($donutImg))
                    <div
                        style="text-align: center; border: 1px solid #cbd5e1; border-radius: 4px; padding: 6px; background-color: #ffffff; margin-bottom: 6px;">
                        <img src="{{ $donutImg }}"
                            style="max-width: 100%; max-height: 180px; width: auto; height: auto; display: inline-block;">
                    </div>
                @endif
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Kategori Status Desa</th>
                            <th class="text-center" style="width: 28%;">Jumlah</th>
                            <th class="text-center" style="width: 24%;">Porsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $labels = $chartAnalytics['desaStatusDistribution']['labels'] ?? [];
                            $series = $chartAnalytics['desaStatusDistribution']['series'] ?? [];
                            $totalDesa = array_sum($series) ?: 1;
                        @endphp
                        @foreach($labels as $idx => $lbl)
                            @php
                                $val = $series[$idx] ?? 0;
                                $pct = round(($val / $totalDesa) * 100, 1);
                            @endphp
                            <tr>
                                <td>
                                    @if($idx === 0)
                                        <span class="badge badge-success">Aman / Aktif</span>
                                    @elseif($idx === 1)
                                        <span class="badge badge-warning">Pembinaan</span>
                                    @else
                                        <span class="badge badge-danger">Ada Aduan</span>
                                    @endif
                                </td>
                                <td class="text-center font-mono">{{ $val }} Desa</td>
                                <td class="text-center font-mono">{{ $pct }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div
                    style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; padding: 5px; margin-top: 4px; font-size: 7.5pt;">
                    <strong>Catatan Status Desa Binaan:</strong><br>
                    • <strong>Desa Aman</strong>: Tidak ada aduan & pembinaan berjalan.<br>
                    • <strong>Perlu Pembinaan</strong>: Memiliki riwayat laporan/kegiatan.<br>
                    • <strong>Ada Aduan Aktif</strong>: Terdapat laporan belum terverifikasi.
                </div>
            </td>
        </tr>
    </table>


    <!-- ================= HALAMAN 2 ================= -->
    <div class="page-break"></div>

    <!-- Header Halaman 2 -->
    <table class="header-table">
        <tr>
            <td style="width: 70%;">
                <div class="logo-title">SIMPEL DBI — KANWIL DITJEN IMIGRASI SUMATERA UTARA</div>
                <div class="logo-subtitle">Lampiran Scorecard & Tingkat Kepatuhan SLA Satker UPT Imigrasi Pembina</div>
            </td>
            <td class="doc-meta" style="width: 30%;">
                Tanggal Cetak: <strong>{{ $tanggalCetak }}</strong><br>
                Halaman: <strong>2 dari 2</strong>
            </td>
        </tr>
    </table>

    <!-- Section IV: Tingkat Kepatuhan SLA per Satker UPT Imigrasi Pembina -->
    <div class="section-title">IV. Tingkat Kepatuhan SLA per Satker UPT Imigrasi Pembina</div>

    @if(!empty($barImg))
        <div
            style="text-align: center; border: 1px solid #cbd5e1; border-radius: 4px; padding: 6px; background-color: #ffffff; margin-bottom: 6px;">
            <img src="{{ $barImg }}"
                style="max-width: 100%; max-height: 200px; width: auto; height: auto; display: inline-block;">
        </div>
    @endif

    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 4%;">No</th>
                <th style="width: 36%;">Nama Satker UPT Imigrasi Pembina</th>
                <th class="text-center" style="width: 12%;">Desa Binaan</th>
                <th class="text-center" style="width: 12%;">Petugas PIMPASA</th>
                <th class="text-center" style="width: 12%;">Total Laporan</th>
                <th class="text-center" style="width: 12%;">Kepatuhan SLA</th>
                <th class="text-center" style="width: 12%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($uptScorecard as $index => $upt)
                <tr>
                    <td class="text-center font-mono">{{ $index + 1 }}</td>
                    <td><strong>{{ $upt['nama'] }}</strong></td>
                    <td class="text-center font-mono">{{ $upt['desa_count'] ?? 0 }} Desa</td>
                    <td class="text-center font-mono">{{ $upt['pimpasa_count'] ?? 0 }} Personel</td>
                    <td class="text-center font-mono">{{ $upt['total_laporan'] ?? 0 }} Tiket</td>
                    <td class="text-center font-mono">{{ $upt['completion_rate'] }}%</td>
                    <td class="text-center">
                        @if($upt['status_kepatuhan'] === 'SANGAT BAIK')
                            <span class="badge badge-success">SANGAT BAIK</span>
                        @elseif($upt['status_kepatuhan'] === 'CUKUP')
                            <span class="badge badge-warning">CUKUP</span>
                        @else
                            <span class="badge badge-danger">PERLU EVALUASI</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="color: #94a3b8; padding: 10px;">Data Satker UPT Imigrasi
                        belum tersedia.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tanda Tangan Footer -->
    <table class="footer-table">
        <tr>
            <td style="width: 60%;">
                <div style="font-size: 7.5pt; color: #64748b;">
                    * Dokumen ini diterbitkan resmi oleh Sistem Informasi Manajemen Pelaporan Desa Binaan Imigrasi
                    (SIMPEL DBI).<br>
                    * Hak Cipta © {{ date('Y') }} Kantor Wilayah Ditjen Imigrasi Sumatera Utara.
                </div>
            </td>
            <td style="width: 40%;">
                <div class="signature-box">
                    Medan, {{ date('d F Y') }}<br>
                    <strong>Administrator Kanwil Sumut</strong>
                    <div class="signature-space"></div>
                    <strong><u>{{ $pimpinanNama }}</u></strong><br>
                    <span style="font-size: 7.5pt; color: #64748b;">NIP. 19850412 200812 1 002</span>
                </div>
            </td>
        </tr>
    </table>

</body>

</html>