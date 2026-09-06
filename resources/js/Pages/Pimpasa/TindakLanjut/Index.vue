<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/components/layout/AppLayout.vue';
import {
    Search,
    Building2,
    CheckCircle2,
    Clock,
    FileText,
    FileEdit,
    RotateCcw,
    ArrowUpDown,
    ArrowUp,
    ArrowDown,
    Calendar,
    Paperclip
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
    updated_at: string;
    submitted_at: string;
    desa?: { nama: string };
    kategoriRef?: { nama_kategori: string };
    lampiran_list?: LampiranItem[];
    tindak_lanjut?: {
        nomor_registrasi: string;
        seksi_penanggung_jawab: string;
        bentuk_intervensi: string;
        status_akhir: string;
    } | null;
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
    };
    statusCounts?: {
        all?: number;
        diverifikasi?: number;
        ditindaklanjuti?: number;
        selesai?: number;
    };
    kategoriOptions?: Array<{ value: string; label: string; count?: number }>;
    desaOptions?: Array<{ value: string; label: string; count?: number }>;
}>();

const searchInput = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || 'all');
const selectedKategori = ref(props.filters.kategori || 'all');
const selectedDesa = ref(props.filters.desa_id || 'all');

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

const sortField = ref<string>('updated_at');
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

const applyFilter = () => {
    router.get('/pimpasa/tindak-lanjut', {
        search: searchInput.value || undefined,
        status: selectedStatus.value === 'all' ? undefined : selectedStatus.value,
        kategori: selectedKategori.value === 'all' ? undefined : selectedKategori.value,
        desa_id: selectedDesa.value === 'all' ? undefined : selectedDesa.value,
    }, { preserveState: true, replace: true });
};

const resetFilter = () => {
    searchInput.value = '';
    selectedStatus.value = 'all';
    selectedKategori.value = 'all';
    selectedDesa.value = 'all';
    applyFilter();
};

const getStatusBadge = (statusStr: string) => {
    switch (statusStr) {
        case 'diverifikasi': return 'bg-sky-50 text-sky-800 border-sky-200/80';
        case 'ditindaklanjuti': return 'bg-indigo-50 text-indigo-800 border-indigo-200/80 font-bold';
        case 'selesai': return 'bg-emerald-50 text-emerald-800 border-emerald-200/80';
        default: return 'bg-slate-50 text-slate-700 border-slate-200/80';
    }
};

const getStatusLabel = (statusStr: string) => {
    switch (statusStr) {
        case 'diverifikasi': return 'Diverifikasi PIMPASA';
        case 'ditindaklanjuti': return 'Proses Lapangan UPT';
        case 'selesai': return 'Selesai';
        default: return statusStr;
    }
};const getSeksiLabel = (val?: string | null) => {
    if (!val) return 'Belum Disposisi';
    if (val === 'inteldak' || val === 'inteldakim') return 'Inteldakim';
    return val.toUpperCase();
};

// Metric stats calculation
const totalPenanganan = computed(() => props.laporan.total);
const butuhDisposisi = computed(() => props.laporan.data.filter(i => i.status === 'diverifikasi').length);
const prosesLapangan = computed(() => props.laporan.data.filter(i => i.status === 'ditindaklanjuti').length);
const selesaiTotal = computed(() => props.laporan.data.filter(i => i.status === 'selesai').length);
</script>

<template>
    <AppLayout title="Disposisi & Tindak Lanjut Lapangan UPT">
        <div class="space-y-6 font-sans">
            
            <!-- Quick Metric KPI Cards (Matching Worklist Verifikasi Presisi) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- Card 1: Total Pengajuan Penanganan -->
                <Card @click="filterByStatus('all')" class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group cursor-pointer hover:border-slate-400 transition-all">
                    <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-slate-300/80 transition-colors">
                        <FileText :size="105" stroke-width="1.0" />
                    </div>

                    <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                        <div class="space-y-0.5 pr-12">
                            <span class="text-xs font-semibold text-slate-500 block leading-tight">Total Penanganan UPT</span>
                            <div class="text-3xl font-bold text-slate-900 font-sans tabular-nums tracking-tight leading-none pt-1">
                                {{ totalPenanganan }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[11px] text-slate-500 font-medium">Seluruh penanganan lapangan</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Card 2: Menunggu Disposisi -->
                <Card @click="filterByStatus('diverifikasi')" class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group cursor-pointer hover:border-sky-400 transition-all">
                    <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-slate-300/80 transition-colors">
                        <Clock :size="105" stroke-width="1.0" />
                    </div>

                    <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                        <div class="space-y-0.5 pr-12">
                            <span class="text-xs font-semibold text-slate-500 block leading-tight">Butuh Disposisi UPT</span>
                            <div class="text-3xl font-bold text-sky-700 font-sans tabular-nums tracking-tight leading-none pt-1">
                                {{ butuhDisposisi }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[11px] text-slate-500 font-medium">Telah diverifikasi PIMPASA</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Card 3: Proses Penanganan Lapangan -->
                <Card @click="filterByStatus('ditindaklanjuti')" class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group cursor-pointer hover:border-indigo-400 transition-all">
                    <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-slate-300/80 transition-colors">
                        <Building2 :size="105" stroke-width="1.0" />
                    </div>

                    <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                        <div class="space-y-0.5 pr-12">
                            <span class="text-xs font-semibold text-slate-500 block leading-tight">Proses Lapangan UPT</span>
                            <div class="text-3xl font-bold text-indigo-700 font-sans tabular-nums tracking-tight leading-none pt-1">
                                {{ prosesLapangan }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[11px] text-slate-500 font-medium">Tindak lanjut seksi teknis</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Card 4: Selesai 100% -->
                <Card @click="filterByStatus('selesai')" class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group cursor-pointer hover:border-emerald-400 transition-all">
                    <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-slate-300/80 transition-colors">
                        <CheckCircle2 :size="105" stroke-width="1.0" />
                    </div>

                    <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                        <div class="space-y-0.5 pr-12">
                            <span class="text-xs font-semibold text-slate-500 block leading-tight">Selesai Ditindaklanjuti</span>
                            <div class="text-3xl font-bold text-emerald-700 font-sans tabular-nums tracking-tight leading-none pt-1">
                                {{ selesaiTotal }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[11px] text-slate-500 font-medium">Berita acara tuntas 100%</p>
                        </div>
                    </CardContent>
                </Card>

            </div>

            <!-- Main Card Section: Data Table -->
            <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white overflow-hidden">
                
                <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">
                            Daftar Disposisi & Tindak Lanjut Lapangan UPT
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Kelola penugasan seksi teknis UPT, bentuk intervensi lapangan, dan status akhir berita acara.
                        </p>
                    </div>
                </div>

                <!-- Toolbar Filter -->
                <div class="p-4 bg-slate-50/50 border-b border-slate-100 grid grid-cols-1 sm:grid-cols-12 gap-3">
                    
                    <!-- Input Search -->
                    <div class="sm:col-span-6 lg:col-span-3 relative">
                        <Search :size="15" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
                        <Input
                            type="text"
                            v-model="searchInput"
                            @keyup.enter="applyFilter"
                            placeholder="Cari tiket, no reg UPT..."
                            class="pl-9 pr-24 text-xs rounded-md bg-white border-slate-200/90 h-9 shadow-2xs"
                        />
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 font-medium pointer-events-none select-none hidden sm:flex items-center gap-1">
                            <kbd class="bg-slate-100 border border-slate-300 text-slate-500 text-[9px] font-sans font-semibold px-1.5 py-0.5 rounded">Enter</kbd>
                        </span>
                    </div>

                    <!-- Filter Status Siklus -->
                    <div class="sm:col-span-6 lg:col-span-2">
                        <Select :model-value="selectedStatus" @update:model-value="onStatusChange">
                            <SelectTrigger class="w-full bg-white border-slate-200/90 rounded-md text-xs font-semibold text-slate-800 shadow-2xs h-9">
                                <SelectValue placeholder="Semua Status" />
                            </SelectTrigger>
                            <SelectContent class="rounded-lg shadow-xl border-slate-200 bg-white">
                                <SelectItem value="all" class="text-xs font-semibold text-slate-900">Semua Status ({{ statusCounts?.all ?? props.laporan.total }})</SelectItem>
                                <SelectItem value="diverifikasi" class="text-xs font-medium">Diverifikasi (Butuh Disposisi) ({{ statusCounts?.diverifikasi ?? 0 }})</SelectItem>
                                <SelectItem value="ditindaklanjuti" class="text-xs text-indigo-800 font-bold">Proses Lapangan UPT ({{ statusCounts?.ditindaklanjuti ?? 0 }})</SelectItem>
                                <SelectItem value="selesai" class="text-xs text-emerald-700 font-semibold">Selesai 100% ({{ statusCounts?.selesai ?? 0 }})</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Filter Kategori Laporan (Combobox Searchable) -->
                    <div class="sm:col-span-6 lg:col-span-3">
                        <Combobox
                            :options="kategoriComboboxOptions"
                            :model-value="selectedKategori"
                            @update:model-value="onKategoriChange"
                            placeholder="Pilih Kategori..."
                            search-placeholder="Cari kategori..."
                            class="w-full h-9 bg-white border-slate-200/90 text-xs font-semibold text-slate-800 shadow-2xs"
                        />
                    </div>

                    <!-- Filter Desa Binaan (Combobox Searchable) -->
                    <div class="sm:col-span-6 lg:col-span-3">
                        <Combobox
                            :options="desaComboboxOptions"
                            :model-value="selectedDesa"
                            @update:model-value="onDesaChange"
                            placeholder="Pilih Desa Binaan..."
                            search-placeholder="Cari desa binaan..."
                            class="w-full h-9 bg-white border-slate-200/90 text-xs font-semibold text-slate-800 shadow-2xs"
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

                                <th class="py-3.5 px-5">Seksi PJ & Intervensi</th>

                                <th class="py-3.5 px-5">Status Siklus</th>

                                <th class="py-3.5 px-5 text-center">Aksi Penanganan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <tr v-if="sortedLaporanData.length === 0">
                                <td colspan="7" class="py-14 text-center text-slate-400">
                                    <FileText :size="36" class="mx-auto mb-2 opacity-40" />
                                    <p class="font-bold text-slate-700 text-xs">Belum ada data disposisi & tindak lanjut UPT</p>
                                </td>
                            </tr>

                            <tr
                                v-for="row in sortedLaporanData"
                                :key="row.id"
                                :class="[
                                    'transition-all duration-150',
                                    row.status === 'diverifikasi'
                                        ? 'bg-blue-50/40 hover:bg-blue-100/50 border-l-4 border-l-blue-500'
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
                                    <span v-if="row.tindak_lanjut?.nomor_registrasi" class="text-[10px] font-sans tabular-nums text-indigo-700 font-semibold block pt-0.5">
                                        {{ row.tindak_lanjut.nomor_registrasi }}
                                    </span>
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

                                <td class="py-4 px-5">
                                    <div class="font-bold text-slate-900 uppercase">
                                        {{ getSeksiLabel(row.tindak_lanjut?.seksi_penanggung_jawab) }}
                                    </div>
                                    <span class="text-[11px] text-slate-500 capitalize block pt-0.5">
                                        {{ row.tindak_lanjut?.bentuk_intervensi?.replace('_', ' ') || '-' }}
                                    </span>
                                </td>

                                <td class="py-4 px-5 whitespace-nowrap">
                                    <span :class="['px-2.5 py-1 rounded-full text-[11px] font-semibold border', getStatusBadge(row.status)]">
                                        {{ getStatusLabel(row.status) }}
                                    </span>
                                </td>

                                <td class="py-4 px-5 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <Link :href="`/pimpasa/tindak-lanjut/${row.id}?from_tindak_lanjut=1`">
                                            <Button
                                                :class="[
                                                    'h-8 px-3 text-xs font-semibold rounded-md flex items-center gap-1.5 cursor-pointer transition-all shadow-2xs',
                                                    row.status === 'diverifikasi'
                                                        ? 'bg-primary hover:bg-[#04407D] text-primary-foreground font-bold'
                                                        : 'bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300'
                                                ]"
                                            >
                                                <FileEdit :size="13" />
                                                <span>{{ row.status === 'diverifikasi' ? 'Input Hasil Lapangan' : 'Detail Penanganan' }}</span>
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
                        dari <span class="font-bold text-slate-900">{{ laporan.total }}</span> Data Disposisi & Tindak Lanjut
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
