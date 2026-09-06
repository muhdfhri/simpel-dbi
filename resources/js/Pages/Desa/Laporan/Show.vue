<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/components/layout/AppLayout.vue';
import {
    ArrowLeft,
    CheckCircle2,
    Clock,
    AlertTriangle,
    FileText,
    Download,
    Calendar,
    MapPin,
    UserCheck,
    Building2,
    History,
    Check,
    Circle,
    Printer,
    FileEdit,
    Tag,
    Users,
    ArrowRight,
    ShieldCheck,
    ChevronRight,
    User,
    ArrowUpRight
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';

interface StatusHistory {
    id: number;
    status_dari: string | null;
    status_ke: string;
    catatan: string | null;
    created_at: string;
    actor: {
        name: string;
        role: string;
    };
}

interface LampiranFile {
    id: number;
    nama_file_asli: string;
    path: string;
    tipe_file: string;
    ukuran_bytes: number;
}

const props = defineProps<{
    laporan: {
        id: number;
        kode_tiket: string;
        judul: string;
        kategori: string;
        tanggal_kejadian: string;
        lokasi_detail: string;
        kronologi: string;
        estimasi_jumlah_orang: number | null;
        status: string;
        submitted_at: string;
        verified_at: string | null;
        followed_up_at: string | null;
        resolved_at: string | null;
        desa: { nama: string };
        verifikasi?: {
            keputusan: string;
            catatan: string | null;
            checklist_validitas: Record<string, boolean>;
            pimpasa: { name: string };
        } | null;
        tindak_lanjut?: {
            nomor_registrasi: string;
            seksi_penanggung_jawab: string;
            bentuk_intervensi: string;
            ringkasan_hasil: string;
            status_akhir: string;
            staf_upt: { name: string };
        } | null;
        status_histories: StatusHistory[];
        lampiran_list: LampiranFile[];
    };
}>();

// Map status badge (Semantic OKLCH / Tailwind Color Palette)
const getStatusBadge = (status: string) => {
    switch (status) {
        case 'diajukan': return 'bg-amber-50 text-amber-800 border-amber-200/80';
        case 'minta_perbaikan': return 'bg-orange-100 text-orange-900 border-orange-300 font-bold';
        case 'diverifikasi': return 'bg-blue-50 text-blue-800 border-blue-200/80';
        case 'ditindaklanjuti': return 'bg-purple-50 text-purple-800 border-purple-200/80';
        case 'selesai': return 'bg-emerald-50 text-emerald-800 border-emerald-200/80';
        case 'ditolak': return 'bg-red-50 text-red-800 border-red-200/80';
        default: return 'bg-slate-50 text-slate-700 border-slate-200/80';
    }
};

const getStatusLabel = (status: string) => {
    switch (status) {
        case 'diajukan': return 'Diajukan';
        case 'minta_perbaikan': return 'Minta Perbaikan';
        case 'diverifikasi': return 'Diverifikasi PIMPASA';
        case 'ditindaklanjuti': return 'Ditindaklanjuti PIMPASA';
        case 'selesai': return 'Selesai';
        case 'ditolak': return 'Ditolak';
        default: return status;
    }
};

const getRoleBadge = (role: string) => {
    switch (role?.toLowerCase()) {
        case 'desa':
        case 'perangkat_desa':
            return { label: 'Perangkat Desa', class: 'bg-emerald-50 text-emerald-700 border-emerald-200' };
        case 'pimpasa':
            return { label: 'PIMPASA', class: 'bg-blue-50 text-blue-700 border-blue-200' };
        case 'upt':
        case 'staf_upt':
            return { label: 'Staf UPT', class: 'bg-purple-50 text-purple-700 border-purple-200' };
        case 'kanwil':
        case 'admin':
            return { label: 'Kanwil / Admin', class: 'bg-slate-100 text-slate-700 border-slate-200' };
        default:
            return { label: role || 'Pengguna', class: 'bg-slate-100 text-slate-600 border-slate-200' };
    }
};

// Helper Format Tanggal & Jam Indonesia Standar
const formatDateTime = (dateStr: string | null) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    const dateFormatted = d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    const timeFormatted = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace('.', ':');
    return `${dateFormatted}, ${timeFormatted} WIB`;
};

// Helper Format Ukuran File Bytes
const formatBytes = (bytes: number) => {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
};

// 4 Tahap Siklus Status dengan Logic Presisi untuk Stepper UI
const isVerifiedOrAbove = ['diverifikasi', 'ditindaklanjuti', 'selesai'].includes(props.laporan.status);
const isFollowedUpOrAbove = ['ditindaklanjuti', 'selesai'].includes(props.laporan.status);
const isResolved = props.laporan.status === 'selesai';

const timelineSteps = [
    {
        step: 1,
        title: 'Diajukan Perangkat Desa',
        desc: 'Laporan berhasil dibuat & masuk antrean verifikasi.',
        status: 'done',
        date: props.laporan.submitted_at,
    },
    {
        step: 2,
        title: 'Verifikasi PIMPASA',
        desc: props.laporan.status === 'minta_perbaikan' ? 'Membutuhkan perbaikan data oleh Perangkat Desa.' : 'Pemeriksaan validitas identitas & lokasi.',
        status: isVerifiedOrAbove ? 'done' : (props.laporan.status === 'minta_perbaikan' ? 'warning' : (props.laporan.status === 'diajukan' ? 'active' : 'pending')),
        date: props.laporan.verified_at,
    },
    {
        step: 3,
        title: 'Tindak Lanjut UPT Imigrasi',
        desc: 'Intervensi & penanganan lapangan oleh staf UPT.',
        status: isFollowedUpOrAbove ? 'done' : (isVerifiedOrAbove ? 'active' : 'pending'),
        date: props.laporan.followed_up_at,
    },
    {
        step: 4,
        title: 'Selesai / Diarsip',
        desc: 'Laporan selesai ditindaklanjuti 100%.',
        status: isResolved ? 'done' : 'pending',
        date: props.laporan.resolved_at,
    },
];
</script>

<template>
    <AppLayout :title="`Tiket ${laporan.kode_tiket}`">
        
        <div class="space-y-6">
            
            <!-- TOP NAVIGATION & ACTION BAR -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                
                <!-- Breadcrumb & Back Link -->
                <div class="flex items-center gap-3">
                    <Link href="/desa/laporan">
                        <Button variant="outline" class="h-9 px-3 text-xs font-semibold rounded-xl border-slate-200 hover:bg-slate-100 text-slate-700 flex items-center gap-1.5 shadow-2xs transition-all">
                            <ArrowLeft :size="15" />
                            <span>Kembali ke Daftar</span>
                        </Button>
                    </Link>
                    <div class="hidden sm:flex items-center gap-2 text-xs text-slate-400">
                        <ChevronRight :size="14" />
                        <span class="text-slate-600 font-medium truncate max-w-[200px] md:max-w-[320px]">
                            Detail Laporan #{{ laporan.kode_tiket }}
                        </span>
                    </div>
                </div>

                <!-- Action Button Group -->
                <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                    <Link v-if="laporan.status === 'diajukan'" :href="`/desa/laporan/${laporan.id}/edit`">
                        <Button variant="outline" class="h-9 px-3.5 text-xs font-semibold rounded-xl border-slate-300 bg-white hover:bg-slate-100 text-slate-800 flex items-center gap-1.5 shadow-2xs cursor-pointer transition-all">
                            <FileEdit :size="15" class="text-slate-600" />
                            <span>Edit Laporan</span>
                        </Button>
                    </Link>

                    <a :href="`/desa/laporan/${laporan.id}/export-pdf`" target="_blank">
                        <Button variant="outline" class="h-9 px-3.5 text-xs font-semibold rounded-xl border-rose-300 bg-rose-50/60 hover:bg-rose-100 text-rose-800 flex items-center gap-1.5 shadow-2xs cursor-pointer transition-all">
                            <Printer :size="15" class="text-rose-700" />
                            <span>Cetak PDF</span>
                        </Button>
                    </a>
                </div>
            </div>

            <!-- Banner Peringatan jika Minta Perbaikan -->
            <div v-if="laporan.status === 'minta_perbaikan'" class="p-5 rounded-2xl bg-gradient-to-r from-amber-500/10 via-amber-50 to-orange-50 border-2 border-amber-300 text-amber-950 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-2xs">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 font-bold text-sm text-orange-950">
                        <AlertTriangle :size="18" class="text-orange-600 shrink-0" />
                        <span>Laporan Membutuhkan Perbaikan Data oleh PIMPASA</span>
                    </div>
                    <p class="text-xs text-amber-900 leading-relaxed pl-6.5">
                        Catatan Petugas: "{{ laporan.verifikasi?.catatan || 'Mohon lengkapi data lokasi dan bukti dokumen.' }}"
                    </p>
                </div>

                <Link :href="`/desa/laporan/${laporan.id}/edit`" class="shrink-0">
                    <Button class="bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow-xs transition-all">
                        <span>Perbaiki Data Sekarang</span>
                    </Button>
                </Link>
            </div>

            <!-- HERO CARD HEADER DETAIL LAPORAN -->
            <Card class="border-slate-200/80 shadow-2xs rounded-2xl bg-white overflow-hidden">
                <div class="p-6 space-y-4">
                    
                    <!-- Top Badge Row: Kode Tiket & Status Badge -->
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Kode Tiket:</span>
                            <span class="font-mono text-sm font-bold text-slate-900 bg-slate-100 px-3 py-1 rounded-xl border border-slate-200/60 shadow-2xs">
                                {{ laporan.kode_tiket }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2">
                            <span :class="['px-3 py-1 rounded-full text-xs font-bold border shadow-2xs', getStatusBadge(laporan.status)]">
                                {{ getStatusLabel(laporan.status) }}
                            </span>
                        </div>
                    </div>

                    <!-- Title & Category -->
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2 flex-wrap text-xs text-slate-500 font-medium">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-slate-100 border border-slate-200/60 font-semibold text-slate-700">
                                <Tag :size="13" class="text-slate-500" />
                                {{ laporan.kategori }}
                            </span>
                            <span>•</span>
                            <span class="inline-flex items-center gap-1 text-slate-600">
                                <Building2 :size="13" class="text-slate-400" />
                                Desa {{ laporan.desa?.nama || 'Binaan' }}
                            </span>
                        </div>

                        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 leading-snug tracking-tight">
                            {{ laporan.judul }}
                        </h1>
                    </div>

                    <!-- Meta Specs Grid Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 pt-2">
                        <div class="p-3.5 rounded-xl bg-slate-50/80 border border-slate-200/60 flex items-start gap-3">
                            <div class="p-2 rounded-lg bg-blue-50 text-blue-600 shrink-0">
                                <Calendar :size="16" />
                            </div>
                            <div class="min-w-0">
                                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Waktu Kejadian</span>
                                <span class="text-xs font-bold text-slate-800 block truncate">
                                    {{ new Date(laporan.tanggal_kejadian).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }} WIB
                                </span>
                            </div>
                        </div>

                        <div class="p-3.5 rounded-xl bg-slate-50/80 border border-slate-200/60 flex items-start gap-3">
                            <div class="p-2 rounded-lg bg-purple-50 text-purple-600 shrink-0">
                                <MapPin :size="16" />
                            </div>
                            <div class="min-w-0">
                                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Lokasi Kejadian</span>
                                <span class="text-xs font-bold text-slate-800 block truncate" :title="laporan.lokasi_detail">
                                    {{ laporan.lokasi_detail }}
                                </span>
                            </div>
                        </div>

                        <div class="p-3.5 rounded-xl bg-slate-50/80 border border-slate-200/60 flex items-start gap-3">
                            <div class="p-2 rounded-lg bg-emerald-50 text-emerald-600 shrink-0">
                                <Users :size="16" />
                            </div>
                            <div class="min-w-0">
                                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Estimasi Terlibat</span>
                                <span class="text-xs font-bold text-slate-800 block">
                                    {{ laporan.estimasi_jumlah_orang ? `${laporan.estimasi_jumlah_orang} Orang` : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </Card>

            <!-- MAIN GRID (Order Mobile Friendly: Tahapan Siklus Laporan Pertama pada Mobile) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                
                <!-- RIGHT SIDEBAR (Order Mobile: 1, Order Desktop: 2 / Right Column) -->
                <div class="lg:col-span-1 lg:order-2 space-y-6">
                    
                    <!-- Stepper Tahapan Siklus Laporan -->
                    <Card class="border-slate-200/80 shadow-2xs rounded-2xl bg-white overflow-hidden">
                        <CardHeader class="pb-4 border-b border-slate-100">
                            <CardTitle class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                <Clock :size="15" class="text-blue-600" />
                                <span>Tahapan Siklus Laporan</span>
                            </CardTitle>
                            <CardDescription class="text-[11px] text-slate-500">
                                Progres verifikasi dan penanganan tiket.
                            </CardDescription>
                        </CardHeader>

                        <CardContent class="pt-6">
                            <div class="relative space-y-6">
                                
                                <div
                                    v-for="(item, idx) in timelineSteps"
                                    :key="item.step"
                                    class="relative flex items-start gap-3.5 group"
                                >
                                    <!-- Garis Vertikal Presisi -->
                                    <div
                                        v-if="idx < timelineSteps.length - 1"
                                        :class="[
                                            'absolute left-4 top-8 -bottom-6 w-0.5 -translate-x-1/2 transition-colors',
                                            item.status === 'done' ? 'bg-emerald-500' : 'bg-slate-200'
                                        ]"
                                    />

                                    <!-- Icon Bulatan Stepper -->
                                    <div
                                        :class="[
                                            'w-8 h-8 rounded-full flex items-center justify-center shrink-0 z-10 transition-all font-semibold text-xs shadow-2xs',
                                            item.status === 'done'
                                                ? 'bg-emerald-600 text-white shadow-emerald-200'
                                                : item.status === 'warning'
                                                    ? 'bg-amber-500 text-white ring-4 ring-amber-100 shadow-amber-200 animate-pulse'
                                                    : item.status === 'active'
                                                        ? 'bg-blue-600 text-white ring-4 ring-blue-100 shadow-blue-200 animate-pulse'
                                                        : 'bg-slate-100 text-slate-400 border border-slate-200'
                                        ]"
                                    >
                                        <Check v-if="item.status === 'done'" :size="16" stroke-width="3" />
                                        <AlertTriangle v-else-if="item.status === 'warning'" :size="16" />
                                        <span v-else>{{ item.step }}</span>
                                    </div>

                                    <!-- Step Text Content -->
                                    <div class="space-y-1 pt-0.5 min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <h5 :class="['text-xs font-bold leading-tight', item.status !== 'pending' ? 'text-slate-900' : 'text-slate-400']">
                                                {{ item.title }}
                                            </h5>

                                            <!-- Pill Badge Waktu -->
                                            <div v-if="item.date" class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-slate-100/90 border border-slate-200/60 text-[10px] font-mono text-slate-600 shrink-0">
                                                <Clock :size="11" class="text-slate-400 shrink-0" />
                                                <span>{{ formatDateTime(item.date) }}</span>
                                            </div>
                                        </div>

                                        <p class="text-[11px] text-slate-500 leading-relaxed pt-0.5">
                                            {{ item.desc }}
                                        </p>
                                    </div>

                                </div>

                            </div>
                        </CardContent>
                    </Card>

                </div>

                <!-- LEFT MAIN PANEL (Order Mobile: 2, Order Desktop: 1 / Left Column 2 Cols) -->
                <div class="lg:col-span-2 lg:order-1 space-y-6">
                    
                    <!-- Kronologi Kejadian Lengkap -->
                    <Card class="border-slate-200/80 shadow-2xs rounded-2xl bg-white">
                        <CardHeader class="pb-3 border-b border-slate-100">
                            <CardTitle class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                <FileText :size="16" class="text-slate-600" />
                                Kronologi Kejadian Lengkap
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="pt-4">
                            <div class="p-4 rounded-xl bg-slate-50/60 border border-slate-200/60 text-xs text-slate-800 leading-relaxed whitespace-pre-line font-normal">
                                {{ laporan.kronologi }}
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Lampiran Berkas / Bukti Dokumen -->
                    <Card class="border-slate-200/80 shadow-2xs rounded-2xl bg-white">
                        <CardHeader class="pb-3 border-b border-slate-100">
                            <div class="flex items-center justify-between">
                                <CardTitle class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                    <Download :size="16" class="text-slate-600" />
                                    Lampiran Berkas & Bukti ({{ laporan.lampiran_list.length }})
                                </CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent class="pt-4">
                            <div v-if="laporan.lampiran_list.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <a
                                    v-for="file in laporan.lampiran_list"
                                    :key="file.id"
                                    :href="`/storage/${file.path}`"
                                    target="_blank"
                                    class="flex items-center justify-between p-3.5 rounded-xl border border-slate-200/80 hover:border-slate-300 bg-white text-xs hover:bg-slate-50 transition-all group shadow-2xs"
                                >
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="p-2 rounded-lg bg-slate-100 text-slate-600 group-hover:bg-blue-50 group-hover:text-blue-600 transition-colors shrink-0">
                                            <FileText :size="18" />
                                        </div>
                                        <div class="min-w-0">
                                            <span class="font-semibold text-slate-800 truncate block group-hover:text-blue-600 transition-colors">
                                                {{ file.nama_file_asli }}
                                            </span>
                                            <span class="text-[10px] text-slate-400 font-mono block">
                                                {{ formatBytes(file.ukuran_bytes) }}
                                            </span>
                                        </div>
                                    </div>
                                    <ArrowUpRight :size="15" class="text-slate-400 group-hover:text-blue-600 shrink-0 transition-colors" />
                                </a>
                            </div>
                            <div v-else class="text-center py-6 text-xs text-slate-400">
                                Tidak ada dokumen lampiran yang diunggah.
                            </div>
                        </CardContent>
                    </Card>

                    <!-- REDESIGNED HISTORI AUDIT TRAIL CARD -->
                    <Card class="border-slate-200/80 shadow-2xs rounded-2xl bg-white overflow-hidden">
                        <CardHeader class="pb-3 border-b border-slate-100">
                            <div class="flex items-center justify-between">
                                <CardTitle class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                    <History :size="15" class="text-blue-600" />
                                    Jejak Audit Trail
                                </CardTitle>
                                <span class="text-[11px] font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md font-mono">
                                    {{ laporan.status_histories.length }} Catatan
                                </span>
                            </div>
                        </CardHeader>

                        <CardContent class="pt-5">
                            <div v-if="laporan.status_histories.length > 0" class="relative pl-3 space-y-6">
                                
                                <!-- Vertical Audit Connector Line -->
                                <div class="absolute left-6 top-3 bottom-3 w-0.5 bg-slate-200 -translate-x-1/2" />

                                <div
                                    v-for="(hist, idx) in laporan.status_histories"
                                    :key="hist.id"
                                    class="relative flex items-start gap-3.5 group"
                                >
                                    <!-- Audit Node Bullet -->
                                    <div class="w-6 h-6 rounded-full bg-white border-2 border-blue-600 text-blue-600 flex items-center justify-center shrink-0 z-10 shadow-2xs mt-0.5">
                                        <div class="w-2 h-2 rounded-full bg-blue-600"></div>
                                    </div>

                                    <!-- Audit Item Box -->
                                    <div class="space-y-1.5 min-w-0 flex-1 bg-slate-50/70 p-3 rounded-xl border border-slate-200/60 hover:bg-slate-50 transition-all">
                                        
                                        <!-- Header: Actor Name + Role Badge + Time -->
                                        <div class="flex flex-wrap items-center justify-between gap-1.5">
                                            <div class="flex items-center gap-1.5 min-w-0 flex-wrap">
                                                <span class="text-xs font-bold text-slate-900 truncate">
                                                    {{ hist.actor?.name || 'Sistem' }}
                                                </span>
                                                <span :class="['px-1.5 py-0.5 rounded text-[10px] font-semibold border', getRoleBadge(hist.actor?.role).class]">
                                                    {{ getRoleBadge(hist.actor?.role).label }}
                                                </span>
                                            </div>
                                            <span class="text-[10px] text-slate-400 font-mono shrink-0">
                                                {{ formatDateTime(hist.created_at) }}
                                            </span>
                                        </div>

                                        <!-- Status Transition Badge -->
                                        <div class="flex items-center gap-1.5 text-[11px] font-medium pt-0.5">
                                            <span class="text-slate-500">Status:</span>
                                            <span v-if="hist.status_dari" class="px-2 py-0.5 rounded text-[10px] bg-slate-200/80 text-slate-700 font-semibold">
                                                {{ getStatusLabel(hist.status_dari) }}
                                            </span>
                                            <ArrowRight v-if="hist.status_dari" :size="12" class="text-slate-400 shrink-0" />
                                            <span :class="['px-2 py-0.5 rounded text-[10px] font-bold border', getStatusBadge(hist.status_ke)]">
                                                {{ getStatusLabel(hist.status_ke) }}
                                            </span>
                                        </div>

                                        <!-- Catatan / Log Detail -->
                                        <div v-if="hist.catatan" class="mt-1 p-2.5 rounded-lg bg-white border border-slate-200/80 text-[11px] text-slate-700 italic leading-relaxed">
                                            "{{ hist.catatan }}"
                                        </div>
                                    </div>

                                </div>

                            </div>
                            <div v-else class="text-center py-6 text-xs text-slate-400">
                                Belum ada rekam histori audit trail.
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Card Hasil Verifikasi PIMPASA (jika ada) -->
                    <Card v-if="laporan.verifikasi" class="border-slate-200/80 shadow-2xs rounded-2xl bg-white">
                        <CardHeader class="pb-3 border-b border-slate-100">
                            <div class="flex items-center justify-between">
                                <CardTitle class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                    <UserCheck :size="16" class="text-blue-600" />
                                    Hasil Verifikasi Petugas PIMPASA
                                </CardTitle>
                                <span class="text-xs font-semibold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-lg">
                                    {{ laporan.verifikasi.pimpasa?.name || 'Petugas PIMPASA' }}
                                </span>
                            </div>
                        </CardHeader>
                        <CardContent class="pt-4 text-xs text-slate-700 space-y-3">
                            <div class="flex items-center justify-between p-3 rounded-xl bg-blue-50/50 border border-blue-100">
                                <span class="font-semibold text-slate-700">Keputusan Verifikator:</span>
                                <span class="capitalize font-bold text-blue-700 bg-white px-2.5 py-0.5 rounded-md border border-blue-200">
                                    {{ getStatusLabel(laporan.verifikasi.keputusan) }}
                                </span>
                            </div>
                            <div v-if="laporan.verifikasi.catatan" class="space-y-1">
                                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Catatan / Instruksi:</span>
                                <p class="p-3 rounded-xl bg-slate-50 border border-slate-200/60 text-slate-800 italic">
                                    "{{ laporan.verifikasi.catatan }}"
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Card Hasil Tindak Lanjut UPT (jika ada) -->
                    <Card v-if="laporan.tindak_lanjut" class="border-slate-200/80 shadow-2xs rounded-2xl bg-white">
                        <CardHeader class="pb-3 border-b border-slate-100">
                            <div class="flex items-center justify-between">
                                <CardTitle class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                    <Building2 :size="16" class="text-purple-600" />
                                    Hasil Tindak Lanjut UPT Imigrasi
                                </CardTitle>
                                <span class="font-mono text-xs font-bold text-purple-900 bg-purple-50 px-2.5 py-1 rounded-lg border border-purple-200">
                                    No. Reg: {{ laporan.tindak_lanjut.nomor_registrasi }}
                                </span>
                            </div>
                        </CardHeader>
                        <CardContent class="pt-4 text-xs text-slate-700 space-y-3">
                            <div class="grid grid-cols-2 gap-3 text-xs bg-purple-50/30 p-3.5 rounded-xl border border-purple-100">
                                <div>
                                    <span class="text-[11px] text-slate-400 block mb-0.5">Seksi Penanggung Jawab</span>
                                    <strong class="uppercase text-slate-900 font-bold block">{{ laporan.tindak_lanjut.seksi_penanggung_jawab }}</strong>
                                </div>
                                <div>
                                    <span class="text-[11px] text-slate-400 block mb-0.5">Bentuk Intervensi</span>
                                    <strong class="capitalize text-slate-900 font-bold block">{{ laporan.tindak_lanjut.bentuk_intervensi }}</strong>
                                </div>
                            </div>
                            <div class="space-y-1">
                                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Ringkasan Hasil Lapangan:</span>
                                <p class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/60 text-slate-800 leading-relaxed">
                                    {{ laporan.tindak_lanjut.ringkasan_hasil }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                </div>

            </div>

        </div>

    </AppLayout>
</template>
