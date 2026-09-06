<script setup lang="ts">
import { ref } from 'vue';
import AppLayout from '@/components/layout/AppLayout.vue';
import {
    ChevronRight,
    ChevronDown
} from 'lucide-vue-next';

const props = defineProps<{
    appName?: string;
}>();

// Navigation sections
const activeSection = ref('tentang');
const activeTabLangkah = ref('langkah1');

const topics = [
    { id: 'tentang', title: '1. Tentang Peran Desa' },
    { id: 'alur', title: '2. Alur Pelaporan Desa' },
    { id: 'cara-isi', title: '3. Cara Mengisi Laporan' },
    { id: 'ekspor', title: '4. Cara Cetak & Download' },
    { id: 'faq', title: '5. Pertanyaan Umum' },
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
        q: 'Siapa saja yang bisa mengirim laporan di desa?',
        a: 'Perangkat Desa yang memegang akun resmi desa ini dapat mengirimkan laporan jika ada temuan keberadaan WNA atau masalah keimigrasian di wilayah desanya.'
    },
    {
        q: 'Berapa lama laporan desa diproses oleh Petugas Imigrasi?',
        a: 'Petugas PIMPASA dan Kantor Imigrasi akan langsung memeriksa laporan begitu dikirim. Laporan biasanya diverifikasi dalam 1x24 jam dan ditindaklanjuti ke lapangan.'
    },
    {
        q: 'Di mana saya bisa mengunduh file rekapitulasi Excel atau PDF?',
        a: 'Buka menu <strong>Daftar Laporan Desa</strong>. Di sudut kanan atas tabel terdapat tombol <strong>Ekspor Excel</strong> dan <strong>Ekspor PDF</strong> yang bisa Anda klik kapan saja.'
    },
    {
        q: 'Apakah laporan yang sudah dikirim bisa diubah atau dihapus?',
        a: 'Jika laporan masih berstatus <em>Diajukan</em>, Anda masih bisa memperbarui isinya. Jika sudah diverifikasi petugas, laporan tidak dapat diubah agar data tetap jujur dan resmi.'
    }
];
</script>

<template>
    <AppLayout title="Panduan Penggunaan Perangkat Desa">
        <div class="space-y-6 font-sans text-slate-800">
            
            <!-- HEADER SIMPLE CLEAN (WITHOUT EXTRA BUTTONS) -->
            <div class="bg-white border border-slate-200 rounded-lg p-5 sm:p-6">
                <div class="space-y-1">
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">
                        Panduan Penggunaan - Perangkat Desa
                    </h1>
                    <p class="text-xs text-slate-500">
                        Petunjuk sederhana tentang cara melaporkan kejadian keimigrasian di desa dan memantau prosesnya hingga selesai.
                    </p>
                </div>
            </div>

            <!-- MAIN CONTAINER WITH STICKY SIDEBAR (2-COLUMN LAYOUT) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <!-- LEFT SIDEBAR STICKY NAVIGATION -->
                <div class="lg:col-span-3 hidden lg:block">
                    <div class="sticky top-0 space-y-4 z-10 pt-0.5">
                        
                        <!-- Section 1: Menu Panduan Navigation -->
                    <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-2xs">
                        <div class="p-3.5 border-b border-slate-100 bg-slate-50">
                            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Menu Panduan
                            </h3>
                        </div>
                        <div class="p-1.5 space-y-0.5">
                            <button
                                v-for="topic in topics"
                                :key="topic.id"
                                @click="scrollToSection(topic.id)"
                                :class="[
                                    'w-full text-left px-3 py-2 rounded-md text-xs font-medium flex items-center justify-between transition-colors',
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

                    <!-- Section 2: Butuh Bantuan Box -->
                    <div class="bg-white border border-slate-200 rounded-lg p-4 space-y-1 text-xs shadow-2xs">
                        <span class="font-bold text-slate-900 block">Butuh Bantuan?</span>
                        <p class="text-slate-500 leading-relaxed text-[11px]">
                            Jika ada kendala saat melapor, Anda dapat menghubungi Petugas PIMPASA yang membina desa Anda.
                        </p>
                    </div>
                </div>
            </div>

                <!-- RIGHT MAIN CONTENT (CLEAN MINIMAL DOCUMENTATION CARDS) -->
                <div class="lg:col-span-9 space-y-6">

                    <!-- SECTION 1: TENTANG PERAN DESA -->
                    <section id="tentang" class="scroll-mt-6">
                        <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-2xs">
                            <div class="p-4 sm:p-5 border-b border-slate-100">
                                <h2 class="text-base font-bold text-slate-900 tracking-tight">
                                    1. Tentang Peran Perangkat Desa
                                </h2>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Tugas dan wewenang Anda di aplikasi SIMPEL DBI.
                                </p>
                            </div>

                            <div class="p-5 space-y-3 text-xs text-slate-600 leading-relaxed">
                                <p>
                                    Sebagai <strong>Perangkat Desa</strong>, Anda bertugas sebagai mata dan telinga awal di wilayah desa. Aplikasi ini dibuat agar Anda bisa dengan mudah menyampaikan laporan jika menemukan hal-hal berikut:
                                </p>

                                <ul class="list-disc pl-5 space-y-1.5 text-slate-700">
                                    <li>Adanya Warga Negara Asing (WNA) atau Tenaga Kerja Asing (TKA) yang tinggal di desa tanpa izin yang jelas.</li>
                                    <li>WNA yang paspor atau izin tinggalnya sudah habis (overstay).</li>
                                    <li>Kegiatan WNA atau perbatasan yang mencurigakan atau meresahkan warga desa.</li>
                                </ul>

                                <p class="pt-1">
                                    Setiap laporan yang Anda kirim akan langsung diterima oleh Petugas Imigrasi (PIMPASA) untuk diperiksa dan ditindaklanjuti.
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- SECTION 2: ALUR PELAPORAN DESA -->
                    <section id="alur" class="scroll-mt-6">
                        <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-2xs">
                            <div class="p-4 sm:p-5 border-b border-slate-100">
                                <h2 class="text-base font-bold text-slate-900 tracking-tight">
                                    2. Alur Pelaporan (Dari Melapor Sampai Selesai)
                                </h2>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Tahapan perjalanan laporan Anda di dalam sistem.
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
                                                <h3 class="font-bold text-slate-900">Langkah 1: Perangkat Desa Kirim Laporan</h3>
                                                <span class="px-2 py-0.5 rounded bg-slate-200 text-slate-700 text-[10px] font-medium">Status: Diajukan</span>
                                            </div>
                                            <p class="text-slate-600 leading-relaxed">
                                                Anda mengisi formulir laporan baru. Setelah dikirim, sistem memberikan nomor tiket laporan dan memberi tahu Petugas Imigrasi.
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
                                                <h3 class="font-bold text-slate-900">Langkah 2: Petugas Imigrasi Memeriksa Laporan</h3>
                                                <span class="px-2 py-0.5 rounded bg-slate-200 text-slate-700 text-[10px] font-medium">Status: Diverifikasi</span>
                                            </div>
                                            <p class="text-slate-600 leading-relaxed">
                                                Petugas PIMPASA mengecek kebenaran isi dan foto laporan. Jika sesuai, status berubah jadi <strong>Diverifikasi</strong>. Jika kurang jelas, petugas akan memberi catatan.
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
                                                <h3 class="font-bold text-slate-900">Langkah 3: Petugas Turun Penanganan Lapangan</h3>
                                                <span class="px-2 py-0.5 rounded bg-slate-200 text-slate-700 text-[10px] font-medium">Status: Ditindaklanjuti</span>
                                            </div>
                                            <p class="text-slate-600 leading-relaxed">
                                                Tim Kantor Imigrasi (UPT) melakukan penanganan atau pemeriksaan langsung ke tempat lokasi laporan di desa Anda.
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
                                                <h3 class="font-bold text-slate-900">Langkah 4: Penanganan Laporan Selesai</h3>
                                                <span class="px-2 py-0.5 rounded bg-slate-200 text-slate-700 text-[10px] font-medium">Status: Selesai</span>
                                            </div>
                                            <p class="text-slate-600 leading-relaxed">
                                                Petugas mengunggah hasil laporan akhir dan menutup tiket. Anda bisa melihat catatan hasil akhir dan mencetak surat/bukti PDF.
                                            </p>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- SECTION 3: CARA MENGISI LAPORAN -->
                    <section id="cara-isi" class="scroll-mt-6">
                        <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-2xs">
                            <div class="p-4 sm:p-5 border-b border-slate-100">
                                <h2 class="text-base font-bold text-slate-900 tracking-tight">
                                    3. Cara Mengisi Form Laporan Baru
                                </h2>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Panduan singkat mengisi formulir laporan.
                                </p>
                            </div>

                            <div class="p-5 space-y-4">
                                <!-- Tabs Step -->
                                <div class="space-y-3">
                                    <div class="grid grid-cols-3 bg-slate-100 p-1 rounded-lg gap-1">
                                        <button
                                            type="button"
                                            @click="activeTabLangkah = 'langkah1'"
                                            :class="[
                                                'py-1.5 px-3 text-xs font-semibold rounded-md transition-colors',
                                                activeTabLangkah === 'langkah1'
                                                    ? 'bg-white text-slate-900 shadow-2xs font-bold'
                                                    : 'text-slate-600 hover:text-slate-900'
                                            ]"
                                        >
                                            1. Buka Form
                                        </button>
                                        <button
                                            type="button"
                                            @click="activeTabLangkah = 'langkah2'"
                                            :class="[
                                                'py-1.5 px-3 text-xs font-semibold rounded-md transition-colors',
                                                activeTabLangkah === 'langkah2'
                                                    ? 'bg-white text-slate-900 shadow-2xs font-bold'
                                                    : 'text-slate-600 hover:text-slate-900'
                                            ]"
                                        >
                                            2. Isi Data Kejadian
                                        </button>
                                        <button
                                            type="button"
                                            @click="activeTabLangkah = 'langkah3'"
                                            :class="[
                                                'py-1.5 px-3 text-xs font-semibold rounded-md transition-colors',
                                                activeTabLangkah === 'langkah3'
                                                    ? 'bg-white text-slate-900 shadow-2xs font-bold'
                                                    : 'text-slate-600 hover:text-slate-900'
                                            ]"
                                        >
                                            3. Unggah Foto & Kirim
                                        </button>
                                    </div>

                                    <div v-show="activeTabLangkah === 'langkah1'" class="p-4 border border-slate-200 rounded-lg text-xs space-y-1.5 text-slate-600">
                                        <p class="font-bold text-slate-900">Langkah 1: Masuk ke Form Laporan</p>
                                        <p>Klik menu <strong>Form Laporan Baru</strong> di sebelah kiri layar.</p>
                                    </div>

                                    <div v-show="activeTabLangkah === 'langkah2'" class="p-4 border border-slate-200 rounded-lg text-xs space-y-1.5 text-slate-600">
                                        <p class="font-bold text-slate-900">Langkah 2: Lengkapi Rincian Kejadian</p>
                                        <ul class="list-disc pl-5 space-y-1">
                                            <li><strong>Judul Laporan</strong>: Tulis ringkasan singkat kejadian.</li>
                                            <li><strong>Kategori</strong>: Pilih jenis masalah (misal: keberadaan TKA / Paspor).</li>
                                            <li><strong>Kronologi / Cerita Kejadian</strong>: Jelaskan kronologi secara singkat dan jelas.</li>
                                            <li><strong>Lokasi Kejadian</strong>: Tentukan titik lokasi atau nama dusun tempat kejadian.</li>
                                        </ul>
                                    </div>

                                    <div v-show="activeTabLangkah === 'langkah3'" class="p-4 border border-slate-200 rounded-lg text-xs space-y-1.5 text-slate-600">
                                        <p class="font-bold text-slate-900">Langkah 3: Lampirkan Foto & Klik Kirim</p>
                                        <p>Unggah foto lokasi atau dokumen pendukung (jika ada), lalu klik tombol <strong>Kirim Laporan Resmi</strong>. Laporan akan otomatis terdaftar.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- SECTION 4: CARA CETAK & DOWNLOAD -->
                    <section id="ekspor" class="scroll-mt-6">
                        <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-2xs">
                            <div class="p-4 sm:p-5 border-b border-slate-100">
                                <h2 class="text-base font-bold text-slate-900 tracking-tight">
                                    4. Cara Cetak PDF & Download Excel
                                </h2>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Cara mengunduh rekapitulasi data laporan desa.
                                </p>
                            </div>

                            <div class="p-5 text-xs text-slate-600 leading-relaxed space-y-3">
                                <p>
                                    Untuk keperluan arsip kantor desa atau laporan berkala, Anda dapat mengunduh dokumen laporan melalui menu <strong>Daftar Laporan Desa</strong>:
                                </p>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                                    <div class="p-3.5 border border-slate-200 rounded-lg space-y-1">
                                        <span class="font-bold text-slate-900 block">1. Ekspor Excel</span>
                                        <p class="text-slate-500">Klik tombol <strong>Ekspor Excel</strong> di kanan atas tabel untuk menyimpan data laporan ke bentuk tabel Microsoft Excel.</p>
                                    </div>

                                    <div class="p-3.5 border border-slate-200 rounded-lg space-y-1">
                                        <span class="font-bold text-slate-900 block">2. Ekspor PDF & Cetak</span>
                                        <p class="text-slate-500">Klik tombol <strong>Ekspor PDF</strong> untuk membuat dokumen cetak PDF resmi ber-kops instansi yang siap dicetak.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- SECTION 5: PERTANYAAN UMUM (FAQ) -->
                    <section id="faq" class="scroll-mt-6">
                        <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-2xs">
                            <div class="p-4 sm:p-5 border-b border-slate-100">
                                <h2 class="text-base font-bold text-slate-900 tracking-tight">
                                    5. Pertanyaan Umum (FAQ)
                                </h2>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Jawaban singkat untuk pertanyaan yang sering ditanyakan.
                                </p>
                            </div>

                            <div class="p-5">
                                <div class="space-y-2">
                                    <div
                                        v-for="(faq, idx) in faqs"
                                        :key="idx"
                                        class="border border-slate-200 rounded-lg bg-white overflow-hidden"
                                    >
                                        <button
                                            type="button"
                                            @click="toggleFaq(idx)"
                                            class="w-full px-4 py-3 text-xs font-bold text-slate-900 flex items-center justify-between text-left hover:bg-slate-50 transition-colors"
                                        >
                                            <span>{{ faq.q }}</span>
                                            <ChevronDown
                                                :class="[
                                                    'w-4 h-4 text-slate-400 shrink-0 transition-transform duration-200',
                                                    openFaqIndex === idx ? 'rotate-180 text-slate-900' : ''
                                                ]"
                                            />
                                        </button>
                                        <div
                                            v-show="openFaqIndex === idx"
                                            class="px-4 pb-3 pt-1 text-xs text-slate-600 border-t border-slate-100 bg-slate-50 leading-relaxed"
                                            v-html="faq.a"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                </div>

            </div>

        </div>
    </AppLayout>
</template>
