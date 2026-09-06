<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/components/layout/AppLayout.vue';
import {
    History,
    Search,
    FileText,
    Calendar,
    Paperclip,
    CheckCircle2,
    XCircle,
    Clock,
    RotateCcw,
    ArrowUpDown,
    ArrowUp,
    ArrowDown,
    ExternalLink,
    Eye,
    ShieldCheck,
    UserCheck,
    AlertCircle
} from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import Combobox from '@/components/ui/combobox/Combobox.vue';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';

interface LampiranItem {
    id: number;
    nama_file_asli: string;
    path: string;
    tipe_file: string;
    ukuran_bytes: number;
}

interface StatusHistoryItem {
    id: number;
    status_dari: string | null;
    status_ke: string;
    catatan: string | null;
    created_at: string;
    actor?: {
        name: string;
        role: string;
    };
}

interface LaporanItem {
    id: number;
    kode_tiket: string;
    judul: string;
    kategori: string;
    status: string;
    tanggal_kejadian: string;
    submitted_at: string;
    kronologi: string;
    lokasi_detail: string;
    lampiran_list?: LampiranItem[];
    verifikasi?: {
        keputusan: string;
        catatan: string | null;
        verified_at: string;
        pimpasa?: { name: string };
    };
    tindak_lanjut?: {
        tindakan: string;
        catatan: string | null;
        completed_at: string;
        staf_upt?: { name: string };
    };
    status_histories?: StatusHistoryItem[];
}

const props = defineProps<{
    laporan: {
        data: LaporanItem[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    stats: {
        total: number;
        selesai: number;
        ditolak: number;
        diproses: number;
        resolution_rate: number;
    };
    filters: {
        search?: string;
        status?: string;
        kategori?: string;
        periode?: string;
    };
    kategoriOptions: Array<{ value: string; label: string }>;
}>();

const searchInput = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || 'all');
const selectedKategori = ref(props.filters.kategori || 'all');
const selectedPeriode = ref(props.filters.periode || 'all');

const kategoriComboboxOptions = computed(() => [
    { value: 'all', label: `Semua Kategori (${props.stats.total})` },
    ...props.kategoriOptions.map(c => ({
        value: String(c.value),
        label: c.label,
    }))
]);

// State untuk Side Drawer Audit Trail
const isAuditSheetOpen = ref(false);
const selectedLaporanForAudit = ref<LaporanItem | null>(null);

const openAuditTrail = (item: LaporanItem) => {
    selectedLaporanForAudit.value = item;
    isAuditSheetOpen.value = true;
};

// Sorting Table State
const sortField = ref<string>('submitted_at');
const sortDirection = ref<'asc' | 'desc'>('desc');

const handleSort = (field: string) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }
};

const sortedLaporanData = computed(() => {
    const data = [...props.laporan.data];
    const field = sortField.value;
    const dir = sortDirection.value === 'asc' ? 1 : -1;

    return data.sort((a, b) => {
        let valA = (a as any)[field] || '';
        let valB = (b as any)[field] || '';

        if (typeof valA === 'string') valA = valA.toLowerCase();
        if (typeof valB === 'string') valB = valB.toLowerCase();

        if (valA < valB) return -1 * dir;
        if (valA > valB) return 1 * dir;
        return 0;
    });
});

// Trigger Filter Navigation
const applyFilter = () => {
    router.get('/desa/riwayat', {
        search: searchInput.value,
        status: selectedStatus.value === 'all' ? '' : selectedStatus.value,
        kategori: selectedKategori.value === 'all' ? '' : selectedKategori.value,
        periode: selectedPeriode.value === 'all' ? '' : selectedPeriode.value,
    }, { preserveState: true, replace: true });
};

const resetFilter = () => {
    searchInput.value = '';
    selectedStatus.value = 'all';
    selectedKategori.value = 'all';
    selectedPeriode.value = 'all';
    applyFilter();
};

import { notify } from '@/lib/toast';

// Print & Export Rekap
const handlePrintPdf = () => {
    notify.info('Mencetak Rekapitulasi PDF', { description: 'Menyiapkan pratinjau dokumen laporan desa...' });
    window.print();
};

const handleExportCsv = () => {
    notify.info('Mengunduh Data Excel', { description: 'Berkas rekapitulasi data laporan desa sedang di-generate...' });
};

// Status Badges (Semantic OKLCH Style)
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
</script>

<template>
    <AppLayout title="Status & Riwayat Tiket">
        
        <!-- High Level Executive KPI Cards (Presisi Matching Master Data Admin Kanwil & DESIGN.md) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Card 1: Total Riwayat Tiket -->
            <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group">
                <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-slate-300/80 transition-colors">
                    <History :size="105" stroke-width="1.0" />
                </div>

                <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                    <div class="space-y-0.5 pr-12">
                        <span class="text-xs font-semibold text-slate-500 block leading-tight">Total Histori Laporan</span>
                        <div class="text-3xl font-bold text-slate-900 font-sans tabular-nums tracking-tight leading-none pt-1">
                            {{ stats.total }}
                        </div>
                    </div>
                    <div class="pt-2">
                        <p class="text-[11px] text-slate-500 font-medium">Arsip riwayat keseluruhan</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Card 2: Laporan Selesai -->
            <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group">
                <div class="absolute -right-4 -bottom-6 text-emerald-200/70 pointer-events-none group-hover:text-emerald-300/80 transition-colors">
                    <CheckCircle2 :size="105" stroke-width="1.0" />
                </div>

                <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                    <div class="space-y-0.5 pr-12">
                        <span class="text-xs font-semibold text-slate-500 block leading-tight">Selesai Ditindaklanjuti</span>
                        <div class="text-3xl font-bold text-emerald-700 font-sans tabular-nums tracking-tight leading-none pt-1">
                            {{ stats.selesai }}
                        </div>
                    </div>
                    <div class="pt-2">
                        <p class="text-[11px] text-slate-500 font-medium">Tindakan UPT rampung</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Card 3: Laporan Ditolak / Gugur -->
            <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group">
                <div class="absolute -right-4 -bottom-6 text-red-200/70 pointer-events-none group-hover:text-red-300/80 transition-colors">
                    <XCircle :size="105" stroke-width="1.0" />
                </div>

                <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                    <div class="space-y-0.5 pr-12">
                        <span class="text-xs font-semibold text-slate-500 block leading-tight">Ditolak / Gugur Syarat</span>
                        <div class="text-3xl font-bold text-red-700 font-sans tabular-nums tracking-tight leading-none pt-1">
                            {{ stats.ditolak }}
                        </div>
                    </div>
                    <div class="pt-2">
                        <p class="text-[11px] text-slate-500 font-medium">Tidak memenuhi kriteria</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Card 4: Tingkat Resolusi (%) -->
            <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group">
                <div class="absolute -right-4 -bottom-6 text-blue-200/70 pointer-events-none group-hover:text-blue-300/80 transition-colors">
                    <ShieldCheck :size="105" stroke-width="1.0" />
                </div>

                <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                    <div class="space-y-0.5 pr-12">
                        <span class="text-xs font-semibold text-slate-500 block leading-tight">Tingkat Resolusi Selesai</span>
                        <div class="text-3xl font-bold text-blue-800 font-sans tabular-nums tracking-tight leading-none pt-1">
                            {{ stats.resolution_rate }}%
                        </div>
                    </div>
                    <div class="pt-2">
                        <p class="text-[11px] text-slate-500 font-medium">Persentase penyelesaian</p>
                    </div>
                </CardContent>
            </Card>

        </div>

        <!-- Main Card: Table, Toolbar, & Export Options -->
        <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white overflow-hidden">
            
            <!-- Card Header Actions -->
            <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900 tracking-tight">
                        Status & Audit Trail Riwayat Tiket
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Pusat arsip pelaporan desa dan pemantauan jejak audit SLA.
                    </p>
                </div>
            </div>

            <!-- Filter Toolbar (Presisi 12-Column Grid Alignment) -->
            <div class="p-4 bg-slate-50/50 border-b border-slate-100 grid grid-cols-1 sm:grid-cols-12 gap-3">
                
                <!-- Search Input -->
                <div class="sm:col-span-6 lg:col-span-4 relative">
                    <Search :size="15" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
                    <Input
                        type="text"
                        v-model="searchInput"
                        @keyup.enter="applyFilter"
                        placeholder="Cari tiket, judul, lokasi..."
                        class="pl-9 pr-24 text-xs rounded-md bg-white border-slate-200/90 h-9 shadow-2xs"
                    />
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 font-medium pointer-events-none select-none hidden sm:flex items-center gap-1">
                        <kbd class="bg-slate-100 border border-slate-300 text-slate-500 text-[9px] font-sans font-semibold px-1.5 py-0.5 rounded">Enter</kbd>
                    </span>
                </div>

                <!-- Periode Filter -->
                <div class="sm:col-span-3 lg:col-span-2">
                    <Select :model-value="selectedPeriode" @update:model-value="(val) => { selectedPeriode = String(val || 'all'); applyFilter(); }">
                        <SelectTrigger class="w-full bg-white border-slate-200/90 rounded-md text-xs font-semibold text-slate-800 shadow-2xs h-9">
                            <SelectValue placeholder="Semua Periode" />
                        </SelectTrigger>
                        <SelectContent class="rounded-lg shadow-xl border-slate-200 bg-white">
                            <SelectItem value="all" class="text-xs font-semibold">Semua Periode</SelectItem>
                            <SelectItem value="this_month" class="text-xs">Bulan Ini</SelectItem>
                            <SelectItem value="this_quarter" class="text-xs">Triwulan Ini</SelectItem>
                            <SelectItem value="this_year" class="text-xs">Tahun Ini</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Status Filter -->
                <div class="sm:col-span-3 lg:col-span-2">
                    <Select :model-value="selectedStatus" @update:model-value="(val) => { selectedStatus = String(val || 'all'); applyFilter(); }">
                        <SelectTrigger class="w-full bg-white border-slate-200/90 rounded-md text-xs font-semibold text-slate-800 shadow-2xs h-9">
                            <SelectValue placeholder="Semua Status" />
                        </SelectTrigger>
                        <SelectContent class="rounded-lg shadow-xl border-slate-200 bg-white">
                            <SelectItem value="all" class="text-xs font-semibold">Semua Status Siklus</SelectItem>
                            <SelectItem value="selesai" class="text-xs text-emerald-600 font-semibold">Selesai</SelectItem>
                            <SelectItem value="ditindaklanjuti" class="text-xs">Ditindaklanjuti PIMPASA</SelectItem>
                            <SelectItem value="diverifikasi" class="text-xs">Diverifikasi PIMPASA</SelectItem>
                            <SelectItem value="minta_perbaikan" class="text-xs text-orange-600 font-semibold">Minta Perbaikan</SelectItem>
                            <SelectItem value="diajukan" class="text-xs">Diajukan</SelectItem>
                            <SelectItem value="ditolak" class="text-xs text-red-600 font-semibold">Ditolak</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Kategori Filter (Searchable Combobox) -->
                <div class="sm:col-span-6 lg:col-span-3">
                    <Combobox
                        :model-value="selectedKategori"
                        @update:model-value="(val) => { selectedKategori = String(val || 'all'); applyFilter(); }"
                        :options="kategoriComboboxOptions"
                        placeholder="Semua Kategori"
                        searchPlaceholder="Cari Kategori..."
                        class="w-full h-9 bg-white border-slate-200/90 rounded-md text-xs font-semibold text-slate-800 shadow-2xs"
                    />
                </div>

                <!-- Reset Filter Button -->
                <div class="sm:col-span-12 lg:col-span-1 flex items-center">
                    <button
                        type="button"
                        @click="resetFilter"
                        class="w-full h-9 px-2.5 bg-white border border-slate-200/90 rounded-md text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-all flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer"
                    >
                        <RotateCcw :size="13" class="text-slate-400 shrink-0" />
                        <span>Reset</span>
                    </button>
                </div>

            </div>

            <!-- Audit Trail Table Container -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-sans border-collapse">
                    <thead>
                        <tr class="bg-slate-100/70 border-b border-slate-200/80 text-slate-700 font-semibold uppercase text-[11px] tracking-wider select-none">
                            <th @click="handleSort('id')" class="py-3.5 px-4 text-center cursor-pointer hover:bg-slate-200/60 transition-colors w-14">
                                <div class="flex items-center justify-center gap-1">
                                    <span>No</span>
                                    <ArrowUp v-if="sortField === 'id' && sortDirection === 'asc'" :size="12" class="text-slate-900" />
                                    <ArrowDown v-else-if="sortField === 'id' && sortDirection === 'desc'" :size="12" class="text-slate-900" />
                                    <ArrowUpDown v-else :size="12" class="text-slate-300" />
                                </div>
                            </th>

                            <th @click="handleSort('kode_tiket')" class="py-3.5 px-5 cursor-pointer hover:bg-slate-200/60 transition-colors">
                                <div class="flex items-center gap-1.5">
                                    <span>Kode Tiket</span>
                                    <ArrowUp v-if="sortField === 'kode_tiket' && sortDirection === 'asc'" :size="12" class="text-slate-900" />
                                    <ArrowDown v-else-if="sortField === 'kode_tiket' && sortDirection === 'desc'" :size="12" class="text-slate-900" />
                                    <ArrowUpDown v-else :size="12" class="text-slate-300" />
                                </div>
                            </th>

                            <th @click="handleSort('judul')" class="py-3.5 px-5 cursor-pointer hover:bg-slate-200/60 transition-colors">
                                <div class="flex items-center gap-1.5">
                                    <span>Judul & Kategori</span>
                                    <ArrowUp v-if="sortField === 'judul' && sortDirection === 'asc'" :size="12" class="text-slate-900" />
                                    <ArrowDown v-else-if="sortField === 'judul' && sortDirection === 'desc'" :size="12" class="text-slate-900" />
                                    <ArrowUpDown v-else :size="12" class="text-slate-300" />
                                </div>
                            </th>

                            <th @click="handleSort('tanggal_kejadian')" class="py-3.5 px-5 cursor-pointer hover:bg-slate-200/60 transition-colors">
                                <div class="flex items-center gap-1.5">
                                    <span>Waktu Kejadian</span>
                                    <ArrowUp v-if="sortField === 'tanggal_kejadian' && sortDirection === 'asc'" :size="12" class="text-slate-900" />
                                    <ArrowDown v-else-if="sortField === 'tanggal_kejadian' && sortDirection === 'desc'" :size="12" class="text-slate-900" />
                                    <ArrowUpDown v-else :size="12" class="text-slate-300" />
                                </div>
                            </th>

                            <th class="py-3.5 px-5">Status Akhir</th>
                            <th class="py-3.5 px-5 text-center">Jejak Audit Trailing</th>
                            <th class="py-3.5 px-5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <tr v-if="sortedLaporanData.length === 0">
                            <td colspan="7" class="py-14 text-center text-slate-400">
                                <History :size="36" class="mx-auto mb-2 opacity-40" />
                                <p class="font-bold text-slate-700 text-xs">Belum ada histori pelaporan</p>
                                <p class="text-[11px] text-slate-400">Seluruh laporan yang dibuat akan diarsipkan jejak auditnya di halaman ini.</p>
                            </td>
                        </tr>

                        <tr
                            v-for="row in sortedLaporanData"
                            :key="row.id"
                            class="hover:bg-slate-50/70 transition-colors"
                        >
                            <!-- Kolom No (Real Data ID font-sans tabular-nums text-slate-900 font-bold) -->
                            <td class="py-4 px-4 text-center font-sans tabular-nums text-slate-900 font-bold">
                                {{ row.id }}
                            </td>

                            <!-- Kode Tiket (font-sans tabular-nums text-slate-900 font-bold - Inter Font) -->
                            <td class="py-4 px-5 font-sans tabular-nums font-bold text-slate-900">
                                {{ row.kode_tiket }}
                            </td>

                            <!-- Judul & Kategori -->
                            <td class="py-4 px-5 max-w-xs">
                                <div class="font-bold text-slate-900 leading-snug line-clamp-1 mb-1">
                                    {{ row.judul }}
                                </div>
                                <span class="inline-block px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[10px] font-semibold uppercase tracking-wider border border-slate-200/80">
                                    {{ row.kategori }}
                                </span>
                            </td>

                            <!-- Tanggal Kejadian -->
                            <td class="py-4 px-5 text-slate-900 font-semibold whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <Calendar :size="13" class="text-slate-400" />
                                    <span>{{ new Date(row.tanggal_kejadian).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) }}</span>
                                </div>
                            </td>

                            <!-- Status Akhir -->
                            <td class="py-4 px-5 whitespace-nowrap">
                                <span :class="['px-2.5 py-1 rounded-full text-[11px] font-semibold border', getStatusBadge(row.status)]">
                                    {{ getStatusLabel(row.status) }}
                                </span>
                            </td>

                            <!-- Jejak Audit Summary (Clean Badge) -->
                            <td class="py-4 px-5 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-[11px] font-semibold border border-slate-200/80">
                                    <Clock :size="11" class="text-slate-400" />
                                    <span>{{ row.status_histories?.length || 1 }} Langkah</span>
                                </span>
                            </td>

                            <!-- Action Buttons -->
                            <td class="py-4 px-5 text-center whitespace-nowrap">
                                <Link :href="`/desa/laporan/${row.id}`">
                                    <button class="btn-action-view-text" title="Lihat Detail Tiket">
                                        <Eye :size="13" />
                                        <span>Detail</span>
                                    </button>
                                </Link>
                            </td>

                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Integrated Pagination Bar (50 Data Per Halaman) -->
            <div v-if="laporan.total > 0" class="p-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
                <div>
                    Menampilkan <span class="font-bold text-slate-900">{{ (laporan.current_page - 1) * laporan.per_page + 1 }}</span>
                    sampai <span class="font-bold text-slate-900">{{ Math.min(laporan.current_page * laporan.per_page, laporan.total) }}</span>
                    dari <span class="font-bold text-slate-900">{{ laporan.total }}</span> Data Riwayat Tiket
                </div>

                <div v-if="laporan.last_page > 1" class="flex items-center gap-1">
                    <Component
                        :is="link.url ? Link : 'span'"
                        v-for="(link, i) in laporan.links"
                        :key="i"
                        :href="link.url || '#'"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors',
                            link.active
                                ? 'bg-slate-900 text-white shadow-2xs font-bold'
                                : link.url
                                    ? 'hover:bg-slate-100 text-slate-700'
                                    : 'text-slate-300 pointer-events-none'
                        ]"
                        v-html="link.label.replace('&laquo; Previous', 'Sebelumnya').replace('Next &raquo;', 'Selanjutnya')"
                    />
                </div>
            </div>

        </Card>

        <!-- Side Drawer Audit Trail (Shadcn UI Sheet) -->
        <Sheet :open="isAuditSheetOpen" @update:open="isAuditSheetOpen = $event">
            <SheetContent class="sm:max-w-md w-full bg-white p-6 overflow-y-auto font-sans">
                <SheetHeader class="pb-4 border-b border-slate-100 space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-700 font-mono text-xs font-bold">
                            {{ selectedLaporanForAudit?.kode_tiket }}
                        </span>
                        <span :class="['px-2 py-0.5 rounded-full text-[10px] font-semibold border', getStatusBadge(selectedLaporanForAudit?.status || '')]">
                            {{ getStatusLabel(selectedLaporanForAudit?.status || '') }}
                        </span>
                    </div>
                    <SheetTitle class="text-base font-bold text-slate-900 leading-snug">
                        {{ selectedLaporanForAudit?.judul }}
                    </SheetTitle>
                    <SheetDescription class="text-xs text-slate-500">
                        Kronologi jejak audit timeline pelaporan dan respon petugas secara real-time.
                    </SheetDescription>
                </SheetHeader>

                <!-- Audit Timeline Flow -->
                <div v-if="selectedLaporanForAudit" class="py-6 space-y-6">
                    
                    <div class="relative pl-6 border-l-2 border-slate-200 space-y-6">
                        
                        <!-- Timeline Step: Status Histories -->
                        <div
                            v-for="(hist, idx) in selectedLaporanForAudit.status_histories"
                            :key="hist.id"
                            class="relative group"
                        >
                            <!-- Circle Dot -->
                            <div class="absolute -left-[31px] top-0.5 w-4 h-4 rounded-full bg-white border-2 border-slate-900 flex items-center justify-center">
                                <div class="w-1.5 h-1.5 rounded-full bg-slate-900"></div>
                            </div>

                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-slate-900 uppercase tracking-wider text-[11px]">
                                        {{ getStatusLabel(hist.status_ke) }}
                                    </span>
                                    <span class="text-[10px] font-mono text-slate-400">
                                        {{ new Date(hist.created_at).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' }) }}
                                    </span>
                                </div>

                                <p class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                    {{ hist.catatan || 'Status berhasil diperbarui oleh sistem.' }}
                                </p>

                                <div v-if="hist.actor" class="flex items-center gap-1.5 text-[11px] text-slate-500 font-medium">
                                    <UserCheck :size="12" class="text-slate-400" />
                                    <span>Oleh: <strong class="text-slate-700">{{ hist.actor.name }}</strong> ({{ hist.actor.role.toUpperCase() }})</span>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Ringkasan Verifikasi PIMPASA (jika ada) -->
                    <div v-if="selectedLaporanForAudit.verifikasi" class="p-4 rounded-2xl bg-blue-50/50 border border-blue-100 space-y-2">
                        <div class="flex items-center gap-2 text-xs font-bold text-blue-900">
                            <ShieldCheck :size="16" class="text-blue-600" />
                            <span>Hasil Verifikasi PIMPASA (Satker)</span>
                        </div>
                        <p class="text-xs text-slate-700">
                            {{ selectedLaporanForAudit.verifikasi.catatan || 'Laporan dinyatakan valid dan memenuhi syarat verifikasi.' }}
                        </p>
                        <div class="text-[11px] text-slate-500">
                            Diverifikasi oleh: <strong class="text-slate-800">{{ selectedLaporanForAudit.verifikasi.pimpasa?.name || 'Petugas PIMPASA' }}</strong>
                        </div>
                    </div>

                    <!-- Ringkasan Tindak Lanjut UPT (jika ada) -->
                    <div v-if="selectedLaporanForAudit.tindak_lanjut" class="p-4 rounded-2xl bg-emerald-50/50 border border-emerald-100 space-y-2">
                        <div class="flex items-center gap-2 text-xs font-bold text-emerald-900">
                            <CheckCircle2 :size="16" class="text-emerald-600" />
                            <span>Tindak Lanjut Lapangan UPT</span>
                        </div>
                        <p class="text-xs text-slate-700">
                            {{ selectedLaporanForAudit.tindak_lanjut.catatan || selectedLaporanForAudit.tindak_lanjut.tindakan }}
                        </p>
                        <div class="text-[11px] text-slate-500">
                            Petugas UPT: <strong class="text-slate-800">{{ selectedLaporanForAudit.tindak_lanjut.staf_upt?.name || 'Tim Lapangan UPT' }}</strong>
                        </div>
                    </div>

                </div>

            </SheetContent>
        </Sheet>

    </AppLayout>
</template>
