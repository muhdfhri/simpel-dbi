<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import VueApexCharts from 'vue3-apexcharts';
import AppLayout from '@/components/layout/AppLayout.vue';
import DesaSpatialMap from './Desa/Components/DesaSpatialMap.vue';
import {
    FileText,
    CheckCircle2,
    Clock,
    AlertTriangle,
    Plus,
    ArrowUpRight,
    MapPin,
    BarChart2,
    PieChart,
    Calendar,
    Activity,
    Layers,
    ChevronRight,
    FileEdit
} from 'lucide-vue-next';

import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectGroup,
    SelectItem,
} from '@/components/ui/select';

const props = defineProps<{
    role?: string;
    desa?: any;
    pimpasaList?: Array<{
        id: number;
        name: string;
        avatar: string;
    }>;
    upt?: any;
    stats?: {
        total: number;
        diajukan: number;
        minta_perbaikan: number;
        diverifikasi: number;
        selesai: number;
    };
    recentLaporan?: Array<{
        id: number;
        kode: string;
        judul: string;
        kategori: string;
        status: string;
        tanggal: string;
    }>;
    chartTrenData?: Record<string, { categories: string[]; series: Array<{ name: string; data: number[] }> }>;
    chartKategoriData?: { labels: string[]; series: number[]; colors?: string[] };
}>();

const activeTab = ref('overview');

// Sub-Tab Navigation (Hanya Overview dan Peta Sebaran)
const tabs = [
    { id: 'overview', name: 'Overview' },
    { id: 'peta', name: 'Peta Sebaran' },
];

// Dynamic KPI Cards
const kpiStats = computed(() => [
    {
        title: 'Total Pengajuan Tiket',
        count: (props.stats?.total ?? 0).toString(),
        subtext: 'Keseluruhan laporan desa',
        trend: 'Real-time',
        isPositive: true,
        icon: FileText,
        colorClass: 'text-slate-900',
    },
    {
        title: 'Diverifikasi PIMPASA',
        count: (props.stats?.diverifikasi ?? 0).toString(),
        subtext: 'Telah lolos verifikasi',
        trend: 'Real-time',
        isPositive: true,
        icon: CheckCircle2,
        colorClass: 'text-emerald-700',
    },
    {
        title: 'Menunggu Verifikasi',
        count: (props.stats?.diajukan ?? 0).toString(),
        subtext: 'Proses peninjauan PIMPASA',
        trend: 'Real-time',
        isPositive: false,
        icon: Clock,
        colorClass: 'text-amber-700',
    },
    {
        title: 'Minta Perbaikan',
        count: (props.stats?.minta_perbaikan ?? 0).toString(),
        subtext: 'Perlu revisi data segera',
        trend: 'Real-time',
        isPositive: false,
        icon: AlertTriangle,
        colorClass: 'text-orange-800',
    },
]);

// Dynamic Timeframe Filtering State untuk Bar Chart (1 Minggu, 1 Bulan, 1 Tahun)
type TimeframeType = '1_minggu' | '1_bulan' | '1_tahun';
const selectedTimeframe = ref<TimeframeType>('1_tahun');

// 1. Dynamic Reactive Chart Data & Options (From Backend Props)
const desaBarSeries = computed(() => {
    if (props.chartTrenData && props.chartTrenData[selectedTimeframe.value]) {
        return props.chartTrenData[selectedTimeframe.value].series;
    }
    return [
        { name: 'Diajukan', data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0] },
        { name: 'Selesai', data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0] },
    ];
});

const desaBarCategories = computed(() => {
    if (props.chartTrenData && props.chartTrenData[selectedTimeframe.value]) {
        return props.chartTrenData[selectedTimeframe.value].categories;
    }
    return ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
});

const desaBarOptions = computed(() => ({
    chart: {
        type: 'bar' as const,
        height: 280,
        toolbar: { show: false },
        fontFamily: 'Inter, sans-serif',
    },
    colors: ['#E8C070', '#033566'],
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: selectedTimeframe.value === '1_minggu' ? '50%' : (selectedTimeframe.value === '1_bulan' ? '45%' : '65%'),
            borderRadius: 6,
            borderRadiusApplication: 'end' as const,
        },
    },
    dataLabels: { enabled: false },
    stroke: { show: true, width: 3, colors: ['transparent'] },
    xaxis: {
        categories: desaBarCategories.value,
        labels: { style: { colors: '#64748b', fontSize: '11px', fontWeight: 600 } },
    },
    yaxis: {
        labels: { style: { colors: '#64748b', fontSize: '11px', fontWeight: 600 } },
    },
    fill: { opacity: 1 },
    tooltip: {
        theme: 'light' as const,
        style: { fontSize: '12px', fontFamily: 'Inter, sans-serif' },
    },
    legend: {
        position: 'top' as const,
        horizontalAlign: 'right' as const,
        fontSize: '11px',
        fontWeight: 600,
        labels: { colors: '#334155' },
    },
    grid: { borderColor: '#f1f5f9', strokeDashArray: 4, padding: { top: -10, bottom: -5 } },
}));

// 2. Chart Distribusi Kategori Laporan (Donut Chart Dynamic)
const kategoriDonutSeries = computed(() => props.chartKategoriData?.series || [0, 0, 0, 0]);
const kategoriDonutLabels = computed(() => props.chartKategoriData?.labels || ['Kegiatan Desa Binaan Imigrasi', 'Laporan Terkait WNA', 'Indikasi TPPO / PMI Non-Prosedural', 'Kejadian Insidentil']);
const kategoriDonutColors = computed(() => props.chartKategoriData?.colors || ['#033566', '#E8C070', '#DC2626', '#7C688C']);

const kategoriDonutOptions = computed(() => ({
    chart: {
        type: 'donut' as const,
        height: 280,
        fontFamily: 'Inter, sans-serif',
    },
    labels: kategoriDonutLabels.value,
    colors: kategoriDonutColors.value,
    dataLabels: { enabled: false },
    legend: {
        position: 'bottom' as const,
        fontSize: '11px',
        fontWeight: 600,
        labels: { colors: '#334155' },
    },
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
                        offsetY: -4,
                    },
                    value: {
                        show: true,
                        fontSize: '22px',
                        fontWeight: 700,
                        color: '#0f172a',
                        offsetY: 4,
                        formatter: (val: string) => `${val}`,
                    },
                    total: {
                        show: true,
                        label: 'Total Laporan',
                        fontSize: '11px',
                        fontWeight: 700,
                        color: '#64748b',
                        formatter: () => `${props.stats?.total ?? 0} Tiket`,
                    },
                },
            },
        },
    },
    tooltip: {
        theme: 'light' as const,
        style: { fontSize: '12px', fontFamily: 'Inter, sans-serif' },
    },
}));

// Reports List (From Database Props)
const reportsList = computed(() => {
    return props.recentLaporan ?? [];
});

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'diajukan': return 'bg-amber-50 text-amber-800 border-amber-200/80';
        case 'minta_perbaikan': return 'bg-orange-100 text-orange-900 border-orange-300 font-bold';
        case 'diverifikasi': return 'bg-blue-50 text-blue-800 border-blue-200/80';
        case 'ditindaklanjuti': return 'bg-purple-50 text-purple-800 border-purple-200/80';
        case 'selesai': return 'bg-emerald-50 text-emerald-800 border-emerald-200/80';
        default: return 'bg-slate-50 text-slate-700 border-slate-200/80';
    }
};

const getStatusLabel = (status: string) => {
    switch (status) {
        case 'diajukan': return 'Diajukan';
        case 'minta_perbaikan': return 'Minta Perbaikan';
        case 'diverifikasi': return 'Diverifikasi PIMPASA';
        case 'ditindaklanjuti': return 'Ditindaklanjuti PIMPASA';
        case 'selesai': return 'Selesai';
        default: return status;
    }
};
</script>

<template>
    <AppLayout title="Beranda Overview">
        <div class="space-y-6 font-sans">
            
            <!-- TOP SUB-TABS NAVIGATION (Hanya Overview dan Peta Sebaran) -->
            <div class="border-b border-slate-200/80 pb-3 flex items-center justify-between">
                <div class="flex items-center gap-6 text-xs font-semibold">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        :class="[
                            'pb-3 -mb-3 transition-colors relative cursor-pointer',
                            activeTab === tab.id
                                ? 'text-primary border-b-2 border-primary font-bold'
                                : 'text-slate-500 hover:text-slate-800'
                        ]"
                    >
                        {{ tab.name }}
                    </button>
                </div>

                <!-- Action Button Pengajuan Laporan Baru -->
                <Link href="/desa/laporan/create">
                    <Button class="bg-primary hover:bg-[#04407D] text-primary-foreground font-semibold rounded-md text-xs px-4 py-2 flex items-center gap-2 shadow-xs">
                        <Plus :size="15" />
                        <span>Pengajuan Laporan Baru</span>
                    </Button>
                </Link>
            </div>

            <!-- CONTENT TAB 1: OVERVIEW -->
            <div v-if="activeTab === 'overview'" class="space-y-6">
                
                <!-- KPI Metrics Grid (Presisi Matching DESIGN.md) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Card
                        v-for="stat in kpiStats"
                        :key="stat.title"
                        class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group"
                    >
                        <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-slate-300/80 transition-colors">
                            <component :is="stat.icon" :size="105" stroke-width="1.0" />
                        </div>

                        <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                            <div class="space-y-0.5 pr-12">
                                <span class="text-xs font-semibold text-slate-500 block leading-tight">{{ stat.title }}</span>
                                <div :class="['text-3xl font-bold font-sans tabular-nums tracking-tight leading-none pt-1', stat.colorClass]">
                                    {{ stat.count }}
                                </div>
                            </div>
                            <div class="pt-2">
                                <p class="text-[11px] text-slate-500 font-medium">{{ stat.subtext }}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- CHARTS & VISUAL ANALYTICS PANEL -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                    
                    <!-- Left Bar Chart: Volume Tren Laporan per Bulan -->
                    <Card class="lg:col-span-2 border-slate-200/80 shadow-2xs rounded-lg bg-white">
                        <div class="px-4 py-2.5 sm:px-5 sm:py-3 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 tracking-tight flex items-center gap-2">
                                    <BarChart2 :size="16" class="text-primary" />
                                    <span>Tren Aktivitas Pelaporan Desa Binaan</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Perbandingan tiket diajukan vs diselesaikan {{ selectedTimeframe === '1_minggu' ? 'harian (1 Minggu)' : (selectedTimeframe === '1_bulan' ? 'mingguan (1 Bulan)' : 'bulanan (1 Tahun)') }}
                                </p>
                            </div>

                            <!-- Dropdown Filtering Rentang Waktu (1 Minggu, 1 Bulan, 1 Tahun) -->
                            <Select v-model="selectedTimeframe">
                                <SelectTrigger class="h-8 w-[125px] border-slate-200 text-xs font-semibold shadow-2xs bg-white">
                                    <SelectValue placeholder="Pilih Rentang" />
                                </SelectTrigger>
                                <SelectContent class="z-50 bg-white shadow-md border-slate-200">
                                    <SelectGroup>
                                        <SelectItem value="1_minggu" class="text-xs font-medium cursor-pointer">
                                            1 Minggu
                                        </SelectItem>
                                        <SelectItem value="1_bulan" class="text-xs font-medium cursor-pointer">
                                            1 Bulan
                                        </SelectItem>
                                        <SelectItem value="1_tahun" class="text-xs font-medium cursor-pointer">
                                            1 Tahun
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </div>
                        <CardContent class="p-2 sm:px-4 sm:pb-2 sm:pt-0">
                            <VueApexCharts
                                type="bar"
                                height="260"
                                :options="desaBarOptions"
                                :series="desaBarSeries"
                            />
                        </CardContent>
                    </Card>

                    <!-- Right Donut Chart: Distribusi Kategori Laporan -->
                    <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white">
                        <div class="px-4 py-2.5 sm:px-5 sm:py-3 border-b border-slate-100">
                            <h3 class="text-sm font-bold text-slate-900 tracking-tight flex items-center gap-2">
                                <PieChart :size="16" class="text-primary" />
                                <span>Distribusi Kategori Laporan</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Persentase isu terdaftar di wilayah desa</p>
                        </div>
                        <CardContent class="p-2 sm:px-4 sm:pb-2 sm:pt-0">
                            <VueApexCharts
                                type="donut"
                                height="260"
                                :options="kategoriDonutOptions"
                                :series="kategoriDonutSeries"
                            />
                        </CardContent>
                    </Card>

                </div>

                <!-- RECENT REPORTS TABLE CONTAINER -->
                <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white overflow-hidden">
                    <div class="px-4 py-2.5 sm:px-5 sm:py-3 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 tracking-tight">
                                Laporan Kejadian Terbaru
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Pemantauan realtime pengajuan tiket pelaporan desa binaan
                            </p>
                        </div>

                        <Link href="/desa/laporan">
                            <Button variant="outline" class="h-8 px-3 text-xs font-semibold rounded-md border-slate-200 hover:bg-slate-50 text-slate-700 flex items-center gap-1.5 shadow-2xs">
                                <span>Lihat Seluruh Laporan</span>
                                <ChevronRight :size="14" />
                            </Button>
                        </Link>
                    </div>

                    <!-- Clean Table (DESIGN.md 100% Inter Font) -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs font-sans border-collapse">
                            <thead>
                                <tr class="bg-slate-100/70 border-b border-slate-200/80 text-slate-700 font-semibold uppercase text-[11px] tracking-wider select-none">
                                    <th class="py-2.5 px-4 text-center w-14">No</th>
                                    <th class="py-2.5 px-5">Kode Tiket</th>
                                    <th class="py-2.5 px-5">Judul Laporan</th>
                                    <th class="py-2.5 px-5">Kategori</th>
                                    <th class="py-2.5 px-5">Status Siklus</th>
                                    <th class="py-2.5 px-5 text-center">Waktu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                <template v-if="reportsList.length > 0">
                                    <tr v-for="item in reportsList" :key="item.id" class="hover:bg-slate-50/70 transition-colors">
                                        <td class="py-3 px-4 text-center font-sans tabular-nums text-slate-900 font-bold">
                                            {{ item.id }}
                                        </td>
                                        <td class="py-3 px-5 font-sans tabular-nums font-bold text-slate-900">
                                            {{ item.kode }}
                                        </td>
                                        <td class="py-3 px-5 font-bold text-slate-900 leading-snug">
                                            {{ item.judul }}
                                        </td>
                                        <td class="py-3 px-5 text-slate-700">
                                            <span class="inline-block px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[10px] font-semibold uppercase tracking-wider border border-slate-200/80">
                                                {{ item.kategori }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-5 whitespace-nowrap">
                                            <span :class="['px-2.5 py-1 rounded-full text-[11px] font-semibold border', getStatusBadge(item.status)]">
                                                {{ getStatusLabel(item.status) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-5 text-center text-slate-500 font-sans tabular-nums font-medium whitespace-nowrap">
                                            {{ item.tanggal }}
                                        </td>
                                    </tr>
                                </template>
                                <tr v-else>
                                    <td colspan="6" class="py-8 text-center text-slate-500 text-xs font-medium">
                                        Belum ada laporan kejadian terbaru di wilayah ini.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>

            </div>

            <!-- CONTENT TAB 2: PETA SEBARAN (GIS Map + Profile Panel PIMPASA & UPT) -->
            <div v-else-if="activeTab === 'peta'" class="space-y-4">
                <DesaSpatialMap
                    :desa="desa"
                    :pimpasa-list="pimpasaList"
                    :upt="upt"
                    :stats="stats"
                />
            </div>

        </div>
    </AppLayout>
</template>
