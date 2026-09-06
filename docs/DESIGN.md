# design.md — SIMPEL DBI Design System

Disusun dalam 2 lapisan seperti pola "Layer 1: Schema" (token mentah yang menentukan warna/font/ukuran di seluruh tampilan) dan "Layer 2: Sections" (aturan konsistensi & Do's/Don'ts — satu file yang jadi rujukan dari warna pertama sampai batasan terakhir). Diimplementasikan di atas shadcn-vue (Reka UI/Radix) + Tailwind CSS v4.

---

## LAPISAN 1: SCHEMA (Design Tokens)

```json
{
  "version": "1.0",
  "name": "SIMPEL DBI Design System",
  "description": "Dashboard pemerintahan yang clean, corporate, dan konsisten untuk 4 aktor: Desa, PIMPASA, UPT, Kanwil.",
  "colors": {
    "primary": "#033566",
    "secondary": "#E8C070",
    "neutral": "#0B1220",
    "background": "#FFFFFF",
    "on-primary": "#FFFFFF",
    "on-secondary": "#1A1300",
    "status": {
      "aman": "#16A34A",
      "perlu-pembinaan": "#E8C070",
      "ada-aduan": "#DC2626",
      "sla-redflag": "#DC2626"
    }
  },
  "typography": {
    "fontFamilySans": "Inter",
    "fontFamilyMono": "JetBrains Mono",
    "h1": { "fontSize": "2.25rem", "fontWeight": 700, "lineHeight": 1.2, "letterSpacing": "-0.02em" },
    "h2": { "fontSize": "1.875rem", "fontWeight": 700, "lineHeight": 1.25, "letterSpacing": "-0.02em" },
    "h3": { "fontSize": "1.5rem", "fontWeight": 600, "lineHeight": 1.3, "letterSpacing": "-0.01em" },
    "h4": { "fontSize": "1.25rem", "fontWeight": 600, "lineHeight": 1.35 },
    "h5": { "fontSize": "1.125rem", "fontWeight": 600, "lineHeight": 1.4 },
    "h6": { "fontSize": "1rem", "fontWeight": 600, "lineHeight": 1.4 },
    "body-lg": { "fontSize": "1.125rem", "fontWeight": 400, "lineHeight": 1.6 },
    "body": { "fontSize": "1rem", "fontWeight": 400, "lineHeight": 1.6 },
    "body-sm": { "fontSize": "0.875rem", "fontWeight": 400, "lineHeight": 1.5 },
    "caption": { "fontSize": "0.75rem", "fontWeight": 500, "lineHeight": 1.4 },
    "code-lg": { "fontFamily": "mono", "fontSize": "1rem", "fontWeight": 500 },
    "code": { "fontFamily": "mono", "fontSize": "0.875rem", "fontWeight": 500 },
    "code-sm": { "fontFamily": "mono", "fontSize": "0.75rem", "fontWeight": 500 }
  },
  "rounded": {
    "sm": "6px",
    "md": "10px",
    "lg": "16px",
    "full": "9999px"
  },
  "spacing": {
    "xs": "4px",
    "sm": "8px",
    "md": "16px",
    "lg": "24px",
    "xl": "32px",
    "2xl": "48px"
  },
  "components": {
    "button-primary": {
      "backgroundColor": "colors.primary",
      "textColor": "colors.on-primary",
      "rounded": "rounded.md",
      "padding": "12px 20px",
      "fontWeight": 600
    },
    "button-primary-hover": {
      "backgroundColor": "#04407D",
      "textColor": "colors.on-primary"
    },
    "button-secondary": {
      "backgroundColor": "colors.secondary",
      "textColor": "colors.on-secondary",
      "rounded": "rounded.md",
      "padding": "12px 20px",
      "fontWeight": 600
    },
    "button-secondary-hover": {
      "backgroundColor": "#DDB05A",
      "textColor": "colors.on-secondary"
    },
    "button-outline": {
      "backgroundColor": "transparent",
      "borderColor": "colors.primary",
      "textColor": "colors.primary",
      "rounded": "rounded.md",
      "padding": "12px 20px"
    },
    "card": {
      "backgroundColor": "#FFFFFF",
      "borderColor": "#E5E7EB",
      "rounded": "rounded.lg",
      "padding": "24px"
    },
    "badge-status": {
      "rounded": "rounded.full",
      "padding": "4px 10px",
      "fontSize": "typography.caption",
      "fontWeight": 600
    },
    "input": {
      "borderColor": "#D1D5DB",
      "borderColorFocus": "colors.primary",
      "rounded": "rounded.sm",
      "padding": "10px 14px"
    },
    "table-header": {
      "backgroundColor": "#F8FAFC",
      "textColor": "#334155",
      "fontWeight": 600,
      "fontSize": "typography.body-sm"
    }
  }
}
```

Token ini yang menentukan warna, font, ukuran, dan bentuk komponen di **seluruh tampilan** — dari form laporan Desa sampai executive dashboard Kanwil.

---

## Implementasi CSS (Tailwind v4 — `globals.css`)

Nilai hex di atas dikonversi ke **OKLCH** (standar warna shadcn/Tailwind v4). Berikut hasil konversinya untuk `--primary` dan `--secondary`:

```css
:root {
  /* Brand */
  --primary: oklch(0.329 0.099 253.2);      /* #033566 */
  --primary-foreground: oklch(1 0 0);        /* #FFFFFF */
  --secondary: oklch(0.827 0.109 83.3);      /* #E8C070 */
  --secondary-foreground: oklch(0.24 0.03 83); /* #1A1300 */

  /* Status semantik (didefinisikan di RULES.md) */
  --status-aman: oklch(0.63 0.17 149);          /* #16A34A */
  --status-perlu-pembinaan: var(--secondary);   /* reuse token secondary, #E8C070 */
  --status-aduan: oklch(0.58 0.22 27);          /* #DC2626 */
  --sla-redflag: var(--status-aduan);

  /* Radius */
  --radius-sm: 6px;
  --radius-md: 10px;
  --radius-lg: 16px;
}

.dark {
  --primary: oklch(0.72 0.10 253.2);
  --primary-foreground: oklch(0.14 0.02 253);
  --secondary: oklch(0.78 0.10 83.3);
  --secondary-foreground: oklch(0.18 0.02 83);
  --status-aman: oklch(0.7 0.16 149);
  --status-aduan: oklch(0.65 0.20 27);
}

@theme inline {
  --color-primary: var(--primary);
  --color-primary-foreground: var(--primary-foreground);
  --color-secondary: var(--secondary);
  --color-secondary-foreground: var(--secondary-foreground);
  --color-status-aman: var(--status-aman);
  --color-status-perlu-pembinaan: var(--status-perlu-pembinaan);
  --color-status-aduan: var(--status-aduan);
  --color-sla-redflag: var(--sla-redflag);

  --font-sans: "Inter", ui-sans-serif, system-ui, sans-serif;
  --font-mono: "JetBrains Mono", ui-monospace, monospace;

  --radius-sm: var(--radius-sm);
  --radius-md: var(--radius-md);
  --radius-lg: var(--radius-lg);
}
```

> **Catatan:** nilai OKLCH `--primary`/`--secondary` di atas hasil konversi manual dari hex — cukup presisi untuk dipakai langsung, tapi kalau butuh akurasi piksel-sempurna, verifikasi sekali lagi lewat [shadcn Theme Generator](https://ui.shadcn.com) atau color picker OKLCH di browser DevTools sebelum final ke production. Warna dark mode (`.dark`) di atas adalah versi lightness dinaikkan supaya tetap kontras di background gelap — sesuaikan lagi saat review visual.

### Detail Skala Tipografi (referensi cepat)

| Level | Font | Ukuran (rem / px) | Weight | Pemakaian |
|---|---|---|---|---|
| H1 | Inter | 2.25rem / 36px | 700 | Judul halaman utama (jarang dipakai, 1x per halaman) |
| H2 | Inter | 1.875rem / 30px | 700 | Judul section besar (mis. "Executive Dashboard") |
| H3 | Inter | 1.5rem / 24px | 600 | Judul card/panel (mis. "Grafik Tren Bulanan") |
| H4 | Inter | 1.25rem / 20px | 600 | Sub-judul dalam card |
| H5 | Inter | 1.125rem / 18px | 600 | Label section kecil |
| H6 | Inter | 1rem / 16px | 600 | Judul level terkecil (setara body tapi bold) |
| Body Large | Inter | 1.125rem / 18px | 400 | Teks pengantar/deskripsi penting |
| Body | Inter | 1rem / 16px | 400 | Teks default — form, paragraf, deskripsi |
| Body Small | Inter | 0.875rem / 14px | 400 | Teks sekunder, keterangan field, tabel |
| Caption | Inter | 0.75rem / 12px | 500 | Label kecil, timestamp, helper text |
| Code Large | JetBrains Mono | 1rem / 16px | 500 | Angka KPI besar di dashboard (opsional mono style) |
| Code | JetBrains Mono | 0.875rem / 14px | 500 | Kode tiket, NIP, nomor SK, kolom angka tabel |
| Code Small | JetBrains Mono | 0.75rem / 12px | 500 | Kode kecil di badge (mis. kode wilayah) |

---

## LAPISAN 2: SECTIONS (Konsistensi & Do's / Don'ts)

Satu bagian ini yang menentukan konsistensi seluruh project — dari warna pertama sampai Do's & Don'ts terakhir.

### 1. Warna
**Do's**
- Pakai `--primary` (#033566) untuk aksi utama: tombol submit, link aktif, header sidebar, item navigasi terpilih.
- Pakai `--secondary` (#E8C070) sebagai aksen/penekanan sekunder: highlight badge non-kritis, hover state ringan, ikon status "perlu pembinaan", elemen dekoratif kecil di dashboard.
- Warna status (`status-aman`/`status-aduan`/`sla-redflag`) **hanya** untuk merepresentasikan kondisi data nyata — jangan dipakai untuk elemen dekoratif lain.
- **Warna Teks Data Hitam Base**: Seluruh data teks di tabel, kartu statistik, detail modal, dan list (kode tiket, NIP, nama UPT, nama desa, angka statistik, % rate) **WAJIB MENGGUNAKAN WARNA HITAM BASE (`text-slate-900 font-bold` / `text-slate-800 font-semibold`)** agar tampilan ultra-clean, minimalis, dan sangat legibel.

**Don'ts**
- Jangan pakai warna-warni (biru, ungu, hijau berlebihan) untuk teks data/angka statistik biasa — seluruh teks data wajib hitam base, **KECUALI** badge status semantik (`status-aman`, `status-perlu-pembinaan`, `status-aduan`/`sla-redflag`).
- Jangan pakai `--secondary` (gold) untuk teks body panjang — kontrasnya kurang kuat untuk keterbacaan lama, hanya untuk aksen/tombol/badge.
- Jangan campur warna gold (`secondary`) dengan warna status "perlu pembinaan" untuk maksud berbeda dalam satu layar — di token ini keduanya sengaja sama (`#E8C070`) supaya tidak ambigu, bukan kebetulan.
- Jangan hardcode `#033566`/`#E8C070` langsung di komponen — selalu lewat `bg-primary`/`text-secondary`, dst.

### 2. Tombol (Button)
**Do's**
- Tombol aksi utama per halaman (submit laporan, simpan verifikasi, kirim disposisi) → `button-primary`.
- Tombol aksi sekunder yang tetap penting (export PDF, lihat detail) → `button-secondary` atau `button-outline`.
- Tombol destruktif (tolak laporan, hapus) tetap pakai token `destructive` bawaan shadcn — **bukan** salah satu dari primary/secondary.
- **Tombol Aksi Ikon Tabel (Global CRUD Icon Buttons)**:
  Seluruh tombol ikon aksi di dalam tabel (kolom **Aksi**) WAJIB menggunakan class utility global soft-background berikut agar tampil konsisten, ultra-clean, dan intuitif:
  - **View / Detail (`Eye`)**: Class `btn-action-view` (Soft Sky Blue `#f0f9ff` bg, `#0284c7` text & border).
  - **Edit / Ubah (`Edit2` / `Pencil`)**: Class `btn-action-edit` (Soft Amber Gold `#fffbeb` bg, `#d97706` text & border).
  - **Delete / Hapus (`Trash2`)**: Class `btn-action-delete` (Soft Rose Red `#fff1f2` bg, `#e11d48` text & border).
  - **Neutral / Extra (`MoreVertical` / `FileText`)**: Class `btn-action-neutral` (Soft Slate Gray `#f1f5f9` bg, `#475569` text & border).
- **Alignment Kolom Aksi Tabel**: Header kolom **Aksi** (`<th class="text-center">`) dan kontainer tombol aksi (`<td class="text-center"><div class="flex justify-center gap-1.5">`) WAJIB menggunakan alignment rata tengah (**center**) agar posisi tombol simetris dan rapi di tengah kolom.

**Don'ts**
- Jangan lebih dari satu `button-primary` yang mencolok dalam satu viewport/card — itu menghilangkan hierarki aksi.
- Jangan pakai gold (`secondary`) untuk tombol submit/simpan — warna ini untuk aksen, bukan aksi utama; commit ke `primary` (navy) untuk itu.
- Jangan gunakan ikon aksi tabel polos tanpa warna background hover/badge (`btn-action-view`, `btn-action-edit`, `btn-action-delete`) agar hierarki aksi tabel tetap tegas dan konsisten se-sistem.

### 3. Tipografi
**Do's**
- Satu H1 per halaman.
- Seluruh teks dan data tabel wajib menggunakan font **Inter** (`font-sans`). Untuk data angka/numerik pada tabel (ID, jumlah desa, durasi SLA, % rate), sertakan class `tabular-nums` agar lebar karakter angka konsisten dan aligned secara vertikal.
- Monospace (`font-mono` / JetBrains Mono) **HANYA & KHUSUS** digunakan untuk Kode Tiket Laporan (`kode_tiket`) dan NIP Pegawai (`NIP`).
- Sentence case untuk label — kecuali singkatan resmi (NIP, SK, BAP, TPPO).

**Don'ts**
- Jangan skip level heading (H2 langsung ke H5) demi alasan visual — ikuti hierarki semantik dulu, baru sesuaikan ukuran lewat CSS kalau perlu.
- Jangan pakai JetBrains Mono (`font-mono`) untuk data angka/teks tabel umum — monospace hanya untuk kode teknis (Kode Tiket & NIP).

### 4. Komponen (Card, Badge, Input, Table & Sorting)
**Do's**
- **Layout KPI Metric Card & Alignment Presisi**:
  - **Penyesuaian Alignment Kiri & Kanan**: Inner padding kartu KPI wajib disamakan presisi (**`p-4 sm:p-5`**) dengan kontainer kartu utama di bawahnya, sehingga tepi teks dan garis dalam kartu di atas **100% sejajar lurus secara vertikal (aligned)** dengan tepi kartu di bawahnya.
  - **Angka Statistik Utama**: Wajib menggunakan font Inter berukuran besar **`text-3xl font-bold font-sans tabular-nums text-slate-900`** agar data mencolok dan rapi.
  - **Posisi Ikon Corner (80% Tampak)**: Ikon Lucide diletakkan di **Pojok Kanan Bawah Kartu** berukuran besar (**`size="105"` / `stroke-width="1.0"`**) secara `absolute -right-4 -bottom-6` dengan `overflow-hidden` sehingga hanya **~80% bagian ikon yang tampak**, memberikan kesan kartu corporate ultra-modern.
- Card dipakai untuk satu unit informasi mandiri (satu KPI, satu laporan, satu profil desa) — radius `lg` (16px) / `rounded-lg` (10px).
- Badge status pakai `rounded-full`, warna sesuai token status, selalu didampingi teks (bukan warna saja).
- Table header pakai background abu muda (`#F8FAFC` / `bg-slate-100/70`) dengan teks bold `body-sm` (`text-slate-700 font-semibold text-xs`) — konsisten di semua modul (worklist PIMPASA, rekap UPT, tabel Kanwil).
- **Hierarki Warna & Font Teks Tabel**:
  - Seluruh data sel pada tabel menggunakan font **Inter** (`font-sans`), dengan `tabular-nums` untuk angka.
  - **Hitam Bold Utama (`text-slate-900 font-bold font-sans`)**: Digunakan untuk identitas data utama seperti Nama Personel, Nama Desa Binaan, Nama Satker UPT, Nomor Urut (No / ID Real Data), dan Angka KPI Statistik. (Pencatatan NIP Resmi dan Kode Tiket tetap `font-mono text-slate-900 font-bold`).
  - **Grey Medium Sekunder (`text-slate-500 font-medium font-sans`)**: Digunakan untuk atribut penjelas sekunder seperti Pangkat/Golongan, UPT Pembina, Klasifikasi Tipe Kanim, Kategori, dan Timestamp Tanggal.
- **Aturan Pengurutan & Sorting Header**:
  - **Indikator Arah Panah Dinamis**:
    - `ArrowUp` (⬆️): Mode Ascending (`asc` - A ke Z / 1 ke 9), muncul saat data diurutkan dari nomor terawal / huruf A di atas.
    - `ArrowDown` (⬇️): Mode Descending (`desc` - Z ke A / 9 ke 1), muncul saat data diurutkan dari nomor terbesar / huruf Z di atas.
    - `ArrowUpDown` (↕️): Mode Netral Inaktif pada kolom yang sedang tidak aktif di-sort.
  - **Kolom No (ID Real Data)**: Kolom "No" wajib menampilkan **ID Real Data** (`p.id`, `d.id`, `u.id`) bukan index loop statis, sehingga saat di-sort secara Descending, angka di layar berpindah secara riil (`50, 49, 48...`).
- Input focus ring pakai warna `primary`, radius `sm` (6px) — lebih kecil dari card supaya terasa beda level.

**Don'ts**
- Jangan gunakan warna-warni mencolok (biru/ungu/hijau berlebihan) pada kolom data biasa seperti NIP, Jumlah Desa, atau Jumlah Personel — seluruh data biasa wajib mematuhi aturan Hitam Bold vs Grey Medium.
- Jangan gunakan index loop statis (`index + 1`) pada kolom No jika tabel mendukung fitur pengurutan (sorting).
- Jangan bikin variasi radius baru di luar 3 token (`sm`/`md`/`lg`) tanpa alasan kuat.
- Jangan hilangkan border/shadow pembeda pada card yang berisi red-flag — elemen ini butuh penekanan visual ekstra (border `status-aduan` lebih tegas), beda dari card biasa.

### 5. Ikon — Lucide
- Package: `lucide-vue-next`.
- Ukuran standar:
  - **20px** — ikon di dalam tombol, item navigasi sidebar, badge.
  - **16px** — ikon inline dengan teks (caption, label, baris tabel).
  - **24px** — ikon untuk empty state, ilustrasi kecil, heading section.
- **Do's**: konsisten satu keluarga ikon (Lucide) di seluruh modul; ikon status (red-flag, checklist, pin peta) selalu didampingi label teks — jangan ikon saja.
- **Don'ts**: jangan campur dengan Heroicons/Font Awesome di komponen manapun; jangan variasikan ukuran ikon di luar tiga standar di atas tanpa alasan kuat.

### 6. Spacing & Layout
**Do's**
- Gunakan skala token (`xs` 4px → `2xl` 48px) untuk semua gap/padding/margin.
- Dashboard grid: gap antar-card konsisten `md`(16px) atau `lg`(24px), pilih satu per halaman, jangan campur.

**Don'ts**
- Jangan pakai nilai spacing arbitrary (`p-[13px]`) kecuali menyesuaikan elemen pihak ketiga (peta Leaflet).

---

## Catatan Penyelarasan dengan `RULES.md`

Design system ini **telah sinkron** dengan `RULES.md`. Keputusan final ikon: **`lucide-vue-next`** dipakai di seluruh sistem, menggantikan referensi Heroicons sebelumnya. Kedua dokumen kini konsisten.