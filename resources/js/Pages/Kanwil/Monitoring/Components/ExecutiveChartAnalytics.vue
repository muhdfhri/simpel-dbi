<script setup lang="ts">
import { ref, computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import { TrendingUp, PieChart, BarChart3, Calendar, AreaChart as AreaIcon, BarChart2, Layers } from 'lucide-vue-next';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import {
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectGroup,
    SelectItem,
} from '@/components/ui/select';

interface TrendItem {
    categories: string[];
    series: Array<{ name: string; data: number[] }>;
}

interface ChartAnalyticsData {
    monthlyTrend?: TrendItem;
    trendData?: {
        '1_minggu': TrendItem;
        '1_bulan': TrendItem;
        '1_tahun': TrendItem;
    };
    desaStatusDistribution: {
        labels: string[];
        series: number[];
        colors: string[];
    };
    uptComplianceRanking: {
        categories: string[];
        completionRates: number[];
        avgHours: number[];
    };
}

const props = defineProps<{
    data: ChartAnalyticsData;
}>();

// Dynamic Filter States for Volume Trend Chart
type TimeframeType = '1_minggu' | '1_bulan' | '1_tahun';
type ChartType = 'area' | 'bar' | 'line';

const selectedTimeframe = ref<TimeframeType>('1_tahun');
const selectedChartType = ref<ChartType>('area');

// Active Trend Dataset based on selectedTimeframe
const activeTrendData = computed<TrendItem>(() => {
    if (props.data?.trendData && props.data.trendData[selectedTimeframe.value]) {
        return props.data.trendData[selectedTimeframe.value];
    }
    return props.data?.monthlyTrend || { categories: [], series: [] };
});

// Dynamic ApexCharts Options for Trend Chart
const trendOptions = computed(() => {
    const isBar = selectedChartType.value === 'bar';
    const isLineCombo = selectedChartType.value === 'line';

    return {
        chart: {
            id: 'volume-trend-chart',
            type: isLineCombo ? 'line' : selectedChartType.value,
            toolbar: { show: false },
            fontFamily: 'Inter, ui-sans-serif, system-ui, sans-serif',
            zoom: { enabled: false },
            stacked: false,
            sparkline: { enabled: false },
        },
        colors: ['#033566', '#16A34A', '#DC2626'],
        dataLabels: { enabled: false },
        annotations: {
            points: (isLineCombo && activeTrendData.value?.series?.[2]?.data)
                ? activeTrendData.value.series[2].data.map((val, idx) => ({
                    x: activeTrendData.value.categories[idx],
                    y: val,
                    yAxisIndex: 2,
                    marker: {
                        size: 5,
                        fillColor: '#DC2626',
                        strokeColor: '#ffffff',
                        strokeWidth: 2,
                    },
                    label: {
                        borderColor: '#DC2626',
                        borderWidth: 1,
                        borderRadius: 4,
                        offsetY: -14,
                        style: {
                            color: '#ffffff',
                            background: '#DC2626',
                            fontSize: '11px',
                            fontFamily: 'Inter, ui-sans-serif, system-ui, sans-serif',
                            fontWeight: '700',
                            padding: { left: 6, right: 6, top: 3, bottom: 3 },
                        },
                        text: `${val}`,
                    },
                }))
                : [],
        },
        stroke: {
            curve: isLineCombo ? 'straight' : 'smooth',
            width: isBar ? 0 : (isLineCombo ? [0, 0, 3.5] : [3, 3, 2.5]),
        },
        markers: {
            size: isBar ? 0 : (isLineCombo ? [0, 0, 5] : 0),
            strokeColors: '#ffffff',
            strokeWidth: 2,
            hover: { size: 6, sizeOffset: 3 },
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '54%',
                borderRadius: 4,
                borderRadiusApplication: 'end',
            },
        },
        fill: {
            type: isBar ? 'solid' : (isLineCombo ? ['solid', 'solid', 'solid'] : 'gradient'),
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.35,
                opacityTo: 0.02,
                stops: [0, 90, 100],
            },
            opacity: isLineCombo ? [0.95, 0.95, 1] : (isBar ? 1 : 0.85),
        },
        grid: {
            borderColor: '#f1f5f9',
            strokeDashArray: 4,
            padding: {
                top: 10,
                right: 15,
                bottom: 12,
                left: 10,
            },
        },
        xaxis: {
            categories: activeTrendData.value.categories,
            labels: {
                style: { colors: '#64748b', fontSize: '11px', fontWeight: 600 },
            },
            axisBorder: { show: false },
            axisTicks: { show: false },
            crosshairs: {
                show: true,
                width: 1,
                position: 'back',
                stroke: {
                    color: '#94a3b8',
                    width: 1,
                    dashArray: 4,
                },
            },
        },
        yaxis: isLineCombo ? [
            {
                title: {
                    text: 'Volume Laporan',
                    style: { color: '#033566', fontSize: '11px', fontWeight: 600 },
                },
                labels: {
                    style: { colors: '#64748b', fontSize: '11px', fontWeight: 600 },
                },
            },
            {
                show: false,
            },
            {
                opposite: true,
                title: {
                    text: 'SLA Breached',
                    style: { color: '#DC2626', fontSize: '11px', fontWeight: 600 },
                },
                labels: {
                    style: { colors: '#DC2626', fontSize: '11px', fontWeight: 600 },
                },
            },
        ] : {
            labels: {
                style: { colors: '#64748b', fontSize: '11px', fontWeight: 600 },
            },
        },
        tooltip: {
            theme: 'light',
            shared: true,
            intersect: false,
            style: { fontSize: '12px', fontFamily: 'Inter, sans-serif' },
        },
        legend: {
            position: 'bottom',
            horizontalAlign: 'center',
            offsetY: 14,
            fontSize: '12px',
            fontWeight: 600,
            labels: { colors: '#334155' },
            markers: { width: 10, height: 10, radius: 10 },
            itemMargin: { horizontal: 16, vertical: 6 },
        },
    };
});

// Dynamic ApexCharts Series for Combo or Standard Mode
const trendSeries = computed(() => {
    const rawSeries = activeTrendData.value.series || [];
    if (selectedChartType.value === 'line') {
        return rawSeries.map((s, idx) => ({
            ...s,
            type: idx === 2 ? 'line' : 'column',
        }));
    }
    return rawSeries.map(s => ({ ...s, type: selectedChartType.value }));
});

// 2. Options Donut Chart Sebaran Status Desa
const desaDonutOptions = computed(() => ({
    chart: {
        id: 'desa-status-donut-chart',
        type: 'donut',
        fontFamily: 'Inter, ui-sans-serif, system-ui, sans-serif',
    },
    labels: props.data.desaStatusDistribution.labels,
    colors: props.data.desaStatusDistribution.colors,
    stroke: { width: 2, colors: ['#ffffff'] },
    plotOptions: {
        pie: {
            donut: {
                size: '74%',
                labels: {
                    show: true,
                    name: {
                        show: true,
                        fontSize: '12px',
                        fontWeight: 600,
                        color: '#64748b',
                    },
                    value: {
                        show: true,
                        fontSize: '24px',
                        fontWeight: 700,
                        color: '#0f172a',
                        formatter: (val: string) => `${val} Desa`,
                    },
                    total: {
                        show: true,
                        label: 'Total Desa Binaan',
                        fontSize: '11px',
                        fontWeight: 700,
                        color: '#64748b',
                        formatter: () => '171 Desa',
                    },
                },
            },
        },
    },
    dataLabels: { enabled: false },
    legend: {
        position: 'bottom',
        fontSize: '11px',
        fontWeight: 600,
        labels: { colors: '#334155' },
        markers: { radius: 12 },
        itemMargin: { horizontal: 10, vertical: 4 },
    },
    tooltip: {
        theme: 'light',
        style: { fontSize: '12px', fontFamily: 'Inter, sans-serif' },
    },
}));

// 3. Options Horizontal Bar Chart Kepatuhan UPT (Presisi Referensi)
const uptBarOptions = computed(() => ({
    chart: {
        id: 'upt-compliance-bar-chart',
        type: 'bar',
        toolbar: { show: false },
        fontFamily: 'Inter, ui-sans-serif, system-ui, sans-serif',
    },
    colors: ['#033566'],
    plotOptions: {
        bar: {
            horizontal: true,
            barHeight: '58%',
            borderRadius: 4,
            borderRadiusApplication: 'end',
            dataLabels: { position: 'top' },
        },
    },
    dataLabels: {
        enabled: true,
        formatter: (val: number) => `${val}%`,
        offsetX: 18,
        style: {
            fontSize: '11px',
            fontWeight: 700,
            colors: ['#033566'],
        },
    },
    grid: {
        borderColor: '#e2e8f0',
        strokeDashArray: 0,
        xaxis: { lines: { show: false } },
        yaxis: { lines: { show: true } },
        padding: {
            top: -15,
            bottom: -5,
            left: 10,
            right: 40,
        },
    },
    xaxis: {
        categories: props.data.uptComplianceRanking.categories,
        min: 0,
        max: 110,
        tickAmount: 4,
        labels: {
            style: { colors: '#64748b', fontSize: '11px', fontWeight: 600 },
            formatter: (val: number) => (val <= 100 ? `${val}%` : ''),
        },
        axisBorder: { show: true, color: '#e2e8f0' },
        axisTicks: { show: true, color: '#cbd5e1' },
    },
    yaxis: {
        labels: {
            minWidth: 140,
            maxWidth: 200,
            style: { colors: '#1e293b', fontSize: '12px', fontWeight: 600 },
        },
    },
    tooltip: {
        theme: 'light',
        style: { fontSize: '12px', fontFamily: 'Inter, sans-serif' },
        y: {
            formatter: (val: number) => `${val}% SLA Tepat Waktu`,
        },
    },
}));

const uptBarSeries = computed(() => [
    {
        name: 'Tingkat Kepatuhan SLA',
        data: props.data.uptComplianceRanking.completionRates,
    },
]);
</script>

<template>
    <div class="space-y-6 font-sans">
        
        <!-- Row 1: Volume Trend Chart (Dropdown Filters) + Donut Status Desa -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left 2 Cols: Dynamic Multi-View Trend Chart -->
            <Card class="lg:col-span-2 border-slate-200/80 shadow-2xs rounded-xl bg-white">
                <CardHeader class="py-3.5 px-5 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="space-y-0.5 min-w-0">
                        <CardTitle class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                            <TrendingUp :size="18" class="text-slate-700 shrink-0" />
                            <span>Tren Volume Laporan & SLA</span>
                        </CardTitle>
                        <p class="text-xs text-slate-500">Perbandingan laporan masuk, selesai, dan red-flag</p>
                    </div>

                    <!-- Clean Sleek Shadcn UI Dropdown Filters -->
                    <div class="flex items-center gap-2 shrink-0 self-start sm:self-auto">
                        <!-- Dropdown 1: Rentang Waktu -->
                        <Select v-model="selectedTimeframe">
                            <SelectTrigger class="w-[110px] h-8 text-xs border-slate-300 rounded-md bg-white shadow-2xs font-semibold text-slate-800">
                                <SelectValue placeholder="Waktu" />
                            </SelectTrigger>
                            <SelectContent class="bg-white rounded-lg shadow-xl z-50">
                                <SelectGroup>
                                    <SelectItem value="1_minggu">1 Minggu</SelectItem>
                                    <SelectItem value="1_bulan">1 Bulan</SelectItem>
                                    <SelectItem value="1_tahun">1 Tahun</SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>

                        <!-- Dropdown 2: Tipe Visualisasi Grafik -->
                        <Select v-model="selectedChartType">
                            <SelectTrigger class="w-[150px] h-8 text-xs border-slate-300 rounded-md bg-white shadow-2xs font-semibold text-slate-800">
                                <SelectValue placeholder="Tipe Chart" />
                            </SelectTrigger>
                            <SelectContent class="bg-white rounded-lg shadow-xl z-50">
                                <SelectGroup>
                                    <SelectItem value="area">Spline Area Chart</SelectItem>
                                    <SelectItem value="bar">Grouped Column Chart</SelectItem>
                                    <SelectItem value="line">Combo Line & Column</SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </div>
                </CardHeader>


                <CardContent class="p-4 sm:p-5 overflow-x-auto min-w-0">
                    <div class="min-w-[500px] sm:min-w-0">
                        <VueApexCharts
                            :key="`${selectedTimeframe}-${selectedChartType}`"
                            :type="selectedChartType === 'line' ? 'line' : selectedChartType"
                            height="390"
                            :options="(trendOptions as any)"
                            :series="trendSeries"
                        />
                    </div>
                </CardContent>
            </Card>

            <!-- Right 1 Col: Donut Chart Sebaran Status 171 Desa Binaan -->
            <Card class="lg:col-span-1 border-slate-200/80 shadow-2xs rounded-xl bg-white overflow-hidden flex flex-col justify-between">
                <CardHeader class="py-4 px-5 border-b border-slate-100 bg-slate-50/50">
                    <CardTitle class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <PieChart :size="18" class="text-slate-700 shrink-0" />
                        <span>Sebaran Status Desa Binaan</span>
                    </CardTitle>
                    <p class="text-xs text-slate-500">Komposisi status 171 Desa Binaan se-Sumut</p>
                </CardHeader>

                <CardContent class="p-4 sm:p-5 flex-1 flex items-center justify-center overflow-hidden">
                    <VueApexCharts
                        type="donut"
                        width="100%"
                        height="320"
                        :options="(desaDonutOptions as any)"
                        :series="data.desaStatusDistribution.series"
                    />
                </CardContent>
            </Card>

        </div>

        <!-- Row 2: Horizontal Bar Chart Rangking Kepatuhan UPT Imigrasi (Presisi Referensi) -->
        <Card class="border-slate-200/80 shadow-2xs rounded-xl bg-white overflow-hidden">
            <CardHeader class="py-4 px-5 border-b border-slate-100 bg-slate-50/50 flex flex-row items-center justify-between">
                <div class="space-y-1">
                    <CardTitle class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                        <BarChart3 :size="18" class="text-slate-700 shrink-0" />
                        <span>Tingkat Kepatuhan SLA per Satker UPT Imigrasi Pembina</span>
                    </CardTitle>
                    <p class="text-xs text-slate-500">Persentase penyelesaian laporan tepat waktu (SLA 24 Jam) pada Satker UPT Imigrasi se-Sumut</p>
                </div>
            </CardHeader>

            <CardContent class="px-4 sm:px-6 pt-3 pb-5 overflow-x-auto min-w-0">
                <div class="min-w-[550px] sm:min-w-0">
                    <VueApexCharts
                        type="bar"
                        height="350"
                        :options="(uptBarOptions as any)"
                        :series="uptBarSeries"
                    />
                </div>
            </CardContent>
        </Card>

    </div>
</template>



