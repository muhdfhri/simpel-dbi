# AGENTS.md — Instruksi AI Coding Assistant: SIMPEL DBI

Dokumen ini adalah panduan wajib bagi AI assistant (GitHub Copilot, Cursor, Antigravity, dsb.) yang mengerjakan kode proyek **SIMPEL DBI (Sistem Informasi Monitoring dan Pelaporan Desa Binaan Imigrasi)**. Baca dokumen ini **sebelum menghasilkan kode apapun**.

---

## 1. Konteks Proyek

SIMPEL DBI adalah sistem informasi internal pemerintahan (login-only, bukan situs publik) untuk **Kantor Wilayah Ditjen Imigrasi Sumatera Utara**. Sistem ini dibangun secara profesional — bukan prototype atau MVP sekali pakai. Kode yang dihasilkan harus production-grade, maintainable, dan konsisten dengan seluruh dokumentasi di folder `docs/`.

**Dokumen referensi utama yang wajib dibaca sebelum mengerjakan task apapun:**

| Dokumen | Isi |
|---|---|
| `MECHANISME.md` | Konteks bisnis, peran pengguna, SLA, alur laporan |
| `ARCHITECTURE.md` | Tech stack, struktur folder, prinsip arsitektur |
| `PRD.md` | Functional & non-functional requirements, user stories |
| `SCHEMA.md` | Skema database lengkap — tabel, kolom, enum, index |
| `RULES.md` | Konvensi frontend wajib: Tailwind v4, shadcn-vue, aksesibilitas |
| `Design.md` | Design system: warna OKLCH, tipografi, spacing, do's/don'ts |
| `AGENTS.md` | File ini — baca selalu di awal |

---

## 2. Tech Stack (Jangan Ganti Tanpa Konfirmasi)

| Layer | Teknologi | Versi / Keterangan |
|---|---|---|
| Backend | Laravel | 13 |
| Bridge | Inertia.js | v3 |
| Frontend | Vue 3 | Composition API + `<script setup>` |
| Bahasa | TypeScript | Wajib di semua file `.vue` dan `.ts` |
| Build tool | Bun | Untuk install & build; jangan pakai npm/yarn |
| Styling | Tailwind CSS | v4 — CSS-first, tanpa `tailwind.config.ts` untuk tema |
| Komponen UI | shadcn-vue | Copy-paste ke `resources/js/Components/ui/` |
| Form | vee-validate + @vee-validate/zod | Tidak boleh validasi form manual |
| Schema validasi | Zod | Dipakai frontend; sinkron konsep ke backend |
| Tabel | @tanstack/vue-table | Untuk worklist & rekap |
| Grafik | @unovis/vue | Grafik dashboard Kanwil |
| Peta | Leaflet + vue-leaflet | Pin desa binaan dengan warna status |
| Kompresi gambar | browser-image-compression | Client-side sebelum upload |
| State management | Pinia | Hanya untuk UI state yang tidak perlu ke server |
| Ikon | lucide-vue-next | Satu-satunya ikon yang dibolehkan |
| RBAC | spatie/laravel-permission | 4 role: desa, pimpasa, upt, kanwil |
| Export PDF | barryvdh/laravel-dompdf | Laporan dinas resmi |
| Export Excel | maatwebsite/laravel-excel | Rekapitulasi tabel |
| Notifikasi real-time | Laravel Reverb | WebSocket self-hosted |
| Notifikasi email | Laravel Mail + SMTP | Reset password & notifikasi sistem |
| Database | MySQL | Gunakan migration, bukan raw SQL |
| Testing | Pest | Unit & feature test |

---

## 3. Aturan Kode Backend (Laravel)

### 3.1 Struktur Umum
- Ikuti struktur folder yang sudah didefinisikan di `ARCHITECTURE.md` Section 4.
- Setiap modul aktor punya namespace controller sendiri: `App\Http\Controllers\Desa\`, `Pimpasa\`, `Upt\`, `Kanwil\`.
- Logika bisnis (SLA, workflow laporan, nomor tiket) **tidak boleh di controller** — masuk ke `app/Services/`.
- Job terjadwal masuk ke `app/Console/Commands/`.

### 3.2 RBAC & Keamanan Data
- Middleware RBAC via `spatie/laravel-permission` diaplikasikan di level route group (`role:desa`, `role:pimpasa`, dst).
- **Setiap query di controller harus dibatasi scope berdasarkan role** — bukan hanya menyembunyikan UI:
  - `desa`: hanya data `laporan` milik `desa_id` user yang login.
  - `pimpasa`: hanya data laporan dari desa-desa di bawah `pimpasa_id`-nya.
  - `upt`: semua data laporan dari desa dalam wilayah kerja UPT-nya.
  - `kanwil`: tanpa scope — full visibility.
- Validasi file upload (tipe & ukuran) **wajib di backend**, tidak boleh hanya di frontend.

### 3.3 Database & Model
- Ikuti skema persis yang ada di `SCHEMA.md` — jangan tambah/ubah kolom tanpa diskusi.
- Semua tabel transaksi (`laporan`, `laporan_verifikasi`, `laporan_tindak_lanjut`, `kegiatan_pembinaan`) wajib pakai `SoftDeletes`.
- Tabel `laporan_status_histories` bersifat **append-only** — jangan buat route atau method untuk update/delete baris di tabel ini.
- Enum kolom wajib menggunakan PHP Enum class, bukan string biasa.
- Kolom `kode_tiket` dan `nomor_registrasi` di-generate via `LaporanWorkflowService`, format `LP-2026-000123`.

### 3.4 SLA
- Perhitungan SLA (`sla_verifikasi_breached`, `sla_tindak_lanjut_breached`, `red_flag`) dilakukan oleh `CheckSlaBreaches` command yang dijadwalkan, **bukan dihitung on-the-fly** saat request — supaya query dashboard tetap cepat.
- SLA verifikasi PIMPASA: 48 jam dari `submitted_at`.
- SLA tindak lanjut UPT: 72 jam (umum) / 6 jam (kategori `indikasi_tppo_pmi`).

---

## 4. Aturan Kode Frontend (Vue 3 + TypeScript + Inertia)

### 4.1 Struktur File
- Semua halaman ada di `resources/js/Pages/` dengan sub-folder per role: `Desa/`, `Pimpasa/`, `Upt/`, `Kanwil/`.
- Komponen dibagi: `Components/ui/` (shadcn-vue), `Components/domain/` (spesifik SIMPEL DBI), `Components/layout/`.
- Nama file komponen: **PascalCase** (`LaporanStatusBadge.vue`) — bukan kebab-case atau camelCase.

### 4.2 TypeScript
- Selalu gunakan `<script setup lang="ts">` — tidak boleh `<script setup>` tanpa TypeScript.
- Tipe model (Laporan, User, DesaBinaan, dst) didefinisikan di `resources/js/types/models.d.ts` dan harus sinkron dengan skema Zod.
- Jangan pakai `any` — gunakan tipe spesifik atau `unknown` jika benar-benar tidak diketahui.

### 4.3 Form & Validasi
- Semua form **wajib** memakai `vee-validate` + `@vee-validate/zod` — tidak boleh membuat validasi manual per-input.
- Zod schema untuk setiap form didefinisikan di file terpisah di `resources/js/schemas/`.
- Komponen form dari shadcn-vue (`FormField`, `FormLabel`, `FormMessage`, dst) wajib dipakai — jangan buat wrapper form sendiri dari nol.

### 4.4 Inertia.js
- Navigasi antar halaman wajib pakai `<Link>` dari `@inertiajs/vue3`, bukan `<router-link>` atau `<a>` biasa.
- Gunakan `useForm()` dari Inertia untuk form yang submit ke server (bukan Axios/fetch manual).
- Props dari server diterima via `defineProps` dengan tipe TypeScript yang jelas.

### 4.5 Tailwind CSS v4
- Import dengan `@import "tailwindcss";` — bukan tiga directive `@tailwind`.
- Semua token warna dalam format **OKLCH** sesuai `Design.md`.
- **Tidak boleh** `tailwind.config.ts` untuk tema — semua di `globals.css`.
- **Tidak boleh** hardcode nilai warna (`bg-blue-600`) — selalu lewat token semantik (`bg-primary`, `bg-status-aman`, dst).
- Animasi komponen shadcn pakai `tw-animate-css`, bukan `tailwindcss-animate`.

### 4.6 Ikon
- Satu-satunya ikon yang dibolehkan: **`lucide-vue-next`**.
- Ukuran: 16px (inline teks), 20px (tombol/nav), 24px (empty state/heading).
- Ikon status (red-flag, checklist, pin peta) **wajib didampingi label teks** — tidak boleh ikon saja.

---

## 5. Perilaku AI yang Diharapkan

### Yang Wajib Dilakukan
- **Selalu cek `SCHEMA.md`** sebelum membuat migration, model, atau query.
- **Selalu cek `Design.md`** sebelum menulis styling apapun — gunakan token yang sudah ada.
- **Selalu cek `RULES.md`** sebelum membuat komponen UI baru.
- Gunakan bahasa Indonesia untuk nama variabel domain (mis. `$laporan`, `$desaBinaan`) tapi tetap ikuti konvensi PHP/JS (camelCase/PascalCase).
- Tambahkan comment singkat dalam Bahasa Indonesia untuk logika bisnis yang tidak obvious.
- Ketika membuat komponen shadcn-vue baru, jalankan perintah `bunx shadcn-vue add [nama]`, lalu edit hasilnya — jangan tulis ulang dari nol.

### Yang Dilarang
- ❌ Jangan buat REST API (`api.php`) — sistem ini pakai Inertia, semua lewat `web.php`.
- ❌ Jangan pakai JWT / Sanctum token — auth pakai session Laravel standar.
- ❌ Jangan buat `tailwind.config.ts` untuk tema warna/font.
- ❌ Jangan hardcode warna (`bg-red-500`) di komponen — selalu token semantik.
- ❌ Jangan pakai `@apply` dalam `@layer base/components` — pakai `@utility` di Tailwind v4.
- ❌ Jangan gunakan ikon selain `lucide-vue-next`.
- ❌ Jangan buat form validasi manual — selalu vee-validate + Zod.
- ❌ Jangan update/delete baris di `laporan_status_histories` — append-only.
- ❌ Jangan query data lintas scope role tanpa izin eksplisit — data isolation adalah prinsip keamanan utama sistem ini.
- ❌ Jangan gunakan `npm` atau `yarn` — proyek ini pakai **Bun**.

---

## 6. Urutan Implementasi (Roadmap)

Ikuti urutan fase di `ARCHITECTURE.md` Section 10 dan `PRD.md` Section 7:

### Fase 1 — Jangka Pendek (Target: 20 September 2026)
1. Setup project Laravel 13 + Inertia v3 + Vue 3 + Bun
2. Konfigurasi Tailwind v4 + shadcn-vue + design tokens
3. Migration semua tabel sesuai `SCHEMA.md`
4. Auth: login, logout, reset password via SMTP
5. RBAC: 4 role via spatie, middleware, route group
6. Seeder: data wilayah Sumut, UPT, desa binaan, akun pengguna contoh
7. Modul Desa: form laporan, riwayat tiket, perbaiki laporan
8. Modul PIMPASA: worklist verifikasi, form pembinaan, rekap personal

### Fase 2 — Jangka Menengah (Target: 10 Oktober 2026)
9. Modul UPT: dashboard, disposisi, penugasan PIMPASA
10. SLA tracking: job `CheckSlaBreaches`, red-flag badge
11. Export sederhana: PDF via dompdf, Excel via maatwebsite
12. Manajemen akun Kanwil: buat 1-per-1, bulk import CSV, reset password

### Fase 3 — Jangka Panjang (Target: Awal November 2026)
13. Kanwil executive dashboard: KPI cards, grafik tren (Unovis), activity log
14. Peta geospasial: Leaflet, pin warna per status desa
15. Filter multi-dimensi: satuan kerja, wilayah, kategori, rentang waktu
16. Laravel Reverb: red-flag alert real-time di dashboard pimpinan
17. Audit trail: tampilan histori perubahan status laporan

---

## 7. Konvensi Penamaan

| Konteks | Konvensi | Contoh |
|---|---|---|
| Nama file PHP | PascalCase | `LaporanWorkflowService.php` |
| Nama class PHP | PascalCase | `CheckSlaBreaches` |
| Method PHP | camelCase | `generateKodeTiket()` |
| Kolom database | snake_case | `sla_verifikasi_breached` |
| Nama file Vue | PascalCase | `LaporanStatusBadge.vue` |
| Variabel TS/JS | camelCase | `desaBinaan`, `kodeTiket` |
| Nama tipe TS | PascalCase | `LaporanStatus`, `DesaBinaan` |
| Route name | dot.notation per role | `desa.laporan.store`, `kanwil.masterdata.index` |
| CSS token | kebab-case | `--status-aman`, `--sla-redflag` |

---

## 8. Cek Sebelum Submit Kode

Sebelum menyelesaikan setiap task, pastikan:

- [ ] Tidak ada warna hardcode di komponen Vue — semua lewat token semantik.
- [ ] Semua query sudah di-scope sesuai role pengguna yang login.
- [ ] Form baru memakai vee-validate + Zod, bukan validasi manual.
- [ ] Tidak ada `tailwind.config.ts` baru untuk tema.
- [ ] File komponen mengikuti struktur folder yang ditetapkan.
- [ ] Tipe TypeScript tersedia di semua props dan return value.
- [ ] Tidak ada `any` tanpa komentar justifikasi.
- [ ] Ikon konsisten `lucide-vue-next` di semua komponen baru.
- [ ] Migration baru sesuai `SCHEMA.md` (nama kolom, tipe, constraint).
- [ ] Tidak ada route di `api.php` yang seharusnya di `web.php`.
- [ ] Jalankan `php artisan test` (Pest) sebelum menyatakan task selesai jika ada test yang relevan.
