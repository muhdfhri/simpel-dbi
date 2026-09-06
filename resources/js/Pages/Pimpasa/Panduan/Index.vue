<script setup lang="ts">
import { ref } from 'vue';
import AppLayout from '@/components/layout/AppLayout.vue';
import {
    ChevronRight,
    ChevronDown,
    CheckSquare,
    FileCheck2,
    Building2,
    FolderKanban,
    BarChart3,
    FileSpreadsheet,
    FileText,
    CheckCircle2,
    AlertCircle,
    XCircle,
    RotateCcw
} from 'lucide-vue-next';

const props = defineProps<{
    appName?: string;
}>();

// Navigation sections
const activeSection = ref('tentang');
const activeTabLangkah = ref('langkah1');

const topics = [
    { id: 'tentang', title: '1. Peran PIMPASA & UPT' },
    { id: 'alur-verifikasi', title: '2. Alur Verifikasi & Disposisi' },
    { id: 'cara-verifikasi', title: '3. Cara Memproses Worklist' },
    { id: 'manajemen-desa', title: '4. Desa Binaan & Kegiatan' },
    { id: 'ekspor', title: '5. Ekspor & Cetak Laporan' },
    { id: 'faq', title: '6. Pertanyaan Umum (FAQ)' },
];

const scrollToSection = (id: string) => {
    activeSection.value = id;
    const element = document.getElementById(id);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
};

// Custom FAQ Accordion State
const openFaqIndex = ref<number | null>(0);
const toggleFaq = (index: number) => {
    openFaqIndex.value = openFaqIndex.value === index ? null : index;
};

const faqs = [
    {
        q: 'Bagaimana cara menerima dan memverifikasi laporan dari Perangkat Desa?',
        a: 'Buka menu <strong>Worklist Verifikasi</strong>, pilih tiket laporan yang berstatus <em>Diajukan</em>, periksa kesesuaian lokasi geospasial serta kelengkapan lampiran foto. Klik tombol <strong>Verifikasi Laporan</strong> lalu pilih salah satu dari tiga keputusan: <em>Diverifikasi PIMPASA</em>, <em>Minta Perbaikan</em>, atau <em>Ditolak</em>.'
    },
    {
        q: 'Kapan laporan diteruskan ke Tim UPT Imigrasi untuk penanganan lapangan?',
        a: 'Setelah Anda menyetujui laporan dengan status <strong>Diverifikasi PIMPASA</strong>, sistem akan otomatis memasukkan tiket laporan ke dalam daftar <strong>Disposisi & Tindak Lanjut UPT</strong> agar tim operasional UPT dapat menangani insiden secara teknis di lapangan.'
    },
    {
        q: 'Bagaimana cara mendownload rekapitulasi data dalam format Excel atau PDF?',
        a: 'Pada setiap menu utama PIMPASA (<strong>Worklist Verifikasi</strong>, <strong>Daftar Desa Binaan</strong>, dan <strong>Rekapitulasi Satker</strong>), tersedia tombol <strong>Export Excel</strong> (format .xlsx) dan <strong>Export PDF</strong> (format A4 Landscape) di sudut kanan atas tabel.'
    },
    {
        q: 'Apa bedanya keputusan Diverifikasi PIMPASA, Minta Perbaikan, dan Ditolak?',
        a: '<strong>Diverifikasi PIMPASA</strong> berarti laporan valid dan diteruskan ke UPT. <strong>Minta Perbaikan</strong> mengembalikan tiket ke Perangkat Desa untuk melengkapi bukti/keterangan. <strong>Ditolak</strong> membatalkan laporan yang tidak terbukti, duplikat, atau di luar kewenangan keimigrasian.'
    },
    {
        q: 'Bagaimana cara mencatat kegiatan pembinaan keimigrasian di desa?',
        a: 'Gunakan menu <strong>Kegiatan Pembinaan</strong>. Klik <strong>+ Tambah Kegiatan Baru</strong> untuk mengunggah dokumentasi sosialisasi, edukasi keimigrasian, atau inspeksi lapangan bersama perangkat desa.'
    }
];
</script>

<template>
    <AppLayout title="Panduan Penggunaan Petugas PIMPASA">
        <div class="space-y-6 font-sans text-slate-800">
            
            <!-- HEADER SIMPLE CLEAN (MATCHING PERANGKAT DESA STYLE) -->
            <div class="bg-white border border-slate-200 rounded-lg p-5 sm:p-6">
                <div class="space-y-1">
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">
                        Panduan Penggunaan - Petugas PIMPASA & UPT Imigrasi
                    </h1>
                    <p class="text-xs text-slate-500">
                        Petunjuk operasional verifikasi laporan kejadian, pelaksanaan disposisi UPT, tata kelola desa binaan, hingga pembuatan rekapitulasi dokumen resmi.
                    </p>
                </div>
            </div>

            <!-- MAIN CONTAINER WITH STICKY SIDEBAR (2-COLUMN LAYOUT) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <!-- LEFT SIDEBAR STICKY NAVIGATION -->
                <div class="lg:col-span-3 hidden lg:block">
                    <div class="sticky top-0 space-y-4 z-10 pt-0.5">
                        
                        <!-- Menu Panduan Navigation -->
                        <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-2xs">
                            <div class="p-3.5 border-b border-slate-100 bg-slate-50">
                                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    Menu Panduan PIMPASA
                                </h3>
                            </div>
                            <div class="p-1.5 space-y-0.5">
                                <button
                                    v-for="topic in topics"
                                    :key="topic.id"
                                    @click="scrollToSection(topic.id)"
                                    :class="[
                                        'w-full text-left px-3 py-2 rounded-md text-xs font-medium flex items-center justify-between transition-colors cursor-pointer',
                                        activeSection === topic.id
                                            ? 'bg-slate-900 text-white font-semibold'
                                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                                    ]"
                                >
                                    <span>{{ topic.title }}</span>
                                    <ChevronRight class="w-3.5 h-3.5 opacity-60 shrink-0" />
                                </button>
                            </div>
                        </div>

                        <!-- Butuh Bantuan Box -->
                        <div class="bg-white border border-slate-200 rounded-lg p-4 space-y-1 text-xs shadow-2xs">
                            <span class="font-bold text-slate-900 block">Dukungan Teknis Satker</span>
                            <p class="text-slate-500 leading-relaxed text-[11px]">
                                Jika menemukan kendala sistem atau sinkronisasi data wilayah, hubungi tim Administrator Kanwil Ditjen Imigrasi Sumut.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- RIGHT MAIN CONTENT (CLEAN MINIMAL DOCUMENTATION CARDS) -->
                <div class="lg:col-span-9 space-y-6">

                    <!-- SECTION 1: TENTANG PERAN PIMPASA -->
                    <section id="tentang" class="scroll-mt-6">
                        <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-2xs">
                            <div class="p-4 sm:p-5 border-b border-slate-100">
                                <h2 class="text-base font-bold text-slate-900 tracking-tight">
                                    1. Tentang Peran Petugas PIMPASA & UPT Imigrasi
                                </h2>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Tugas utama, wewenang verifikasi, dan koordinasi dengan Kantor Wilayah Ditjen Imigrasi.
                                </p>
                            </div>

                            <div class="p-5 space-y-3 text-xs text-slate-600 leading-relaxed">
                                <p>
                                    Sebagai <strong>Petugas Pembina PIMPASA</strong> (Petugas Imigrasi Pembina Desa Binaan), Anda merupakan verifikator garis depan yang memvalidasi setiap laporan kejadian keimigrasian dari Perangkat Desa di wilayah pengampuan Satker UPT Anda.
                                </p>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2">
                                    <div class="p-3.5 border border-slate-200 rounded-lg bg-slate-50/50 space-y-1">
                                        <div class="flex items-center gap-2 font-bold text-slate-900">
                                            <CheckSquare :size="16" class="text-slate-700 shrink-0" />
                                            <span>Verifikasi Worklist Laporan</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 leading-normal">
                                            Memeriksa keabsahan bukti foto, keterangan tempat kejadian, dan kesesuaian identitas WNA/TKA yang dilaporkan.
                                        </p>
                                    </div>

                                    <div class="p-3.5 border border-slate-200 rounded-lg bg-slate-50/50 space-y-1">
                                        <div class="flex items-center gap-2 font-bold text-slate-900">
                                            <FileCheck2 :size="16" class="text-slate-700 shrink-0" />
                                            <span>Disposisi & Penanganan UPT</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 leading-normal">
                                            Meneruskan tiket diverifikasi ke tim intelijen & penindakan keimigrasian UPT untuk penanganan fisik di lapangan.
                                        </p>
                                    </div>
                                </div>

                                <p class="pt-2">
                                    Setiap tindakan verifikasi yang Anda berikan secara langsung memengaruhi indikator kinerja <strong>Resolution Rate</strong> dan tingkat kerawanan wilayah pada dashboard eksekutif Kanwil.
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- SECTION 2: ALUR VERIFIKASI & DISPOSISI -->
                    <section id="alur-verifikasi" class="scroll-mt-6">
                        <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-2xs">
                            <div class="p-4 sm:p-5 border-b border-slate-100">
                                <h2 class="text-base font-bold text-slate-900 tracking-tight">
                                    2. Alur Operasional Verifikasi & Disposisi
                                </h2>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Tahapan lengkap penanganan tiket dari masuk hingga penyelesaian 100%.
                                </p>
                            </div>

                            <div class="p-5 space-y-4">
                                <div class="space-y-3">
                                    
                                    <!-- Step 1 -->
                                    <div class="p-4 border border-slate-200 rounded-lg flex items-start gap-3 bg-slate-50/50">
                                        <div class="w-6 h-6 rounded-full bg-slate-900 text-white font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">
                                            1
                                        </div>
                                        <div class="space-y-1 flex-1 text-xs">
                                            <div class="flex items-center justify-between gap-2">
                                                <h3 class="font-bold text-slate-900">Langkah 1: Laporan Diajukan Perangkat Desa</h3>
                                                <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 border border-amber-200 text-[10px] font-bold">Status: Diajukan</span>
                                            </div>
                                            <p class="text-slate-600 leading-relaxed">
                                                Laporan baru masuk ke antrean <strong>Worklist Verifikasi</strong> PIMPASA. Notifikasi tiket otomatis aktif untuk segera diperiksa.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Step 2 -->
                                    <div class="p-4 border border-slate-200 rounded-lg flex items-start gap-3 bg-slate-50/50">
                                        <div class="w-6 h-6 rounded-full bg-slate-900 text-white font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">
                                            2
                                        </div>
                                        <div class="space-y-1 flex-1 text-xs">
                                            <div class="flex items-center justify-between gap-2">
                                                <h3 class="font-bold text-slate-900">Langkah 2: Penelaahan & Keputusan PIMPASA</h3>
                                                <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 border border-blue-200 text-[10px] font-bold">Status: Diverifikasi</span>
                                            </div>
                                            <p class="text-slate-600 leading-relaxed">
                                                Petugas meneliti lampiran berkas dan titik lokasi geospasial. Jika disetujui, laporan berstatus <strong>Diverifikasi PIMPASA</strong>.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Step 3 -->
                                    <div class="p-4 border border-slate-200 rounded-lg flex items-start gap-3 bg-slate-50/50">
                                        <div class="w-6 h-6 rounded-full bg-slate-900 text-white font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">
                                            3
                                        </div>
                                        <div class="space-y-1 flex-1 text-xs">
                                            <div class="flex items-center justify-between gap-2">
                                                <h3 class="font-bold text-slate-900">Langkah 3: Disposisi & Tindak Lanjut UPT</h3>
                                                <span class="px-2 py-0.5 rounded bg-purple-100 text-purple-800 border border-purple-200 text-[10px] font-bold">Status: Ditindaklanjuti</span>
                                            </div>
                                            <p class="text-slate-600 leading-relaxed">
                                                Staf operasional UPT melaksanakan operasi penanganan lapangan, pengawasan keimigrasian, atau sosialisasi hukum.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Step 4 -->
                                    <div class="p-4 border border-slate-200 rounded-lg flex items-start gap-3 bg-slate-50/50">
                                        <div class="w-6 h-6 rounded-full bg-slate-900 text-white font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">
                                            4
                                        </div>
                                        <div class="space-y-1 flex-1 text-xs">
                                            <div class="flex items-center justify-between gap-2">
                                                <h3 class="font-bold text-slate-900">Langkah 4: Penyelesaian Kasus & Penutupan Tiket</h3>
                                                <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 border border-emerald-200 text-[10px] font-bold">Status: Selesai 100%</span>
                                            </div>
                                            <p class="text-slate-600 leading-relaxed">
                                                Hasil operasi lapangan diunggah sebagai berita acara resmi, tiket ditutup, dan data terekam pada rekapitulasi satker.
                                            </p>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- SECTION 3: CARA MEMPROSES WORKLIST -->
                    <section id="cara-verifikasi" class="scroll-mt-6">
                        <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-2xs">
                            <div class="p-4 sm:p-5 border-b border-slate-100">
                                <h2 class="text-base font-bold text-slate-900 tracking-tight">
                                    3. Panduan Pengambilan Keputusan Verifikasi
                                </h2>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Penjelasan teknis 3 pilihan opsi keputusan saat menelaah tiket laporan.
                                </p>
                            </div>

                            <div class="p-5 space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-3 bg-slate-100 p-1 rounded-lg gap-1">
                                    <button
                                        @click="activeTabLangkah = 'langkah1'"
                                        :class="[
                                            'py-2 px-3 text-xs font-bold rounded-md transition-all cursor-pointer text-center',
                                            activeTabLangkah === 'langkah1'
                                                ? 'bg-white text-emerald-800 shadow-2xs border border-slate-200'
                                                : 'text-slate-600 hover:text-slate-900'
                                        ]"
                                    >
                                        1. Setujui (Diverifikasi)
                                    </button>
                                    <button
                                        @click="activeTabLangkah = 'langkah2'"
                                        :class="[
                                            'py-2 px-3 text-xs font-bold rounded-md transition-all cursor-pointer text-center',
                                            activeTabLangkah === 'langkah2'
                                                ? 'bg-white text-amber-800 shadow-2xs border border-slate-200'
                                                : 'text-slate-600 hover:text-slate-900'
                                        ]"
                                    >
                                        2. Minta Perbaikan
                                    </button>
                                    <button
                                        @click="activeTabLangkah = 'langkah3'"
                                        :class="[
                                            'py-2 px-3 text-xs font-bold rounded-md transition-all cursor-pointer text-center',
                                            activeTabLangkah === 'langkah3'
                                                ? 'bg-white text-red-800 shadow-2xs border border-slate-200'
                                                : 'text-slate-600 hover:text-slate-900'
                                        ]"
                                    >
                                        3. Tolak Laporan
                                    </button>
                                </div>

                                <!-- Tab Content 1: Setujui -->
                                <div v-if="activeTabLangkah === 'langkah1'" class="p-4 border border-emerald-200 bg-emerald-50/40 rounded-lg space-y-2 text-xs">
                                    <div class="flex items-center gap-2 font-bold text-emerald-900">
                                        <CheckCircle2 :size="16" class="text-emerald-700 shrink-0" />
                                        <span>Keputusan: Diverifikasi PIMPASA</span>
                                    </div>
                                    <p class="text-slate-600 leading-relaxed">
                                        Pilih opsi ini apabila data uraian kejadian jelas, tempat lokasi teridentifikasi di wilayah desa binaan, dan bukti pendukung valid. Tiket akan diteruskan ke tim UPT.
                                    </p>
                                </div>

                                <!-- Tab Content 2: Minta Perbaikan -->
                                <div v-if="activeTabLangkah === 'langkah2'" class="p-4 border border-amber-200 bg-amber-50/40 rounded-lg space-y-2 text-xs">
                                    <div class="flex items-center gap-2 font-bold text-amber-900">
                                        <AlertCircle :size="16" class="text-amber-700 shrink-0" />
                                        <span>Keputusan: Minta Perbaikan Data</span>
                                    </div>
                                    <p class="text-slate-600 leading-relaxed">
                                        Pilih opsi ini jika bukti foto kurang jelas, alamat spesifik tidak lengkap, atau uraian laporan masih samar. Berikan catatan instruksi revisi yang spesifik untuk Perangkat Desa.
                                    </p>
                                </div>

                                <!-- Tab Content 3: Tolak Laporan -->
                                <div v-if="activeTabLangkah === 'langkah3'" class="p-4 border border-red-200 bg-red-50/40 rounded-lg space-y-2 text-xs">
                                    <div class="flex items-center gap-2 font-bold text-red-900">
                                        <XCircle :size="16" class="text-red-700 shrink-0" />
                                        <span>Keputusan: Ditolak</span>
                                    </div>
                                    <p class="text-slate-600 leading-relaxed">
                                        Pilih opsi ini jika laporan terindikasi palsu, duplikat dari tiket lain, atau materi aduan tidak terkait dengan keimigrasian/pembinaan desa. Tiket akan langsung diarsip dan ditutup.
                                    </p>
                                </div>

                            </div>
                        </div>
                    </section>

                    <!-- SECTION 4: MANAJEMEN DESA BINAAN & KEGIATAN -->
                    <section id="manajemen-desa" class="scroll-mt-6">
                        <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-2xs">
                            <div class="p-4 sm:p-5 border-b border-slate-100">
                                <h2 class="text-base font-bold text-slate-900 tracking-tight">
                                    4. Manajemen Desa Binaan & Kegiatan Pembinaan
                                </h2>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Pemantauan direktori wilayah pengampuan dan pencatatan sosialisasi.
                                </p>
                            </div>

                            <div class="p-5 space-y-3 text-xs text-slate-600 leading-relaxed">
                                <div class="space-y-3">
                                    <div class="p-3.5 border border-slate-200 rounded-lg bg-slate-50/50 space-y-1">
                                        <h3 class="font-bold text-slate-900 flex items-center gap-2">
                                            <Building2 :size="15" class="text-slate-700 shrink-0" />
                                            <span>Direktori & Profil Desa Binaan</span>
                                        </h3>
                                        <p class="text-[11px] text-slate-500 leading-relaxed">
                                            Melalui menu <strong>Daftar Desa Binaan</strong>, Anda dapat memantau seluruh wilayah desa pengampuan UPT, kontak Perangkat Desa terdaftar, jumlah akumulasi tiket, serta status indeks kerawanan (<em>Aman / Pembinaan Aktif / Rentan</em>).
                                        </p>
                                    </div>

                                    <div class="p-3.5 border border-slate-200 rounded-lg bg-slate-50/50 space-y-1">
                                        <h3 class="font-bold text-slate-900 flex items-center gap-2">
                                            <FolderKanban :size="15" class="text-slate-700 shrink-0" />
                                            <span>Kegiatan Pembinaan & Sosialisasi</span>
                                        </h3>
                                        <p class="text-[11px] text-slate-500 leading-relaxed">
                                            Menu <strong>Kegiatan Pembinaan</strong> digunakan untuk mencatat dan mendokumentasikan agenda rutin PIMPASA seperti sosialisasi aturan keimigrasian, penyuluhan pencegahan TPPO, dan rapat koordinasi bersama perangkat desa.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- SECTION 5: FITUR EKSPOR & CETAK -->
                    <section id="ekspor" class="scroll-mt-6">
                        <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-2xs">
                            <div class="p-4 sm:p-5 border-b border-slate-100">
                                <h2 class="text-base font-bold text-slate-900 tracking-tight">
                                    5. Cara Ekspor & Cetak Dokumen Laporan Resmi
                                </h2>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Panduan pengunduhan laporan dalam format Excel dan PDF.
                                </p>
                            </div>

                            <div class="p-5 space-y-3 text-xs text-slate-600 leading-relaxed">
                                <p>
                                    Seluruh menu utama di aplikasi SIMPEL DBI mendukung fitur ekspor dokumen otomatis dengan standar formatting:
                                </p>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                                    <div class="p-3.5 border border-emerald-200 bg-emerald-50/30 rounded-lg space-y-1">
                                        <div class="flex items-center gap-2 font-bold text-emerald-900">
                                            <FileSpreadsheet :size="16" class="text-emerald-700 shrink-0" />
                                            <span>Ekspor Excel</span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 leading-normal">
                                            Mengunduh berkas tabel spreadsheet native yang sudah terformat dengan header Navy Corporate, auto-column width, dan baris terstruktur rapi.
                                        </p>
                                    </div>

                                    <div class="p-3.5 border border-rose-200 bg-rose-50/30 rounded-lg space-y-1">
                                        <div class="flex items-center gap-2 font-bold text-rose-900">
                                            <FileText :size="16" class="text-rose-700 shrink-0" />
                                            <span>Ekspor PDF</span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 leading-normal">
                                            Mencetak dokumen resmi format PDF A4 Landscape yang dilengkapi kop instansi, metadata ringkasan, serta blok tanda tangan pengesahan struktural.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- SECTION 6: PERTANYAAN UMUM (FAQ) -->
                    <section id="faq" class="scroll-mt-6">
                        <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-2xs">
                            <div class="p-4 sm:p-5 border-b border-slate-100">
                                <h2 class="text-base font-bold text-slate-900 tracking-tight">
                                    6. Pertanyaan Umum (FAQ) PIMPASA
                                </h2>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Jawaban atas kendala dan pertanyaan umum seputar verifikasi & penanganan.
                                </p>
                            </div>

                            <div class="p-5 space-y-2">
                                <div
                                    v-for="(faq, index) in faqs"
                                    :key="index"
                                    class="border border-slate-200 rounded-lg overflow-hidden"
                                >
                                    <button
                                        @click="toggleFaq(index)"
                                        class="w-full p-3.5 text-left text-xs font-bold text-slate-900 bg-slate-50/60 hover:bg-slate-100 flex items-center justify-between gap-3 transition-colors cursor-pointer"
                                    >
                                        <span>{{ faq.q }}</span>
                                        <ChevronDown
                                            :class="[
                                                'w-4 h-4 text-slate-400 shrink-0 transition-transform duration-200',
                                                openFaqIndex === index ? 'rotate-180 text-slate-900' : ''
                                            ]"
                                        />
                                    </button>
                                    <div
                                        v-if="openFaqIndex === index"
                                        class="p-3.5 text-xs text-slate-600 leading-relaxed border-t border-slate-100 bg-white"
                                        v-html="faq.a"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </section>

                </div>
            </div>

        </div>
    </AppLayout>
</template>
