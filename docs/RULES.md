# RULES.md — Design & Frontend Convention SIMPEL DBI

Dokumen ini adalah aturan wajib untuk semua pekerjaan UI di proyek SIMPEL DBI. Berlaku untuk siapa pun yang menulis kode frontend (manusia atau AI assistant) agar hasilnya konsisten, dapat diakses, dan tidak terasa seperti template generik.

---

## 1. Prinsip Desain

SIMPEL DBI adalah **sistem internal pemerintahan**, bukan produk konsumen. Prioritas desain: **kejelasan, kepercayaan, dan efisiensi kerja** — bukan kesan "wah" atau branding agresif. Pengguna (perangkat desa, PIMPASA, staf UPT, pimpinan Kanwil) menggunakan sistem ini untuk bekerja, seringkali dalam kondisi terburu-buru atau sinyal internet terbatas.

- **Netral tapi tidak generik.** Hindari template "SaaS card kit" default: jangan tumpuk semua konten jadi kartu rounded seragam dengan shadow abu-abu yang sama di mana-mana tanpa alasan hierarki. Gunakan card hanya ketika benar-benar merepresentasikan satu unit informasi mandiri (KPI, satu laporan, satu desa).
- **Warna punya makna, bukan dekorasi.** Warna status (merah/kuning/hijau) di sistem ini **merepresentasikan kondisi nyata** (red-flag SLA, perlu pembinaan, aman) — jangan pakai warna-warna itu untuk elemen dekoratif lain, supaya makna tidak bias/tercampur.
- **Tidak perlu render ulang kepribadian brand di tiap layar.** Satu sistem identitas visual (warna, tipografi, spacing) dipakai konsisten dari halaman Desa sampai dashboard Kanwil — jangan biarkan tiap modul terasa dibuat oleh tim berbeda.
- **Hindari filler visual generik AI:** jangan tambahkan eyebrow label ALL-CAPS di atas setiap heading, jangan beri angka urut (01/02/03) kecuali kontennya memang sebuah urutan/proses, jangan tambahkan '→' otomatis di akhir teks tombol, jangan gunakan gradient dekoratif tanpa fungsi.
- **Motion secukupnya.** Animasi hanya untuk merespons aksi pengguna (buka dialog, expand baris tabel, konfirmasi submit) — bukan animasi "fade-slide-up" di tiap section saat scroll.

---

## 2. Tailwind CSS v4 — Aturan Konfigurasi

Proyek ini pakai **Tailwind v4 CSS-first configuration**. Tidak ada `tailwind.config.js`/`.ts` untuk tema — semua definisi warna, radius, dan font ada di satu file CSS global.

### Wajib
- Import Tailwind dengan `@import "tailwindcss";` (bukan tiga directive `@tailwind base/components/utilities` gaya v3).
- Definisikan token warna dalam format **OKLCH**, bukan HSL — ini standar baru shadcn/Tailwind v4.
- Semua token warna didefinisikan di `:root` (light mode) dan `.dark` (dark mode), lalu diekspos ke Tailwind lewat blok `@theme inline`:
  ```css
  :root {
    --primary: oklch(0.21 0.006 285.9);
    --primary-foreground: oklch(0.98 0 0);
    /* ...token lain */
  }
  .dark {
    --primary: oklch(0.92 0.004 286);
    --primary-foreground: oklch(0.21 0.006 285.9);
  }
  @theme inline {
    --color-primary: var(--primary);
    --color-primary-foreground: var(--primary-foreground);
  }
  ```
- Pakai `@tailwindcss/vite` sebagai plugin build (bukan PostCSS classic) di `vite.config.ts`.
- Untuk animasi komponen shadcn, gunakan `tw-animate-css`, **bukan** `tailwindcss-animate` (sudah deprecated di v4).

### Dilarang
- Jangan buat `tailwind.config.ts` untuk menaruh warna tema — v4 mengabaikannya untuk keperluan ini.
- Jangan pakai `hsl(var(--background))` — format lama, ganti ke OKLCH langsung via `@theme inline`.
- Jangan nested `.dark { @theme { ... } }` — v4 tidak mendukung `@theme` bersarang. Definisikan variabel dulu di `.dark`, baru mapping-nya sekali di `@theme inline`.
- Jangan pakai `@apply` di dalam `@layer base`/`@layer components` untuk styling komponen kustom — di v4 gunakan `@utility` sebagai gantinya untuk menghindari breaking change urutan cascade layer.
- Jangan tambah `dark:` manual untuk warna semantik (`bg-primary`, `text-muted-foreground`, dst) — token semantik shadcn sudah otomatis menyesuaikan lewat `.dark`.

---

## 3. shadcn-vue & Radix/Reka UI — Aturan Komponen

- **Jangan reinvent primitive.** Untuk dialog, dropdown, popover, tooltip, tabs — selalu pakai komponen shadcn-vue yang sudah ada (dibangun di atas Reka UI, port Vue dari Radix), jangan bikin versi custom dari nol. Primitive ini sudah menangani keyboard navigation, focus trap, dan ARIA attributes yang benar.
- **Jangan hapus/override behavior aksesibilitas bawaan** (focus ring, `aria-*`, `role`) demi alasan estetika semata. Kalau focus ring terasa mengganggu visual, ubah warnanya lewat token `--ring`, jangan `outline-none` tanpa pengganti.
- Tambah komponen lewat CLI (`shadcn-vue add [nama]`), lalu **edit langsung source code-nya** di `resources/js/Components/ui/` sesuai kebutuhan — ini model "copy-paste ownership" shadcn, bukan library yang di-`npm install` lalu di-patch dari luar.
- Primitive shadcn-vue terbaru punya atribut `data-slot` di tiap elemen — pertahankan atribut ini saat kustomisasi, berguna untuk styling terarah dan debugging.
- Komponen form (`Field`, `FieldLabel`, `FieldError`, dst dari shadcn-vue) **wajib** dipasangkan dengan `vee-validate` + `@vee-validate/zod` — jangan bikin state error manual per-input di luar pola ini, supaya validasi konsisten di semua form (laporan desa, verifikasi PIMPASA, disposisi UPT).

---

## 4. Sistem Warna Semantik

Selain token dasar shadcn (`primary`, `secondary`, `muted`, `destructive`, `accent`), tambahkan token khusus domain berikut di `:root`/`.dark`, lalu map di `@theme inline` (pola sama seperti contoh token `warning` di dokumentasi shadcn-vue):

| Token | Kegunaan | Referensi Warna |
|---|---|---|
| `--status-aman` | Pin peta & badge status desa "Aman/Aktif" | Hijau |
| `--status-perlu-pembinaan` | Pin peta & badge status desa "Perlu Pembinaan" | Kuning |
| `--status-aduan` | Pin peta & badge status desa "Ada Aduan Belum Tertangani" | Merah |
| `--sla-redflag` | Badge/border laporan yang melewati SLA | Merah (bisa reuse `destructive`, tapi beri nama semantik sendiri agar jelas maknanya beda dari "error validasi") |

**Aturan pakai:** komponen (`LaporanStatusBadge.vue`, `MapDesaBinaan.vue`) merujuk ke token semantik ini (`bg-status-aman`, dst), **bukan** hardcode nama warna Tailwind (`bg-green-500`) langsung di komponen — supaya kalau suatu saat palet direvisi, cukup ubah satu file token, bukan cari-ganti di semua komponen.

---

## 5. Tipografi

- Satu keluarga font utama untuk seluruh sistem (**Inter** / `font-sans`). Tidak perlu font kedua untuk heading; bedakan lewat weight dan ukuran, bukan ganti typeface.
- **Tipografi Data Tabel**: Seluruh isi data pada tabel (nama, status, angka statistik, ID real, durasi SLA, persentase) **WAJIB menggunakan font Inter (`font-sans`)**. Untuk angka di tabel/dashboard (jumlah laporan, SLA jam, statistik, ID), gunakan `font-variant-numeric: tabular-nums` (Tailwind: `tabular-nums`) agar posisi angka sejajar rapi secara vertikal tanpa mengubah font ke monospace.
- **Penggunaan Monospace (`font-mono` / JetBrains Mono)**: `font-mono` dilarang digunakan untuk angka/teks tabel biasa. `font-mono` **HANYA & KHUSUS** digunakan untuk kode identitas teknis resmi seperti Kode Tiket Laporan (`kode_tiket`), NIP Pegawai (`NIP`), atau potongan skrip/log sistem.
- Skala ukuran konsisten dan terbatas (mis. `text-sm`, `text-base`, `text-lg`, `text-xl`, `text-2xl`) — jangan pakai ukuran arbitrary (`text-[15.5px]`) tanpa alasan kuat.
- **Jangan** pakai all-caps (`uppercase tracking-wide`) untuk label kolom tabel atau field form kecuali untuk singkatan resmi (NIP, SK, BAP, TPPO). Label kolom tabel cukup sentence case biasa — ini sistem kerja, bukan halaman marketing.

---

## 6. Layout & Spacing

- Pakai skala spacing default Tailwind (`4, 6, 8, 12, 16, 24...`) secara konsisten — jangan campur nilai custom (`p-[13px]`) kecuali menyesuaikan elemen pihak ketiga (peta Leaflet, misalnya).
- Dashboard KPI cards: grid rata, gap seragam, tidak semua card harus punya shadow — reserve shadow/border lebih tegas untuk elemen yang butuh penekanan (mis. red-flag).
- Tabel data (Tanstack Table) adalah elemen utama di modul PIMPASA/UPT/Kanwil — beri ruang yang cukup, jangan dipaksa masuk card sempit dengan padding berlebihan yang mengorbankan jumlah baris terlihat.
- Form pelaporan (sisi Desa, sering diakses lewat HP) — layout satu kolom, field besar, target tap minimal 44×44px untuk kenyamanan input di mobile web dengan koneksi lambat.

---

## 7. Ikon

- Konsisten pakai `lucide-vue-next` di seluruh sistem — jangan campur dengan icon set lain (Heroicons, Font Awesome, dst) di komponen berbeda.
- Ukuran ikon default `16px` (inline dengan teks) atau `20px` (button/nav) — jangan variatif tanpa pola.
- Ikon status (red-flag, checklist verifikasi, pin peta) harus selalu didampingi label teks, bukan ikon saja — supaya tidak ambigu bagi pengguna dengan literasi digital berbeda-beda (terutama modul Desa).

---

## 8. Aksesibilitas (Wajib, Bukan Opsional)

- Semua interaksi harus bisa dilakukan lewat keyboard (Tab, Enter, Esc) — otomatis terpenuhi selama tidak meng-override primitive Reka UI/Radix.
- Kontras warna teks-background minimal WCAG AA (4.5:1 untuk teks normal) — cek terutama untuk teks di atas warna status (hijau/kuning/merah).
- Setiap form input punya `<Label>` yang terhubung (bukan hanya placeholder sebagai label).
- Pesan error validasi (dari Zod/vee-validate) ditampilkan sebagai teks, bukan hanya warna border merah — supaya tetap jelas bagi pengguna buta warna.
- `prefers-reduced-motion` dihormati — animasi non-esensial dimatikan otomatis untuk pengguna yang mengaktifkan setting ini di OS/browser.

---

## 9. Struktur File Komponen

```
resources/js/Components/
├── ui/                     # Hasil generate shadcn-vue, edit langsung di sini
│   ├── button/
│   ├── dialog/
│   ├── table/
│   └── ...
├── domain/                 # Komponen spesifik SIMPEL DBI, dibangun di atas ui/
│   ├── LaporanStatusBadge.vue
│   ├── SlaRedFlag.vue
│   ├── MapDesaBinaan.vue
│   └── VerifikasiChecklist.vue
└── layout/
    ├── AppSidebar.vue      # Beda per role: Desa/PIMPASA/UPT/Kanwil
    └── AppHeader.vue
```

Aturan penamaan: **PascalCase** untuk nama file & nama komponen (`LaporanStatusBadge.vue`), **bukan** kebab-case atau camelCase.

---

## 10. Arsitektur Backend — Controller Tipis, Service Tebal

**Aturan utama:** Controller **tidak boleh** mengandung logika bisnis. Controller hanya bertugas sebagai orchestrator: menerima request, mendelegasikan ke Service, dan mengembalikan response (Inertia render atau redirect). Semua logika bisnis, kalkulasi, dan interaksi kompleks dengan database ada di `app/Services/`.

### Pola yang Wajib Diikuti

```php
// ✅ BENAR — Controller tipis, delegasi ke Service
class LaporanController extends Controller
{
    public function __construct(private LaporanWorkflowService $workflow) {}

    public function store(StoreLaporanRequest $request): RedirectResponse
    {
        $laporan = $this->workflow->submit($request->validated(), auth()->user());
        return redirect()->route('desa.laporan.show', $laporan)
            ->with('success', 'Laporan berhasil diajukan.');
    }
}

// ❌ SALAH — logika bisnis di dalam controller
class LaporanController extends Controller
{
    public function store(Request $request)
    {
        $kode = 'LP-' . now()->year . '-' . str_pad(Laporan::count() + 1, 6, '0', STR_PAD_LEFT);
        $laporan = Laporan::create([...$request->all(), 'kode_tiket' => $kode]);
        LaporanStatusHistory::create([...]);
        // dst... semua logika campur di controller
    }
}
```

### Pembagian Tanggung Jawab

| Lapisan | Lokasi | Tanggung Jawab |
|---|---|---|
| **Controller** | `app/Http/Controllers/` | Terima request, validasi via FormRequest, panggil Service, return response |
| **FormRequest** | `app/Http/Requests/` | Validasi input (rules, authorization) |
| **Service** | `app/Services/` | Logika bisnis: workflow, kalkulasi, orkestrasi multi-model |
| **Model** | `app/Models/` | Relasi Eloquent, scope query, accessor/mutator |
| **Job/Command** | `app/Jobs/`, `app/Console/` | Proses background (SLA check, red flag, queue) |

### Daftar Service yang Sudah Direncanakan (ARCHITECTURE.md)

```
app/Services/
├── AuthService.php               # Login, logout, reset password
├── LaporanWorkflowService.php    # Submit, verifikasi, disposisi, selesai
├── SlaService.php                # Kalkulasi & pengecekan batas SLA
├── GeospatialAggregationService.php  # Kalkulasi status desa untuk peta
└── UserManagementService.php     # Buat akun, bulk import, nonaktifkan
```

### Aturan Tambahan
- Setiap Service **wajib di-inject via constructor** (bukan `new Service()` di dalam method) — supaya mudah di-mock saat testing.
- Service **tidak boleh** mengembalikan Inertia response atau redirect — hanya mengembalikan data/model/exception.
- Jika satu Service terlalu besar (>200 baris), pertimbangkan untuk dipecah menjadi sub-service atau menggunakan Action class (satu class per satu action).

---

## 11. Checklist Sebelum Merge — Frontend & Backend

### Frontend
- [ ] Tidak ada warna hardcode (`bg-red-500`) — semua lewat token semantik.
- [ ] Tidak ada `tailwind.config.ts` baru untuk tema warna/font.
- [ ] Form baru memakai `vee-validate` + Zod schema, bukan validasi manual.
- [ ] Primitive shadcn-vue tidak di-override untuk menghilangkan fitur aksesibilitas.
- [ ] Sudah dicek di breakpoint mobile (khusus modul Desa) dan desktop (modul UPT/Kanwil).
- [ ] Ikon konsisten pakai `lucide-vue-next`.
- [ ] Dark mode tidak pecah (karena token semantik otomatis switch, tapi tetap perlu dicek visual).

### Backend
- [ ] Controller tidak mengandung logika bisnis — semua didelegasikan ke Service.
- [ ] Setiap method controller punya FormRequest terpisah untuk validasi.
- [ ] Service di-inject via constructor, bukan diinstansiasi langsung.
- [ ] Query di controller sudah di-scope sesuai role pengguna yang login.
- [ ] Tidak ada route di `api.php` — semua lewat `web.php` via Inertia.
- [ ] Perubahan status laporan selalu mencatat ke `laporan_status_histories`.