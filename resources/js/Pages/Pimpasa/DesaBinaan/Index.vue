<script setup lang="ts">
import { ref, computed } from 'vue';
import AppLayout from '@/components/layout/AppLayout.vue';
import {
    Building2,
    MapPin,
    Phone,
    UserCheck,
    ShieldAlert,
    FileText,
    FileSpreadsheet,
    CheckCircle2,
    Search,
    RotateCcw,
    ArrowUpDown,
    ArrowUp,
    ArrowDown,
    AlertCircle,
    Eye,
    ShieldCheck,
    ChevronLeft,
    ChevronRight
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

interface DesaItem {
    id: number;
    nama: string;
    kecamatan: string;
    kabupaten: string;
    kode_desa: string;
    kepala_desa: string;
    kontak: string;
    pimpasa_name: string;
    total_laporan: number;
    laporan_selesai: number;
    laporan_aduan: number;
    indeks_kerawanan: string;
    upt_nama: string;
}

const props = defineProps<{
    desaList: DesaItem[];
}>();

const searchInput = ref('');
const selectedDesa = ref('all');
const selectedKerawanan = ref('all');

const sortField = ref<string>('id');
const sortDirection = ref<'asc' | 'desc'>('asc');

const handleSort = (field: string) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }
};

const onDesaChange = (val: any) => {
    selectedDesa.value = String(val || 'all');
    currentPage.value = 1;
};

const onKerawananChange = (val: any) => {
    selectedKerawanan.value = String(val || 'all');
    currentPage.value = 1;
};

const desaComboboxOptions = computed(() => [
    { value: 'all', label: `Semua Desa Binaan (${props.desaList.length})` },
    ...props.desaList.map(d => ({
        value: String(d.id),
        label: `${d.nama} (${d.total_laporan} Laporan)`
    }))
]);

const kerawananComboboxOptions = computed(() => [
    { value: 'all', label: `Semua Status Kerawanan (${totalDesa.value})` },
    { value: 'rendah', label: `Kondusif / Aman (${desaAman.value})` },
    { value: 'sedang', label: `Pembinaan Aktif (${desaSedang.value})` },
    { value: 'tinggi', label: `Rentan / Laporan Aktif (${desaRentan.value})` },
]);

const filteredDesaList = computed(() => {
    let result = [...props.desaList];

    // Search filter
    if (searchInput.value.trim() !== '') {
        const query = searchInput.value.toLowerCase();
        result = result.filter(d =>
            d.nama.toLowerCase().includes(query) ||
            d.kode_desa.toLowerCase().includes(query) ||
            d.kecamatan.toLowerCase().includes(query) ||
            d.kepala_desa.toLowerCase().includes(query)
        );
    }

    // Desa filter
    if (selectedDesa.value !== 'all') {
        result = result.filter(d => String(d.id) === selectedDesa.value);
    }

    // Kerawanan filter
    if (selectedKerawanan.value !== 'all') {
        result = result.filter(d => d.indeks_kerawanan === selectedKerawanan.value);
    }

    // Sort
    const field = sortField.value;
    const dir = sortDirection.value === 'asc' ? 1 : -1;

    return result.sort((a, b) => {
        let valA = (a as any)[field] || '';
        let valB = (b as any)[field] || '';

        if (typeof valA === 'string') valA = valA.toLowerCase();
        if (typeof valB === 'string') valB = valB.toLowerCase();

        if (valA < valB) return -1 * dir;
        if (valA > valB) return 1 * dir;
        return 0;
    });
});

// Pagination (50 Data Per Halaman)
const currentPage = ref(1);
const perPage = ref(50);

const paginatedDesaList = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    const end = start + perPage.value;
    return filteredDesaList.value.slice(start, end);
});

const totalPages = computed(() => Math.ceil(filteredDesaList.value.length / perPage.value) || 1);

const prevPage = () => {
    if (currentPage.value > 1) currentPage.value--;
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) currentPage.value++;
};

const resetFilter = () => {
    searchInput.value = '';
    selectedDesa.value = 'all';
    selectedKerawanan.value = 'all';
    currentPage.value = 1;
};

const filterByKerawananCard = (val: string) => {
    selectedKerawanan.value = val;
    currentPage.value = 1;
};

const getKerawananBadge = (level: string) => {
    switch (level) {
        case 'tinggi': return 'bg-red-50 text-red-700 border-red-200/80 font-bold';
        case 'sedang': return 'bg-amber-50 text-amber-800 border-amber-200/80 font-bold';
        default: return 'bg-emerald-50 text-emerald-800 border-emerald-200/80 font-semibold';
    }
};

const getKerawananLabel = (level: string) => {
    switch (level) {
        case 'tinggi': return 'Rentan / Aduan';
        case 'sedang': return 'Pembinaan Aktif';
        default: return 'Kondusif / Aman';
    }
};

// Metric stats
const totalDesa = computed(() => props.desaList.length);
const desaAman = computed(() => props.desaList.filter(d => d.indeks_kerawanan === 'rendah').length);
const desaSedang = computed(() => props.desaList.filter(d => d.indeks_kerawanan === 'sedang').length);
const desaRentan = computed(() => props.desaList.filter(d => d.indeks_kerawanan === 'tinggi').length);

const exportExcel = () => {
    window.location.href = '/pimpasa/desa-binaan/export-excel';
};

const exportPdf = () => {
    window.open('/pimpasa/desa-binaan/export-pdf', '_blank');
};
</script>

<template>
    <AppLayout title="Direktori & Profil Desa Binaan UPT">
        <div class="space-y-6 font-sans">
            
            <!-- Quick Metric KPI Cards (Matching Presisi Worklist Verifikasi & Tindak Lanjut) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- Card 1: Total Wilayah Desa Binaan -->
                <Card @click="filterByKerawananCard('all')" class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group cursor-pointer hover:border-slate-400 transition-all">
                    <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-slate-300/80 transition-colors">
                        <Building2 :size="105" stroke-width="1.0" />
                    </div>

                    <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                        <div class="space-y-0.5 pr-12">
                            <span class="text-xs font-semibold text-slate-500 block leading-tight">Total Desa Binaan UPT</span>
                            <div class="text-3xl font-bold text-slate-900 font-sans tabular-nums tracking-tight leading-none pt-1">
                                {{ totalDesa }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[11px] text-slate-500 font-medium">Wilayah pengampuan UPT</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Card 2: Kondusif / Aman -->
                <Card @click="filterByKerawananCard('rendah')" class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group cursor-pointer hover:border-emerald-400 transition-all">
                    <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-slate-300/80 transition-colors">
                        <ShieldCheck :size="105" stroke-width="1.0" />
                    </div>

                    <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                        <div class="space-y-0.5 pr-12">
                            <span class="text-xs font-semibold text-slate-500 block leading-tight">Kondusif & Aman</span>
                            <div class="text-3xl font-bold text-emerald-700 font-sans tabular-nums tracking-tight leading-none pt-1">
                                {{ desaAman }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[11px] text-slate-500 font-medium">Insiden keimigrasian terkendali</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Card 3: Pembinaan Aktif -->
                <Card @click="filterByKerawananCard('sedang')" class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group cursor-pointer hover:border-amber-400 transition-all">
                    <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-slate-300/80 transition-colors">
                        <UserCheck :size="105" stroke-width="1.0" />
                    </div>

                    <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                        <div class="space-y-0.5 pr-12">
                            <span class="text-xs font-semibold text-slate-500 block leading-tight">Pembinaan Aktif</span>
                            <div class="text-3xl font-bold text-amber-700 font-sans tabular-nums tracking-tight leading-none pt-1">
                                {{ desaSedang }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[11px] text-slate-500 font-medium">Edukasi & sosialisasi rutin</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Card 4: Rentan / Aduan Kritis -->
                <Card @click="filterByKerawananCard('tinggi')" class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group cursor-pointer hover:border-red-400 transition-all">
                    <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-slate-300/80 transition-colors">
                        <ShieldAlert :size="105" stroke-width="1.0" />
                    </div>

                    <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                        <div class="space-y-0.5 pr-12">
                            <span class="text-xs font-bold text-red-900 block leading-tight">Rentan / Laporan Aktif</span>
                            <div class="text-3xl font-bold text-red-700 font-sans tabular-nums tracking-tight leading-none pt-1">
                                {{ desaRentan }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[11px] text-red-600 font-semibold">Butuh perhatian & pantauan UPT</p>
                        </div>
                    </CardContent>
                </Card>

            </div>

            <!-- Main Card Section: Data Table -->
            <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white overflow-hidden">
                
                <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">
                            Direktori & Profil Desa Binaan UPT Imigrasi
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Pemantauan seluruh desa pengampuan, perangkat desa terdaftar, dan akumulasi laporan keimigrasian.
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

                <!-- Toolbar Filter -->
                <div class="p-4 bg-slate-50/50 border-b border-slate-100 grid grid-cols-1 sm:grid-cols-12 gap-3">
                    
                    <!-- Search Input -->
                    <div class="sm:col-span-6 lg:col-span-3 relative">
                        <Search :size="15" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
                        <Input
                            type="text"
                            v-model="searchInput"
                            placeholder="Cari desa, kec, kades..."
                            class="pl-9 pr-24 text-xs rounded-md bg-white border-slate-200/90 h-9 shadow-2xs"
                        />
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 font-medium pointer-events-none select-none hidden sm:flex items-center gap-1">
                            <kbd class="bg-slate-100 border border-slate-300 text-slate-500 text-[9px] font-sans font-semibold px-1.5 py-0.5 rounded">Enter</kbd>
                        </span>
                    </div>

                    <!-- Filter Status Kerawanan -->
                    <div class="sm:col-span-6 lg:col-span-3">
                        <Select v-model="selectedKerawanan" @update:model-value="onKerawananChange">
                            <SelectTrigger class="w-full bg-white border-slate-200/90 rounded-md text-xs font-semibold text-slate-800 shadow-2xs h-9">
                                <SelectValue placeholder="Semua Status Kerawanan" />
                            </SelectTrigger>
                            <SelectContent class="rounded-lg shadow-xl border-slate-200 bg-white">
                                <SelectItem value="all" class="text-xs font-semibold text-slate-900">Semua Status Kerawanan ({{ totalDesa }})</SelectItem>
                                <SelectItem value="rendah" class="text-xs text-emerald-700 font-semibold">Kondusif / Aman ({{ desaAman }})</SelectItem>
                                <SelectItem value="sedang" class="text-xs text-amber-700 font-semibold">Pembinaan Aktif ({{ desaSedang }})</SelectItem>
                                <SelectItem value="tinggi" class="text-xs text-red-700 font-bold">Rentan / Laporan Aktif ({{ desaRentan }})</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Combobox Filter Desa Binaan (Searchable) -->
                    <div class="sm:col-span-6 lg:col-span-5">
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

                                <th @click="handleSort('kode_desa')" class="py-3.5 px-5 cursor-pointer hover:bg-slate-200/60 transition-colors">
                                    <div class="flex items-center gap-1.5">
                                        <span>Kode & Nama Desa</span>
                                        <ArrowUp v-if="sortField === 'kode_desa' && sortDirection === 'asc'" :size="12" class="text-slate-900" />
                                        <ArrowDown v-else-if="sortField === 'kode_desa' && sortDirection === 'desc'" :size="12" class="text-slate-900" />
                                        <ArrowUpDown v-else :size="12" class="text-slate-300" />
                                    </div>
                                </th>

                                <th @click="handleSort('kecamatan')" class="py-3.5 px-5 cursor-pointer hover:bg-slate-200/60 transition-colors">
                                    <div class="flex items-center gap-1.5">
                                        <span>Wilayah Administratif</span>
                                        <ArrowUp v-if="sortField === 'kecamatan' && sortDirection === 'asc'" :size="12" class="text-slate-900" />
                                        <ArrowDown v-else-if="sortField === 'kecamatan' && sortDirection === 'desc'" :size="12" class="text-slate-900" />
                                        <ArrowUpDown v-else :size="12" class="text-slate-300" />
                                    </div>
                                </th>

                                <th class="py-3.5 px-5">Perangkat Desa / Kontak</th>

                                <th @click="handleSort('total_laporan')" class="py-3.5 px-5 cursor-pointer hover:bg-slate-200/60 transition-colors text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <span>Total Laporan</span>
                                        <ArrowUp v-if="sortField === 'total_laporan' && sortDirection === 'asc'" :size="12" class="text-slate-900" />
                                        <ArrowDown v-else-if="sortField === 'total_laporan' && sortDirection === 'desc'" :size="12" class="text-slate-900" />
                                        <ArrowUpDown v-else :size="12" class="text-slate-300" />
                                    </div>
                                </th>

                                <th class="py-3.5 px-5">Status Kerawanan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <tr v-if="filteredDesaList.length === 0">
                                <td colspan="6" class="py-14 text-center text-slate-400">
                                    <Building2 :size="36" class="mx-auto mb-2 opacity-40" />
                                    <p class="font-bold text-slate-700 text-xs">Belum ada data desa binaan</p>
                                </td>
                            </tr>

                            <tr
                                v-for="desa in paginatedDesaList"
                                :key="desa.id"
                                :class="[
                                    'transition-all duration-150',
                                    desa.indeks_kerawanan === 'tinggi'
                                        ? 'bg-red-50/40 hover:bg-red-100/50 border-l-4 border-l-red-500'
                                        : 'hover:bg-slate-50/70'
                                ]"
                            >
                                <!-- Kolom No -->
                                <td class="py-4 px-4 text-center font-sans tabular-nums text-slate-900 font-bold">
                                    {{ desa.id }}
                                </td>

                                <td class="py-4 px-5">
                                    <div class="font-sans tabular-nums text-xs font-semibold text-slate-600">
                                        {{ desa.kode_desa }}
                                    </div>
                                    <span class="text-xs font-bold text-slate-900 block pt-0.5">
                                        {{ desa.nama }}
                                    </span>
                                </td>

                                <td class="py-4 px-5">
                                    <div class="font-semibold text-slate-800">
                                        Kec. {{ desa.kecamatan }}
                                    </div>
                                    <span class="text-[11px] text-slate-500 font-medium block pt-0.5">
                                        {{ desa.kabupaten }}
                                    </span>
                                </td>

                                <td class="py-4 px-5">
                                    <div class="font-bold text-slate-900">
                                        {{ desa.kepala_desa }}
                                    </div>
                                    <span class="text-[11px] text-slate-500 font-sans tabular-nums font-medium block pt-0.5">
                                        {{ desa.kontak }}
                                    </span>
                                </td>

                                <td class="py-4 px-5 text-center">
                                    <span class="font-sans tabular-nums font-bold text-slate-900 text-sm bg-slate-100 px-2.5 py-1 rounded-md border border-slate-200/80 inline-block">
                                        {{ desa.total_laporan }} Tiket
                                    </span>
                                    <span v-if="desa.laporan_selesai > 0" class="text-[10px] text-emerald-700 font-semibold block pt-1">
                                        {{ desa.laporan_selesai }} Selesai
                                    </span>
                                </td>

                                <td class="py-4 px-5 whitespace-nowrap">
                                    <span :class="['px-2.5 py-1 rounded-full text-[11px] font-semibold border inline-block', getKerawananBadge(desa.indeks_kerawanan)]">
                                        {{ getKerawananLabel(desa.indeks_kerawanan) }}
                                    </span>
                                </td>

                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Integrated Pagination Bar (50 Data Per Halaman) -->
                <div v-if="filteredDesaList.length > 0" class="p-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
                    <div>
                        Menampilkan <span class="font-bold text-slate-900">{{ (currentPage - 1) * perPage + 1 }}</span>
                        sampai <span class="font-bold text-slate-900">{{ Math.min(currentPage * perPage, filteredDesaList.length) }}</span>
                        dari <span class="font-bold text-slate-900">{{ filteredDesaList.length }}</span> Desa Binaan
                    </div>

                    <div v-if="totalPages > 1" class="flex items-center gap-1.5">
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="currentPage === 1"
                            @click="prevPage"
                            class="h-8 px-2.5 text-xs font-semibold rounded-md border-slate-200"
                        >
                            Sebelumnya
                        </Button>

                        <span class="px-2 font-semibold text-slate-700">Halaman {{ currentPage }} dari {{ totalPages }}</span>

                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="currentPage === totalPages"
                            @click="nextPage"
                            class="h-8 px-2.5 text-xs font-semibold rounded-md border-slate-200"
                        >
                            Selanjutnya
                        </Button>
                    </div>
                </div>

            </Card>

        </div>
    </AppLayout>
</template>

