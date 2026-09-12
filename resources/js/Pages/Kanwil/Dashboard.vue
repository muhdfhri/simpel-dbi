<script setup lang="ts">
import { ref } from 'vue';
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

const chartAnalyticsRef = ref<any>(null);
const isExporting = ref(false);

const exportPdf = async () => {
    if (isExporting.value) return;
    try {
        isExporting.value = true;
        notify.info('Mengunduh Laporan PDF Eksekutif...', { description: 'Kanwil Ditjenim Sumatera Utara' });

        let images = { trendImg: null, donutImg: null, barImg: null };
        if (chartAnalyticsRef.value && typeof chartAnalyticsRef.value.getChartImages === 'function') {
            images = await chartAnalyticsRef.value.getChartImages();
        }

        const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '';

        const formData = new FormData();
        formData.append('_token', csrfToken);
        if (images.trendImg) formData.append('trendImg', images.trendImg);
        if (images.donutImg) formData.append('donutImg', images.donutImg);
        if (images.barImg) formData.append('barImg', images.barImg);

        const response = await fetch('/kanwil/dashboard/export-pdf', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `ringkasan-eksekutif-kanwil-${new Date().toISOString().slice(0, 10)}.pdf`;
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error('Failed to export PDF:', error);
        notify.error('Gagal mengunduh Laporan PDF');
    } finally {
        setTimeout(() => {
            isExporting.value = false;
        }, 1000);
    }
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
                    <Button :disabled="isExporting" @click="exportPdf" size="sm" class="h-9 px-4 rounded-md bg-primary hover:bg-[#04407D] text-primary-foreground text-xs font-semibold gap-2 shadow-xs">
                        <FileDown :size="15" />
                        <span>{{ isExporting ? 'Memproses...' : 'Cetak Ringkasan Eksekutif' }}</span>
                    </Button>
                </div>
            </div>

            <!-- Top 4 KPI Metrics Cards -->
            <KpiSummaryCards :kpi="kpiData" />

            <!-- Executive Visual Analytics ApexCharts (Area, Donut, Bar Chart) -->
            <ExecutiveChartAnalytics ref="chartAnalyticsRef" v-if="chartAnalytics" :data="chartAnalytics" />

        </div>
    </AppLayout>
</template>
