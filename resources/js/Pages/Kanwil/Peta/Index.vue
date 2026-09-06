<script setup lang="ts">
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import AppLayout from '@/components/layout/AppLayout.vue';
import {
    MapPin,
    Maximize2,
    Minimize2,
    Globe,
    Layers,
    Building2,
    Users,
    Filter,
    FileText
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import MultiCombobox from '@/components/ui/combobox/MultiCombobox.vue';
import SpatialSidePanel from './Components/SpatialSidePanel.vue';

interface LatestLaporan {
    id: number;
    kode_tiket: string;
    judul: string;
    kategori: string;
    status: string;
    status_label: string;
    submitted_at?: string | null;
    submitted_at_formatted: string;
}

interface DesaMarker {
    id: number;
    nama: string;
    upt_id: number;
    upt_nama: string;
    pimpasa_name: string;
    lat: number;
    lng: number;
    status_terkini: string;
    total_laporan: number;
    laporan_diajukan: number;
    laporan_diverifikasi: number;
    laporan_selesai: number;
    latest_laporan_at?: string | null;
    latest_laporan?: LatestLaporan | null;
}

interface UptProps {
    id: number;
    nama: string;
    tipe: string;
}

const props = defineProps<{
    desaMarkers: DesaMarker[];
    uptList: UptProps[];
}>();

const mapContainer = ref<HTMLElement | null>(null);
const selectedDesa = ref<DesaMarker | null>(props.desaMarkers[0] || null);
const isFullscreen = ref(false);
const activeTileKey = ref<'osm' | 'satellite' | 'voyager'>('osm');

// --- MULTI-SELECT COMBOBOX STATES ---
const selectedUptIds = ref<string[]>([]);
const selectedStatuses = ref<string[]>([]);

let mapInstance: any = null;
let currentTileLayer: any = null;
let markersGroup: any = null;

// MultiCombobox Options
const uptOptions = computed(() =>
    props.uptList.map(u => ({
        value: u.id.toString(),
        label: u.nama,
        description: u.tipe,
    }))
);

const statusOptions = [
    { value: 'aman', label: 'Aman', description: 'Kondisi kondusif & bebas aduan' },
    { value: 'pembinaan', label: 'Perlu Pembinaan', description: 'Memerlukan intervensi PIMPASA' },
    { value: 'aduan', label: 'Ada Aduan', description: 'Terdapat tiket aduan aktif' },
];

// Tile Layers Configuration (100% Gratis, Open-Source & Tanpa Watermark API Key)
const tileLayersConfig = {
    osm: {
        name: 'Peta Standar (OpenStreetMap)',
        url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        attribution: '&copy; OpenStreetMap contributors',
    },
    satellite: {
        name: 'Peta Satelit (Tampak Langsung)',
        url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
        attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
    },
    voyager: {
        name: 'Peta Minimalis (Vektor Clean)',
        url: 'https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}',
        attribution: 'Tiles &copy; Esri &mdash; Esri, DeLorme, NAVTEQ',
    },
};

// Filter Markers Based on Multi-Select Arrays
const filteredMarkers = computed(() => {
    let list = [...props.desaMarkers];

    if (selectedUptIds.value.length > 0) {
        list = list.filter(d => selectedUptIds.value.includes(d.upt_id.toString()));
    }

    if (selectedStatuses.value.length > 0) {
        list = list.filter(d => selectedStatuses.value.includes(d.status_terkini));
    }

    return list;
});

// Watch Filters to Redraw Leaflet Markers
watch([selectedUptIds, selectedStatuses], () => {
    renderMarkers();
});

// Count Statuses & Overall Reports
const countAman = computed(() => props.desaMarkers.filter(d => d.status_terkini === 'aman').length);
const countPembinaan = computed(() => props.desaMarkers.filter(d => d.status_terkini === 'pembinaan').length);
const countAduan = computed(() => props.desaMarkers.filter(d => d.status_terkini === 'aduan').length);
const totalLaporanAll = computed(() => props.desaMarkers.reduce((acc, d) => acc + (d.total_laporan || 0), 0));

const toggleFullscreen = () => {
    isFullscreen.value = !isFullscreen.value;
    nextTick(() => {
        if (mapInstance) {
            setTimeout(() => mapInstance.invalidateSize(), 300);
        }
    });
};

const selectDesaMarker = (d: DesaMarker) => {
    selectedDesa.value = d;
    if (mapInstance) {
        mapInstance.flyTo([d.lat, d.lng], 12, { duration: 1.2 });
    }
};

// --- BASEMAP SWITCHER FUNCTION ---
const switchTileLayer = (key: 'osm' | 'satellite' | 'voyager') => {
    activeTileKey.value = key;
    if (!mapInstance || typeof (window as any).L === 'undefined') return;
    const L = (window as any).L;

    if (currentTileLayer) {
        mapInstance.removeLayer(currentTileLayer);
    }

    const config = tileLayersConfig[key];
    currentTileLayer = L.tileLayer(config.url, {
        attribution: config.attribution,
        maxZoom: 19,
    }).addTo(mapInstance);
};

// --- INITIALIZE LEAFLET MAP ---
const initMap = async () => {
    if (!mapContainer.value) return;

    if (typeof (window as any).L === 'undefined') {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        document.head.appendChild(link);

        const script = document.createElement('script');
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        document.head.appendChild(script);

        await new Promise((res) => (script.onload = res));
    }

    const L = (window as any).L;

    const initialLat = selectedDesa.value ? selectedDesa.value.lat : 3.5952;
    const initialLng = selectedDesa.value ? selectedDesa.value.lng : 98.6722;

    mapInstance = L.map(mapContainer.value, {
        zoomControl: false,
    }).setView([initialLat, initialLng], 10);

    L.control.zoom({ position: 'topright' }).addTo(mapInstance);

    switchTileLayer('osm');
    renderMarkers();
};

const renderMarkers = () => {
    if (!mapInstance || typeof (window as any).L === 'undefined') return;
    const L = (window as any).L;

    if (markersGroup) {
        mapInstance.removeLayer(markersGroup);
    }

    markersGroup = L.layerGroup().addTo(mapInstance);

    filteredMarkers.value.forEach((d) => {
        const colorClass = d.status_terkini === 'aman' ? '#10b981' : (d.status_terkini === 'pembinaan' ? '#f59e0b' : '#ef4444');

        const customIcon = L.divIcon({
            className: 'custom-leaflet-pin',
            html: `<div style="background-color: ${colorClass}; width: 22px; height: 22px; border-radius: 50%; border: 3px solid white; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.3); cursor: pointer;"></div>`,
            iconSize: [22, 22],
            iconAnchor: [11, 11],
        });

        const marker = L.marker([d.lat, d.lng], { icon: customIcon }).addTo(markersGroup);

        // Permanent Label Nama Desa & Angka Laporan Di Atas Pin Marker
        marker.bindTooltip(`${d.nama} (${d.total_laporan} Laporan)`, {
            permanent: true,
            direction: 'top',
            offset: [0, -12],
            className: 'custom-permanent-village-label',
        });

        const popupContent = `
            <div style="font-family: Inter, sans-serif; padding: 6px; min-width: 210px;">
                <div style="font-weight: bold; font-size: 13px; color: #0f172a;">${d.nama}</div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">${d.upt_nama}</div>
                <div style="margin-top: 4px; font-size: 11px; font-weight: 600; color: #033566;">Pengampu: ${d.pimpasa_name}</div>
                <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #e2e8f0; display: flex; flex-direction: column; gap: 4px; font-size: 11px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #64748b; font-weight: 500;">Total Laporan:</span>
                        <span style="font-weight: 700; color: #033566; font-variant-numeric: tabular-nums;">${d.total_laporan} Tiket</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #64748b; font-weight: 500;">Diverifikasi:</span>
                        <span style="font-weight: 600; color: #0284c7; font-variant-numeric: tabular-nums;">${d.laporan_diverifikasi || 0}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #64748b; font-weight: 500;">Selesai:</span>
                        <span style="font-weight: 600; color: #16a34a; font-variant-numeric: tabular-nums;">${d.laporan_selesai || 0}</span>
                    </div>
                    <div style="margin-top: 4px; text-align: right;">
                        <span style="font-size: 10px; font-weight: bold; padding: 2px 6px; border-radius: 99px; text-transform: uppercase; background: ${colorClass}15; color: ${colorClass}; border: 1px solid ${colorClass}40;">${d.status_terkini}</span>
                    </div>
                </div>
            </div>
        `;

        marker.bindPopup(popupContent, { className: 'custom-shadcn-popup' });

        marker.on('click', () => {
            selectDesaMarker(d);
        });
    });
};

onMounted(() => {
    initMap();
});
</script>

<template>
    <AppLayout title="Peta Sebaran Desa Binaan — Kanwil Sumut">
        <div class="space-y-4 font-sans">
            
            <!-- Executive Filter Toolbar with MultiCombobox Select2 Style -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-2xs space-y-4">
                
                <!-- Row 1: Metrics Badges & Action Buttons -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                    <!-- Metrics Summary Pins -->
                    <div class="flex items-center gap-2.5 sm:gap-3.5 text-xs flex-wrap">
                        <div class="flex items-center gap-1.5 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200/70 text-emerald-800 font-bold text-[11px]">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-2xs"></span>
                            <span>Aman ({{ countAman }})</span>
                        </div>
                        <div class="flex items-center gap-1.5 bg-amber-50 px-2.5 py-1 rounded-full border border-amber-200/70 text-amber-800 font-bold text-[11px]">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-2xs"></span>
                            <span>Perlu Pembinaan ({{ countPembinaan }})</span>
                        </div>
                        <div class="flex items-center gap-1.5 bg-red-50 px-2.5 py-1 rounded-full border border-red-200/70 text-red-800 font-bold text-[11px]">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500 shadow-2xs"></span>
                            <span>Ada Aduan ({{ countAduan }})</span>
                        </div>
                        <div class="flex items-center gap-1.5 bg-blue-50 px-2.5 py-1 rounded-full text-blue-800 border border-blue-200/80 font-bold font-sans tabular-nums text-[11px]">
                            <FileText :size="13" class="text-blue-600 shrink-0" />
                            <span>Total Laporan: {{ totalLaporanAll }} Tiket</span>
                        </div>
                    </div>

                    <!-- Right Controls: Basemap & Fullscreen Toggle -->
                    <div class="flex items-center gap-2 self-start md:self-auto w-full md:w-auto">
                        <!-- Basemap Switcher Dropdown -->
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="h-9 px-3 rounded-xl border-slate-300 text-xs font-bold gap-1.5 flex-1 md:flex-none justify-center bg-white"
                                >
                                    <Layers :size="14" class="text-blue-600 shrink-0" />
                                    <span class="truncate">Tipe: {{ activeTileKey === 'osm' ? 'Standar' : (activeTileKey === 'satellite' ? 'Satelit' : 'Vektor') }}</span>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent class="w-56 p-1.5 rounded-xl shadow-xl bg-white border border-slate-200 z-50" align="end">
                                <DropdownMenuLabel class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-2 py-1">Pilih Tampilan Peta</DropdownMenuLabel>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem
                                    @click="switchTileLayer('osm')"
                                    :class="['px-2.5 py-2 rounded-lg text-xs font-semibold cursor-pointer flex items-center justify-between', activeTileKey === 'osm' ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-100']"
                                >
                                    <div class="flex items-center gap-2">
                                        <Globe :size="15" class="text-blue-600" />
                                        <span>Peta Standar / Jalan</span>
                                    </div>
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    @click="switchTileLayer('satellite')"
                                    :class="['px-2.5 py-2 rounded-lg text-xs font-semibold cursor-pointer flex items-center justify-between', activeTileKey === 'satellite' ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-100']"
                                >
                                    <div class="flex items-center gap-2">
                                        <Layers :size="15" class="text-emerald-600" />
                                        <span>Satelit (Tampak Langsung)</span>
                                    </div>
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    @click="switchTileLayer('voyager')"
                                    :class="['px-2.5 py-2 rounded-lg text-xs font-semibold cursor-pointer flex items-center justify-between', activeTileKey === 'voyager' ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-100']"
                                >
                                    <div class="flex items-center gap-2">
                                        <MapPin :size="15" class="text-purple-600" />
                                        <span>Vektor Clean Modern</span>
                                    </div>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>

                        <!-- Fullscreen Toggle Button -->
                        <Button
                            @click="toggleFullscreen"
                            variant="outline"
                            size="sm"
                            class="h-9 px-3 rounded-xl border-slate-300 text-xs font-bold gap-1.5 flex-1 md:flex-none justify-center"
                        >
                            <component :is="isFullscreen ? Minimize2 : Maximize2" :size="14" class="shrink-0" />
                            <span>{{ isFullscreen ? 'Keluar' : 'Fullscreen' }}</span>
                        </Button>
                    </div>
                </div>

                <!-- Row 2: Multi-Select Searchable Combobox Filter Row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- Multi-Select Satker UPT Imigrasi -->
                    <div class="space-y-1">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Filter Satker UPT</span>
                        <MultiCombobox
                            v-model="selectedUptIds"
                            :options="uptOptions"
                            placeholder="Semua Satker UPT Imigrasi se-Sumut..."
                            searchPlaceholder="Cari Kanim UPT..."
                        />
                    </div>

                    <!-- Multi-Select Status Kerawanan -->
                    <div class="space-y-1">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Filter Status Kerawanan</span>
                        <MultiCombobox
                            v-model="selectedStatuses"
                            :options="statusOptions"
                            placeholder="Semua Status Kerawanan..."
                            searchPlaceholder="Pilih status..."
                        />
                    </div>
                </div>

            </div>

            <!-- Main GIS Workspace Container -->
            <div :class="[
                'transition-all duration-300 grid gap-4',
                isFullscreen ? 'fixed inset-0 z-50 bg-slate-900 p-4 grid-cols-1' : 'grid-cols-1 lg:grid-cols-3'
            ]">
                
                <!-- Map Left Container (2 Columns) -->
                <div :class="[
                    'relative rounded-2xl overflow-hidden border border-slate-200/90 shadow-xl bg-slate-100 min-h-[420px]',
                    isFullscreen ? 'w-full h-full' : 'lg:col-span-2 h-[420px] lg:h-[calc(100vh-290px)]'
                ]">
                    
                    <!-- Leaflet DOM Mount Point -->
                    <div ref="mapContainer" class="w-full h-full z-10"></div>

                    <!-- Floating Fullscreen Close Trigger -->
                    <button
                        v-if="isFullscreen"
                        @click="toggleFullscreen"
                        class="absolute top-4 left-4 z-20 bg-white/95 backdrop-blur-md px-3.5 py-2 rounded-xl shadow-lg border border-slate-200 text-xs font-bold text-slate-800 flex items-center gap-2 hover:bg-white transition-colors cursor-pointer"
                    >
                        <Minimize2 :size="14" class="text-blue-600" />
                        <span>Tutup Mode Fullscreen</span>
                    </button>
                </div>

                <!-- Side Panel Right Container (1 Column - Mode Normal) -->
                <div v-if="!isFullscreen" class="w-full">
                    <SpatialSidePanel :desa="selectedDesa" @close="selectedDesa = null" />
                </div>

            </div>

        </div>
    </AppLayout>
</template>

<style>
/* Styling Permanent Tooltip Label Nama Desa di Atas Pin Marker */
.custom-permanent-village-label {
    background: rgba(255, 255, 255, 0.95) !important;
    backdrop-filter: blur(4px) !important;
    border: 1px solid rgba(226, 232, 240, 0.9) !important;
    border-radius: 6px !important;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important;
    color: #0f172a !important;
    font-family: 'Inter', sans-serif !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    padding: 2px 6px !important;
    white-space: nowrap !important;
    pointer-events: none !important;
}

.custom-permanent-village-label::before {
    border-top-color: rgba(255, 255, 255, 0.95) !important;
}
</style>
