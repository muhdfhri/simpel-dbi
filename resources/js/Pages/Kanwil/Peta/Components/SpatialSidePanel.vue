<script setup lang="ts">
import { Building2, User, MapPin, Ticket, ShieldCheck, AlertTriangle, AlertCircle, X, Clock, FileText } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

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
    upt_nama: string;
    pimpasa_name: string;
    lat: number;
    lng: number;
    status_terkini: string;
    total_laporan: number;
    laporan_diajukan?: number;
    laporan_diverifikasi?: number;
    laporan_selesai?: number;
    latest_laporan_at?: string | null;
    latest_laporan?: LatestLaporan | null;
}

const props = defineProps<{
    desa: DesaMarker | null;
}>();

const emit = defineEmits(['close']);
</script>

<template>
    <div class="h-full bg-white border border-slate-200/90 rounded-2xl shadow-xl p-5 flex flex-col justify-between font-sans">
        
        <!-- Header Panel -->
        <div>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    Detail Spasial Desa
                </span>
                <Button v-if="desa" @click="emit('close')" variant="ghost" size="sm" class="h-7 w-7 p-0 rounded-lg text-slate-400 hover:text-slate-700">
                    <X :size="14" />
                </Button>
            </div>

            <!-- Empty State -->
            <div v-if="!desa" class="py-16 text-center space-y-2">
                <MapPin :size="32" class="mx-auto text-slate-300 animate-bounce" />
                <p class="text-xs font-bold text-slate-700">Pilih Marker Desa di Peta</p>
                <p class="text-[11px] text-slate-400 max-w-[200px] mx-auto">
                    Klik salah satu titik lokasi desa di peta untuk menampilkan detail spasial resmi.
                </p>
            </div>

            <!-- Content Detail -->
            <div v-else class="space-y-4 pt-3">
                
                <!-- Nama Desa & UPT -->
                <div>
                    <h3 class="text-base font-bold text-slate-900 leading-snug">
                        {{ desa.nama }}
                    </h3>
                    <p class="text-xs font-medium text-slate-500 mt-0.5 flex items-center gap-1.5">
                        <Building2 :size="13" class="text-slate-400 shrink-0" />
                        <span>{{ desa.upt_nama }}</span>
                    </p>
                </div>

                <!-- Laporan Terbaru Wilayah ini -->
                <div v-if="desa.latest_laporan" class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80 space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider flex items-center gap-1">
                            <Clock :size="12" class="text-primary" />
                            <span>Laporan Paling Baru</span>
                        </span>
                        <span class="font-sans tabular-nums font-bold text-[10px] text-slate-900 bg-slate-200/70 px-1.5 py-0.5 rounded border border-slate-300/60">
                            {{ desa.latest_laporan.kode_tiket }}
                        </span>
                    </div>
                    <h5 class="text-xs font-bold text-slate-900 leading-snug line-clamp-1">
                        {{ desa.latest_laporan.judul }}
                    </h5>
                    <div class="flex items-center justify-between text-[10px] pt-1 border-t border-slate-200/60">
                        <span class="text-slate-500 font-medium">
                            {{ desa.latest_laporan.submitted_at_formatted }}
                        </span>
                        <span class="font-bold text-slate-800">
                            {{ desa.latest_laporan.status_label }}
                        </span>
                    </div>
                </div>

                <div class="h-px bg-slate-100"></div>

                <!-- Attributes Table Style -->
                <div class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between py-1">
                        <span class="text-slate-500 font-medium">Petugas PIMPASA:</span>
                        <span class="font-bold text-slate-900 truncate max-w-[170px] text-right">{{ desa.pimpasa_name }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-t border-slate-100/60">
                        <span class="text-slate-500 font-medium">Koordinat GPS:</span>
                        <span class="font-mono font-bold text-slate-800">{{ desa.lat.toFixed(4) }}, {{ desa.lng.toFixed(4) }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-t border-slate-100/60">
                        <span class="text-slate-500 font-medium">Total Aduan Masuk:</span>
                        <span class="font-mono font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200/80">
                            {{ desa.total_laporan }} Tiket
                        </span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-t border-slate-100/60">
                        <span class="text-slate-500 font-medium">Status Diverifikasi:</span>
                        <span class="font-mono font-bold text-sky-700 bg-sky-50 px-2 py-0.5 rounded-md border border-sky-200/80">
                            {{ desa.laporan_diverifikasi ?? 0 }} Tiket
                        </span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-t border-slate-100/60">
                        <span class="text-slate-500 font-medium">Status Selesai:</span>
                        <span class="font-mono font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200/80">
                            {{ desa.laporan_selesai ?? 0 }} Tiket
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
        </div>

        <!-- Footer Info -->
        <div v-if="desa" class="pt-4 border-t border-slate-100 text-[10px] text-slate-400 flex items-center justify-between">
            <span>Sistem Pemetaan SIMPEL DBI</span>
            <span class="font-mono text-emerald-600 font-bold">● Active GIS Sync</span>
        </div>

    </div>
</template>
