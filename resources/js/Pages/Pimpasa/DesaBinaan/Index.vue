<script setup lang="ts">
import { ref, computed } from 'vue';
import AppLayout from '@/components/layout/AppLayout.vue';
import {
    Building2,
    MapPin,
    Phone,
    UserCheck,
    Mail,
    FileText,
    FileSpreadsheet,
    Search,
    RotateCcw,
    ArrowUpDown,
    ArrowUp,
    ArrowDown,
    ChevronLeft,
    ChevronRight,
    Users,
    Contact,
    Shield
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent } from '@/components/ui/card';
import Combobox from '@/components/ui/combobox/Combobox.vue';

interface DesaItem {
    id: number;
    nama: string;
    kabupaten: string;
    kode_desa: string;
    kepala_desa: string;
    kontak: string;
    pimpasa_name: string;
    upt_nama: string;
}

const props = defineProps<{
    desaList: DesaItem[];
}>();

const searchInput = ref('');
const selectedDesa = ref('all');

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

const desaComboboxOptions = computed(() => [
    { value: 'all', label: `Semua Desa Binaan (${props.desaList.length})` },
    ...props.desaList.map(d => ({
        value: String(d.id),
        label: `${d.nama} (${d.kabupaten})`
    }))
]);

const filteredDesaList = computed(() => {
    let result = [...props.desaList];

    // Search filter
    if (searchInput.value.trim() !== '') {
        const query = searchInput.value.toLowerCase();
        result = result.filter(d =>
            d.nama.toLowerCase().includes(query) ||
            d.kode_desa.toLowerCase().includes(query) ||
            d.kabupaten.toLowerCase().includes(query) ||
            d.kepala_desa.toLowerCase().includes(query) ||
            d.pimpasa_name.toLowerCase().includes(query)
        );
    }

    // Desa filter
    if (selectedDesa.value !== 'all') {
        result = result.filter(d => String(d.id) === selectedDesa.value);
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
    currentPage.value = 1;
};

// Metric stats
const totalDesa = computed(() => props.desaList.length);
const totalKabupaten = computed(() => new Set(props.desaList.map(d => d.kabupaten)).size);
const totalPimpasa = computed(() => new Set(props.desaList.map(d => d.pimpasa_name)).size);

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
            
            <!-- Quick Metric KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                
                <!-- Card 1: Total Wilayah Desa Binaan -->
                <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none">
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

                <!-- Card 2: Total Kabupaten/Kota Terjangkau -->
                <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none">
                        <MapPin :size="105" stroke-width="1.0" />
                    </div>

                    <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                        <div class="space-y-0.5 pr-12">
                            <span class="text-xs font-semibold text-slate-500 block leading-tight">Sebaran Kabupaten/Kota</span>
                            <div class="text-3xl font-bold text-blue-700 font-sans tabular-nums tracking-tight leading-none pt-1">
                                {{ totalKabupaten }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[11px] text-slate-500 font-medium">Kabupaten/Kota dalam wilayah UPT</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Card 3: Petugas PIMPASA Pengampu -->
                <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none">
                        <UserCheck :size="105" stroke-width="1.0" />
                    </div>

                    <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                        <div class="space-y-0.5 pr-12">
                            <span class="text-xs font-semibold text-slate-500 block leading-tight">Petugas PIMPASA Pembina</span>
                            <div class="text-3xl font-bold text-emerald-700 font-sans tabular-nums tracking-tight leading-none pt-1">
                                {{ totalPimpasa }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[11px] text-slate-500 font-medium">Petugas pengampu lapangan</p>
                        </div>
                    </CardContent>
                </Card>

            </div>

            <!-- Main Card Section: Data Table -->
            <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white overflow-hidden">
                
                <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">
                            Direktori Master Data & Profil Desa Binaan UPT Imigrasi
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Direktori resmi kontak Perangkat Desa terdaftar dan Petugas PIMPASA pengampu wilayah.
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

                <!-- Toolbar Filter (Clean 2-Tier Split Layout) -->
                <div class="p-4 bg-slate-50/60 border-b border-slate-100 space-y-3">
                    
                    <!-- Row 1: Search Bar Utama & Reset Button -->
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                        <div class="relative w-full flex-1">
                            <Search :size="15" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
                            <Input
                                type="text"
                                v-model="searchInput"
                                placeholder="Cari desa/kelurahan, kabupaten/kota, perangkat desa..."
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

                    <!-- Row 2: Filter Column for Desa Binaan -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        
                        <!-- Col 1: Pilih Desa Binaan -->
                        <div class="space-y-1 sm:col-span-2">
                            <label class="text-[11px] font-bold text-slate-500 flex items-center gap-1">
                                <Building2 :size="12" class="text-slate-400 shrink-0" /> Desa Binaan UPT
                            </label>
                            <Combobox
                                :options="desaComboboxOptions"
                                :model-value="selectedDesa"
                                @update:model-value="onDesaChange"
                                placeholder="Pilih Desa Binaan..."
                                search-placeholder="Cari nama desa binaan..."
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

                                <th @click="handleSort('nama')" class="py-3.5 px-5 cursor-pointer hover:bg-slate-200/60 transition-colors">
                                    <div class="flex items-center gap-1.5">
                                        <span>Nama Desa Binaan</span>
                                        <ArrowUp v-if="sortField === 'nama' && sortDirection === 'asc'" :size="12" class="text-slate-900" />
                                        <ArrowDown v-else-if="sortField === 'nama' && sortDirection === 'desc'" :size="12" class="text-slate-900" />
                                        <ArrowUpDown v-else :size="12" class="text-slate-300" />
                                    </div>
                                </th>

                                <th @click="handleSort('kabupaten')" class="py-3.5 px-5 cursor-pointer hover:bg-slate-200/60 transition-colors">
                                    <div class="flex items-center gap-1.5">
                                        <span>Kabupaten / Kota</span>
                                        <ArrowUp v-if="sortField === 'kabupaten' && sortDirection === 'asc'" :size="12" class="text-slate-900" />
                                        <ArrowDown v-else-if="sortField === 'kabupaten' && sortDirection === 'desc'" :size="12" class="text-slate-900" />
                                        <ArrowUpDown v-else :size="12" class="text-slate-300" />
                                    </div>
                                </th>

                                <th class="py-3.5 px-5">Perangkat Desa / Kontak Resmi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <tr v-if="filteredDesaList.length === 0">
                                <td colspan="4" class="py-14 text-center text-slate-400">
                                    <Building2 :size="36" class="mx-auto mb-2 opacity-40" />
                                    <p class="font-bold text-slate-700 text-xs">Belum ada data desa binaan</p>
                                </td>
                            </tr>

                            <tr
                                v-for="desa in paginatedDesaList"
                                :key="desa.id"
                                class="hover:bg-slate-50/70 transition-all duration-150"
                            >
                                <!-- Kolom No -->
                                <td class="py-4 px-4 text-center font-sans tabular-nums text-slate-900 font-bold">
                                    {{ desa.id }}
                                </td>

                                <!-- Kolom Nama Desa -->
                                <td class="py-4 px-5">
                                    <span class="text-xs font-bold text-slate-900 block">
                                        {{ desa.nama }}
                                    </span>
                                </td>

                                <!-- Kolom Kabupaten / Kota -->
                                <td class="py-4 px-5">
                                    <div class="font-semibold text-slate-800 flex items-center gap-1.5">
                                        <MapPin :size="13" class="text-slate-400 shrink-0" />
                                        <span>{{ desa.kabupaten }}</span>
                                    </div>
                                </td>

                                <!-- Kolom Perangkat Desa & Kontak -->
                                <td class="py-4 px-5">
                                     <div class="font-bold text-slate-900">
                                         {{ desa.kepala_desa }}
                                     </div>
                                     <div class="flex items-center gap-2 pt-1">
                                         <a
                                             v-if="desa.kontak"
                                             :href="desa.kontak.includes('@') ? 'mailto:' + desa.kontak : 'https://wa.me/' + desa.kontak.replace(/\D/g,'')"
                                             target="_blank"
                                             class="inline-flex items-center gap-1 text-[11px] text-blue-700 hover:underline font-medium bg-blue-50 px-2 py-0.5 rounded border border-blue-200/80"
                                         >
                                             <Mail v-if="desa.kontak.includes('@')" :size="11" />
                                             <Phone v-else :size="11" />
                                             <span>{{ desa.kontak }}</span>
                                         </a>
                                         <span v-else class="text-[11px] text-slate-400 italic">Belum ada kontak</span>
                                     </div>
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

