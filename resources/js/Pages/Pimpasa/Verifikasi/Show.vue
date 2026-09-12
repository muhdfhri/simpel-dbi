<script setup lang="ts">
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/components/layout/AppLayout.vue';
import {
    ArrowLeft,
    CheckCircle2,
    Clock,
    AlertTriangle,
    FileText,
    Download,
    UserCheck,
    Building2,
    History,
    XCircle,
    Send,
    Tag,
    MapPin,
    AlignLeft,
    Calendar,
    Users,
    Paperclip,
    ShieldAlert,
    CheckSquare2,
    FileCheck2,
    ChevronRight,
    ArrowRight
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { notify } from '@/lib/toast';

interface LampiranFile {
    id: number;
    nama_file_asli: string;
    path: string;
    tipe_file: string;
    ukuran_bytes: number;
}

interface LaporanProps {
    id: number;
    kode_tiket: string;
    judul: string;
    kategori: string;
    status: string;
    tanggal_kejadian: string;
    lokasi_detail: string;
    kronologi: string;
    estimasi_jumlah_orang: number | null;
    submitted_at: string;
    desa?: { nama: string };
    kategoriRef?: { nama_kategori: string };
    status_histories?: Array<{ actor?: { name: string } }>;
    verifikasi?: {
        keputusan: string;
        catatan: string | null;
        checklist_validitas?: Record<string, boolean>;
        pimpasa?: { name: string };
    } | null;
    tindak_lanjut?: {
        nomor_registrasi: string;
        seksi_penanggung_jawab: string;
        bentuk_intervensi: string;
        ringkasan_hasil: string;
        status_akhir: string;
    } | null;
    lampiran_list: LampiranFile[];
}

const props = defineProps<{
    laporan: LaporanProps;
}>();

const form = useForm({
    keputusan: props.laporan.verifikasi?.keputusan || 'diverifikasi',
    checklist_validitas: {
        kelengkapan_identitas: props.laporan.verifikasi?.checklist_validitas?.kelengkapan_identitas ?? true,
        kesesuaian_lokasi: props.laporan.verifikasi?.checklist_validitas?.kesesuaian_lokasi ?? true,
        indikasi_awal_valid: props.laporan.verifikasi?.checklist_validitas?.indikasi_awal_valid ?? true,
    },
    catatan: props.laporan.verifikasi?.catatan || '',
});

const getStatusBadge = (statusStr: string) => {
    switch (statusStr) {
        case 'diajukan': return 'bg-amber-50 text-amber-800 border-amber-200/80';
        case 'minta_perbaikan': return 'bg-orange-50 text-orange-900 border-orange-300 font-bold animate-pulse';
        case 'diverifikasi': return 'bg-blue-50 text-blue-800 border-blue-200/80';
        case 'ditindaklanjuti': return 'bg-purple-50 text-purple-800 border-purple-200/80';
        case 'selesai': return 'bg-emerald-50 text-emerald-800 border-emerald-200/80';
        case 'ditolak': return 'bg-red-50 text-red-800 border-red-200/80';
        default: return 'bg-slate-50 text-slate-700 border-slate-200/80';
    }
};

const getStatusLabel = (statusStr: string) => {
    switch (statusStr) {
        case 'diajukan': return 'Perlu Verifikasi';
        case 'minta_perbaikan': return 'Minta Perbaikan';
        case 'diverifikasi': return 'Diverifikasi PIMPASA';
        case 'ditindaklanjuti': return 'Ditindaklanjuti PIMPASA';
        case 'selesai': return 'Selesai';
        case 'ditolak': return 'Ditolak';
        default: return statusStr;
    }
};

const submitDecision = (dec: 'diverifikasi' | 'minta_perbaikan' | 'ditolak') => {
    if ((dec === 'minta_perbaikan' || dec === 'ditolak') && (!form.catatan || !form.catatan.trim())) {
        notify.error('Catatan Wajib Diisi', {
            description: `Silakan isi Catatan / Instruksi PIMPASA terlebih dahulu sebelum memilih ${dec === 'minta_perbaikan' ? 'Minta Perbaikan' : 'Tolak Laporan'}.`
        });
        return;
    }

    form.keputusan = dec;
    const prevStatus = props.laporan.status;

    // 1. Optimistic UI Update: Langsung ubah status badge lokal pada milidetik ke-0
    props.laporan.status = dec;

    // 2. Background HTTP Request
    form.post(`/pimpasa/verifikasi/${props.laporan.id}`, {
        onSuccess: () => {
            const msg = dec === 'diverifikasi'
                ? 'Laporan berhasil diverifikasi!'
                : (dec === 'minta_perbaikan' ? 'Permintaan perbaikan telah dikirim ke Perangkat Desa.' : 'Laporan telah ditolak.');

            if (dec === 'diverifikasi') notify.success('Verifikasi Berhasil', { description: msg });
            else if (dec === 'minta_perbaikan') notify.warning('Minta Perbaikan Terkirim', { description: msg });
            else notify.error('Laporan Ditolak', { description: msg });
        },
        onError: () => {
            // Rollback jika terjadi kesalahan server
            props.laporan.status = prevStatus;
            notify.error('Gagal Memproses Verifikasi', { description: 'Mohon periksa kembali kelengkapan catatan / form.' });
        }
    });
};
</script>

<template>
    <AppLayout :title="`Verifikasi Tiket ${laporan.kode_tiket}`">
        <div class="space-y-6 font-sans">
            
            <!-- TOP NAVIGATION & ACTION BAR -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                
                <!-- Breadcrumb & Back Link -->
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <Link href="/pimpasa/verifikasi">
                        <Button variant="outline" class="h-9 px-3 text-xs font-semibold rounded-xl border-slate-200 hover:bg-slate-100 text-slate-700 flex items-center gap-1.5 shadow-2xs transition-all cursor-pointer shrink-0">
                            <ArrowLeft :size="15" />
                            <span>Kembali</span>
                        </Button>
                    </Link>
                    <div class="flex items-center gap-1.5 text-xs text-slate-400 min-w-0">
                        <ChevronRight :size="14" class="shrink-0" />
                        <span class="text-slate-700 font-bold truncate">
                            Detail Tiket #{{ laporan.kode_tiket }}
                        </span>
                    </div>
                </div>

                <!-- Action Status & Navigation -->
                <div class="flex items-center gap-2.5 sm:gap-3 flex-wrap">
                    <span :class="['px-3 py-1 rounded-full text-xs font-semibold border shrink-0', getStatusBadge(laporan.status)]">
                        {{ getStatusLabel(laporan.status) }}
                    </span>
                    
                    <Link v-if="['diverifikasi', 'ditindaklanjuti'].includes(laporan.status)" :href="`/pimpasa/tindak-lanjut/${laporan.id}`" class="flex-1 sm:flex-none">
                        <Button class="w-full sm:w-auto h-9 bg-primary hover:bg-[#04407D] text-white font-bold rounded-xl text-xs px-3.5 flex items-center justify-center gap-2 shadow-xs cursor-pointer">
                            <span class="truncate">Input Tindak Lanjut UPT</span>
                            <ArrowRight :size="15" class="text-white shrink-0 stroke-[2.5]" />
                        </Button>
                    </Link>
                </div>

            </div>

            <!-- Main Content Grid (8 Cols Left, 4 Cols Right) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                
                <!-- LEFT MAIN PANEL (8 Cols) -->
                <div class="lg:col-span-8 space-y-6">
                    
                    <!-- SECTION 1: Informasi Pelaporan -->
                    <Card class="border-slate-200/80 shadow-2xs rounded-2xl bg-white overflow-hidden">
                        <CardHeader class="pb-3.5 border-b border-slate-100 px-5 sm:px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="space-y-0.5">
                                <CardTitle class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                    <Tag :size="16" class="text-blue-600 shrink-0" />
                                    <span>Informasi Utama Pelaporan</span>
                                </CardTitle>
                                <CardDescription class="text-xs text-slate-500">
                                    Data laporan resmi yang diajukan oleh Perangkat Desa Binaan.
                                </CardDescription>
                            </div>
                            <div class="self-start sm:self-auto">
                                <span class="inline-block px-3 py-1 rounded-lg bg-blue-50 text-blue-900 text-xs font-bold border border-blue-200/80">
                                    {{ laporan.kategoriRef?.nama_kategori || laporan.kategori || 'Kegiatan Desa Binaan Imigrasi' }}
                                </span>
                            </div>
                        </CardHeader>

                        <CardContent class="p-6 space-y-5">
                            
                            <!-- Judul Laporan & Nama Pelapor -->
                            <div class="space-y-2">
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Judul Kejadian</span>
                                <h2 class="text-lg font-bold text-slate-900 leading-snug">
                                    {{ laporan.judul }}
                                </h2>
                                <div class="flex items-center gap-2 pt-1 text-xs text-slate-600 font-medium">
                                    <UserCheck :size="14" class="text-slate-400 shrink-0" />
                                    <span>
                                        Pelapor: <strong class="text-slate-900 font-bold">{{ (laporan as any).status_histories?.[0]?.actor?.name || 'Perangkat Desa' }}</strong> 
                                        (<span class="text-blue-700 font-semibold">{{ laporan.desa?.nama || 'Desa Binaan' }}</span>)
                                    </span>
                                </div>
                            </div>

                            <!-- Grid Meta Specs -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-xl bg-slate-50/80 border border-slate-100">
                                <div class="space-y-1">
                                    <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                                        <Calendar :size="13" class="text-slate-400" />
                                        <span>Waktu Kejadian</span>
                                    </span>
                                    <p class="text-xs font-bold text-slate-900">
                                        {{ new Date(laporan.tanggal_kejadian).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                                    </p>
                                </div>

                                <div class="space-y-1">
                                    <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                                        <Users :size="13" class="text-slate-400" />
                                        <span>Estimasi Terlibat</span>
                                    </span>
                                    <p class="text-xs font-bold text-slate-900">
                                        {{ laporan.estimasi_jumlah_orang ? `${laporan.estimasi_jumlah_orang} Orang` : '-' }}
                                    </p>
                                </div>
                            </div>

                        </CardContent>
                    </Card>

                    <!-- SECTION 2: Detail Lokasi Kejadian -->
                    <Card class="border-slate-200/80 shadow-2xs rounded-2xl bg-white overflow-hidden">
                        <CardHeader class="pb-3.5 border-b border-slate-100 px-6 py-4">
                            <CardTitle class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <MapPin :size="16" class="text-red-500" />
                                Detail Lokasi Kejadian / Alamat Patokan
                            </CardTitle>
                            <CardDescription class="text-xs text-slate-500">
                                Patokan alamat detail lokasi kejadian di lapangan.
                            </CardDescription>
                        </CardHeader>

                        <CardContent class="p-6">
                            <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-200/80 text-xs text-slate-900 font-medium leading-relaxed">
                                {{ laporan.lokasi_detail }}
                            </div>
                        </CardContent>
                    </Card>

                    <!-- SECTION 3: Rincian Kronologi Kejadian -->
                    <Card class="border-slate-200/80 shadow-2xs rounded-2xl bg-white overflow-hidden">
                        <CardHeader class="pb-3.5 border-b border-slate-100 px-6 py-4">
                            <CardTitle class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <AlignLeft :size="16" class="text-emerald-600" />
                                Rincian Kronologi Kejadian
                            </CardTitle>
                            <CardDescription class="text-xs text-slate-500">
                                Urutan peristiwa dan penjelasan fakta dari Perangkat Desa.
                            </CardDescription>
                        </CardHeader>

                        <CardContent class="p-6">
                            <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-200/80 text-xs text-slate-800 leading-relaxed font-normal whitespace-pre-line">
                                {{ laporan.kronologi }}
                            </div>
                        </CardContent>
                    </Card>

                    <!-- SECTION 4: Lampiran Berkas Bukti -->
                    <Card class="border-slate-200/80 shadow-2xs rounded-2xl bg-white overflow-hidden">
                        <CardHeader class="pb-3.5 border-b border-slate-100 px-5 sm:px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="space-y-0.5">
                                <CardTitle class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                    <Paperclip :size="16" class="text-indigo-600 shrink-0" />
                                    <span>Lampiran Dokumen Bukti</span>
                                </CardTitle>
                                <CardDescription class="text-xs text-slate-500">
                                    Berkas bukti pendukung foto/dokumen dari lapangan.
                                </CardDescription>
                            </div>
                            <div class="self-start sm:self-auto">
                                <span class="inline-block text-xs font-bold text-slate-700 bg-slate-100 px-3 py-1 rounded-full border border-slate-200/80">
                                    {{ laporan.lampiran_list.length }} Berkas
                                </span>
                            </div>
                        </CardHeader>

                        <CardContent class="p-6">
                            <div v-if="laporan.lampiran_list.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <a
                                    v-for="file in laporan.lampiran_list"
                                    :key="file.id"
                                    :href="`/storage/${file.path}`"
                                    target="_blank"
                                    class="flex items-center justify-between p-3.5 rounded-xl border border-slate-200 hover:border-slate-300 bg-white hover:bg-slate-50 transition-all group shadow-2xs"
                                >
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center shrink-0 group-hover:bg-primary group-hover:text-white transition-colors">
                                            <FileText :size="16" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold text-slate-900 truncate group-hover:text-primary transition-colors">
                                                {{ file.nama_file_asli }}
                                            </p>
                                            <p class="text-[10px] text-slate-400 uppercase font-mono">
                                                {{ file.tipe_file || 'DOKUMEN' }}
                                            </p>
                                        </div>
                                    </div>
                                    <Download :size="15" class="text-slate-400 group-hover:text-slate-700 shrink-0" />
                                </a>
                            </div>
                            <div v-else class="text-center py-6 text-slate-400 text-xs">
                                Tidak ada lampiran dokumen bukti.
                            </div>
                        </CardContent>
                    </Card>

                </div>

                <!-- RIGHT SIDEBAR PANEL (4 Cols - Verification Decision Box) -->
                <div class="lg:col-span-4 space-y-6">
                    
                    <Card class="border-slate-200/80 shadow-2xs rounded-2xl bg-white sticky top-20 overflow-hidden">
                        <CardHeader class="pb-3.5 border-b border-slate-100 px-5 py-4">
                            <CardTitle class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <UserCheck :size="16" class="text-primary" />
                                <span>Form Keputusan Verifikasi</span>
                            </CardTitle>
                            <CardDescription class="text-xs text-slate-500">
                                Periksa checklist validitas dan tentukan keputusan penanganan PIMPASA.
                            </CardDescription>
                        </CardHeader>

                        <CardContent class="p-5 space-y-5">
                            
                            <!-- Checklist Validitas Panel -->
                            <div class="space-y-3 p-4 rounded-xl bg-slate-50/80 border border-slate-200/80">
                                <Label class="text-xs font-bold text-slate-800 uppercase tracking-wider block">
                                    Checklist Validitas Data:
                                </Label>
                                
                                <label class="flex items-start gap-2.5 cursor-pointer text-xs font-medium text-slate-700 select-none">
                                    <input type="checkbox" v-model="form.checklist_validitas.kelengkapan_identitas" class="mt-0.5 rounded border-slate-300 text-primary focus:ring-primary" />
                                    <span>Kelengkapan Subjek & Identitas Laporan</span>
                                </label>

                                <label class="flex items-start gap-2.5 cursor-pointer text-xs font-medium text-slate-700 select-none">
                                    <input type="checkbox" v-model="form.checklist_validitas.kesesuaian_lokasi" class="mt-0.5 rounded border-slate-300 text-primary focus:ring-primary" />
                                    <span>Kesesuaian Lokasi di Desa Binaan</span>
                                </label>

                                <label class="flex items-start gap-2.5 cursor-pointer text-xs font-medium text-slate-700 select-none">
                                    <input type="checkbox" v-model="form.checklist_validitas.indikasi_awal_valid" class="mt-0.5 rounded border-slate-300 text-primary focus:ring-primary" />
                                    <span>Indikasi Awal Pelanggaran Valid</span>
                                </label>
                            </div>

                            <!-- Catatan / Instruksi PIMPASA -->
                            <div class="space-y-2">
                                <Label for="catatan" class="text-xs font-bold text-slate-700 uppercase tracking-wider block">
                                    Catatan / Instruksi PIMPASA
                                </Label>
                                <Textarea
                                    id="catatan"
                                    rows="4"
                                    v-model="form.catatan"
                                    placeholder="Tuliskan catatan perbaikan untuk Perangkat Desa, atau instruksi untuk Tim Tindak Lanjut UPT..."
                                    class="text-xs rounded-xl p-3.5 border-slate-300 shadow-2xs font-normal leading-relaxed focus:ring-2 focus:ring-primary"
                                />
                                <p v-if="form.errors.catatan" class="text-xs text-red-500 font-medium">
                                    {{ form.errors.catatan }}
                                </p>
                            </div>

                            <!-- Decision Action Buttons Box -->
                            <div class="pt-4 border-t border-slate-100 space-y-2.5">
                                
                                <!-- Setujui & Diverifikasi -->
                                <Button
                                    type="button"
                                    @click="submitDecision('diverifikasi')"
                                    :disabled="form.processing"
                                    class="w-full bg-primary hover:bg-[#04407D] text-primary-foreground font-bold py-3 rounded-xl text-xs shadow-xs transition-all flex items-center justify-center gap-2 cursor-pointer"
                                >
                                    <CheckCircle2 :size="15" />
                                    <span>Setujui & Diverifikasi UPT</span>
                                </Button>

                                <!-- Minta Perbaikan -->
                                <Button
                                    type="button"
                                    @click="submitDecision('minta_perbaikan')"
                                    :disabled="form.processing"
                                    class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-2.5 rounded-xl text-xs shadow-xs transition-all flex items-center justify-center gap-2 cursor-pointer"
                                >
                                    <AlertTriangle :size="15" />
                                    <span>Kembalikan: Minta Perbaikan</span>
                                </Button>

                                <!-- Tolak Laporan -->
                                <Button
                                    type="button"
                                    @click="submitDecision('ditolak')"
                                    :disabled="form.processing"
                                    class="w-full bg-white hover:bg-red-50 text-red-600 font-bold py-2 rounded-xl text-xs border border-red-200 flex items-center justify-center gap-2 transition-colors cursor-pointer"
                                >
                                    <XCircle :size="15" />
                                    <span>Tolak Laporan Ini</span>
                                </Button>

                            </div>

                        </CardContent>
                    </Card>

                </div>

            </div>

        </div>
    </AppLayout>
</template>

