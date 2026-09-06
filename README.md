# SIMPEL DBI — Sistem Pelaporan Desa Binaan Imigrasi

Sistem Pelaporan Desa Binaan Imigrasi (SIMPEL DBI) adalah platform aplikasi manajemen pengawasan keimigrasian terpadu yang dirancang khusus untuk Kantor Wilayah Direktorat Jenderal Imigrasi Sumatera Utara beserta Satuan Kerja UPT Imigrasi di bawah pengampuannya. 

Platform ini dirancang untuk mewujudkan efisiensi, transparansi, dan akuntabilitas pengawasan keimigrasian di tingkat desa binaan dengan menghubungkan tiga entitas pemangku kepentingan (Perangkat Desa Binaan, Petugas PIMPASA UPT Imigrasi, dan Administrator Kantor Wilayah) ke dalam satu arsitektur sistem yang terintegrasi secara real-time.

---

## Gambaran Umum Arsitektur Sistem

SIMPEL DBI mengintegrasikan alur kerja end-to-end mulai dari penerimaan aduan insidental dan kegiatan pembinaan di tingkat desa, verifikasi administratif dan teknis oleh petugas PIMPASA (Petugas Imigrasi Pembina Desa Binaan), penanganan operasional lapangan oleh Seksi Inteldakim Satker UPT Imigrasi, hingga pengawasan tingkat tinggi (Executive Command Center) oleh Administrator Kantor Wilayah.

Sistem ini menerapkan prinsip keamanan hirarki multitenant, di mana isolasi data antar desa binaan dan Satuan Kerja UPT Imigrasi terjamin secara ketat, sementara pimpinan Kantor Wilayah memiliki akses pemantauan secara menyeluruh di tingkat provinsi Sumatera Utara.

---

## Arsitektur Tiga Peran Utama (3-Role RBAC Architecture)

### 1. Peran Perangkat Desa Binaan (`role: desa`)
Perangkat Desa bertindak sebagai garda terdepan di masyarakat desa binaan yang bertugas menyampaikan informasi kejadian keimigrasian dan memantau perkembangannya.

- **Modul Pengajuan Laporan Kejadian**:
  - Penomoran otomatis Kode Tiket Registrasi berbasis format tahunan `LP-YYYY-XXXXXX`.
  - Formulir input dinamis yang mencakup nama kejadian, kategori aduan, lokasi detail/patokan alamat, estimasi jumlah orang terlibat, kronologi peristiwa, dan tanggal kejadian.
- **Pengunggahan Berkas Bukti Polymorphic**:
  - Sistem pengunggahan file terisolasi yang mendukung foto kejadian (JPG, PNG, WEBP) dan berkas berita acara (PDF, DOCX).
- **Monitoring Timeline & Status Tiket**:
  - Pelacakan alur audit trail laporan secara real-time dari status `Diajukan`, `Minta Perbaikan`, `Diverifikasi`, `Ditindaklanjuti`, `Selesai 100%`, hingga `Ditolak`.
- **Modul Perbaikan Data (Re-Submission)**:
  - Kemampuan memperbarui isi formulir dan melampirkan berkas bukti tambahan secara langsung ketika menerima catatan revisi dari Petugas PIMPASA.
- **Ekspor Dokumen Kedinasan**:
  - Generasi berkas Lembar Bukti Registrasi Tiket Individual PDF dan Rekapitulasi Laporan Desa format Spreadsheet XLSX.
- **Pengaturan Profil Akun**:
  - Pengelolaan data identitas desa, nomor kontak WhatsApp operasional, dan pemetaan otomatis Kantor Imigrasi Pembina (UPT) yang menaungi desa tersebut.

### 2. Peran Petugas PIMPASA / Satker UPT Imigrasi (`role: pimpasa`)
Petugas PIMPASA bertindak sebagai verifikator teknis dan pelaksana disposisi penanganan lapangan di tingkat Kantor Imigrasi Pembina.

- **Modul Worklist Verifikasi Ad-Hoc**:
  - Antrean laporan masuk dari seluruh desa binaan yang dinaungi oleh UPT Imigrasi terkait.
  - Filter pencarian berbasis multi-kriteria (status siklus, kategori aduan, dan pencarian kata kunci desa).
- **Formulir Keputusan Verifikasi**:
  - Evaluasi checklist validitas data (Kelengkapan Identitas, Kesesuaian Lokasi Desa Binaan, dan Indikasi Awal Pelanggaran Valid).
  - Tiga opsi keputusan: *Setujui & Diverifikasi UPT*, *Kembalikan: Minta Perbaikan*, dan *Tolak Laporan Ini*.
- **Otomatisasi Routing Penanganan**:
  - Alur navigasi cerdas yang me-redirect pengguna secara otomatis ke Form Tindak Lanjut UPT begitu verifikasi disetujui.
- **Modul Disposisi & Tindak Lanjut UPT**:
  - Pencatatan Nomor Registrasi Penanganan UPT otomatis (`REG-UPT-YYYY-XXXX`).
  - Penunjukan Seksi Penanggung Jawab (Inteldakim - Intelijen dan Penindakan Keimigrasian).
  - Penentuan Bentuk Intervensi Lapangan (Pemeriksaan & Inspeksi Lapangan, Sosialisasi & Penyuluhan, Operasi Gabungan, atau Proses Hukum Projustitia).
  - Penyusunan Ringkasan Berita Acara Hasil Penanganan Lapangan hingga status penanganan dinyatakan tuntas 100%.
- **Manajemen Kegiatan Pembinaan Desa**:
  - Pendokumentasian agenda penyuluhan, rapat koordinasi, dan sosialisasi keimigrasian di desa binaan.
- **Pemetaan Wilayah Kerja UPT**:
  - Pemantauan status kerawanan desa binaan di bawah naungan UPT Imigrasi.

### 3. Peran Executive & Administrator Kanwil (`role: kanwil`)
Pimpinan dan Administrator Kantor Wilayah memegang wewenang pengawasan tertinggi atas kepatuhan penanganan aduan di seluruh UPT Sumatera Utara.

- **Executive Monitoring & SLA Command Center**:
  - Dasbor pengawasan kepatuhan Service Level Agreement (SLA) penanganan laporan.
  - Peringatan dini otomatis untuk aduan yang mendekati atau melampaui batas waktu penanganan 24 jam / 48 jam.
  - Fitur *Tegur UPT SLA* untuk memberikan perintah eskalasi instan kepada Kepala UPT/PIMPASA terkait.
- **Master Data System Management**:
  - Pengelolaan CRUD master data 171 Desa Binaan Imigrasi.
  - Pengelolaan master data 11 Satuan Kerja UPT Imigrasi se-Sumatera Utara.
  - Pengelolaan data akun pengguna seluruh role (Perangkat Desa, PIMPASA, dan Administrator Kanwil).
  - Pengelolaan kategori laporan dinamis dan pembatasan status aktif/nonaktif.
- **Peta Geospasial Interactive (Leaflet Engine)**:
  - Pemetaan titik lokasi geospasial desa binaan berbasis koordinat latitude dan longitude.
  - Visualisasi indikator warna status kerawanan (Aman, Pembinaan, Aduan Kritis).
- **Executive Scorecard & Rekapitulasi Analytics**:
  - Analisis kinerja penyelesaian aduan antar UPT Imigrasi dan ekspor laporan rekapitulasi ke format PDF & XLSX.

---

## Ekosistem Notifikasi Multi-Channel

Sistem ini menerapkan pengiriman notifikasi multi-channel yang bekerja secara synchronous & real-time:

- **Channel Notifikasi In-App Inbox**:
  - Lencana lonceng sistem yang memperbarui jumlah notifikasi belum dibaca secara real-time.
- **Channel Push Notification Seluler (`ntfy`)**:
  - Pengiriman pesan HTTP Push ke server `ntfy.sh` (atau self-hosted) yang memicu banner pop-up notifikasi layar di smartphone pengguna.
- **Channel Surat Elektronik Kedinasan (`Email SMTP`)**:
  - Pengiriman email formal dengan header resmi *Kanwil Direktorat Jenderal Imigrasi Sumatera Utara* untuk setiap pembaruan tiket laporan.

---

## Siklus Hidup Status Laporan (Report Lifecycle)

1. **Diajukan (`diajukan`)**: Laporan baru saja dikirimkan oleh Perangkat Desa Binaan dan menunggu proses verifikasi PIMPASA.
2. **Minta Perbaikan (`minta_perbaikan`)**: Berkas dikembalikan ke Perangkat Desa karena membutuhkan kelengkapan data atau bukti tambahan.
3. **Diverifikasi (`diverifikasi`)**: Laporan dinyatakan valid oleh PIMPASA dan masuk ke antrean penanganan lapangan UPT Imigrasi.
4. **Ditindaklanjuti (`ditindaklanjuti`)**: Seksi Inteldakim UPT sedang melaksanakan pemeriksaan atau operasi di lapangan.
5. **Selesai (`selesai`)**: Penanganan lapangan tuntas 100%, berita acara telah diunggah, dan tiket laporan resmi ditutup.
6. **Ditolak (`ditolak`)**: Laporan dinyatakan tidak valid atau di luar kewenangan keimigrasian.

---

## Spesifikasi Teknologi & Arsitektur Perangkat Lunak

### Backend Architecture
- **Framework Core**: Laravel 13 (`laravel/framework: ^13.0`, PHP 8.3+)
- **ORM & Database Handling**: Eloquent ORM, Database Transactions, Append-Only Status Audit History
- **Security & Authorization**: Spatie Laravel-Permission (`spatie/laravel-permission: ^8.3`)
- **Document Generators**: Barryvdh Laravel DomPDF (`barryvdh/laravel-dompdf: ^3.1`) & Maatwebsite Excel (`maatwebsite/excel: ^4.0`)
- **Interactive Console Shell**: Laravel Tinker (`laravel/tinker: ^3.0`)
- **Notification Engines**: Laravel Mail (SMTP), HTTP Client Push (`ntfy`), Database Notifications

### Frontend Architecture
- **Framework Core**: Vue 3 (`vue: ^3.5.42`, Composition API `<script setup>`)
- **Single Page Application Adapter**: Inertia.js (`@inertiajs/vue3: ^3.7.0` & `inertiajs/inertia-laravel: ^3.3`)
- **Design System & Component Library**: Reka UI (`reka-ui: ^2.10.4`), Shadcn Vue Components, Class Variance Authority (`class-variance-authority: ^0.7.1`)
- **Styling Engine**: Tailwind CSS v4 (`tailwindcss: ^4.3.3`, `@tailwindcss/vite: ^4.3.3`, `tailwind-merge: ^3.6.0`, `tw-animate-css: ^1.4.0`)
- **Icons & Visual Assets**: Lucide Vue Next (`lucide-vue-next: ^1.0.0`, `@lucide/vue: ^1.39.0`)
- **Data Table Engine**: TanStack Vue Table (`@tanstack/vue-table: ^9.2.4`)
- **Data Visualization & Analytics**: ApexCharts (`apexcharts: ^7.1.0`, `vue3-apexcharts: ^1.11.1`)
- **Form Validation & Schemas**: Vee-Validate (`vee-validate: ^4.15.1`), Zod (`zod: 3.25.76`, `@vee-validate/zod: ^4.15.1`)
- **Toast Notifications**: Vue Sonner (`vue-sonner: ^2.0.9`)
- **Build Tooling & Package Runner**: Bun (`bun dev`), Vite (`vite: ^7.0.7`), `@vitejs/plugin-vue: ^6.0.8`, Axios (`axios: ^1.11.0`), `@vueuse/core: ^14.4.0`, TypeScript (`typescript: ^7.0.2`), Concurrently (`concurrently: ^9.0.1`)

---

## Daftar Library & Dependencies (Manifest)

### Composer Packages (`composer.json`)

#### Production Dependencies (`require`):
- `php`: `^8.3`
- `laravel/framework`: `^13.0`
- `inertiajs/inertia-laravel`: `^3.3`
- `spatie/laravel-permission`: `^8.3`
- `barryvdh/laravel-dompdf`: `^3.1`
- `maatwebsite/excel`: `^4.0`
- `laravel/tinker`: `^3.0`

#### Development Dependencies (`require-dev`):
- `fakerphp/faker`: `^1.23`
- `laravel/pail`: `^1.2.5`
- `laravel/pint`: `^1.27`
- `mockery/mockery`: `^1.6`
- `nunomaduro/collision`: `^8.6`
- `phpunit/phpunit`: `^12.5.12`

### NPM Packages (`package.json`)

#### Production Dependencies (`dependencies`):
- `@inertiajs/vue3`: `^3.7.0`
- `@lucide/vue`: `^1.39.0`
- `@tanstack/vue-table`: `^9.2.4`
- `@vee-validate/zod`: `^4.15.1`
- `@vitejs/plugin-vue`: `^6.0.8`
- `@vueuse/core`: `^14.4.0`
- `apexcharts`: `^7.1.0`
- `class-variance-authority`: `^0.7.1`
- `clsx`: `^2.1.1`
- `lucide-vue-next`: `^1.0.0`
- `reka-ui`: `^2.10.4`
- `tailwind-merge`: `^3.6.0`
- `tw-animate-css`: `^1.4.0`
- `vee-validate`: `^4.15.1`
- `vue`: `^3.5.42`
- `vue-sonner`: `^2.0.9`
- `vue3-apexcharts`: `^1.11.1`
- `zod`: `3.25.76`

#### Development Dependencies (`devDependencies`):
- `@tailwindcss/vite`: `^4.3.3`
- `@types/node`: `^26.4.1`
- `axios`: `^1.11.0`
- `concurrently`: `^9.0.1`
- `laravel-vite-plugin`: `^2.0.0`
- `tailwindcss`: `^4.3.3`
- `typescript`: `^7.0.2`
- `vite`: `^7.0.7`

---

## Struktur Basis Data Utama

Sistem menggunakan skema basis data relational MySQL yang dinormalisasi dengan relasi sebagai berikut:

- `users`: Menyimpan data kredensial, role (`desa`, `pimpasa`, `kanwil`), `upt_id`, `desa_id`, NIP, dan kontak.
- `upt`: Master data 11 Satuan Kerja UPT Imigrasi di Sumatera Utara.
- `desa_binaan`: Master data 171 Desa Binaan Imigrasi lengkap dengan koordinat geospasial (`lat`, `lng`), `upt_id`, dan `pimpasa_id`.
- `laporan`: Menyimpan data tiket laporan (`kode_tiket`), `desa_id`, `kategori_id`, lokasi detail, kronologi, dan status terkini.
- `laporan_verifikasi`: Catatan berita acara verifikasi PIMPASA, checklist validitas, dan keputusan.
- `laporan_tindak_lanjut`: Record hasil intervensi lapangan UPT, nomor registrasi penanganan, seksi PJ (Inteldakim), dan status akhir.
- `laporan_status_histories`: Audit trail riwayat perubahan status laporan (append-only).
- `kegiatan_pembinaan`: Catatan pelaksanaan kegiatan pembinaan desa binaan imigrasi.
- `lampiran`: Berkas bukti polymorphic pendukung laporan kejadian.

---

## Hak Cipta & Lisensi Pengembang

Copyright 2026 [Muhammad Fahri](https://muhammadfahri.my.id/). All Rights Reserved.

Sistem Pelaporan Desa Binaan Imigrasi (SIMPEL DBI) ini dikembangkan secara khusus untuk mendukung tata kelola pengawasan keimigrasian di lingkungan Kantor Wilayah Direktorat Jenderal Imigrasi Sumatera Utara dan seluruh Satuan Kerja UPT Imigrasi Pembina.
