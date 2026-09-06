<script setup lang="ts">
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    MapPin,
    UserCheck,
    Building2,
    Layers,
    Maximize2,
    Minimize2,
    Navigation,
    UserX,
    Search,
    Filter,
    ShieldAlert,
    CheckCircle2,
    Clock,
    FileText
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import MultiCombobox from '@/components/ui/combobox/MultiCombobox.vue';

interface DesaItem {
    id: number;
    nama: string;
    kecamatan?: string;
    kabupaten?: string;
    kode_desa?: string;
    lat: number;
    lng: number;
    status_terkini: string;
    pimpasa_id?: number;
    pimpasa_name?: string;
    total_laporan: number;
    laporan_diverifikasi: number;
    laporan_selesai: number;
    upt_nama?: string;
}

interface PerangkatDesaUser {
    id: number;
    name: string;
    email: string;
    avatar: string;
    desa_id?: number;
    desa_nama: string;
    kecamatan: string;
    lat: number;
    lng: number;
    status_terkini: string;
}

interface UptProps {
    id: number;
    nama: string;
    tipe: string;
}

const props = defineProps<{
    desaBinaanList: DesaItem[];
    perangkatDesaList: PerangkatDesaUser[];
    upt: UptProps | null;
    stats?: {
        total: number;
        diajukan: number;
        diverifikasi: number;
        selesai: number;
    } | null;
}>();

const mapContainer = ref<HTMLElement | null>(null);
const isFullscreen = ref(false);
const activeTileKey = ref<'osm' | 'satellite' | 'voyager'>('osm');

// Search & MultiCombobox State untuk Desa Binaan & Status Kerawanan
const searchQuery = ref('');
const selectedDesaIds = ref<string[]>([]);
const selectedStatuses = ref<string[]>([]);

let mapInstance: any = null;
let currentTileLayer: any = null;
const markerMap = new Map<number, any>();

// MultiCombobox Options untuk Filter Desa Binaan UPT PIMPASA
const desaOptions = computed(() =>
    props.desaBinaanList.map(d => ({
        value: d.id.toString(),
        label: d.nama,
        description: d.kecamatan ? `Kec. ${d.kecamatan}` : 'Desa Binaan',
    }))
);

const statusOptions = [
    { value: 'aman', label: 'Aman', description: 'Kondisi kondusif & bebas aduan' },
    { value: 'pembinaan', label: 'Perlu Pembinaan', description: 'Memerlukan pembinaan PIMPASA' },
    { value: 'aduan', label: 'Ada Aduan', description: 'Terdapat tiket aduan aktif' },
];

// Calculation Status Summary Pins Top Header
const countAman = computed(() => props.desaBinaanList.filter(d => d.status_terkini === 'aman').length);
const countPembinaan = computed(() => props.desaBinaanList.filter(d => d.status_terkini === 'pembinaan').length);
const countAduan = computed(() => props.desaBinaanList.filter(d => d.status_terkini === 'aduan').length);
const totalLaporanAll = computed(() => props.desaBinaanList.reduce((acc, d) => acc + (d.total_laporan || 0), 0));

// Tile Layers Configuration
const tileLayersConfig = {
    osm: {
        name: 'Peta Standar (OpenStreetMap)',
        url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        attribution: '&copy; OpenStreetMap contributors',
    },
    satellite: {
        name: 'Peta Satelit (Tampak Langsung)',
        url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
        attribution: 'Tiles &copy; Esri &mdash; Source: Esri',
    },
    voyager: {
        name: 'Peta Vektor Minimalis',
        url: 'https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}',
        attribution: 'Tiles &copy; Esri',
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

// Filter Perangkat Desa Users berdasarkan MultiCombobox Desa & Status Kerawanan & Search Input
const filteredPerangkatDesaList = computed(() => {
    return props.perangkatDesaList.filter((user) => {
        const matchesSearch = searchQuery.value === '' ||
            user.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            user.desa_nama.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            user.kecamatan.toLowerCase().includes(searchQuery.value.toLowerCase());

        const matchesDesa = selectedDesaIds.value.length === 0 || (user.desa_id && selectedDesaIds.value.includes(user.desa_id.toString()));
        const matchesStatus = selectedStatuses.value.length === 0 || selectedStatuses.value.includes(user.status_terkini);

        return matchesSearch && matchesDesa && matchesStatus;
    });
});

// Update marker visibility dan recalculate bounds
const updateMapMarkersVisibility = () => {
    if (!mapInstance || typeof (window as any).L === 'undefined') return;

    const visibleBounds: any[] = [];

    props.desaBinaanList.forEach((desa) => {
        const marker = markerMap.get(desa.id);
        if (!marker) return;

        const matchesDesa = selectedDesaIds.value.length === 0 || selectedDesaIds.value.includes(desa.id.toString());
        const matchesStatus = selectedStatuses.value.length === 0 || selectedStatuses.value.includes(desa.status_terkini);
        const matchesSearch = searchQuery.value === '' || desa.nama.toLowerCase().includes(searchQuery.value.toLowerCase());

        const isVisible = matchesDesa && matchesStatus && matchesSearch;

        if (isVisible) {
            if (!mapInstance.hasLayer(marker)) {
                marker.addTo(mapInstance);
            }
            visibleBounds.push([desa.lat, desa.lng]);
        } else {
            if (mapInstance.hasLayer(marker)) {
                mapInstance.removeLayer(marker);
            }
        }
    });

    if (visibleBounds.length > 0) {
        mapInstance.fitBounds(visibleBounds, { padding: [50, 50], maxZoom: 14 });
    }
};

watch([selectedDesaIds, selectedStatuses, searchQuery], () => {
    updateMapMarkersVisibility();
});

// FlyTo Map Focus Animation untuk tombol di panel kanan
const flyToLocation = (lat: number, lng: number, villageId?: number) => {
    if (!mapInstance) return;
    mapInstance.flyTo([lat, lng], 14, {
        animate: true,
        duration: 1.2,
    });

    if (villageId && markerMap.has(villageId)) {
        const marker = markerMap.get(villageId);
        setTimeout(() => {
            if (marker && mapInstance.hasLayer(marker)) {
                marker.openPopup();
            }
        }, 1200);
    }
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
    
    // Tentukan titik tengah peta
    const centerLat = props.desaBinaanList.length > 0 ? props.desaBinaanList[0].lat : 3.5952;
    const centerLng = props.desaBinaanList.length > 0 ? props.desaBinaanList[0].lng : 98.6722;

    mapInstance = L.map(mapContainer.value, {
        zoomControl: false,
    }).setView([centerLat, centerLng], 11);

    L.control.zoom({ position: 'topright' }).addTo(mapInstance);
    switchTileLayer('osm');

    // Render Markers seluruh Desa Binaan UPT PIMPASA
    props.desaBinaanList.forEach((desa) => {
        const colorClass = desa.status_terkini === 'aduan' ? '#ef4444' : (desa.status_terkini === 'pembinaan' ? '#f59e0b' : '#10b981');

        const customIcon = L.divIcon({
            className: 'custom-leaflet-pin-pimpasa',
            html: `<div style="background-color: ${colorClass}; width: 26px; height: 26px; border-radius: 50%; border: 3px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;"><div style="width: 8px; height: 8px; background: white; border-radius: 50%;"></div></div>`,
            iconSize: [26, 26],
            iconAnchor: [13, 13],
        });

        const marker = L.marker([desa.lat, desa.lng], { icon: customIcon }).addTo(mapInstance);
        markerMap.set(desa.id, marker);

        marker.bindTooltip(`Desa Binaan: ${desa.nama} (${desa.total_laporan} Laporan)`, {
            permanent: true,
            direction: 'top',
            offset: [0, -14],
            className: 'custom-permanent-village-label',
        });

        const popupContent = `
            <div style="font-family: Inter, sans-serif; padding: 6px; min-width: 220px;">
                <div style="font-weight: 700; font-size: 13px; color: #0f172a;">${desa.nama}</div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Petugas Pembina: <b>${desa.pimpasa_name}</b></div>
                <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #e2e8f0; display: flex; flex-direction: column; gap: 4px; font-size: 11px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #64748b; font-weight: 500;">Total Laporan Desa:</span>
                        <span style="font-weight: 700; color: #033566; font-variant-numeric: tabular-nums;">${desa.total_laporan} Tiket</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #64748b; font-weight: 500;">Diverifikasi PIMPASA:</span>
                        <span style="font-weight: 600; color: #0284c7; font-variant-numeric: tabular-nums;">${desa.laporan_diverifikasi}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #64748b; font-weight: 500;">Selesai UPT:</span>
                        <span style="font-weight: 600; color: #16a34a; font-variant-numeric: tabular-nums;">${desa.laporan_selesai}</span>
                    </div>
                </div>
            </div>
        `;

        marker.bindPopup(popupContent);
    });
};

onMounted(() => {
    initMap();
});
</script>

<template>
    <div class="space-y-4 font-sans">
        
        <!-- EXECUTIVE FILTER TOOLBAR (IDENTIK ADMIN/KANWIL PETA HEADER) -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-2xs space-y-3">
            
            <!-- TOP ROW: Summary Metrics & Controls -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                <!-- Metrics Summary Pins -->
                <div class="flex items-center gap-3 text-xs flex-wrap">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 shadow-2xs"></span>
                        <span class="font-bold text-slate-700">Aman ({{ countAman }})</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-amber-500 shadow-2xs"></span>
                        <span class="font-bold text-slate-700">Perlu Pembinaan ({{ countPembinaan }})</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-red-500 shadow-2xs"></span>
                        <span class="font-bold text-slate-700">Ada Aduan ({{ countAduan }})</span>
                    </div>
                    <div class="h-4 w-px bg-slate-200 hidden sm:block"></div>
                    <div class="flex items-center gap-1.5 bg-blue-50 px-2.5 py-1 rounded-full text-blue-800 border border-blue-200/80 font-bold font-sans tabular-nums text-[11px]">
                        <FileText :size="13" class="text-blue-600 shrink-0" />
                        <span>Total Laporan: {{ totalLaporanAll }} Tiket</span>
                    </div>
                </div>

                <!-- Right Controls: Basemap Switcher & Fullscreen Toggle -->
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <Button
                        @click="switchTileLayer(activeTileKey === 'osm' ? 'satellite' : (activeTileKey === 'satellite' ? 'voyager' : 'osm'))"
                        variant="outline"
                        size="sm"
                        class="flex-1 md:flex-none justify-center h-9 px-3 rounded-xl border-slate-300 text-xs font-bold gap-1.5 shrink-0 bg-white"
                    >
                        <Layers :size="14" class="text-blue-600 shrink-0" />
                        <span class="truncate">Tipe Peta: {{ activeTileKey === 'osm' ? 'Standar' : (activeTileKey === 'satellite' ? 'Satelit' : 'Vektor') }}</span>
                    </Button>

                    <Button
                        @click="toggleFullscreen"
                        variant="outline"
                        size="sm"
                        class="flex-1 md:flex-none justify-center h-9 px-3 rounded-xl border-slate-300 text-xs font-bold gap-1.5 shrink-0"
                    >
                        <component :is="isFullscreen ? Minimize2 : Maximize2" :size="14" class="shrink-0" />
                        <span class="truncate">{{ isFullscreen ? 'Keluar Fullscreen' : 'Mode Fullscreen' }}</span>
                    </Button>
                </div>
            </div>

            <!-- BOTTOM ROW: MultiCombobox Filtering Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-slate-100">
                <!-- Filter Desa Binaan (Multi-Select >1 Desa) -->
                <div class="space-y-1">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Filter Desa Binaan (Bisa Pilih > 1 Desa)</span>
                    <MultiCombobox
                        v-model="selectedDesaIds"
                        :options="desaOptions"
                        placeholder="Semua Desa Binaan UPT..."
                        searchPlaceholder="Cari Desa Binaan..."
                    />
                </div>

                <!-- Filter Status Kerawanan -->
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

        <!-- GIS WORKSPACE GRID (2 Panel Layout) -->
        <div :class="[
            'grid gap-4 transition-all duration-300',
            isFullscreen ? 'fixed inset-0 z-50 bg-slate-900 p-4 grid-cols-1' : 'grid-cols-1 lg:grid-cols-3 min-h-[480px]'
        ]">
            
            <!-- LEFT PANEL: Leaflet Map Workspace (2 Kolom) -->
            <div :class="[
                'relative rounded-2xl overflow-hidden border border-slate-200/90 shadow-xs bg-slate-100',
                isFullscreen ? 'w-full h-full' : 'lg:col-span-2 min-h-[440px]'
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
                            Perangkat Desa Binaan
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 font-sans tabular-nums">
                            {{ filteredPerangkatDesaList.length }} / {{ perangkatDesaList.length }} User
                        </span>
                    </div>

                    <!-- List User Perangkat Desa Binaan dengan Attributes Table Style Identik Admin -->
                    <div v-if="filteredPerangkatDesaList && filteredPerangkatDesaList.length > 0" class="space-y-3 max-h-[380px] overflow-y-auto pr-1.5 custom-thin-scrollbar">
                        <div
                            v-for="user in filteredPerangkatDesaList"
                            :key="user.id"
                            class="p-3.5 rounded-xl border border-slate-200/80 bg-slate-50/40 hover:bg-slate-100/60 transition-colors space-y-2.5"
                        >
                            <!-- Header Item User -->
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-primary text-primary-foreground flex items-center justify-center text-xs font-bold shadow-2xs shrink-0">
                                        {{ user.avatar }}
                                    </div>
                                    <div class="min-w-0">
                                        <h5 class="text-xs font-bold text-slate-900 truncate">
                                            {{ user.name }}
                                        </h5>
                                        <p class="text-[11px] font-medium text-slate-500 truncate flex items-center gap-1 mt-0.5">
                                            <Building2 :size="12" class="text-slate-400 shrink-0" />
                                            <span>{{ user.desa_nama }}</span>
                                        </p>
                                    </div>
                                </div>

                                <span
                                    :class="[
                                        'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase border shrink-0',
                                        user.status_terkini === 'aman' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                                        (user.status_terkini === 'pembinaan' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-red-50 text-red-700 border-red-200')
                                    ]"
                                >
                                    {{ user.status_terkini }}
                                </span>
                            </div>

                            <!-- Attributes Table Style Rows (Identik SpatialSidePanel Admin) -->
                            <div class="pt-2 border-t border-slate-200/60 space-y-1.5 text-xs">
                                <div class="flex items-center justify-between py-0.5">
                                    <span class="text-slate-500 font-medium text-[11px]">Kecamatan Wilayah:</span>
                                    <span class="font-bold text-slate-800 text-[11px]">{{ user.kecamatan }}</span>
                                </div>
                                <div class="flex items-center justify-between py-0.5 border-t border-slate-100">
                                    <span class="text-slate-500 font-medium text-[11px]">Koordinat GPS:</span>
                                    <span class="font-mono font-bold text-slate-700 text-[11px]">{{ user.lat.toFixed(4) }}, {{ user.lng.toFixed(4) }}</span>
                                </div>
                            </div>

                            <!-- Action Button Fokus Lokasi Leaflet Map -->
                            <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between">
                                <span class="text-[10px] text-slate-400 font-medium">Perangkat Desa Pembina</span>
                                <Button
                                    @click="flyToLocation(user.lat, user.lng, user.desa_id)"
                                    size="sm"
                                    variant="outline"
                                    class="h-7 px-2.5 text-[11px] font-bold border-slate-200 hover:bg-white text-primary flex items-center gap-1.5 shadow-2xs cursor-pointer rounded-lg"
                                >
                                    <Navigation :size="12" />
                                    <span>Fokus Lokasi</span>
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State Identik SpatialSidePanel -->
                    <div v-else class="py-12 text-center space-y-2">
                        <UserX :size="32" class="mx-auto text-slate-300" />
                        <p class="text-xs font-bold text-slate-700">Tidak Ada Perangkat Desa Ditemukan</p>
                        <p class="text-[11px] text-slate-400 max-w-[200px] mx-auto">
                            Tidak ada user yang cocok dengan kriteria filter desa binaan saat ini.
                        </p>
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
