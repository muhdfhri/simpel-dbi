<script setup lang="ts">
import AppLayout from '@/components/layout/AppLayout.vue';
import { FileDown } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import KpiSummaryCards from './Monitoring/Components/KpiSummaryCards.vue';
import ExecutiveChartAnalytics from './Monitoring/Components/ExecutiveChartAnalytics.vue';
import { notify } from '@/lib/toast';

const props = defineProps<{
    kpiData: any;
    chartAnalytics: any;
}>();

const exportPdf = () => {
    notify.info('Mengunduh Laporan PDF Eksekutif...', { description: 'Format Resmi Dinas Kanwil Kemenkumham Sumut.' });
};
</script>

<template>
    <AppLayout title="Beranda & Executive Analytics — Kanwil Sumut">
        <div class="space-y-6 font-sans">
            
            <!-- Top Header & Action Toolbar -->
            <div class="bg-white border border-slate-200/80 rounded-xl p-4 sm:p-5 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        Executive Dashboard & Visual Analytics
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Analisis tren kinerja pelaporan, status desa binaan, dan tingkat kepatuhan SLA Satker UPT</p>
                </div>

                <div class="flex items-center gap-2.5 shrink-0">
                    <Button @click="exportPdf" size="sm" class="h-9 px-4 rounded-md bg-primary hover:bg-[#04407D] text-primary-foreground text-xs font-semibold gap-2 shadow-xs">
                        <FileDown :size="15" />
                        <span>Cetak Ringkasan Eksekutif</span>
                    </Button>
                </div>
            </div>

            <!-- Top 4 KPI Metrics Cards -->
            <KpiSummaryCards :kpi="kpiData" />

            <!-- Executive Visual Analytics ApexCharts (Area, Donut, Bar Chart) -->
            <ExecutiveChartAnalytics v-if="chartAnalytics" :data="chartAnalytics" />

        </div>
    </AppLayout>
</template>
