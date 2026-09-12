<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/components/layout/AppLayout.vue';
import {
    FileSpreadsheet,
    FileText,
    Search,
    Clock,
    AlertTriangle,
    CheckCircle2,
    RotateCcw,
    ShieldAlert,
    Calendar,
    Filter,
    Building2
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
import SlaControlTable from './Components/SlaControlTable.vue';

interface UptOption {
    id: number;
    nama: string;
}

interface SlaIncident {
    id: number;
    nomor_tiket: string;
    judul: string;
    kategori: string;
    desa_nama: string;
    upt_nama: string;
    pelapor_nama: string;
    status: string;
    created_at_formatted: string;
    hours_elapsed: number;
    hours_remaining: number;
    sla_status: 'terlambat' | 'peringatan' | 'tepat_waktu' | 'breached' | 'warning' | 'on_track';
    jumlah_teguran?: number;
}

const props = defineProps<{
    kpiData: any;
    slaIncidents: SlaIncident[];
    uptListOptions: UptOption[];
    filters?: {
        search?: string;
        upt_id?: string;
        sla_status?: string;
        tanggal_mulai?: string;
        tanggal_selesai?: string;
    };
}>();

const searchInput = ref<string>(props.filters?.search || '');
const filterUpt = ref<string>(props.filters?.upt_id || 'all');
const filterSlaStatus = ref<string>(props.filters?.sla_status || 'all');
const tanggalMulai = ref<string>(props.filters?.tanggal_mulai || '');
const tanggalSelesai = ref<string>(props.filters?.tanggal_selesai || '');

const normalizedIncidents = computed(() => {
    return props.slaIncidents.map(inc => {
        let statusKey = inc.sla_status;
        if (statusKey === 'breached') statusKey = 'terlambat';
        if (statusKey === 'warning') statusKey = 'peringatan';
        if (statusKey === 'on_track') statusKey = 'tepat_waktu';
        return {
            ...inc,
            sla_status: statusKey
        };
    });
});

const applyFilter = () => {
    const params: Record<string, string> = {};
    if (searchInput.value) params.search = searchInput.value;
    if (filterUpt.value && filterUpt.value !== 'all') params.upt_id = filterUpt.value;
    if (filterSlaStatus.value && filterSlaStatus.value !== 'all') params.sla_status = filterSlaStatus.value;
    if (tanggalMulai.value) params.tanggal_mulai = tanggalMulai.value;
    if (tanggalSelesai.value) params.tanggal_selesai = tanggalSelesai.value;

    router.get('/kanwil/monitoring/sla-control', params, { preserveState: true, replace: true });
};

const filterByStatus = (statusVal: string) => {
    filterSlaStatus.value = statusVal;
    applyFilter();
};

const resetFilter = () => {
    searchInput.value = '';
    filterUpt.value = 'all';
    filterSlaStatus.value = 'all';
    tanggalMulai.value = '';
    tanggalSelesai.value = '';
    applyFilter();
};

const filteredSlaIncidents = computed(() => {
    let list = [...normalizedIncidents.value];

    if (filterUpt.value !== 'all') {
        const targetUpt = props.uptListOptions.find(u => u.id.toString() === filterUpt.value);
        if (targetUpt) {
            list = list.filter(i => i.upt_nama.toLowerCase().includes(targetUpt.nama.toLowerCase()));
        }
    }

    if (filterSlaStatus.value !== 'all') {
        list = list.filter(i => i.sla_status === filterSlaStatus.value);
    }

    if (searchInput.value.trim() !== '') {
        const q = searchInput.value.toLowerCase().trim();
        list = list.filter(i =>
            i.nomor_tiket.toLowerCase().includes(q) ||
            i.judul.toLowerCase().includes(q) ||
            i.desa_nama.toLowerCase().includes(q) ||
            i.upt_nama.toLowerCase().includes(q) ||
            i.kategori.toLowerCase().includes(q)
        );
    }

    return list;
});

// Metric stats calculations
const totalIncidents = computed(() => normalizedIncidents.value.length);
const totalTerlambat = computed(() => normalizedIncidents.value.filter(i => i.sla_status === 'terlambat').length);
const totalPeringatan = computed(() => normalizedIncidents.value.filter(i => i.sla_status === 'peringatan').length);
const totalTepatWaktu = computed(() => normalizedIncidents.value.filter(i => i.sla_status === 'tepat_waktu').length);

const uptComboboxOptions = computed(() => {
    return [
        {
            value: 'all',
            label: `Semua Satker UPT Imigrasi (${totalIncidents.value})`,
        },
        ...props.uptListOptions.map(u => {
            const count = normalizedIncidents.value.filter(i => i.upt_nama.toLowerCase().includes(u.nama.toLowerCase())).length;
            return {
                value: u.id.toString(),
                label: `${u.nama} (${count})`,
            };
        })
    ];
});

const onUptChange = (val: any) => {
    filterUpt.value = String(val || 'all');
    applyFilter();
};

const exportExcel = () => {
    const params = new URLSearchParams();
    if (filterUpt.value && filterUpt.value !== 'all') params.append('upt_id', filterUpt.value);
    if (filterSlaStatus.value && filterSlaStatus.value !== 'all') params.append('sla_status', filterSlaStatus.value);
    if (searchInput.value) params.append('search', searchInput.value);
    if (tanggalMulai.value) params.append('tanggal_mulai', tanggalMulai.value);
    if (tanggalSelesai.value) params.append('tanggal_selesai', tanggalSelesai.value);
    window.location.href = `/kanwil/monitoring/sla-control/export-excel?${params.toString()}`;
};

const exportPdf = () => {
    const params = new URLSearchParams();
    if (filterUpt.value && filterUpt.value !== 'all') params.append('upt_id', filterUpt.value);
    if (filterSlaStatus.value && filterSlaStatus.value !== 'all') params.append('sla_status', filterSlaStatus.value);
    if (searchInput.value) params.append('search', searchInput.value);
    if (tanggalMulai.value) params.append('tanggal_mulai', tanggalMulai.value);
    if (tanggalSelesai.value) params.append('tanggal_selesai', tanggalSelesai.value);
    window.open(`/kanwil/monitoring/sla-control/export-pdf?${params.toString()}`, '_blank');
};
</script>

<template>
    <AppLayout title="Kendali SLA & Eskalasi">
        <div class="space-y-6 font-sans">
            
            <!-- Quick Metric KPI Cards (Matching Presisi Worklist Verifikasi PIMPASA) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- Card 1: Total Insiden SLA -->
                <Card @click="filterByStatus('all')" class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group cursor-pointer hover:border-slate-400 transition-all">
                    <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-slate-300/80 transition-colors">
                        <ShieldAlert :size="105" stroke-width="1.0" />
                    </div>

                    <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                        <div class="space-y-0.5 pr-12">
                            <span class="text-xs font-semibold text-slate-500 block leading-tight">Total Laporan Aktif</span>
                            <div class="text-3xl font-bold text-slate-900 font-sans tabular-nums tracking-tight leading-none pt-1">
                                {{ totalIncidents }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[11px] text-slate-500 font-medium">Dalam pemantauan SLA 24 jam</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Card 2: Terlambat (> 24 Jam) -->
                <Card @click="filterByStatus('terlambat')" class="border-red-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group cursor-pointer hover:border-red-400 transition-all">
                    <div class="absolute -right-4 -bottom-6 text-red-100/70 pointer-events-none group-hover:text-red-200/80 transition-colors">
                        <AlertTriangle :size="105" stroke-width="1.0" />
                    </div>

                    <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                        <div class="space-y-0.5 pr-12">
                            <span class="text-xs font-semibold text-red-600 block leading-tight">Terlambat (> 24 Jam)</span>
                            <div class="text-3xl font-bold text-red-700 font-sans tabular-nums tracking-tight leading-none pt-1">
                                {{ totalTerlambat }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[11px] text-red-600 font-semibold">Perlu teguran UPT Imigrasi</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Card 3: Peringatan (Sisa < 6 Jam) -->
                <Card @click="filterByStatus('peringatan')" class="border-amber-300/80 shadow-2xs rounded-lg bg-amber-50/30 relative overflow-hidden group cursor-pointer hover:border-amber-500 transition-all">
                    <div class="absolute -right-4 -bottom-6 text-amber-200/80 pointer-events-none group-hover:text-amber-300/80 transition-colors">
                        <Clock :size="105" stroke-width="1.0" />
                    </div>

                    <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                        <div class="space-y-0.5 pr-12">
                            <span class="text-xs font-bold text-amber-900 block leading-tight">Peringatan (Sisa &lt; 6 Jam)</span>
                            <div class="text-3xl font-bold text-amber-900 font-sans tabular-nums tracking-tight leading-none pt-1">
                                {{ totalPeringatan }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[11px] text-amber-800 font-semibold">Mendekati batas SLA 24 jam</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Card 4: Tepat Waktu (< 18 Jam) -->
                <Card @click="filterByStatus('tepat_waktu')" class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group cursor-pointer hover:border-emerald-400 transition-all">
                    <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-slate-300/80 transition-colors">
                        <CheckCircle2 :size="105" stroke-width="1.0" />
                    </div>

                    <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                        <div class="space-y-0.5 pr-12">
                            <span class="text-xs font-semibold text-slate-500 block leading-tight">Tepat Waktu</span>
                            <div class="text-3xl font-bold text-emerald-700 font-sans tabular-nums tracking-tight leading-none pt-1">
                                {{ totalTepatWaktu }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[11px] text-slate-500 font-medium">Penanganan berjalan lancar</p>
                        </div>
                    </CardContent>
                </Card>

            </div>

            <!-- Main Data Table Container (Matching Pimpasa Worklist Style) -->
            <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white overflow-hidden">
                
                <!-- Table Header Bar with Title & Export Actions -->
                <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">
                            Pusat Kendali SLA & Eskalasi Insiden (Standar 24 Jam)
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Pemantauan kepatuhan durasi penanganan operasional Satker UPT Imigrasi se-Sumatera Utara.
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
                                placeholder="Cari nomor tiket, judul, UPT..."
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

                    <!-- Row 2: 4 Filter Columns with Clear Labels (Dari Tanggal, Sampai Tanggal, Status SLA, Satker UPT Imigrasi) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        
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

                        <!-- Col 3: Filter Status SLA (Bahasa Indonesia) -->
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-500 flex items-center gap-1">
                                <Filter :size="12" class="text-slate-400 shrink-0" /> Status SLA
                            </label>
                            <Select v-model="filterSlaStatus" @update:model-value="applyFilter">
                                <SelectTrigger class="w-full bg-white border-slate-200/90 rounded-md text-xs font-semibold text-slate-800 shadow-2xs h-9">
                                    <SelectValue placeholder="Semua Status SLA" />
                                </SelectTrigger>
                                <SelectContent class="rounded-lg shadow-xl border-slate-200 bg-white">
                                    <SelectGroup>
                                        <SelectItem value="all" class="text-xs font-semibold text-slate-900">
                                            Semua Status SLA ({{ totalIncidents }})
                                        </SelectItem>
                                        <SelectItem value="terlambat" class="text-xs text-red-700 font-bold">
                                            🔴 Terlambat (> 24 Jam) ({{ totalTerlambat }})
                                        </SelectItem>
                                        <SelectItem value="peringatan" class="text-xs text-amber-800 font-bold">
                                            🟡 Peringatan (Sisa &lt; 6 Jam) ({{ totalPeringatan }})
                                        </SelectItem>
                                        <SelectItem value="tepat_waktu" class="text-xs text-emerald-700 font-semibold">
                                            🟢 Tepat Waktu ({{ totalTepatWaktu }})
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Col 4: Filter Satker UPT (Searchable Combobox) -->
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-500 flex items-center gap-1">
                                <Building2 :size="12" class="text-slate-400 shrink-0" /> Satker UPT Imigrasi
                            </label>
                            <Combobox
                                :model-value="filterUpt"
                                @update:model-value="onUptChange"
                                :options="uptComboboxOptions"
                                placeholder="Semua Satker UPT Imigrasi"
                                searchPlaceholder="Cari Kanim / UPT..."
                                class="w-full h-9 bg-white border-slate-200/90 rounded-md text-xs font-semibold text-slate-800 shadow-2xs"
                            />
                        </div>

                    </div>
                </div>

                <!-- SLA Control Table Component -->
                <SlaControlTable :incidents="filteredSlaIncidents" />

            </Card>

        </div>
    </AppLayout>
</template>
