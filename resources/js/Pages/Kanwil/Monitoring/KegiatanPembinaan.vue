<script setup lang="ts">
import { ref, computed } from 'vue';
import AppLayout from '@/components/layout/AppLayout.vue';
import {
    FileSpreadsheet,
    FileText,
    Search,
    FolderKanban,
    Users,
    Building2,
    Award,
    RotateCcw
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import Combobox from '@/components/ui/combobox/Combobox.vue';
import KegiatanPembinaanTable from './Components/KegiatanPembinaanTable.vue';

interface UptOption {
    id: number;
    nama: string;
}

interface KegiatanItem {
    id: number;
    judul: string;
    jenis_pembinaan: string;
    tanggal: string | null;
    tanggal_formatted: string;
    jumlah_peserta: number;
    status: string;
    lokasi: string;
    ringkasan_materi: string;
    desa_nama: string;
    upt_nama: string;
    pimpasa_nama: string;
    lampiran_count: number;
    lampiran_list: Array<{
        id: number;
        nama_file_asli: string;
        path: string;
        tipe_file: string;
        ukuran_bytes: number;
    }>;
}

const props = defineProps<{
    kpiMetrics: {
        total_kegiatan: number;
        total_peserta: number;
        total_desa_terjangkau: number;
        top_upt: string;
    };
    kegiatanList: KegiatanItem[];
    uptListOptions: UptOption[];
}>();

const searchInput = ref<string>('');
const filterUpt = ref<string>('all');
const filterJenis = ref<string>('all');

const filterByJenis = (jenisVal: string) => {
    filterJenis.value = jenisVal;
};

const resetFilter = () => {
    searchInput.value = '';
    filterUpt.value = 'all';
    filterJenis.value = 'all';
};

const filteredKegiatan = computed(() => {
    let list = [...props.kegiatanList];

    if (filterUpt.value !== 'all') {
        const targetUpt = props.uptListOptions.find(u => u.id.toString() === filterUpt.value);
        if (targetUpt) {
            list = list.filter(k => k.upt_nama.toLowerCase().includes(targetUpt.nama.toLowerCase()));
        }
    }

    if (filterJenis.value !== 'all') {
        list = list.filter(k => k.jenis_pembinaan.toLowerCase().includes(filterJenis.value.toLowerCase()));
    }

    if (searchInput.value.trim() !== '') {
        const q = searchInput.value.toLowerCase().trim();
        list = list.filter(k =>
            k.judul.toLowerCase().includes(q) ||
            k.desa_nama.toLowerCase().includes(q) ||
            k.upt_nama.toLowerCase().includes(q) ||
            k.pimpasa_nama.toLowerCase().includes(q) ||
            k.lokasi.toLowerCase().includes(q)
        );
    }

    return list;
});

const uptComboboxOptions = computed(() => {
    return [
        {
            value: 'all',
            label: `Semua Satker UPT Imigrasi (${props.kegiatanList.length})`,
        },
        ...props.uptListOptions.map(u => {
            const count = props.kegiatanList.filter(k => k.upt_nama.toLowerCase().includes(u.nama.toLowerCase())).length;
            return {
                value: u.id.toString(),
                label: `${u.nama} (${count})`,
            };
        })
    ];
});

const jenisComboboxOptions = computed(() => {
    return [
        {
            value: 'all',
            label: `Semua Jenis Pembinaan (${props.kegiatanList.length})`,
        },
        {
            value: 'Penyuluhan Hukum',
            label: `Penyuluhan Hukum (${props.kegiatanList.filter(k => k.jenis_pembinaan.includes('Penyuluhan')).length})`,
        },
        {
            value: 'Edukasi Paspor',
            label: `Edukasi Paspor & PMI (${props.kegiatanList.filter(k => k.jenis_pembinaan.includes('Edukasi') || k.jenis_pembinaan.includes('Paspor')).length})`,
        },
        {
            value: 'Pemantauan Wilayah',
            label: `Pemantauan Wilayah (${props.kegiatanList.filter(k => k.jenis_pembinaan.includes('Pemantauan')).length})`,
        },
        {
            value: 'Sosialisasi',
            label: `Sosialisasi & Rapat (${props.kegiatanList.filter(k => k.jenis_pembinaan.includes('Sosialisasi') || k.jenis_pembinaan.includes('Rapat')).length})`,
        },
    ];
});

const exportExcel = () => {
    const params = new URLSearchParams();
    if (filterUpt.value && filterUpt.value !== 'all') params.append('upt_id', filterUpt.value);
    if (filterJenis.value && filterJenis.value !== 'all') params.append('jenis', filterJenis.value);
    if (searchInput.value) params.append('search', searchInput.value);
    window.location.href = `/kanwil/monitoring/kegiatan-pembinaan/export-excel?${params.toString()}`;
};

const exportPdf = () => {
    const params = new URLSearchParams();
    if (filterUpt.value && filterUpt.value !== 'all') params.append('upt_id', filterUpt.value);
    if (filterJenis.value && filterJenis.value !== 'all') params.append('jenis', filterJenis.value);
    if (searchInput.value) params.append('search', searchInput.value);
    window.open(`/kanwil/monitoring/kegiatan-pembinaan/export-pdf?${params.toString()}`, '_blank');
};
</script>

<template>
    <AppLayout title="Monitoring Pembinaan Desa">
        <div class="space-y-6 font-sans">
            
            <!-- Quick Metric KPI Cards Grid (Matching Presisi Worklist & SLA Style) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- Card 1: Total Kegiatan Pembinaan -->
                <Card @click="filterByJenis('all')" class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group cursor-pointer hover:border-slate-400 transition-all">
                    <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-slate-300/80 transition-colors">
                        <FolderKanban :size="105" stroke-width="1.0" />
                    </div>

                    <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                        <div class="space-y-0.5 pr-12">
                            <span class="text-xs font-semibold text-slate-500 block leading-tight">Total Kegiatan Pembinaan</span>
                            <div class="text-3xl font-bold text-slate-900 font-sans tabular-nums tracking-tight leading-none pt-1">
                                {{ kpiMetrics.total_kegiatan }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[11px] text-slate-500 font-medium">Aktivitas PIMPASA se-Sumut</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Card 2: Total Peserta Terliterasi -->
                <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group hover:border-blue-400 transition-all">
                    <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-blue-200/80 transition-colors">
                        <Users :size="105" stroke-width="1.0" />
                    </div>

                    <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                        <div class="space-y-0.5 pr-12">
                            <span class="text-xs font-semibold text-slate-500 block leading-tight">Warga Terliterasi</span>
                            <div class="text-3xl font-bold text-blue-700 font-sans tabular-nums tracking-tight leading-none pt-1">
                                {{ kpiMetrics.total_peserta }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[11px] text-slate-500 font-medium">Masyarakat desa teredukasi</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Card 3: Desa Terjangkau -->
                <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group hover:border-emerald-400 transition-all">
                    <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-emerald-200/80 transition-colors">
                        <Building2 :size="105" stroke-width="1.0" />
                    </div>

                    <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                        <div class="space-y-0.5 pr-12">
                            <span class="text-xs font-semibold text-slate-500 block leading-tight">Desa Binaan Terjangkau</span>
                            <div class="text-3xl font-bold text-emerald-700 font-sans tabular-nums tracking-tight leading-none pt-1">
                                {{ kpiMetrics.total_desa_terjangkau }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[11px] text-slate-500 font-medium">Sudah mendapatkan pembinaan</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Card 4: Top UPT Pembina -->
                <Card class="border-amber-300/80 shadow-2xs rounded-lg bg-amber-50/30 relative overflow-hidden group hover:border-amber-500 transition-all">
                    <div class="absolute -right-4 -bottom-6 text-amber-200/80 pointer-events-none group-hover:text-amber-300/80 transition-colors">
                        <Award :size="105" stroke-width="1.0" />
                    </div>

                    <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                        <div class="space-y-0.5 pr-12">
                            <span class="text-xs font-bold text-amber-900 block leading-tight">UPT Pembina Teraktif</span>
                            <div class="text-lg font-bold text-amber-900 font-sans tracking-tight leading-snug pt-1 truncate">
                                {{ kpiMetrics.top_upt }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[11px] text-amber-800 font-semibold">Frekuensi kegiatan terbanyak</p>
                        </div>
                    </CardContent>
                </Card>

            </div>

            <!-- Main Data Table Container -->
            <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white overflow-hidden">
                
                <!-- Table Header Bar -->
                <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">
                            Monitoring & Rekapitulasi Pembinaan Desa Binaan Imigrasi
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Pengawasan aktivitas sosialisasi, penyuluhan hukum, dan pembinaan wilayah oleh Petugas PIMPASA se-Sumut.
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

                <!-- Clean Filter Toolbar -->
                <div class="p-4 bg-slate-50/50 border-b border-slate-100 grid grid-cols-1 sm:grid-cols-12 gap-3">
                    
                    <!-- Search Input -->
                    <div class="sm:col-span-6 lg:col-span-4 relative">
                        <Search :size="15" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
                        <Input
                            type="text"
                            v-model="searchInput"
                            placeholder="Cari judul, PIMPASA, desa, UPT..."
                            class="pl-9 pr-24 text-xs rounded-md bg-white border-slate-200/90 h-9 shadow-2xs"
                        />
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 font-medium pointer-events-none select-none hidden sm:flex items-center gap-1">
                            <kbd class="bg-slate-100 border border-slate-300 text-slate-500 text-[9px] font-sans font-semibold px-1.5 py-0.5 rounded">Enter</kbd>
                        </span>
                    </div>

                    <!-- Filter Satker UPT (Searchable Combobox) -->
                    <div class="sm:col-span-6 lg:col-span-4">
                        <Combobox
                            v-model="filterUpt"
                            :options="uptComboboxOptions"
                            placeholder="Semua Satker UPT Imigrasi"
                            searchPlaceholder="Cari Kanim / UPT..."
                            class="w-full h-9 bg-white border-slate-200/90 rounded-md text-xs font-semibold text-slate-800 shadow-2xs"
                        />
                    </div>

                    <!-- Filter Jenis Pembinaan (Searchable Combobox) -->
                    <div class="sm:col-span-6 lg:col-span-3">
                        <Combobox
                            v-model="filterJenis"
                            :options="jenisComboboxOptions"
                            placeholder="Semua Jenis Pembinaan"
                            searchPlaceholder="Cari Jenis Pembinaan..."
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

                <!-- Table Component -->
                <KegiatanPembinaanTable :kegiatan-list="filteredKegiatan" />

            </Card>

        </div>
    </AppLayout>
</template>
