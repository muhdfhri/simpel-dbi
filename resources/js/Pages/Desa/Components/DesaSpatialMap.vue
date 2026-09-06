<script setup lang="ts">
import { ref, onMounted, nextTick, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    MapPin,
    UserCheck,
    Building2,
    Phone,
    Mail,
    ShieldCheck,
    Plus,
    Maximize2,
    Minimize2,
    Layers,
    Globe,
    CheckCircle2,
    AlertCircle,
    UserX,
    ExternalLink
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

interface DesaProps {
    id: number;
    nama: string;
    status_terkini: string;
    lat: number;
    lng: number;
    upt_nama: string;
}

interface PimpasaItem {
    id: number;
    name: string;
    avatar: string;
}

interface UptProps {
    id: number;
    nama: string;
    tipe: string;
}

interface StatsProps {
    total: number;
    diajukan: number;
    minta_perbaikan: number;
    diverifikasi: number;
    selesai: number;
}

const props = defineProps<{
    desa: DesaProps | null;
    pimpasaList?: PimpasaItem[] | null;
    upt: UptProps | null;
    stats?: StatsProps | null;
}>();

const mapContainer = ref<HTMLElement | null>(null);
const isFullscreen = ref(false);
const activeTileKey = ref<'osm' | 'satellite' | 'voyager'>('osm');

let mapInstance: any = null;
let currentTileLayer: any = null;

// Tile Layers Configuration (100% Gratis, Open-Source & Clean)
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
        name: 'Peta Vektor Minimalis',
        url: 'https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}',
        attribution: 'Tiles &copy; Esri &mdash; Esri, DeLorme, NAVTEQ',
    },
};

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

const toggleFullscreen = () => {
    isFullscreen.value = !isFullscreen.value;
    nextTick(() => {
        if (mapInstance) {
            setTimeout(() => mapInstance.invalidateSize(), 300);
        }
    });
};

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
    const centerLat = props.desa?.lat || 3.5952;
    const centerLng = props.desa?.lng || 98.6722;

    mapInstance = L.map(mapContainer.value, {
        zoomControl: false,
    }).setView([centerLat, centerLng], 12);

    L.control.zoom({ position: 'topright' }).addTo(mapInstance);
    switchTileLayer('osm');

    // Marker Desa Binaan
    if (props.desa) {
        const colorClass = props.desa.status_terkini === 'aman' ? '#10b981' : (props.desa.status_terkini === 'pembinaan' ? '#f59e0b' : '#ef4444');

        const customIcon = L.divIcon({
            className: 'custom-leaflet-pin',
            html: `<div style="background-color: ${colorClass}; width: 26px; height: 26px; border-radius: 50%; border: 3px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;"><div style="width: 8px; height: 8px; background: white; border-radius: 50%;"></div></div>`,
            iconSize: [26, 26],
            iconAnchor: [13, 13],
        });

        const marker = L.marker([centerLat, centerLng], { icon: customIcon }).addTo(mapInstance);

        const totalLaporan = props.stats?.total ?? 0;
        const diverifikasiCount = props.stats?.diverifikasi ?? 0;
        const selesaiCount = props.stats?.selesai ?? 0;

        marker.bindTooltip(`Desa Binaan: ${props.desa.nama} (${totalLaporan} Laporan)`, {
            permanent: true,
            direction: 'top',
            offset: [0, -14],
            className: 'custom-permanent-village-label',
        });

        const popupContent = `
            <div style="font-family: Inter, sans-serif; padding: 6px; min-width: 200px;">
                <div style="font-weight: 700; font-size: 13px; color: #0f172a;">${props.desa.nama}</div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">${props.desa.upt_nama}</div>
                <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #e2e8f0; display: flex; flex-direction: column; gap: 4px; font-size: 11px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #64748b; font-weight: 500;">Total Laporan:</span>
                        <span style="font-weight: 700; color: #033566; font-variant-numeric: tabular-nums;">${totalLaporan} Tiket</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #64748b; font-weight: 500;">Diverifikasi:</span>
                        <span style="font-weight: 600; color: #0284c7; font-variant-numeric: tabular-nums;">${diverifikasiCount}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #64748b; font-weight: 500;">Selesai:</span>
                        <span style="font-weight: 600; color: #16a34a; font-variant-numeric: tabular-nums;">${selesaiCount}</span>
                    </div>
                </div>
            </div>
        `;

        marker.bindPopup(popupContent);
    }
};

onMounted(() => {
    initMap();
});
</script>

<template>
    <div class="space-y-4 font-sans">
        
        <!-- Toolbar Atas: Basemap Switcher & Status Info (Responsive Clean Layout) -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-2xs space-y-3">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                
                <!-- Left Title & Info Badge -->
                <div class="flex items-start sm:items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center border border-slate-200/60 shrink-0 mt-0.5 sm:mt-0">
                        <MapPin :size="18" />
                    </div>
                    <div class="space-y-0.5">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-sm font-bold text-slate-900 leading-tight">
                                Wilayah Desa Binaan: {{ desa?.nama || 'Desa Binaan' }}
                            </h3>
                            <span v-if="stats" class="px-2 py-0.5 rounded-md text-[10px] sm:text-[11px] font-bold font-sans tabular-nums bg-blue-50 text-blue-800 border border-blue-200/80">
                                {{ stats.total }} Laporan Terdaftar
                            </span>
                        </div>
                        <p class="text-xs text-slate-500">
                            Pemataan koordinat desa & petugas PIMPASA pengampu resmi
                        </p>
                    </div>
                </div>

                <!-- Right Control Buttons (Basemap & Fullscreen) -->
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <!-- Switcher basemap -->
                    <Button
                        @click="switchTileLayer(activeTileKey === 'osm' ? 'satellite' : (activeTileKey === 'satellite' ? 'voyager' : 'osm'))"
                        variant="outline"
                        size="sm"
                        class="h-9 px-3 rounded-xl border-slate-300 text-xs font-bold gap-1.5 shadow-2xs bg-white flex-1 md:flex-none justify-center"
                    >
                        <Layers :size="14" class="text-blue-600 shrink-0" />
                        <span class="truncate">Tipe: {{ activeTileKey === 'osm' ? 'Standar' : (activeTileKey === 'satellite' ? 'Satelit' : 'Vektor') }}</span>
                    </Button>

                    <Button
                        @click="toggleFullscreen"
                        variant="outline"
                        size="sm"
                        class="h-9 px-3 rounded-xl border-slate-300 text-xs font-bold gap-1.5 shadow-2xs bg-white flex-1 md:flex-none justify-center"
                    >
                        <component :is="isFullscreen ? Minimize2 : Maximize2" :size="14" class="shrink-0" />
                        <span>{{ isFullscreen ? 'Keluar' : 'Fullscreen' }}</span>
                    </Button>
                </div>
            </div>
        </div>

        <!-- GIS WORKSPACE GRID (2 Panel Layout) -->
        <div :class="[
            'grid gap-4 transition-all duration-300',
            isFullscreen ? 'fixed inset-0 z-50 bg-slate-900 p-4 grid-cols-1' : 'grid-cols-1 lg:grid-cols-3'
        ]">
            
            <!-- LEFT PANEL: Leaflet Map Workspace (2 Kolom) -->
            <div :class="[
                'relative rounded-2xl overflow-hidden border border-slate-200/90 shadow-xs bg-slate-100 min-h-[420px]',
                isFullscreen ? 'w-full h-full' : 'lg:col-span-2 h-[420px] lg:h-[calc(100vh-270px)]'
            ]">
                <div ref="mapContainer" class="w-full h-full z-10"></div>

                <!-- Floating Fullscreen Close Trigger -->
                <button
                    v-if="isFullscreen"
                    @click="toggleFullscreen"
                    class="absolute top-4 left-4 z-20 bg-white/95 backdrop-blur-md px-3.5 py-2 rounded-xl shadow-lg border border-slate-200 text-xs font-bold text-slate-800 flex items-center gap-2 hover:bg-white transition-colors cursor-pointer"
                >
                    <Minimize2 :size="14" class="text-primary" />
                    <span>Tutup Mode Fullscreen</span>
                </button>
            </div>

            <!-- RIGHT PANEL: CARD MATCHING EXECUTIVE PANEL ADMIN ('Detail Spasial Desa') -->
            <div v-if="!isFullscreen" class="h-full bg-white border border-slate-200/90 rounded-2xl shadow-xl p-5 flex flex-col justify-between font-sans">
                
                <!-- Header Panel (Identik Admin Side Panel) -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            Tim Petugas PIMPASA
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                            Pembina Wilayah
                        </span>
                    </div>

                    <!-- Detail Pengampu & Status Wilayah -->
                    <div v-if="desa" class="space-y-3">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 leading-snug">
                                {{ desa.nama }}
                            </h3>
                            <p class="text-xs font-medium text-slate-500 mt-0.5 flex items-center gap-1.5">
                                <Building2 :size="13" class="text-slate-400 shrink-0" />
                                <span>{{ desa.upt_nama }}</span>
                            </p>
                        </div>

                        <div class="h-px bg-slate-100"></div>

                        <!-- Attributes Table Style (Identik SpatialSidePanel) -->
                        <div class="space-y-2 text-xs">
                            <div class="flex items-center justify-between py-1 border-t border-slate-100/60">
                                <span class="text-slate-500 font-medium">Koordinat GPS:</span>
                                <span class="font-mono font-bold text-slate-800">{{ desa.lat.toFixed(4) }}, {{ desa.lng.toFixed(4) }}</span>
                            </div>

                            <div class="flex items-center justify-between py-1 border-t border-slate-100/60">
                                <span class="text-slate-500 font-medium">Total Aduan Desa:</span>
                                <span class="font-mono font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200/80">
                                    {{ stats?.total ?? 0 }} Tiket
                                </span>
                            </div>

                            <div class="flex items-center justify-between py-1 border-t border-slate-100/60">
                                <span class="text-slate-500 font-medium">Status Diverifikasi:</span>
                                <span class="font-mono font-bold text-sky-700 bg-sky-50 px-2 py-0.5 rounded-md border border-sky-200/80">
                                    {{ stats?.diverifikasi ?? 0 }} Tiket
                                </span>
                            </div>

                            <div class="flex items-center justify-between py-1 border-t border-slate-100/60">
                                <span class="text-slate-500 font-medium">Status Selesai:</span>
                                <span class="font-mono font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200/80">
                                    {{ stats?.selesai ?? 0 }} Tiket
                                </span>
                            </div>

                            <div class="flex items-center justify-between py-1 border-t border-slate-100/60">
                                <span class="text-slate-500 font-medium">Status Kerawanan:</span>
                                <span
                                    :class="[
                                        'px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase border',
                                        desa.status_terkini === 'aman' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                                        (desa.status_terkini === 'pembinaan' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-red-50 text-red-700 border-red-200')
                                    ]"
                                >
                                    {{ desa.status_terkini }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- List Nama Petugas PIMPASA -->
                    <div class="pt-2 border-t border-slate-100 space-y-2">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Daftar Petugas Pengampu:</span>
                        <div v-if="pimpasaList && pimpasaList.length > 0" class="space-y-2 max-h-[160px] overflow-y-auto pr-1 custom-thin-scrollbar">
                            <div
                                v-for="officer in pimpasaList"
                                :key="officer.id"
                                class="flex items-center gap-2.5 p-2 rounded-xl border border-slate-200/80 bg-slate-50/40 hover:bg-slate-100/60 transition-colors"
                            >
                                <div class="w-7 h-7 rounded-lg bg-slate-900 text-white flex items-center justify-center text-[11px] font-bold shadow-2xs shrink-0">
                                    {{ officer.avatar }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h5 class="text-xs font-bold text-slate-900 truncate">
                                        {{ officer.name }}
                                    </h5>
                                    <p class="text-[10px] text-slate-500 font-medium">
                                        Petugas Pembina PIMPASA
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-xs text-slate-400 italic">Belum ada petugas terdaftar.</div>
                    </div>

                    <!-- Action CTA Button -->
                    <div class="pt-2 border-t border-slate-100">
                        <Link href="/desa/laporan/create" class="w-full">
                            <Button class="w-full h-9 bg-primary hover:bg-[#04407D] text-primary-foreground font-semibold text-xs rounded-xl shadow-xs flex items-center justify-center gap-2">
                                <Plus :size="15" />
                                <span>Buat Pengajuan Laporan Baru</span>
                            </Button>
                        </Link>
                    </div>
                </div>

                <!-- Footer Info Panel Identik Admin Side Panel -->
                <div class="pt-4 border-t border-slate-100 text-[10px] text-slate-400 flex items-center justify-between">
                    <span>Sistem Pemetaan SIMPEL DBI</span>
                    <span class="font-mono text-emerald-600 font-bold">● Active GIS Sync</span>
                </div>

            </div>

        </div>

    </div>
</template>

<style>
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
