<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/components/layout/AppLayout.vue';
import {
    Plus,
    Search,
    FileText,
    FileSpreadsheet,
    Eye,
    Calendar,
    Paperclip,
    Clock,
    AlertCircle,
    CheckCircle2,
    RotateCcw,
    ArrowUpDown,
    ArrowUp,
    ArrowDown,
    ExternalLink,
    FileCheck2,
    Filter,
    Tag,
    MapPin
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

interface LampiranItem {
    id: number;
    nama_file_asli: string;
    path: string;
    tipe_file: string;
    ukuran_bytes: number;
}

interface LaporanItem {
    id: number;
    kode_tiket: string;
    judul: string;
    kategori: string;
    status: string;
    tanggal_kejadian: string;
    submitted_at: string;
    desa?: { nama: string };
    lampiran_list?: LampiranItem[];
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
    filters: {
        search?: string;
        status?: string;
        kategori?: string;
        desa_id?: string;
        tanggal_mulai?: string;
        tanggal_selesai?: string;
    };
    statusCounts?: {
        all?: number;
        diajukan?: number;
        minta_perbaikan?: number;
        diverifikasi?: number;
        ditindaklanjuti?: number;
        selesai?: number;
        ditolak?: number;
    };
    kategoriOptions: Array<{ value: string; label: string; count?: number }>;
    desaOptions?: Array<{ value: string; label: string; count?: number }>;
    statusOptions: Array<{ value: string; label: string }>;
}>();

const searchInput = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || 'all');
const selectedKategori = ref(props.filters.kategori || 'all');
const selectedDesa = ref(props.filters.desa_id || 'all');
const tanggalMulai = ref(props.filters.tanggal_mulai || '');
const tanggalSelesai = ref(props.filters.tanggal_selesai || '');

const filterByStatus = (statusValue: string) => {
    selectedStatus.value = statusValue;
    applyFilter();
};

const onStatusChange = (val: any) => {
    selectedStatus.value = String(val || 'all');
    applyFilter();
};

const onKategoriChange = (val: any) => {
    selectedKategori.value = String(val || 'all');
    applyFilter();
};

const onDesaChange = (val: any) => {
    selectedDesa.value = String(val || 'all');
    applyFilter();
};

const kategoriComboboxOptions = computed(() => [
    { value: 'all', label: `Semua Kategori (${props.statusCounts?.all ?? props.laporan.total})` },
    ...(props.kategoriOptions || []).map(c => ({
        value: String(c.value),
        label: `${c.label} (${c.count ?? 0})`
    }))
]);

const desaComboboxOptions = computed(() => [
    { value: 'all', label: `Semua Desa Binaan (${props.statusCounts?.all ?? props.laporan.total})` },
    ...(props.desaOptions || []).map(d => ({
        value: String(d.value),
        label: `${d.label} (${d.count ?? 0})`
    }))
]);

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

const activeLampiranPopoverId = ref<number | null>(null);

const toggleLampiranPopover = (id: number) => {
    if (activeLampiranPopoverId.value === id) {
        activeLampiranPopoverId.value = null;
    } else {
        activeLampiranPopoverId.value = id;
    }
};

const applyFilter = () => {
    const params: Record<string, string> = {};
    if (searchInput.value) params.search = searchInput.value;
    if (selectedStatus.value && selectedStatus.value !== 'all') params.status = selectedStatus.value;
    if (selectedKategori.value && selectedKategori.value !== 'all') params.kategori = selectedKategori.value;
    if (selectedDesa.value && selectedDesa.value !== 'all') params.desa_id = selectedDesa.value;
    if (tanggalMulai.value) params.tanggal_mulai = tanggalMulai.value;
    if (tanggalSelesai.value) params.tanggal_selesai = tanggalSelesai.value;

    router.get('/pimpasa/verifikasi', params, { preserveState: true, replace: true });
};

const resetFilter = () => {
    searchInput.value = '';
    selectedStatus.value = 'all';
    selectedKategori.value = 'all';
    selectedDesa.value = 'all';
    tanggalMulai.value = '';
    tanggalSelesai.value = '';

    router.get('/pimpasa/verifikasi', {}, { preserveState: false, replace: true });
};

const buildQueryParams = () => {
    const params = new URLSearchParams();
    if (searchInput.value) params.append('search', searchInput.value);
    if (selectedStatus.value && selectedStatus.value !== 'all') params.append('status', selectedStatus.value);
    if (selectedKategori.value && selectedKategori.value !== 'all') params.append('kategori', selectedKategori.value);
    if (selectedDesa.value && selectedDesa.value !== 'all') params.append('desa_id', selectedDesa.value);
    if (tanggalMulai.value) params.append('tanggal_mulai', tanggalMulai.value);
    if (tanggalSelesai.value) params.append('tanggal_selesai', tanggalSelesai.value);
    return params;
};

const exportExcel = () => {
    const params = buildQueryParams();
    window.location.href = `/pimpasa/verifikasi/export-excel?${params.toString()}`;
};

const exportPdf = () => {
    const params = buildQueryParams();
    window.open(`/pimpasa/verifikasi/export-pdf?${params.toString()}`, '_blank');
};

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'diajukan':
            return 'bg-amber-50 text-amber-800 border-amber-200/80';
        case 'minta_perbaikan':
            return 'bg-orange-50 text-orange-900 border-orange-300 font-bold animate-pulse';
        case 'diverifikasi':
            return 'bg-blue-50 text-blue-800 border-blue-200/80';
        case 'ditindaklanjuti':
            return 'bg-purple-50 text-purple-800 border-purple-200/80';
        case 'selesai':
            return 'bg-emerald-50 text-emerald-800 border-emerald-200/80';
        case 'ditolak':
            return 'bg-red-50 text-red-800 border-red-200/80';
        default:
            return 'bg-slate-50 text-slate-700 border-slate-200/80';
    }
};

const getStatusLabel = (status: string) => {
    switch (status) {
        case 'diajukan': return 'Perlu Verifikasi';
        case 'minta_perbaikan': return 'Minta Perbaikan';
        case 'diverifikasi': return 'Diverifikasi PIMPASA';
        case 'ditindaklanjuti': return 'Ditindaklanjuti PIMPASA';
        case 'selesai': return 'Selesai';
        case 'ditolak': return 'Ditolak';
        default: return status;
    }
};

// Metric stats calculation
const totalLaporan = computed(() => props.laporan.total);
const perluVerifikasi = computed(() => props.laporan.data.filter(i => i.status === 'diajukan').length);
const mintaPerbaikan = computed(() => props.laporan.data.filter(i => i.status === 'minta_perbaikan').length);
const diverifikasiSelesai = computed(() => props.laporan.data.filter(i => ['diverifikasi', 'ditindaklanjuti', 'selesai'].includes(i.status)).length);
</script>

<template>
    <AppLayout title="Worklist Verifikasi Laporan">
        <div class="space-y-6 font-sans">
            
            <!-- Quick Metric KPI Cards (Matching Presisi Desa & Kanwil Master Data) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- Card 1: Total Laporan Masuk -->
                <Card @click="filterByStatus('all')" class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group cursor-pointer hover:border-slate-400 transition-all">
                    <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-slate-300/80 transition-colors">
                        <FileText :size="105" stroke-width="1.0" />
                    </div>

                    <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                        <div class="space-y-0.5 pr-12">
                            <span class="text-xs font-semibold text-slate-500 block leading-tight">Total Laporan Masuk</span>
                            <div class="text-3xl font-bold text-slate-900 font-sans tabular-nums tracking-tight leading-none pt-1">
                                {{ totalLaporan }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[11px] text-slate-500 font-medium">Seluruh laporan desa binaan</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Card 2: Menunggu Verifikasi -->
                <Card @click="filterByStatus('diajukan')" class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group cursor-pointer hover:border-amber-400 transition-all">
                    <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-slate-300/80 transition-colors">
                        <Clock :size="105" stroke-width="1.0" />
                    </div>

                    <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                        <div class="space-y-0.5 pr-12">
                            <span class="text-xs font-semibold text-slate-500 block leading-tight">Menunggu Verifikasi</span>
                            <div class="text-3xl font-bold text-amber-700 font-sans tabular-nums tracking-tight leading-none pt-1">
                                {{ perluVerifikasi }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[11px] text-slate-500 font-medium">Perlu tindakan PIMPASA</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Card 3: Minta Perbaikan -->
                <Card @click="filterByStatus('minta_perbaikan')" class="border-amber-300/80 shadow-2xs rounded-lg bg-amber-50/30 relative overflow-hidden group cursor-pointer hover:border-amber-500 transition-all">
                    <div class="absolute -right-4 -bottom-6 text-amber-200/80 pointer-events-none group-hover:text-amber-300/80 transition-colors">
                        <AlertCircle :size="105" stroke-width="1.0" />
                    </div>

                    <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                        <div class="space-y-0.5 pr-12">
                            <span class="text-xs font-bold text-amber-900 block leading-tight">Minta Perbaikan Data</span>
                            <div class="text-3xl font-bold text-amber-900 font-sans tabular-nums tracking-tight leading-none pt-1">
                                {{ mintaPerbaikan }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[11px] text-amber-800 font-semibold">Dikembalikan ke perangkat desa</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Card 4: Diverifikasi & Ditindaklanjuti -->
                <Card @click="filterByStatus('diverifikasi')" class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group cursor-pointer hover:border-emerald-400 transition-all">
                    <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-slate-300/80 transition-colors">
                        <CheckCircle2 :size="105" stroke-width="1.0" />
                    </div>

                    <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                        <div class="space-y-0.5 pr-12">
                            <span class="text-xs font-semibold text-slate-500 block leading-tight">Telah Diverifikasi</span>
                            <div class="text-3xl font-bold text-emerald-700 font-sans tabular-nums tracking-tight leading-none pt-1">
                                {{ diverifikasiSelesai }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[11px] text-slate-500 font-medium">Disetujui / Disposisi UPT</p>
                        </div>
                    </CardContent>
                </Card>

            </div>

            <!-- Main Card Section: Data Table -->
            <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white overflow-hidden">
                
                <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">
                            Worklist Verifikasi Laporan Desa Binaan
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Tinjau, periksa kelengkapan berkas, dan berikan verifikasi atas laporan kejadian dari Perangkat Desa.
                        </p>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <Button
                            @click="exportExcel"
                            variant="outline"
                            size="sm"
                            class="h-9 px-3.5 border-emerald-200 bg-emerald-50/70 hover:bg-emerald-100/90 text-emerald-800 font-semibold rounded-md text-xs flex items-center gap-2 shadow-2xs transition-all cursor-pointer"
                        >
                            <FileSpreadsheet :size="15" class="text-emerald-700 shrink-0" />
                            <span>Export Excel</span>
                        </Button>

                        <Button
                            @click="exportPdf"
                            variant="outline"
                            size="sm"
                            class="h-9 px-3.5 border-rose-200 bg-rose-50/70 hover:bg-rose-100/90 text-rose-800 font-semibold rounded-md text-xs flex items-center gap-2 shadow-2xs transition-all cursor-pointer"
                        >
                            <FileText :size="15" class="text-rose-700 shrink-0" />
                            <span>Export PDF</span>
                        </Button>
                    </div>
                </div>

                <!-- Toolbar Filter (Clean Structured 2-Tier Split Layout) -->
                <div class="p-4 bg-slate-50/60 border-b border-slate-100 space-y-3">
                    
                    <!-- Row 1: Search Bar Utama & Reset Button -->
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                        <div class="relative w-full flex-1">
                            <Search :size="15" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
                            <Input
                                type="text"
                                v-model="searchInput"
                                @keyup.enter="applyFilter"
                                placeholder="Cari kode tiket, judul laporan, atau nama desa binaan..."
                                class="pl-9 pr-24 text-xs rounded-md bg-white border-slate-200/90 h-9.5 shadow-2xs w-full focus:ring-1 focus:ring-slate-400"
                            />
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 font-medium pointer-events-none select-none hidden sm:flex items-center gap-1">
                                <kbd class="bg-slate-100 border border-slate-300 text-slate-500 text-[9px] font-sans font-semibold px-1.5 py-0.5 rounded">Enter</kbd>
                            </span>
                        </div>

                        <button
                            type="button"
                            @click="resetFilter"
                            class="w-full sm:w-auto h-9.5 px-4 bg-white border border-slate-200/90 rounded-md text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-all flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer shrink-0"
                            title="Reset Filter"
                        >
                            <RotateCcw :size="13" class="text-slate-400 shrink-0" />
                            <span>Reset Filter</span>
                        </button>
                    </div>

                    <!-- Row 2: 5 Filter Columns with Clear Labels (Dari Tanggal, Sampai Tanggal, Status, Kategori, Desa Binaan) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                        
                        <!-- Col 1: Dari Tanggal -->
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-500 flex items-center gap-1">
                                <Calendar :size="12" class="text-slate-400 shrink-0" /> Dari Tanggal
                            </label>
                            <Input
                                type="date"
                                v-model="tanggalMulai"
                                @change="applyFilter"
                                class="text-xs bg-white border-slate-200/90 h-9 rounded-md shadow-2xs w-full px-3 text-slate-800 font-semibold cursor-pointer"
                                title="Dari Tanggal"
                            />
                        </div>

                        <!-- Col 2: Sampai Tanggal -->
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-500 flex items-center gap-1">
                                <Calendar :size="12" class="text-slate-400 shrink-0" /> Sampai Tanggal
                            </label>
                            <Input
                                type="date"
                                v-model="tanggalSelesai"
                                @change="applyFilter"
                                class="text-xs bg-white border-slate-200/90 h-9 rounded-md shadow-2xs w-full px-3 text-slate-800 font-semibold cursor-pointer"
                                title="Sampai Tanggal"
                            />
                        </div>

                        <!-- Col 3: Status Siklus Filter -->
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-500 flex items-center gap-1">
                                <Filter :size="12" class="text-slate-400 shrink-0" /> Status Siklus
                            </label>
                            <Select :model-value="selectedStatus" @update:model-value="onStatusChange">
                                <SelectTrigger class="w-full bg-white border-slate-200/90 rounded-md text-xs font-semibold text-slate-800 shadow-2xs h-9">
                                    <SelectValue placeholder="Semua Status Siklus" />
                                </SelectTrigger>
                                <SelectContent class="rounded-lg shadow-xl border-slate-200 bg-white">
                                    <SelectItem value="all" class="text-xs font-semibold text-slate-900">Semua Status ({{ statusCounts?.all ?? props.laporan.total }})</SelectItem>
                                    <SelectItem value="diajukan" class="text-xs font-medium">Perlu Verifikasi ({{ statusCounts?.diajukan ?? 0 }})</SelectItem>
                                    <SelectItem value="minta_perbaikan" class="text-xs text-amber-800 font-bold">Minta Perbaikan ({{ statusCounts?.minta_perbaikan ?? 0 }})</SelectItem>
                                    <SelectItem value="diverifikasi" class="text-xs font-medium">Diverifikasi ({{ statusCounts?.diverifikasi ?? 0 }})</SelectItem>
                                    <SelectItem value="ditindaklanjuti" class="text-xs font-medium">Ditindaklanjuti ({{ statusCounts?.ditindaklanjuti ?? 0 }})</SelectItem>
                                    <SelectItem value="selesai" class="text-xs text-emerald-700 font-semibold">Selesai ({{ statusCounts?.selesai ?? 0 }})</SelectItem>
                                    <SelectItem value="ditolak" class="text-xs text-red-700 font-semibold">Ditolak ({{ statusCounts?.ditolak ?? 0 }})</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Col 4: Kategori Laporan Filter -->
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-500 flex items-center gap-1">
                                <Tag :size="12" class="text-slate-400 shrink-0" /> Kategori Laporan
                            </label>
                            <Combobox
                                :options="kategoriComboboxOptions"
                                :model-value="selectedKategori"
                                @update:model-value="onKategoriChange"
                                placeholder="Semua Kategori Laporan"
                                search-placeholder="Cari kategori..."
                                class="w-full h-9 bg-white border-slate-200/90 text-xs font-semibold text-slate-800 shadow-2xs"
                            />
                        </div>

                        <!-- Col 5: Desa Binaan Filter -->
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-500 flex items-center gap-1">
                                <MapPin :size="12" class="text-slate-400 shrink-0" /> Desa Binaan
                            </label>
                            <Combobox
                                :options="desaComboboxOptions"
                                :model-value="selectedDesa"
                                @update:model-value="onDesaChange"
                                placeholder="Semua Desa Binaan"
                                search-placeholder="Cari desa binaan..."
                                class="w-full h-9 bg-white border-slate-200/90 text-xs font-semibold text-slate-800 shadow-2xs"
                            />
                        </div>

                    </div>
                </div>

                <!-- Vue Data Table -->
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
                                        <span>Kode Tiket & Desa</span>
                                        <ArrowUp v-if="sortField === 'kode_tiket' && sortDirection === 'asc'" :size="12" class="text-slate-900" />
                                        <ArrowDown v-else-if="sortField === 'kode_tiket' && sortDirection === 'desc'" :size="12" class="text-slate-900" />
                                        <ArrowUpDown v-else :size="12" class="text-slate-300" />
                                    </div>
                                </th>

                                <th @click="handleSort('judul')" class="py-3.5 px-5 cursor-pointer hover:bg-slate-200/60 transition-colors">
                                    <div class="flex items-center gap-1.5">
                                        <span>Judul Laporan</span>
                                        <ArrowUp v-if="sortField === 'judul' && sortDirection === 'asc'" :size="12" class="text-slate-900" />
                                        <ArrowDown v-else-if="sortField === 'judul' && sortDirection === 'desc'" :size="12" class="text-slate-900" />
                                        <ArrowUpDown v-else :size="12" class="text-slate-300" />
                                    </div>
                                </th>

                                <th class="py-3.5 px-5">Kategori Laporan</th>

                                <th @click="handleSort('submitted_at')" class="py-3.5 px-5 cursor-pointer hover:bg-slate-200/60 transition-colors">
                                    <div class="flex items-center gap-1.5">
                                        <span>Waktu Pengajuan</span>
                                        <ArrowUp v-if="sortField === 'submitted_at' && sortDirection === 'asc'" :size="12" class="text-slate-900" />
                                        <ArrowDown v-else-if="sortField === 'submitted_at' && sortDirection === 'desc'" :size="12" class="text-slate-900" />
                                        <ArrowUpDown v-else :size="12" class="text-slate-300" />
                                    </div>
                                </th>

                                <th class="py-3.5 px-5 text-center">Lampiran</th>
                                <th class="py-3.5 px-5">Status Siklus</th>
                                <th class="py-3.5 px-5 text-center">Aksi Verifikasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <tr v-if="sortedLaporanData.length === 0">
                                <td colspan="8" class="py-14 text-center text-slate-400">
                                    <FileText :size="36" class="mx-auto mb-2 opacity-40" />
                                    <p class="font-bold text-slate-700 text-xs">Belum ada pengajuan laporan</p>
                                </td>
                            </tr>

                            <tr
                                v-for="row in sortedLaporanData"
                                :key="row.id"
                                :class="[
                                    'transition-all duration-150',
                                    row.status === 'diajukan'
                                        ? 'bg-amber-50/50 hover:bg-amber-100/60 border-l-4 border-l-amber-500'
                                        : 'hover:bg-slate-50/70'
                                ]"
                            >
                                <!-- Kolom No -->
                                <td class="py-4 px-4 text-center font-sans tabular-nums text-slate-900 font-bold">
                                    {{ row.id }}
                                </td>

                                <td class="py-4 px-5">
                                    <div class="font-sans tabular-nums font-bold text-slate-900">
                                        {{ row.kode_tiket }}
                                    </div>
                                    <span class="text-[11px] text-slate-500 font-medium">
                                        {{ row.desa?.nama || 'Desa Binaan' }}
                                    </span>
                                </td>

                                <td class="py-4 px-5 max-w-xs">
                                    <div class="font-bold text-slate-900 leading-snug line-clamp-2">
                                        {{ row.judul }}
                                    </div>
                                </td>

                                <td class="py-4 px-5 whitespace-nowrap">
                                    <span
                                        :class="[
                                            'inline-block px-2.5 py-1 rounded text-[11px] font-semibold tracking-tight border',
                                            (row as any).kategoriRef?.nama_kategori || (row as any).kategori?.nama_kategori || (typeof row.kategori === 'string' ? row.kategori : null)
                                                ? 'bg-slate-100 text-slate-800 border-slate-200/80'
                                                : 'bg-slate-50 text-slate-400 border-slate-200/60 italic'
                                        ]"
                                    >
                                        {{ (row as any).kategoriRef?.nama_kategori || (row as any).kategori?.nama_kategori || (typeof row.kategori === 'string' ? row.kategori : null) || 'Tidak ada kategori' }}
                                    </span>
                                </td>

                                <td class="py-4 px-5 text-slate-900 font-semibold whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <Calendar :size="13" class="text-slate-400" />
                                        <span>{{ new Date(row.submitted_at || row.tanggal_kejadian).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) }}</span>
                                    </div>
                                </td>

                                <td class="py-4 px-5 text-center whitespace-nowrap">
                                    <div v-if="row.lampiran_list && row.lampiran_list.length > 0" class="inline-flex items-center justify-center">
                                        <button
                                            type="button"
                                            @click.stop="toggleLampiranPopover(row.id)"
                                            class="inline-flex items-center justify-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-900 text-[11px] font-bold transition-colors cursor-pointer border border-slate-200/80 whitespace-nowrap leading-none shrink-0"
                                        >
                                            <Paperclip :size="13" class="text-slate-500 shrink-0" />
                                            <span class="whitespace-nowrap">{{ row.lampiran_list.length }} Berkas</span>
                                        </button>
                                    </div>
                                    <span v-else class="text-slate-300 text-[11px]">-</span>
                                </td>

                                <td class="py-4 px-5 whitespace-nowrap">
                                    <span :class="['px-2.5 py-1 rounded-full text-[11px] font-semibold border', getStatusBadge(row.status)]">
                                        {{ getStatusLabel(row.status) }}
                                    </span>
                                </td>

                                <td class="py-4 px-5 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <Link :href="['diverifikasi', 'ditindaklanjuti', 'selesai'].includes(row.status) ? `/pimpasa/tindak-lanjut/${row.id}` : `/pimpasa/verifikasi/${row.id}`">
                                            <Button
                                                :class="[
                                                    'h-8 px-3 text-xs font-semibold rounded-md flex items-center gap-1.5 cursor-pointer transition-all shadow-2xs',
                                                    row.status === 'diajukan'
                                                        ? 'bg-primary hover:bg-[#04407D] text-primary-foreground font-bold'
                                                        : 'bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300'
                                                ]"
                                            >
                                                <Eye :size="13" />
                                                <span>
                                                    {{ row.status === 'diajukan' ? 'Proses Verifikasi' : (['diverifikasi', 'ditindaklanjuti'].includes(row.status) ? 'Tindak Lanjut UPT' : 'Detail Tiket') }}
                                                </span>
                                            </Button>
                                        </Link>
                                    </div>
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
                        dari <span class="font-bold text-slate-900">{{ laporan.total }}</span> Data Laporan
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

        </div>
    </AppLayout>
</template>

