<script setup lang="ts">
import { ref } from 'vue';
import AppLayout from '@/components/layout/AppLayout.vue';
import {
    ChevronRight,
    ChevronDown,
    BarChart3,
    ShieldAlert,
    Award,
    FolderKanban,
    Map,
    Settings,
    FileSpreadsheet,
    FileText,
    CheckCircle2,
    AlertCircle,
    Building2,
    Users
} from 'lucide-vue-next';

const props = defineProps<{
    appName?: string;
}>();

// Navigation sections
const activeSection = ref('tentang');
const activeTabLangkah = ref('langkah1');

const topics = [
    { id: 'tentang', title: '1. Peran Executive Kanwil' },
    { id: 'executive-monitoring', title: '2. Executive Monitoring' },
    { id: 'peta-sebaran', title: '3. Peta Sebaran Desa' },
    { id: 'master-data', title: '4. Tata Kelola Master Data' },
    { id: 'ekspor', title: '5. Ekspor Laporan' },
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
        q: 'Apa tugas dan wewenang utama pengguna role Kanwil di SIMPEL DESI?',
        a: 'Role Kanwil bertindak sebagai <strong>Executive Monitoring & System Super-Admin</strong> se-Wilayah Sumatera Utara. Pengguna Kanwil berwewenang memantau kepatuhan SLA 24 jam seluruh UPT, menelaah scorecard kepatuhan Satker, memonitor peta geospasial sebaran desa binaan, serta mengelola master data user (Kanwil, PIMPASA, Perangkat Desa), UPT, dan kategori aduan.'
    },
    {
        q: 'Bagaimana cara melakukan eskalasi atau peneguran ke Kepala UPT jika terjadi keterlambatan SLA?',
        a: 'Buka menu <strong>Executive Monitoring ➔ Kendali SLA & Eskalasi</strong>. Pada baris laporan yang berstatus <em>🔴 Terlambat (> 24 Jam)</em>, klik tombol <strong>Tegur UPT</strong>. Sistem akan langsung mengirimkan peringatan resmi dan notifikasi eskalasi ke Satker UPT penanggung jawab.'
    },
    {
        q: 'Bagaimana cara membaca Indikator Status Kepatuhan UPT pada Scorecard?',
        a: 'Status kepatuhan UPT dihitung secara otomatis oleh sistem:<br/>- <strong>🟢 SANGAT BAIK</strong>: Tidak ada keterlambatan SLA (> 24 jam) dan Rate Penyelesaian ≥ 85%.<br/>- <strong>🟡 CUKUP</strong>: Terdapat 1-2 keterlambatan SLA atau Rate Penyelesaian antara 70% s.d. 84.9%.<br/>- <strong>🔴 PERLU EVALUASI</strong>: Terdapat > 2 keterlambatan SLA atau Rate Penyelesaian < 70%.'
    },
    {
        q: 'Bagaimana cara menambah Satker UPT Imigrasi atau Akun Petugas PIMPASA baru?',
        a: 'Gunakan menu <strong>Master Data System</strong>. Pilih tab <strong>Kelola UPT Imigrasi</strong> untuk menambah Satker UPT baru, atau tab <strong>Kelola Petugas PIMPASA</strong> untuk mendaftarkan akun verifikator UPT beserta penetapan wilayah pengampuannya.'
    },
    {
        q: 'Apakah dokumen hasil Ekspor PDF & Excel dari Kanwil sudah memenuhi standar resmi?',
        a: 'Ya, seluruh dokumen PDF yang diunduh dari menu Executive Monitoring telah dilengkapi Kop Resmi <strong>KANTOR WILAYAH DIREKTORAT JENDERAL IMIGRASI SUMATERA UTARA</strong>, metadata tanggal cetak, serta blok pengesahan struktural pimpinan Kanwil.'
    }
];
</script>

<template>
    <AppLayout title="Panduan Penggunaan Executive Kanwil">
        <div class="space-y-6 font-sans text-slate-800">
            
            <!-- HEADER SIMPLE CLEAN (MATCHING PIMPASA & DESA STYLE) -->
            <div class="bg-white border border-slate-200 rounded-lg p-5 sm:p-6">
                <div class="space-y-1">
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">
                        Panduan Penggunaan - Executive Monitoring & Administrator Kanwil
                    </h1>
                    <p class="text-xs text-slate-500">
                        Petunjuk operasional pemantauan kendali SLA, scorecard kepatuhan UPT, peta geospasial sebaran desa binaan, ekspor dokumen laporan, hingga tata kelola master data sistem se-Sumatera Utara.
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
                                    Menu Panduan Kanwil
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
                            <span class="font-bold text-slate-900 block">Dukungan Sistem Kanwil</span>
                            <p class="text-slate-500 leading-relaxed text-[11px]">
                                Jika memerlukan bantuan teknis server, migrasi data master UPT, atau penyesuaian hak akses role, hubungi tim IT Administrator Ditjen Imigrasi Pusat.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- RIGHT MAIN CONTENT (CLEAN MINIMAL DOCUMENTATION CARDS) -->
                <div class="lg:col-span-9 space-y-6">

                    <!-- SECTION 1: TENTANG PERAN EXECUTIVE KANWIL -->
                    <section id="tentang" class="scroll-mt-6">
                        <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-2xs">
                            <div class="p-4 sm:p-5 border-b border-slate-100">
                                <h2 class="text-base font-bold text-slate-900 tracking-tight">
                                    1. Tentang Peran Executive Monitoring & Administrator Kanwil
                                </h2>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Fungsi pengawasan eksekutif, pembinaan UPT Imigrasi, dan manajemen sistem tingkat wilayah.
                                </p>
                            </div>

                            <div class="p-5 space-y-3 text-xs text-slate-600 leading-relaxed">
                                <p>
                                    Sebagai <strong>Administrator & Executive Kanwil</strong> Ditjen Imigrasi Sumatera Utara, Anda memegang kendali penuh dalam pengawasan kinerja operasional seluruh Satker UPT Imigrasi serta pengawalan program Desa Binaan Imigrasi se-Sumatera Utara.
                                </p>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2">
                                    <div class="p-3.5 border border-slate-200 rounded-lg bg-slate-50/50 space-y-1">
                                        <div class="flex items-center gap-2 font-bold text-slate-900">
                                            <ShieldAlert :size="16" class="text-slate-700 shrink-0" />
                                            <span>Pengawasan SLA & Eskalasi Insiden</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 leading-normal">
                                            Memastikan seluruh aduan keimigrasian direspon oleh UPT sesuai batas standar waktu 24 jam serta memberikan teguran resmi apabila terjadi keterlambatan.
                                        </p>
                                    </div>

                                    <div class="p-3.5 border border-slate-200 rounded-lg bg-slate-50/50 space-y-1">
                                        <div class="flex items-center gap-2 font-bold text-slate-900">
                                            <FolderKanban :size="16" class="text-slate-700 shrink-0" />
                                            <span>Tata Kelola Master Data System</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 leading-normal">
                                            Mengelola akun pengguna (Kanwil, PIMPASA, Perangkat Desa), data UPT Imigrasi, serta matriks hak akses permission sistem.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- SECTION 2: EXECUTIVE MONITORING -->
                    <section id="executive-monitoring" class="scroll-mt-6">
                        <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-2xs">
                            <div class="p-4 sm:p-5 border-b border-slate-100">
                                <h2 class="text-base font-bold text-slate-900 tracking-tight">
                                    2. Fitur Utama Executive Monitoring
                                </h2>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Panduan pengawasan Kendali SLA, Scorecard Kepatuhan UPT, dan Pembinaan Desa.
                                </p>
                            </div>

                            <div class="p-5 space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-3 bg-slate-100 p-1 rounded-lg gap-1">
                                    <button
                                        @click="activeTabLangkah = 'langkah1'"
                                        :class="[
                                            'py-2 px-3 text-xs font-bold rounded-md transition-all cursor-pointer text-center',
                                            activeTabLangkah === 'langkah1'
                                                ? 'bg-white text-slate-900 shadow-2xs border border-slate-200'
                                                : 'text-slate-600 hover:text-slate-900'
                                        ]"
                                    >
                                        1. Kendali SLA & Tegur UPT
                                    </button>
                                    <button
                                        @click="activeTabLangkah = 'langkah2'"
                                        :class="[
                                            'py-2 px-3 text-xs font-bold rounded-md transition-all cursor-pointer text-center',
                                            activeTabLangkah === 'langkah2'
                                                ? 'bg-white text-slate-900 shadow-2xs border border-slate-200'
                                                : 'text-slate-600 hover:text-slate-900'
                                        ]"
                                    >
                                        2. Scorecard Kepatuhan UPT
                                    </button>
                                    <button
                                        @click="activeTabLangkah = 'langkah3'"
                                        :class="[
                                            'py-2 px-3 text-xs font-bold rounded-md transition-all cursor-pointer text-center',
                                            activeTabLangkah === 'langkah3'
                                                ? 'bg-white text-slate-900 shadow-2xs border border-slate-200'
                                                : 'text-slate-600 hover:text-slate-900'
                                        ]"
                                    >
                                        3. Monitoring Pembinaan
                                    </button>
                                </div>

                                <!-- Tab Content 1: Kendali SLA -->
                                <div v-if="activeTabLangkah === 'langkah1'" class="p-4 border border-slate-200 bg-slate-50/50 rounded-lg space-y-2 text-xs">
                                    <div class="flex items-center gap-2 font-bold text-slate-900">
                                        <ShieldAlert :size="16" class="text-red-600 shrink-0" />
                                        <span>Pusat Kendali SLA & Eskalasi Insiden</span>
                                    </div>
                                    <p class="text-slate-600 leading-relaxed">
                                        Memantau durasi penanganan tiket aduan secara real-time berdasarkan standar 24 jam. Pada tiket yang berstatus <strong>🔴 Terlambat (> 24 Jam)</strong>, pimpinan dapat menekan tombol <strong>Tegur UPT</strong> untuk meneruskan perintah tindak lanjut langsung ke Kepala Satker UPT Imigrasi.
                                    </p>
                                </div>

                                <!-- Tab Content 2: Scorecard UPT -->
                                <div v-if="activeTabLangkah === 'langkah2'" class="p-4 border border-slate-200 bg-slate-50/50 rounded-lg space-y-2 text-xs">
                                    <div class="flex items-center gap-2 font-bold text-slate-900">
                                        <Award :size="16" class="text-amber-600 shrink-0" />
                                        <span>Scorecard Kepatuhan Satker UPT Imigrasi</span>
                                    </div>
                                    <p class="text-slate-600 leading-relaxed">
                                        Menyajikan evaluasi komparatif kualitatif antar UPT Imigrasi di Sumatera Utara. Indikator menilai persentase penyelesaian (<em>Completion Rate</em>), rerata durasi SLA, dan mengelompokkan status UPT menjadi <em>SANGAT BAIK</em>, <em>CUKUP</em>, atau <em>PERLU EVALUASI</em>. Mengklik tombol <strong>Detail UPT</strong> akan menampilkan rincian desa binaan & petugas PIMPASA pengampunya.
                                    </p>
                                </div>

                                <!-- Tab Content 3: Monitoring Pembinaan -->
                                <div v-if="activeTabLangkah === 'langkah3'" class="p-4 border border-slate-200 bg-slate-50/50 rounded-lg space-y-2 text-xs">
                                    <div class="flex items-center gap-2 font-bold text-slate-900">
                                        <FolderKanban :size="16" class="text-blue-600 shrink-0" />
                                        <span>Monitoring Pembinaan Desa Lintas UPT</span>
                                    </div>
                                    <p class="text-slate-600 leading-relaxed">
                                        Memantau rekapitulasi pelaksanaan agenda pembinaan keimigrasian di desa-desa binaan (penyuluhan hukum, pencegahan TPPO, dsb) yang diunggah oleh petugas PIMPASA se-Sumatera Utara.
                                    </p>
                                </div>

                            </div>
                        </div>
                    </section>

                    <!-- SECTION 3: PETA SEBARAN DESA -->
                    <section id="peta-sebaran" class="scroll-mt-6">
                        <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-2xs">
                            <div class="p-4 sm:p-5 border-b border-slate-100">
                                <h2 class="text-base font-bold text-slate-900 tracking-tight">
                                    3. Peta Geospasial Sebaran Desa Binaan
                                </h2>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Pemantauan peta interaktif geospasial sebaran desa binaan di Sumatera Utara.
                                </p>
                            </div>

                            <div class="p-5 space-y-3 text-xs text-slate-600 leading-relaxed">
                                <div class="p-3.5 border border-slate-200 rounded-lg bg-slate-50/50 space-y-1">
                                    <h3 class="font-bold text-slate-900 flex items-center gap-2">
                                        <Map :size="15" class="text-slate-700 shrink-0" />
                                        <span>Visualisasi Marker Geospasial</span>
                                    </h3>
                                    <p class="text-[11px] text-slate-500 leading-relaxed">
                                        Menu <strong>Peta Sebaran Desa</strong> menampilkan peta interaktif lokasi desa binaan imigrasi di seluruh Kabupaten/Kota di Sumatera Utara. Marker warna menunjukkan tingkat kerawanan/kepatuhan wilayah desa (<em>Hijau = Aman/Aktif, Kuning = Peringatan, Merah = Rawan Breached SLA</em>).
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- SECTION 4: TATA KELOLA MASTER DATA -->
                    <section id="master-data" class="scroll-mt-6">
                        <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-2xs">
                            <div class="p-4 sm:p-5 border-b border-slate-100">
                                <h2 class="text-base font-bold text-slate-900 tracking-tight">
                                    4. Tata Kelola Master Data System
                                </h2>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Manajemen pengguna, Satker UPT, Kategori Aduan, dan Hak Akses Permission.
                                </p>
                            </div>

                            <div class="p-5 space-y-3 text-xs text-slate-600 leading-relaxed">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div class="p-3.5 border border-slate-200 rounded-lg bg-slate-50/50 space-y-1">
                                        <h3 class="font-bold text-slate-900 flex items-center gap-2">
                                            <Users :size="15" class="text-slate-700 shrink-0" />
                                            <span>Manajemen Akun User & UPT</span>
                                        </h3>
                                        <p class="text-[11px] text-slate-500 leading-relaxed">
                                            Kanwil memiliki wewenang penuh untuk menambah, memperbarui, atau menonaktifkan akun Admin Kanwil, Petugas PIMPASA UPT, serta Perangkat Desa.
                                        </p>
                                    </div>

                                    <div class="p-3.5 border border-slate-200 rounded-lg bg-slate-50/50 space-y-1">
                                        <h3 class="font-bold text-slate-900 flex items-center gap-2">
                                            <Settings :size="15" class="text-slate-700 shrink-0" />
                                            <span>Matriks Hak Akses & Kategori</span>
                                        </h3>
                                        <p class="text-[11px] text-slate-500 leading-relaxed">
                                            Mengatur permission fitural masing-masing role dan mengelola master acuan kategori aduan keimigrasian.
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
                                    5. Cara Ekspor & Cetak Dokumen Laporan
                                </h2>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Panduan pengunduhan rekapitulasi eksekutif format Excel dan PDF.
                                </p>
                            </div>

                            <div class="p-5 space-y-3 text-xs text-slate-600 leading-relaxed">
                                <p>
                                    Seluruh menu Executive Monitoring Kanwil (Kendali SLA, Scorecard UPT, Monitoring Pembinaan) mendukung fitur pengunduhan dokumen resmi:
                                </p>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                                    <div class="p-3.5 border border-emerald-200 bg-emerald-50/30 rounded-lg space-y-1">
                                        <div class="flex items-center gap-2 font-bold text-emerald-900">
                                            <FileSpreadsheet :size="16" class="text-emerald-700 shrink-0" />
                                            <span>Ekspor Excel (.xlsx)</span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 leading-normal">
                                            Mengunduh berkas tabel terformat dengan header Corporate Navy `#033566`, perataan angka presisi, dan garis sel rapi.
                                        </p>
                                    </div>

                                    <div class="p-3.5 border border-rose-200 bg-rose-50/30 rounded-lg space-y-1">
                                        <div class="flex items-center gap-2 font-bold text-rose-900">
                                            <FileText :size="16" class="text-rose-700 shrink-0" />
                                            <span>Ekspor PDF (.pdf)</span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 leading-normal">
                                            Mencetak laporan PDF A4 Landscape dengan Kop Resmi <strong>KANTOR WILAYAH DIREKTORAT JENDERAL IMIGRASI SUMATERA UTARA</strong>, metadata tanggal cetak, dan blok tanda tangan Struktural Kanwil.
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
                                    6. Pertanyaan Umum (FAQ) Administrator Kanwil
                                </h2>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Jawaban atas kendala operasional dan pengelolaan sistem executive monitoring.
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
