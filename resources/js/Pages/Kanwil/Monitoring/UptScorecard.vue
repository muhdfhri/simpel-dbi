<script setup lang="ts">
import { ref, computed } from 'vue';
import AppLayout from '@/components/layout/AppLayout.vue';
import {
    FileSpreadsheet,
    FileText,
    Search,
    RotateCcw,
    Building2,
    CheckCircle2,
    AlertTriangle,
    Users
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
// @ts-ignore
import UptComplianceScorecard from './Components/UptComplianceScorecard.vue';

interface ScorecardItem {
    id: number;
    nama: string;
    tipe: string;
    desa_count: number;
    pimpasa_count: number;
    total_laporan: number;
    laporan_selesai: number;
    breached_count: number;
    completion_rate: number;
    avg_sla_hours: number;
    status_kepatuhan: string;
}

const props = withDefaults(defineProps<{
    scorecards?: ScorecardItem[];
    uptScorecard?: ScorecardItem[];
}>(), {
    scorecards: () => [],
    uptScorecard: () => []
});

const activeScorecards = computed(() => {
    return props.scorecards && props.scorecards.length > 0
        ? props.scorecards
        : (props.uptScorecard || []);
});

// Filter States
const searchInput = ref<string>('');
const filterStatus = ref<string>('all');
const filterType = ref<string>('all');
const filterUpt = ref<string>('all');

const onUptChange = (val: any) => {
    filterUpt.value = String(val || 'all');
};

const onStatusChange = (val: any) => {
    filterStatus.value = String(val || 'all');
};

const uptComboboxOptions = computed(() => [
    { value: 'all', label: `Semua Satker UPT Imigrasi (${activeScorecards.value.length})` },
    ...activeScorecards.value.map(u => ({
        value: String(u.id),
        label: `${u.nama} (${u.total_laporan} Laporan)`
    }))
]);

// Compute Status Metrics Counts
const statusCounts = computed(() => {
    const list = activeScorecards.value;
    const counts = {
        all: list.length,
        sangat_baik: 0,
        cukup: 0,
        perlu_evaluasi: 0
    };
    list.forEach(item => {
        if (item.status_kepatuhan === 'SANGAT BAIK') counts.sangat_baik++;
        else if (item.status_kepatuhan === 'CUKUP') counts.cukup++;
        else if (item.status_kepatuhan === 'PERLU EVALUASI') counts.perlu_evaluasi++;
    });
    return counts;
});

const filterByStatus = (statusVal: string) => {
    filterStatus.value = statusVal;
};

const resetFilter = () => {
    searchInput.value = '';
    filterStatus.value = 'all';
    filterType.value = 'all';
    filterUpt.value = 'all';
};

// Filtered Scorecards Computation
const filteredScorecards = computed(() => {
    let list = [...activeScorecards.value];

    // UPT Filter
    if (filterUpt.value !== 'all') {
        list = list.filter(i => String(i.id) === filterUpt.value);
    }

    // Status Filter
    if (filterStatus.value !== 'all') {
        if (filterStatus.value === 'SANGAT BAIK') {
            list = list.filter(i => i.status_kepatuhan === 'SANGAT BAIK');
        } else if (filterStatus.value === 'CUKUP') {
            list = list.filter(i => i.status_kepatuhan === 'CUKUP');
        } else if (filterStatus.value === 'PERLU EVALUASI') {
            list = list.filter(i => i.status_kepatuhan === 'PERLU EVALUASI');
        }
    }

    // Type Filter
    if (filterType.value !== 'all') {
        list = list.filter(i => i.tipe === filterType.value);
    }

    // Search Query
    if (searchInput.value.trim() !== '') {
        const q = searchInput.value.toLowerCase().trim();
        list = list.filter(i =>
            i.nama.toLowerCase().includes(q) ||
            i.tipe.toLowerCase().includes(q)
        );
    }

    return list;
});

// Quick Metric Stats Calculations
const totalSatker = computed(() => activeScorecards.value.length);
const sangatBaikCount = computed(() => statusCounts.value.sangat_baik);
const perluEvaluasiCount = computed(() => statusCounts.value.perlu_evaluasi);
const totalDesa = computed(() => activeScorecards.value.reduce((acc, curr) => acc + (curr.desa_count || 0), 0));
const totalPimpasa = computed(() => activeScorecards.value.reduce((acc, curr) => acc + (curr.pimpasa_count || 0), 0));

// Declare Ziggy route global for TypeScript
declare const route: (name: string, params?: any) => string;

// Export Downloads
const exportExcel = () => {
    window.location.href = '/kanwil/monitoring/upt-scorecard/export-excel';
};

const exportPdf = () => {
    window.open('/kanwil/monitoring/upt-scorecard/export-pdf', '_blank');
};
</script>

<template>
    <AppLayout title="Scorecard Kepatuhan UPT">
        <div class="space-y-6 font-sans">
            
            <!-- Quick Metric KPI Cards (Matching Exact Kendali SLA & Eskalasi Style) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- Card 1: Total UPT Imigrasi -->
                <Card @click="filterByStatus('all')" class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group cursor-pointer hover:border-slate-400 transition-all">
                    <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-slate-300/80 transition-colors">
                        <Building2 :size="105" stroke-width="1.0" />
                    </div>

                    <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                        <div class="space-y-0.5 pr-12">
                            <span class="text-xs font-semibold text-slate-500 block leading-tight">Total UPT Imigrasi</span>
                            <div class="text-3xl font-bold text-slate-900 font-sans tabular-nums tracking-tight leading-none pt-1">
                                {{ totalSatker }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[11px] text-slate-500 font-medium">Satker Kerja Sumatera Utara</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Card 2: Kepatuhan Sangat Baik -->
                <Card @click="filterByStatus('SANGAT BAIK')" class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group cursor-pointer hover:border-emerald-400 transition-all">
                    <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-slate-300/80 transition-colors">
                        <CheckCircle2 :size="105" stroke-width="1.0" />
                    </div>

                    <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                        <div class="space-y-0.5 pr-12">
                            <span class="text-xs font-semibold text-slate-500 block leading-tight">Kepatuhan Sangat Baik</span>
                            <div class="text-3xl font-bold text-emerald-700 font-sans tabular-nums tracking-tight leading-none pt-1">
                                {{ sangatBaikCount }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[11px] text-slate-500 font-medium">
                                {{ totalSatker > 0 ? Math.round((sangatBaikCount / totalSatker) * 100) : 0 }}% Kepatuhan Tinggi
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Card 3: Perlu Evaluasi -->
                <Card @click="filterByStatus('PERLU EVALUASI')" class="border-red-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group cursor-pointer hover:border-red-400 transition-all">
                    <div class="absolute -right-4 -bottom-6 text-red-100/70 pointer-events-none group-hover:text-red-200/80 transition-colors">
                        <AlertTriangle :size="105" stroke-width="1.0" />
                    </div>

                    <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                        <div class="space-y-0.5 pr-12">
                            <span class="text-xs font-semibold text-red-600 block leading-tight">Perlu Evaluasi</span>
                            <div class="text-3xl font-bold text-red-700 font-sans tabular-nums tracking-tight leading-none pt-1">
                                {{ perluEvaluasiCount }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[11px] text-red-600 font-semibold">{{ perluEvaluasiCount }} UPT Butuh Perhatian</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Card 4: Total Desa & PIMPASA -->
                <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none">
                        <Users :size="105" stroke-width="1.0" />
                    </div>

                    <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                        <div class="space-y-0.5 pr-12">
                            <span class="text-xs font-semibold text-slate-500 block leading-tight">Total Desa & PIMPASA</span>
                            <div class="text-3xl font-bold text-slate-900 font-sans tabular-nums tracking-tight leading-none pt-1">
                                {{ totalDesa }} / {{ totalPimpasa }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[11px] text-slate-500 font-medium">Desa Binaan / Petugas PIMPASA</p>
                        </div>
                    </CardContent>
                </Card>

            </div>

            <!-- Main Data Table Container (Matching Exact Kendali SLA & Eskalasi Style) -->
            <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white overflow-hidden">
                
                <!-- Table Header Bar with Title & Export Actions -->
                <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">
                            Scorecard Kepatuhan & Kinerja UPT Imigrasi
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Evaluasi kepatuhan SLA, tingkat penyelesaian tiket, dan performa Satker UPT Imigrasi se-Sumatera Utara.
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

                <!-- Clean Filter Toolbar (Matching Kendali SLA Style) -->
                <div class="p-4 bg-slate-50/50 border-b border-slate-100 grid grid-cols-1 sm:grid-cols-12 gap-3">
                    
                    <!-- Input Search -->
                    <div class="sm:col-span-6 lg:col-span-3 relative">
                        <Search :size="15" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
                        <Input
                            type="text"
                            v-model="searchInput"
                            placeholder="Cari nama UPT Imigrasi..."
                            class="pl-9 pr-24 text-xs rounded-md bg-white border-slate-200/90 h-9 shadow-2xs"
                        />
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 font-medium pointer-events-none select-none hidden sm:flex items-center gap-1">
                            <kbd class="bg-slate-100 border border-slate-300 text-slate-500 text-[9px] font-sans font-semibold px-1.5 py-0.5 rounded">Enter</kbd>
                        </span>
                    </div>

                    <!-- Filter Status Kepatuhan -->
                    <div class="sm:col-span-6 lg:col-span-3">
                        <Select v-model="filterStatus">
                            <SelectTrigger class="w-full bg-white border-slate-200/90 rounded-md text-xs font-semibold text-slate-800 shadow-2xs h-9">
                                <SelectValue placeholder="Semua Status Kepatuhan" />
                            </SelectTrigger>
                            <SelectContent class="rounded-lg shadow-xl border-slate-200 bg-white">
                                <SelectGroup>
                                    <SelectItem value="all" class="text-xs font-semibold text-slate-900">
                                        Semua Status Kepatuhan ({{ statusCounts.all }})
                                    </SelectItem>
                                    <SelectItem value="SANGAT BAIK" class="text-xs text-emerald-700 font-bold">
                                        🟢 SANGAT BAIK ({{ statusCounts.sangat_baik }})
                                    </SelectItem>
                                    <SelectItem value="CUKUP" class="text-xs text-amber-800 font-bold">
                                        🟡 CUKUP ({{ statusCounts.cukup }})
                                    </SelectItem>
                                    <SelectItem value="PERLU EVALUASI" class="text-xs text-red-700 font-bold">
                                        🔴 PERLU EVALUASI ({{ statusCounts.perlu_evaluasi }})
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Combobox Filter Satker UPT (Searchable) -->
                    <div class="sm:col-span-6 lg:col-span-5">
                        <Combobox
                            :options="uptComboboxOptions"
                            :model-value="filterUpt"
                            @update:model-value="onUptChange"
                            placeholder="Semua Satker UPT Imigrasi..."
                            search-placeholder="Cari Kanim / UPT..."
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

                <!-- Scorecard Table Component -->
                <UptComplianceScorecard :scorecards="filteredScorecards" />

            </Card>

        </div>
    </AppLayout>
</template>
