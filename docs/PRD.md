# PRD — SIMPEL DBI
**Sistem Informasi Monitoring dan Pelaporan Desa Binaan Imigrasi**
Kantor Wilayah Ditjen Imigrasi Sumatera Utara

| | |
|---|---|
| Status | Draft |
| Cakupan Wilayah | Sumatera Utara — 11 UPT Keimigrasian, 171 Desa Binaan, 53 PIMPASA |
| Stack Teknis | Lihat `architecture.md` (Vue 3 + TS + Laravel + Inertia.js) |

---

## 1. Problem Statement

Program Desa Binaan Imigrasi (DBI) menempatkan Petugas Imigrasi Pembina Desa (PIMPASA) dan aparatur desa sebagai garda terdepan deteksi dini kerawanan keimigrasian, khususnya pencegahan Tindak Pidana Perdagangan Orang (TPPO) dan Tindak Pidana Penyelundupan Manusia (TPPM). Namun mekanisme pelaporan dan pengawasan yang berjalan saat ini memiliki kelemahan struktural yang menghambat efektivitas program:

1. **Fragmentasi saluran pelaporan** — laporan kegiatan dan temuan lapangan masih memanfaatkan WhatsApp, surat konvensional, atau email non-standar, sehingga data terfragmentasi dan rentan hilang.
2. **Tidak ada standardisasi data** — format laporan yang tidak seragam menyebabkan informasi sering tidak lengkap (kurang kronologis, tanpa koordinat presisi, tanpa lampiran valid).
3. **Beban administrasi rekapitulasi tinggi** — konsolidasi data dari satuan kerja bawah ke Kanwil memakan waktu berhari-hari karena direkap manual dari riwayat chat grup.
4. **Keterlambatan deteksi dini (early warning delay)** — informasi dugaan CPMI Non-Prosedural atau keberadaan WNA mencurigakan sering terlambat sampai ke unit penindakan (Intelkim/Wasdakim).
5. **Tidak ada audit trail & repositori institusional** — tidak ada database tunggal, sehingga kesinambungan data hilang saat terjadi mutasi/pergantian personel PIMPASA atau kepala desa.

Akibatnya, Kanwil dan UPT tidak memiliki visibilitas real-time atas kondisi 171 desa binaan yang tersebar di wilayah geografis luas dengan jalur perbatasan pesisir yang dinamis, sehingga respons terhadap potensi ancaman keimigrasian menjadi reaktif, bukan preventif.

---

## 2. Goals

### Tujuan Strategis
- **G1.** Mendigitalisasi 100% instrumen pelaporan Desa Binaan Imigrasi, mengubah waktu pemrosesan laporan dari hitungan hari menjadi real-time.
- **G2.** Menstandardisasi struktur pelaporan kegiatan pembinaan, pengawasan WNA, dan deteksi potensi PMI Non-Prosedural.
- **G3.** Menyediakan instrumen pengawasan berjenjang yang transparan bagi pimpinan Kantor Imigrasi dan Divisi Keimigrasian Kanwil.
- **G4.** Membangun repositori data spasial dan statistik keimigrasian sebagai rujukan kebijakan preventif.

### Target Keberhasilan Terukur (KPI)
| KPI | Target |
|---|---|
| Tingkat adopsi & keaktifan pelaporan berkala | 100% dari 171 desa binaan yang ditetapkan |
| Efisiensi waktu konsolidasi laporan periodik | Reduksi ≥90% dibanding metode konvensional |
| Ketersediaan rekam jejak tindak lanjut (audit trail) | 100% laporan memiliki jejak lengkap dan dapat dipertanggungjawabkan |
| Kecepatan respons intervensi dini kasus TPPO/TPPM | Peningkatan minimal 50% |

---

## 3. Target Users

| Aktor | Peran | Jumlah Estimasi | Konteks Akses |
|---|---|---|---|
| **Perangkat Desa Binaan** | Informan & pelapor tingkat desa | ~171 (1 akun/desa, aparatur desa yang ditunjuk) | Mobile web, sinyal internet sering terbatas |
| **PIMPASA** | Verifikator & pembina lapangan | 53 (rata-rata membina ~3,2 desa/orang) | Mobile web / desktop, mobilitas tinggi di lapangan |
| **Kantor Imigrasi (UPT)** | Pengawas & eksekutor operasional | 11–22 (1–2 akun per UPT, dari 11 UPT) | Desktop, di kantor |
| **Kantor Wilayah (Kanwil)** | Pengambil kebijakan provinsi & super admin | 5–7 (Kepala Kanwil, Action Leader, Admin/Tim IT, Koordinator Monev, dll.) | Desktop, akses penuh lintas UPT |

**Total estimasi pengguna sistem: ±240–253 akun.**

---

## 4. User Stories

### Perangkat Desa Binaan
- Sebagai perangkat desa, saya ingin mengirimkan laporan kegiatan/temuan tanpa biaya fisik, agar pelaporan lebih murah dan cepat dibanding surat konvensional.
- Sebagai perangkat desa, saya ingin laporan saya mendapat nomor tiket resmi, agar saya punya kepastian bahwa laporan akan ditindaklanjuti.
- Sebagai perangkat desa, saya ingin mengunggah foto/dokumen pendukung dengan ukuran file terkompresi otomatis, agar tidak menghabiskan kuota internet saya yang terbatas.
- Sebagai perangkat desa, saya ingin melihat riwayat dan status semua laporan yang pernah saya kirim, agar saya bisa memantau progres tanpa harus menghubungi PIMPASA berulang kali.
- Sebagai perangkat desa, saya ingin memperbaiki laporan yang diberi status "Minta Perbaikan", agar laporan saya bisa lanjut diproses.

### PIMPASA
- Sebagai PIMPASA, saya ingin melihat daftar laporan yang perlu saya verifikasi (worklist), agar saya tahu prioritas kerja harian saya.
- Sebagai PIMPASA, saya ingin memeriksa kelengkapan laporan dengan checklist validitas, agar proses verifikasi konsisten dan tidak ada yang terlewat.
- Sebagai PIMPASA, saya ingin bisa menolak atau meminta perbaikan laporan yang tidak relevan/tidak lengkap, disertai catatan evaluasi.
- Sebagai PIMPASA, saya ingin mencatat kegiatan pembinaan mandiri (sosialisasi, bimtek, sambang desa) langsung di sistem, agar saya tidak perlu menyusun laporan bulanan manual ke UPT.
- Sebagai PIMPASA, saya ingin melihat rekap frekuensi pembinaan saya per desa, agar saya bisa mengevaluasi kinerja saya sendiri.

### Kantor Imigrasi (UPT)
- Sebagai staf UPT, saya ingin melihat dashboard pengawasan seluruh desa binaan dan PIMPASA di wilayah kerja saya, agar saya punya visibilitas operasional penuh.
- Sebagai Kepala Seksi Intelkim/Wasdakim, saya ingin menerima disposisi laporan kritis yang sudah terverifikasi, agar saya bisa segera menindaklanjuti kasus TPPO/TPPM.
- Sebagai staf UPT, saya ingin mengunggah Berita Acara/Laporan Hasil Penugasan setelah penanganan lapangan selesai, agar laporan bisa ditutup secara resmi.
- Sebagai staf UPT, saya ingin menugaskan dan memetakan personel PIMPASA ke desa binaan tertentu, agar zonasi pembinaan jelas dan terdokumentasi (SK penugasan).
- Sebagai staf UPT, saya ingin melihat indikator SLA (laporan yang mendekati/melewati batas waktu), agar saya bisa memprioritaskan penanganan.

### Kanwil
- Sebagai Kepala Kanwil, saya ingin melihat executive dashboard tingkat provinsi (KPI utama, tren bulanan, peta sebaran), agar saya punya gambaran menyeluruh tanpa perlu meminta laporan manual dari 11 UPT.
- Sebagai pengambil kebijakan Kanwil, saya ingin memfilter data berdasarkan satuan kerja, wilayah administratif, kategori laporan, dan rentang waktu, agar saya bisa melakukan analisis multi-dimensi untuk kebijakan preventif.
- Sebagai pengambil kebijakan Kanwil, saya ingin melihat peta geospasial dengan indikator warna status desa (aman/perlu pembinaan/ada aduan), agar saya bisa mengidentifikasi wilayah rawan secara visual.
- Sebagai Admin sistem Kanwil, saya ingin mengelola master data (UPT, desa, akun pengguna, parameter sistem), agar data referensi selalu akurat dan terpusat.
- Sebagai Koordinator Monev Kanwil, saya ingin mengekspor rekapitulasi ke PDF resmi dan Excel siap olah, agar saya bisa menyusun laporan dinas periodik tanpa rekap manual.
- Sebagai pimpinan (Kakanim/Kadiv), saya ingin menerima notifikasi red-flag otomatis saat laporan melewati batas SLA, agar tidak ada kasus yang terbengkalai tanpa disadari.

---

## 5. Functional Requirements

### FR-1 — Autentikasi & RBAC
- FR-1.1 Sistem menyediakan single sign-on berbasis peran dengan 4 level akses: Desa, PIMPASA, UPT, Kanwil.
- FR-1.2 Setiap role memiliki batasan visibilitas data sesuai matriks hak akses (Bab III): Desa hanya lihat data desanya sendiri; PIMPASA hanya desa binaan di bawah surat tugasnya; UPT seluruh desa di wilayah kerjanya; Kanwil akses penuh lintas UPT.
- FR-1.3 Sistem dapat diakses dari perangkat mobile web maupun desktop (responsif).

### FR-2 — Modul Pelaporan Desa Binaan
- FR-2.1 Form pengajuan laporan dengan field wajib: kategori (Kegiatan DBI/WNA/Indikasi TPPO-PMI Non-Prosedural/Insidentil), judul, tanggal & jam peristiwa, lokasi detail (dusun/RT/RW), kronologi rinci, estimasi jumlah orang terkait (opsional), lampiran (1–5 file JPG/PNG/PDF, maks 5MB/file).
- FR-2.2 Sistem melakukan kompresi gambar otomatis di sisi klien sebelum unggah.
- FR-2.3 Setiap laporan mendapat nomor tiket unik otomatis.
- FR-2.4 Desa dapat melihat riwayat dan status seluruh laporan yang pernah diajukan, termasuk catatan interaktif dari PIMPASA.
- FR-2.5 Desa dapat memperbaiki dan mengirim ulang laporan berstatus "Minta Perbaikan".

### FR-3 — Modul Verifikasi PIMPASA
- FR-3.1 Worklist verifikasi menampilkan laporan yang menunggu tindakan, terfilter berdasarkan desa binaan PIMPASA yang bersangkutan.
- FR-3.2 Form verifikasi dengan checklist validitas fakta & bukti (boolean array), catatan hasil verifikasi (kondisional), dan keputusan (Diverifikasi/Minta Perbaikan/Ditolak).
- FR-3.3 Form input kegiatan pembinaan mandiri: jenis pembinaan (Sosialisasi/Bimtek/Rapat Koordinasi/Sambang Desa), desa sasaran, tanggal & waktu, jumlah peserta, ringkasan materi, foto dokumentasi (2–5 file).
- FR-3.4 Modul rekapitulasi menampilkan frekuensi pembinaan personal PIMPASA per desa binaan.

### FR-4 — Modul UPT (Kantor Imigrasi)
- FR-4.1 Dashboard pengawasan menampilkan ringkasan operasional per wilayah kerja: distribusi desa binaan, jumlah laporan per seksi, indikator SLA.
- FR-4.2 Modul disposisi berjenjang dari Kakanim/Kasi ke staf pelaksana, dengan form: nomor registrasi penanganan, seksi penanggung jawab (Intelkim/Wasdakim), bentuk intervensi, ringkasan hasil pemeriksaan lapangan, status akhir (Dalam Proses/Selesai), unggah berkas BAP (PDF).
- FR-4.3 Modul penugasan PIMPASA: manajemen SK penugasan dan pemetaan zonasi desa binaan.

### FR-5 — Modul Kanwil (Executive Center)
- FR-5.1 Executive dashboard dengan Top KPI Cards: total desa binaan terdaftar, total laporan masuk periode berjalan, laporan dalam proses tindak lanjut, laporan terselesaikan.
- FR-5.2 Grafik tren komparatif bulanan (multi-axis): pembinaan rutin vs fluktuasi aduan kerawanan.
- FR-5.3 Peta geospasial interaktif dengan pin berwarna sesuai status wilayah (hijau: aman/aktif, kuning: perlu pembinaan, merah: ada aduan belum tertangani).
- FR-5.4 Real-time activity log: 10 transaksi data terbaru beserta status penanganan.
- FR-5.5 Filter multi-dimensi: satuan kerja, wilayah administratif (kab/kota → kecamatan → desa), kategori laporan, rentang waktu (harian/bulanan/triwulanan/semesteran/tahunan).
- FR-5.6 Engine ekspor rekapitulasi ke format PDF resmi dan Excel siap olah.
- FR-5.7 Modul master data: satuan kerja UPT, wilayah administratif, data PIMPASA (nama/NIP/golongan/kontak), penetapan SK Desa Binaan.

### FR-6 — Siklus Status & SLA
- FR-6.1 Setiap laporan mengikuti 4 status baku: Diajukan → Diverifikasi PIMPASA → Ditindaklanjuti UPT → Selesai.
- FR-6.2 SLA verifikasi PIMPASA: maksimal 2×24 jam sejak laporan diajukan.
- FR-6.3 SLA penanganan UPT: maksimal 3×24 jam untuk kasus umum, kurang dari 6 jam untuk dugaan darurat TPPO/TPPM.
- FR-6.4 Sistem otomatis menandai laporan yang melewati SLA dengan label Red Flag Alert, ditampilkan di dashboard Kakanim dan Kadiv.

### FR-7 — Notifikasi
- FR-7.1 Sistem mengirim indikator visual (badge/warna) saat status laporan berubah.
- FR-7.2 Sistem menampilkan peringatan red-flag secara otomatis pada dashboard pimpinan tanpa perlu refresh manual (target: real-time via WebSocket, minimal: polling berkala).

---

## 6. Non-Functional Requirements

| Kategori | Kebutuhan |
|---|---|
| **Ketersediaan (Availability)** | Sistem harus dapat diakses selama jam kerja operasional (minimal 99% uptime pada jam kerja); downtime terjadwal dikomunikasikan sebelumnya. |
| **Kinerja (Performance)** | Waktu muat halaman dashboard < 3 detik pada koneksi standar; form pelaporan tetap responsif pada koneksi lambat/sinyal terbatas di wilayah desa. |
| **Skalabilitas** | Arsitektur cukup menangani ±250 akun pengguna dan pertumbuhan data laporan tahunan tanpa perubahan arsitektur signifikan (monolith Laravel pada server tunggal memadai). |
| **Keamanan** | RBAC diterapkan di level query backend (bukan hanya UI); validasi file upload (tipe & ukuran) dilakukan di server; password di-hash standar Laravel; sesi auth aman dari CSRF. |
| **Auditabilitas** | Setiap perubahan status laporan (submit, verifikasi, disposisi, penyelesaian) tercatat dengan timestamp dan aktor yang melakukan, tidak dapat dihapus, untuk keperluan audit kinerja birokrasi. |
| **Usabilitas** | Antarmuka mobile-friendly untuk perangkat desa dengan literasi digital bervariasi; form input divalidasi real-time dengan pesan error yang jelas. |
| **Efisiensi Data (Kuota)** | Kompresi gambar otomatis sebelum upload untuk mengakomodasi wilayah dengan sinyal internet terbatas. |
| **Kompatibilitas** | Dapat diakses dari browser modern di perangkat mobile maupun desktop tanpa instalasi aplikasi tambahan (web responsif, bukan native app). |
| **Portabilitas Data** | Ekspor data mendukung format standar (PDF untuk laporan dinas, Excel untuk data siap olah) agar kompatibel dengan alur kerja administrasi existing. |
| **Maintainability** | Struktur kode modular (per role/modul) dengan type safety (TypeScript + Zod) untuk memudahkan pemeliharaan oleh tim kecil/individu developer. |

---

## 8. Strategi Penanganan Kendala Sinyal & Infrastruktur Desa Pedalaman/Kepulauan (Deferred Roadmap)

Mengingat beberapa Desa Binaan Imigrasi berada di area pedalaman/kepulauan (seperti Kepulauan Nias, Nias Selatan, Kepulauan Batu, Mandailing Natal pedalaman) dengan kendala sinyal internet yang tidak stabil (*blank spot* / *intermittent network*), berikut adalah spesifikasi strategi teknis & operasional penanganan offline yang disiapkan sebagai catatan pengembangan masa depan (*Future Roadmap / Deferred Implementation*):

### 8.1 PWA Installable App Shell (Offline First UI Caching)
- **Arsitektur App Shell**: Menggunakan *Service Worker Cache Storage* sehingga setelah pertama kali dibuka/di-install oleh Perangkat Desa (`Add to Home Screen`), seluruh antarmuka (HTML, CSS, JS, dan Form UI) tersimpan permanen di memori lokal HP.
- **Akses Tanpa Koneksi**: Saat berada di lokasi tanpa sinyal (0 bar), Perangkat Desa tetap dapat membuka aplikasi dari ikon Home Screen HP secara instan (0 detik loading timeout) untuk melakukan input aduan & kompresi foto bukti secara lokal.

### 8.2 IndexedDB Offline Queue & Auto-Sync Engine
- **Local Draft Storage**: Form laporan dan foto bukti yang dikirim saat offline otomatis disimpan di *IndexedDB Local Storage* browser dengan status `"Offline Draft"`.
- **Automatic Background Sync**: Begitu perangkat HP mendeteksi kembali sinyal internet (`window.addEventListener('online')`), Service Worker akan melakukan sinkronisasi otomatis ke server Laravel backend tanpa memerlukan re-entry data dari pengguna.
- **Idempotency Protection**: Setiap draft offline diberi *Client UUID* unik untuk mencegah terjadinya duplikasi laporan saat sinyal putus-nyambung (*re-try submission*).

### 8.3 Hybrid Fallback Protocol: SMS / USSD Gateway Auto-Parser
- **Kanal Cadangan Non-Internet**: Untuk daerah pedalaman yang tidak memiliki sinyal data 4G sama sekali namun masih menangkap sinyal seluler GSM biasa (SMS), sistem menyediakan kanal SMS Gateway.
- **Format SMS Terstruktur**: Perangkat Desa dapat mengikutsertakan laporan singkat via format SMS (contoh: `DBI#KODE_DESA#KATEGORI#KRONOLOGI`). Server SMS Gateway di Satker UPT akan melakukan *parsing* otomatis menjadi nomor tiket resmi di SIMPEL DBI.

---

## 9. Scope

### Dalam Cakupan (In Scope)
- 4 modul aktor: Desa Binaan Portal, PIMPASA Officer Workspace, UPT Management, Kanwil Executive Center.
- Siklus hidup laporan lengkap (4 status) dengan SLA dan eskalasi otomatis.
- RBAC 4 level dengan batasan visibilitas data sesuai matriks akses.
- Dashboard analitik dan peta geospasial (data desa binaan se-Sumatera Utara).
- Ekspor laporan ke PDF dan Excel.
- Master data (UPT, wilayah administratif, PIMPASA, desa binaan).
- Notifikasi status & red-flag SLA.

### Di Luar Cakupan (Out of Scope) — Fase Awal
- Aplikasi mobile native (Android/iOS) — sistem berbasis web responsif saja.
- Integrasi langsung dengan pangkalan data pusat Direktorat Jenderal Imigrasi (disebutkan sebagai rekomendasi keberlanjutan jangka panjang di luar fase pembangunan ini).
- Modul analitik prediktif/machine learning untuk prediksi kerawanan wilayah.
- Multi-provinsi/multi-Kanwil (sistem ini spesifik untuk Kanwil Sumatera Utara).
- Integrasi WhatsApp/SMS gateway untuk notifikasi (kanal notifikasi awal cukup di dalam sistem/dashboard).

### Pembagian Fase (selaras `architecture.md`)
| Fase | Cakupan | Target Waktu |
|---|---|---|
| Jangka Pendek | Auth + RBAC dasar, modul Desa + PIMPASA (Diajukan ↔ Diverifikasi) | 20 September 2026 |
| Jangka Menengah | Modul UPT (disposisi, SLA tracking dasar), ekspor sederhana | 10 Oktober 2026 |
| Jangka Panjang | Kanwil executive dashboard, peta geospasial, red-flag otomatis, notifikasi real-time | Awal November 2026 |