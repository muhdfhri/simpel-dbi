<script setup lang="ts">
import { ref, computed } from 'vue';
import AppLayout from '@/components/layout/AppLayout.vue';
import {
    FileDown,
    FileSpreadsheet,
    Filter,
    RotateCcw,
    ShieldAlert,
    Building2,
    Calendar
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import KpiSummaryCards from './Components/KpiSummaryCards.vue';
import SlaControlTable from './Components/SlaControlTable.vue';
import UptComplianceScorecard from './Components/UptComplianceScorecard.vue';
import LiveActivityFeed from './Components/LiveActivityFeed.vue';
import { notify } from '@/lib/toast';

interface UptOption {
    id: number;
    nama: string;
}

const props = defineProps<{
    kpiData: any;
    uptScorecard: any[];
    slaIncidents: any[];
    activityFeed: any[];
    uptListOptions: UptOption[];
}>();

// Toolbar Filter States
const filterPeriode = ref<string>('bulan_ini');
const filterUpt = ref<string>('all');
const filterSlaStatus = ref<string>('all');

// Computed Filtered SLA Incidents
const filteredSlaIncidents = computed(() => {
    let list = [...props.slaIncidents];

    if (filterUpt.value !== 'all') {
        list = list.filter(i => i.upt_nama.toLowerCase().includes(props.uptListOptions.find(u => u.id.toString() === filterUpt.value)?.nama.toLowerCase() || ''));
    }

    if (filterSlaStatus.value !== 'all') {
        list = list.filter(i => i.sla_status === filterSlaStatus.value);
    }

    return list;
});

const exportPdfReport = () => {
    notify.info('Mengunduh Laporan PDF Eksekutif...', {
        description: 'Format Resmi Dinas Kanwil Ditjenim Sumut sedang dibuat.'
    });
};

const exportExcelReport = () => {
    notify.info('Mengunduh Rekapitulasi Excel...', {
        description: 'Seluruh data SLA & Kepatuhan Satker UPT sedang diekspor.'
    });
};
</script>

<template>
    <AppLayout title="Pusat Kendali & Executive Monitoring — Kanwil Sumut">
        <div class="space-y-5 font-sans">
            
            <!-- Executive Filter Toolbar & Action Buttons -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-2xs space-y-3">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    
                    <div>
                        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            Pusat Kendali & Executive Monitoring
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 uppercase tracking-wider font-mono">
                                Real-Time Provincial Center
                            </span>
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Pemantauan akuntabilitas SLA, kepatuhan 6 Satker UPT, dan penanganan kasus keimigrasian se-Sumut</p>
                    </div>

                    <!-- Right Controls: Export Actions -->
                    <div class="flex items-center gap-2.5">
                        <Button
                            @click="exportPdfReport"
                            variant="outline"
                            size="sm"
                            class="h-9 px-3.5 rounded-xl border-slate-300 text-xs font-bold gap-2 shrink-0 bg-white"
                        >
                            <FileDown :size="15" class="text-red-600" />
                            <span>Cetak PDF Resmi</span>
                        </Button>

                        <Button
                            @click="exportExcelReport"
                            size="sm"
                            class="h-9 px-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold gap-2 shrink-0 shadow-xs"
                        >
                            <FileSpreadsheet :size="15" />
                            <span>Ekspor Rekap Excel</span>
                        </Button>
                    </div>
                </div>

                <!-- Multi-Dimension Filter Controls -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t border-slate-100">
                    
                    <!-- Filter Rentang Waktu Periode -->
                    <div>
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Periode Waktu</span>
                        <Select v-model="filterPeriode">
                            <SelectTrigger class="h-9 text-xs border-slate-300 rounded-xl bg-white">
                                <SelectValue placeholder="Pilih Periode" />
                            </SelectTrigger>
                            <SelectContent class="bg-white rounded-xl shadow-xl">
                                <SelectGroup>
                                    <SelectItem value="hari_ini">Hari Ini</SelectItem>
                                    <SelectItem value="minggu_ini">Minggu Ini</SelectItem>
                                    <SelectItem value="bulan_ini">Bulan Ini (Berjalan)</SelectItem>
                                    <SelectItem value="triwulan">Triwulan I - 2026</SelectItem>
                                    <SelectItem value="tahun_ini">Tahun 2026</SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Filter Satker UPT Imigrasi -->
                    <div>
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Satker UPT Pembina</span>
                        <Select v-model="filterUpt">
                            <SelectTrigger class="h-9 text-xs border-slate-300 rounded-xl bg-white">
                                <SelectValue placeholder="Semua UPT Imigrasi" />
                            </SelectTrigger>
                            <SelectContent class="bg-white rounded-xl shadow-xl">
                                <SelectGroup>
                                    <SelectItem value="all">Semua Satker UPT Imigrasi</SelectItem>
                                    <SelectItem v-for="u in uptListOptions" :key="u.id" :value="u.id.toString()">
                                        {{ u.nama }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Filter Status SLA -->
                    <div>
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Status Kepatuhan SLA</span>
                        <Select v-model="filterSlaStatus">
                            <SelectTrigger class="h-9 text-xs border-slate-300 rounded-xl bg-white">
                                <SelectValue placeholder="Semua Status SLA" />
                            </SelectTrigger>
                            <SelectContent class="bg-white rounded-xl shadow-xl">
                                <SelectGroup>
                                    <SelectItem value="all">Semua Status SLA</SelectItem>
                                    <SelectItem value="terlambat">🔴 Terlambat (> 24 Jam)</SelectItem>
                                    <SelectItem value="peringatan">🟡 Peringatan (Sisa < 6 Jam)</SelectItem>
                                    <SelectItem value="tepat_waktu">🟢 Tepat Waktu</SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </div>

                </div>
            </div>

            <!-- Top 4 Executive KPI Cards -->
            <KpiSummaryCards :kpi="kpiData" />

            <!-- Main Executive Grid Container (SLA Control & UPT Scorecard) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                
                <!-- Left 2 Columns: SLA Control Center Table & UPT Compliance Scorecard -->
                <div class="lg:col-span-2 space-y-5">
                    <SlaControlTable :incidents="filteredSlaIncidents" />
                    <UptComplianceScorecard :scorecards="uptScorecard" />
                </div>

                <!-- Right 1 Column: Live Activity Feed Log -->
                <div class="lg:col-span-1">
                    <LiveActivityFeed :feed="activityFeed" :upt-options="uptListOptions" />
                </div>

            </div>

        </div>
    </AppLayout>
</template>
