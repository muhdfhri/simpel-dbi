<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import VueApexCharts from 'vue3-apexcharts';
import AppLayout from '@/components/layout/AppLayout.vue';
import PimpasaSpatialMap from './Components/PimpasaSpatialMap.vue';
import {
    Clock,
    FileCheck2,
    AlertTriangle,
    CheckCircle2,
    BarChart2,
    PieChart,
    Building2,
    ShieldAlert,
    UserCheck,
    FileText,
    TrendingUp,
    ChevronRight,
    ArrowRight,
    MapPin,
    Layers,
    Plus
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

interface SlaWarningItem {
    id: number;
    kode_tiket: string;
    judul: string;
    submitted_at: string;
}

interface LaporanItem {
    id: number;
    kode_tiket: string;
    judul: string;
    kategori: string;
    status: string;
    submitted_at: string;
    desa?: { nama: string };
}

interface ChartItem {
    month: string;
    total: number;
}

interface KategoriItem {
    name: string;
    count: number;
    color: string;
}

const props = defineProps<{
    stats: {
        total: number;
        diajukan: number;
        minta_perbaikan: number;
        diverifikasi: number;
        ditindaklanjuti: number;
        selesai: number;
        resolution_rate: number;
        sla_warning_list: SlaWarningItem[];
        monthly_chart: ChartItem[];
        kategori_chart: KategoriItem[];
    };
    recentAntrean: {
        data: LaporanItem[];
    };
    desaBinaanList?: any[];
    perangkatDesaList?: any[];
    upt?: any;
}>();

const activeTab = ref('overview');

// Sub-Tab Navigation (Hanya Overview dan Peta Sebaran)
const tabs = [
    { id: 'overview', name: 'Overview' },
    { id: 'peta', name: 'Peta Sebaran' },
];

// Dynamic Timeframe Filtering State untuk Bar Chart (1 Minggu, 1 Bulan, 1 Tahun)
type TimeframeType = '1_minggu' | '1_bulan' | '1_tahun';
const selectedTimeframe = ref<TimeframeType>('1_tahun');

// Dynamic KPI Cards dengan watermark icons presisi Desa Overview
const kpiStats = computed(() => [
    {
        title: 'Antrean Verifikasi',
        count: (props.stats?.diajukan ?? 0).toString(),
        subtext: 'Butuh pemeriksaan & verifikasi',
        trend: 'Real-time',
        icon: Clock,
        colorClass: 'text-amber-700',
    },
    {
        title: 'Revisi Perangkat Desa',
        count: (props.stats?.minta_perbaikan ?? 0).toString(),
        subtext: 'Menunggu kelengkapan dokumen',
        trend: 'Real-time',
        icon: AlertTriangle,
        colorClass: 'text-orange-800',
    },
    {
        title: 'Proses UPT Lapangan',
        count: ((props.stats?.diverifikasi ?? 0) + (props.stats?.ditindaklanjuti ?? 0)).toString(),
        subtext: 'Penanganan operasional UPT',
        trend: 'Real-time',
        icon: UserCheck,
        colorClass: 'text-blue-800',
    },
    {
        title: 'Penyelesaian Laporan',
        count: (props.stats?.selesai ?? 0).toString(),
        subtext: `Resolution rate: ${props.stats?.resolution_rate ?? 0}%`,
        trend: 'Real-time',
        icon: CheckCircle2,
        colorClass: 'text-emerald-700',
    },
]);

// 1. Dynamic ApexCharts Bar Chart (Volume Pelaporan)
const pimpasaBarCategories = computed(() => {
    if (props.stats?.monthly_chart && props.stats.monthly_chart.length > 0) {
        return props.stats.monthly_chart.map(m => m.month);
    }
    return ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];
});

const pimpasaBarSeries = computed(() => {
    const data = props.stats?.monthly_chart && props.stats.monthly_chart.length > 0
        ? props.stats.monthly_chart.map(m => m.total)
        : [0, 0, 0, 0, 0, 0];
    
    return [
        {
            name: 'Volume Pelaporan',
            data: data
        }
    ];
});

const pimpasaBarOptions = computed(() => ({
    chart: {
        type: 'bar' as const,
        height: 280,
        toolbar: { show: false },
        fontFamily: 'Inter, sans-serif',
    },
    colors: ['#033566'],
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: selectedTimeframe.value === '1_minggu' ? '50%' : (selectedTimeframe.value === '1_bulan' ? '45%' : '55%'),
            borderRadius: 6,
            borderRadiusApplication: 'end' as const,
        },
    },
    dataLabels: { enabled: false },
    stroke: { show: true, width: 3, colors: ['transparent'] },
    xaxis: {
        categories: pimpasaBarCategories.value,
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
    grid: { borderColor: '#f1f5f9', strokeDashArray: 4, padding: { top: -20, bottom: -10 } },
}));

// 2. Dynamic ApexCharts Donut Chart (Sebaran Kategori)
const kategoriDonutSeries = computed(() => {
    if (props.stats?.kategori_chart && props.stats.kategori_chart.length > 0) {
        return props.stats.kategori_chart.map(k => k.count);
    }
    return [0, 0, 0, 0];
});

const kategoriDonutLabels = computed(() => {
    if (props.stats?.kategori_chart && props.stats.kategori_chart.length > 0) {
        return props.stats.kategori_chart.map(k => k.name);
    }
    return ['Kegiatan Desa Binaan Imigrasi', 'Laporan Terkait WNA', 'Indikasi TPPO / PMI Non-Prosedural', 'Kejadian Insidentil'];
});

const kategoriDonutColors = computed(() => {
    if (props.stats?.kategori_chart && props.stats.kategori_chart.length > 0) {
        return props.stats.kategori_chart.map(k => k.color || '#033566');
    }
    return ['#033566', '#E8C070', '#DC2626', '#7C688C'];
});

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
        case 'diajukan': return 'Perlu Verifikasi';
        case 'minta_perbaikan': return 'Revisi Perangkat Desa';
        case 'diverifikasi': return 'Diverifikasi PIMPASA';
        case 'ditindaklanjuti': return 'Tindak Lanjut UPT';
        case 'selesai': return 'Selesai';
        default: return status;
    }
};
</script>

<template>
    <AppLayout title="Beranda Overview PIMPASA">
        <div class="space-y-4 sm:space-y-5 font-sans">
            
            <!-- TOP SUB-TABS NAVIGATION (Overview & Peta Sebaran) -->
            <div class="border-b border-slate-200/80 pb-2.5 flex items-center justify-between">
                <div class="flex items-center gap-6 text-xs font-semibold">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        :class="[
                            'pb-2.5 -mb-2.5 transition-colors relative cursor-pointer',
                            activeTab === tab.id
                                ? 'text-primary border-b-2 border-primary font-bold'
                                : 'text-slate-500 hover:text-slate-800'
                        ]"
                    >
                        {{ tab.name }}
                    </button>
                </div>

                <!-- Action Button Buka Antrean Verifikasi -->
                <Link href="/pimpasa/verifikasi">
                    <Button class="bg-primary hover:bg-[#04407D] text-primary-foreground font-semibold rounded-md text-xs px-4 py-2 flex items-center gap-2 shadow-xs">
                        <FileCheck2 :size="15" />
                        <span>Buka Antrean Verifikasi ({{ stats.diajukan }})</span>
                    </Button>
                </Link>
            </div>

            <!-- CONTENT TAB 1: OVERVIEW -->
            <div v-if="activeTab === 'overview'" class="space-y-4 sm:space-y-5">
                
                <!-- KPI Metrics Grid (Presisi Matching DESIGN.md & Desa Overview) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Card
                        v-for="stat in kpiStats"
                        :key="stat.title"
                        class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group"
                    >
                        <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-slate-300/80 transition-colors">
                            <component :is="stat.icon" :size="105" stroke-width="1.0" />
                        </div>

                        <CardContent class="p-4 sm:p-4.5 flex flex-col justify-between h-full min-h-[88px] relative z-10">
                            <div class="space-y-0.5 pr-12">
                                <span class="text-xs font-semibold text-slate-500 block leading-tight">{{ stat.title }}</span>
                                <div :class="['text-3xl font-bold font-sans tabular-nums tracking-tight leading-none pt-1', stat.colorClass]">
                                    {{ stat.count }}
                                </div>
                            </div>
                            <div class="pt-1.5">
                                <p class="text-[11px] text-slate-500 font-medium">{{ stat.subtext }}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- CHARTS & VISUAL ANALYTICS PANEL -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                    
                    <!-- Left Bar Chart: Volume Tren Pelaporan -->
                    <Card class="lg:col-span-2 border-slate-200/80 shadow-2xs rounded-lg bg-white">
                        <div class="px-4 py-2.5 sm:px-5 sm:py-3 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 tracking-tight flex items-center gap-2">
                                    <BarChart2 :size="16" class="text-primary" />
                                    <span>Tren Volume Pelaporan Desa Binaan</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Intensitas pengajuan laporan desa binaan {{ selectedTimeframe === '1_minggu' ? 'harian (1 Minggu)' : (selectedTimeframe === '1_bulan' ? 'mingguan (1 Bulan)' : 'bulanan (1 Tahun)') }}
                                </p>
                            </div>

                            <!-- Dropdown Filtering Rentang Waktu -->
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
                                :options="pimpasaBarOptions"
                                :series="pimpasaBarSeries"
                            />
                        </CardContent>
                    </Card>

                    <!-- Right Donut Chart: Sebaran Kategori Laporan -->
                    <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white">
                        <div class="px-4 py-2.5 sm:px-5 sm:py-3 border-b border-slate-100">
                            <h3 class="text-sm font-bold text-slate-900 tracking-tight flex items-center gap-2">
                                <PieChart :size="16" class="text-primary" />
                                <span>Sebaran Kategori Laporan</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Proporsi isu keimigrasian di wilayah kerja</p>
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

                <!-- MAIN WORKFLOW: TABLE ANTREAN LAPORAN MEMBUTUHKAN TINDAKAN -->
                <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white overflow-hidden">
                    <div class="px-4 py-2.5 sm:px-5 sm:py-3 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 tracking-tight">
                                Antrean Laporan Membutuhkan Tindakan
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Daftar laporan terbaru yang menunggu keputusan verifikasi PIMPASA
                            </p>
                        </div>

                        <Link href="/pimpasa/verifikasi">
                            <Button variant="outline" class="h-8 px-3 text-xs font-semibold rounded-md border-slate-200 hover:bg-slate-50 text-slate-700 flex items-center gap-1.5 shadow-2xs">
                                <span>Lihat Seluruh Antrean</span>
                                <ChevronRight :size="14" />
                            </Button>
                        </Link>
                    </div>

                    <!-- Clean Table (100% Inter Font Matching Desa Overview) -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs font-sans border-collapse">
                            <thead>
                                <tr class="bg-slate-100/70 border-b border-slate-200/80 text-slate-700 font-semibold uppercase text-[11px] tracking-wider select-none">
                                    <th class="py-2.5 px-4 text-center w-12">No</th>
                                    <th class="py-2.5 px-4">Kode Tiket</th>
                                    <th class="py-2.5 px-4">Judul Laporan</th>
                                    <th class="py-2.5 px-4">Desa Binaan</th>
                                    <th class="py-2.5 px-4">Status</th>
                                    <th class="py-2.5 px-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                <tr v-if="recentAntrean.data.length === 0">
                                    <td colspan="6" class="py-6 text-center text-slate-400 font-medium">
                                        Tidak ada antrean laporan yang membutuhkan tindakan saat ini.
                                    </td>
                                </tr>
                                <tr v-for="(item, index) in recentAntrean.data.slice(0, 20)" :key="item.id" class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-3 px-4 text-center font-sans tabular-nums text-slate-900 font-bold">
                                        {{ index + 1 }}
                                    </td>
                                    <td class="py-3 px-4 font-sans tabular-nums font-bold text-slate-900 whitespace-nowrap">
                                        {{ item.kode_tiket }}
                                    </td>
                                    <td class="py-3 px-4 font-bold text-slate-900 leading-snug">
                                        <div class="line-clamp-2">{{ item.judul }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-slate-700 whitespace-nowrap font-medium">
                                        {{ item.desa?.nama || '-' }}
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <span :class="['px-2.5 py-1 rounded-full text-[11px] font-semibold border', getStatusBadge(item.status)]">
                                            {{ getStatusLabel(item.status) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        <Link :href="`/pimpasa/verifikasi/${item.id}`">
                                            <Button size="sm" class="h-7.5 px-3 text-xs bg-primary hover:bg-[#04407D] text-primary-foreground font-semibold rounded-md shadow-2xs">
                                                <span>Periksa & Verifikasi</span>
                                            </Button>
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>

            </div>

            <!-- CONTENT TAB 2: PETA SEBARAN (GIS Spatial Map PIMPASA) -->
            <div v-else-if="activeTab === 'peta'" class="space-y-4">
                <PimpasaSpatialMap
                    :desa-binaan-list="desaBinaanList || []"
                    :perangkat-desa-list="perangkatDesaList || []"
                    :upt="upt || null"
                    :stats="stats"
                />
            </div>

        </div>
    </AppLayout>
</template>
