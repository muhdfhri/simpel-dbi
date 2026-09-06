<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import {
    ArrowUpDown,
    ArrowUp,
    ArrowDown,
    Building2,
    Eye,
    CheckCircle2,
    Clock,
    AlertTriangle,
    X,
    ShieldAlert
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

interface DesaItem {
    id: number;
    nama: string;
    pimpasa: string;
    status: string;
    laporan: number;
}

interface ScorecardItem {
    id: number;
    nama: string;
    tipe: string;
    desa_count: number;
    pimpasa_count: number;
    total_laporan: number;
    laporan_selesai: number;
    breached_count: number;
    completion_rate: number;
    avg_sla_hours: number;
    status_kepatuhan: string;
    desa_list?: DesaItem[];
}

const props = defineProps<{
    scorecards: ScorecardItem[];
}>();

const currentPage = ref(1);
const pageSize = ref(10);
const sortField = ref<string>('completion_rate');
const sortOrder = ref<'asc' | 'desc'>('desc');

// Reset to page 1 whenever scorecards change due to filters
watch(() => props.scorecards, () => {
    currentPage.value = 1;
});

const toggleSort = (field: string) => {
    if (sortField.value === field) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortOrder.value = 'desc';
    }
};

const processedScorecards = computed(() => {
    let list = [...props.scorecards];

    list.sort((a, b) => {
        let valA: any = '';
        let valB: any = '';

        if (sortField.value === 'nama') { valA = a.nama; valB = b.nama; }
        else if (sortField.value === 'tipe') { valA = a.tipe; valB = b.tipe; }
        else if (sortField.value === 'desa_count') { valA = a.desa_count; valB = b.desa_count; }
        else if (sortField.value === 'pimpasa_count') { valA = a.pimpasa_count; valB = b.pimpasa_count; }
        else if (sortField.value === 'total_laporan') { valA = a.total_laporan; valB = b.total_laporan; }
        else if (sortField.value === 'laporan_selesai') { valA = a.laporan_selesai; valB = b.laporan_selesai; }
        else if (sortField.value === 'completion_rate') { valA = a.completion_rate; valB = b.completion_rate; }
        else if (sortField.value === 'avg_sla_hours') { valA = a.avg_sla_hours; valB = b.avg_sla_hours; }
        else if (sortField.value === 'status_kepatuhan') { valA = a.status_kepatuhan; valB = b.status_kepatuhan; }

        if (typeof valA === 'number' && typeof valB === 'number') {
            return sortOrder.value === 'asc' ? valA - valB : valB - valA;
        }

        const cmp = String(valA).localeCompare(String(valB));
        return sortOrder.value === 'asc' ? cmp : -cmp;
    });

    return list;
});

const paginatedScorecards = computed(() => {
    const start = (currentPage.value - 1) * pageSize.value;
    return processedScorecards.value.slice(start, start + pageSize.value);
});

const totalPages = computed(() => Math.ceil(processedScorecards.value.length / pageSize.value) || 1);

// Detail Modal State
const isModalOpen = ref(false);
const selectedUpt = ref<ScorecardItem | null>(null);

const openDetailModal = (upt: ScorecardItem) => {
    selectedUpt.value = upt;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    selectedUpt.value = null;
};

const mockVillageList = [
    { nama: 'Desa Binaan Imigrasi Namorambe', pimpasa: 'Budi Santoso, S.SH.', status: 'Aktif', laporan: 18 },
    { nama: 'Desa Binaan Imigrasi Pancur Batu', pimpasa: 'Siti Rahmawati, A.Md.Im.', status: 'Aktif', laporan: 14 },
    { nama: 'Desa Binaan Imigrasi Kutalimbaru', pimpasa: 'Rian Hidayat, S.Tr.Im.', status: 'Aktif', laporan: 12 },
    { nama: 'Desa Binaan Imigrasi Sibolangit', pimpasa: 'Dewi Lestari, S.H.', status: 'Perlu Pendampingan', laporan: 6 },
];
</script>

<template>
    <div class="bg-white font-sans overflow-hidden">
        
        <!-- Table Container (Exact SLA Control Table Style) -->
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left text-slate-600 font-sans border-collapse">
                <thead class="bg-slate-100/70 text-slate-700 font-semibold uppercase tracking-wider text-[11px] border-b border-slate-200/80">
                    <tr>
                        <th @click="toggleSort('nama')" class="px-4 py-3.5 text-center w-14 cursor-pointer hover:bg-slate-200/60 transition-colors select-none">
                            <div class="flex items-center justify-center gap-1">
                                <span>NO</span>
                            </div>
                        </th>

                        <!-- Satker UPT Imigrasi Header -->
                        <th 
                            @click="toggleSort('nama')" 
                            class="px-5 py-3.5 cursor-pointer hover:bg-slate-200/60 transition-colors select-none whitespace-nowrap"
                        >
                            <div class="flex items-center gap-1.5">
                                <span>Satker UPT Imigrasi</span>
                                <ArrowUp v-if="sortField === 'nama' && sortOrder === 'asc'" :size="12" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'nama' && sortOrder === 'desc'" :size="12" class="text-slate-900" />
                                <ArrowUpDown v-else :size="12" class="text-slate-300" />
                            </div>
                        </th>

                        <!-- Jumlah Desa Header -->
                        <th 
                            @click="toggleSort('desa_count')" 
                            class="px-5 py-3.5 text-center cursor-pointer hover:bg-slate-200/60 transition-colors select-none whitespace-nowrap"
                        >
                            <div class="flex items-center justify-center gap-1.5">
                                <span>Desa</span>
                                <ArrowUp v-if="sortField === 'desa_count' && sortOrder === 'asc'" :size="12" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'desa_count' && sortOrder === 'desc'" :size="12" class="text-slate-900" />
                                <ArrowUpDown v-else :size="12" class="text-slate-300" />
                            </div>
                        </th>

                        <!-- Jumlah PIMPASA Header -->
                        <th 
                            @click="toggleSort('pimpasa_count')" 
                            class="px-5 py-3.5 text-center cursor-pointer hover:bg-slate-200/60 transition-colors select-none whitespace-nowrap"
                        >
                            <div class="flex items-center justify-center gap-1.5">
                                <span>PIMPASA</span>
                                <ArrowUp v-if="sortField === 'pimpasa_count' && sortOrder === 'asc'" :size="12" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'pimpasa_count' && sortOrder === 'desc'" :size="12" class="text-slate-900" />
                                <ArrowUpDown v-else :size="12" class="text-slate-300" />
                            </div>
                        </th>

                        <!-- Total Tiket Header (Centered) -->
                        <th 
                            @click="toggleSort('total_laporan')" 
                            class="px-5 py-3.5 text-center cursor-pointer hover:bg-slate-200/60 transition-colors select-none whitespace-nowrap"
                        >
                            <div class="flex items-center justify-center gap-1.5">
                                <span>Total Tiket</span>
                                <ArrowUp v-if="sortField === 'total_laporan' && sortOrder === 'asc'" :size="12" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'total_laporan' && sortOrder === 'desc'" :size="12" class="text-slate-900" />
                                <ArrowUpDown v-else :size="12" class="text-slate-300" />
                            </div>
                        </th>

                        <!-- Selesai Header (Centered) -->
                        <th class="px-5 py-3.5 text-center whitespace-nowrap">Selesai</th>

                        <!-- Rate Penyelesaian (%) Header (Centered) -->
                        <th 
                            @click="toggleSort('completion_rate')" 
                            class="px-5 py-3.5 text-center cursor-pointer hover:bg-slate-200/60 transition-colors select-none whitespace-nowrap"
                        >
                            <div class="flex items-center justify-center gap-1.5">
                                <span>Rate Selesai</span>
                                <ArrowUp v-if="sortField === 'completion_rate' && sortOrder === 'asc'" :size="12" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'completion_rate' && sortOrder === 'desc'" :size="12" class="text-slate-900" />
                                <ArrowUpDown v-else :size="12" class="text-slate-300" />
                            </div>
                        </th>

                        <!-- Rerata SLA Header -->
                        <th class="px-5 py-3.5 text-right whitespace-nowrap">Rerata SLA</th>

                        <!-- Status Kepatuhan Header -->
                        <th class="px-5 py-3.5 text-center whitespace-nowrap">Status Kepatuhan</th>

                        <!-- Aksi Header -->
                        <th class="px-5 py-3.5 text-center whitespace-nowrap">Aksi Pimpinan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    
                    <!-- Empty State -->
                    <tr v-if="processedScorecards.length === 0">
                        <td colspan="11" class="p-10 text-center text-slate-400 italic font-medium">
                            <ShieldAlert :size="32" class="mx-auto mb-2 opacity-40 text-slate-400" />
                            <p class="font-bold text-slate-700 text-xs">Tidak ada data UPT Imigrasi yang cocok dengan filter</p>
                        </td>
                    </tr>

                    <tr 
                        v-for="(item, index) in paginatedScorecards" 
                        :key="item.id" 
                        :class="[
                            'transition-all duration-150',
                            item.status_kepatuhan === 'PERLU EVALUASI'
                                ? 'bg-red-50/40 hover:bg-red-100/50 border-l-4 border-l-red-500'
                                : (item.status_kepatuhan === 'CUKUP'
                                    ? 'bg-amber-50/40 hover:bg-amber-100/50 border-l-4 border-l-amber-500'
                                    : 'hover:bg-slate-50/70')
                        ]"
                    >
                        <!-- No -->
                        <td class="px-4 py-4 text-center font-sans tabular-nums font-bold text-slate-900">
                            {{ (currentPage - 1) * pageSize + index + 1 }}
                        </td>

                        <!-- Satker UPT Imigrasi -->
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="font-bold text-slate-900 text-xs tracking-tight">{{ item.nama }}</div>
                        </td>

                        <!-- Jumlah Desa -->
                        <td class="px-5 py-4 text-center font-bold text-slate-900 tabular-nums">
                            {{ item.desa_count }}
                        </td>

                        <!-- Jumlah PIMPASA -->
                        <td class="px-5 py-4 text-center font-bold text-slate-900 tabular-nums">
                            {{ item.pimpasa_count }}
                        </td>

                        <!-- Total Tiket (Centered) -->
                        <td class="px-5 py-4 text-center font-bold text-slate-900 tabular-nums">
                            {{ item.total_laporan }}
                        </td>

                        <!-- Selesai (Centered) -->
                        <td class="px-5 py-4 text-center font-bold text-emerald-700 tabular-nums">
                            {{ item.laporan_selesai }}
                        </td>

                        <!-- Rate Penyelesaian (%) (Centered) -->
                        <td class="px-5 py-4 text-center whitespace-nowrap">
                            <div class="font-bold text-slate-900 text-xs tabular-nums">{{ item.completion_rate }}%</div>
                            <div class="w-16 h-1.5 bg-slate-100 rounded-full mx-auto mt-1 overflow-hidden">
                                <div 
                                    class="h-full rounded-full transition-all duration-300"
                                    :class="{
                                        'bg-emerald-500': item.completion_rate >= 85,
                                        'bg-amber-500': item.completion_rate >= 70 && item.completion_rate < 85,
                                        'bg-red-500': item.completion_rate < 70
                                    }"
                                    :style="{ width: `${item.completion_rate}%` }"
                                ></div>
                            </div>
                        </td>

                        <!-- Rerata SLA -->
                        <td class="px-5 py-4 text-right font-semibold text-slate-700 tabular-nums whitespace-nowrap">
                            {{ item.avg_sla_hours }} Jam
                        </td>

                        <!-- Status Kepatuhan Badge (Matching SLA Control Style) -->
                        <td class="px-5 py-4 text-center whitespace-nowrap">
                            <span 
                                v-if="item.status_kepatuhan === 'SANGAT BAIK'"
                                class="px-3 py-1 rounded-full text-[11px] font-semibold uppercase border bg-emerald-50 text-emerald-800 border-emerald-200/80 inline-flex items-center gap-1.5 shadow-2xs"
                            >
                                <CheckCircle2 :size="13" /> SANGAT BAIK
                            </span>
                            <span 
                                v-else-if="item.status_kepatuhan === 'CUKUP'"
                                class="px-3 py-1 rounded-full text-[11px] font-semibold uppercase border bg-amber-50 text-amber-900 border-amber-300/80 inline-flex items-center gap-1.5 shadow-2xs"
                            >
                                <Clock :size="13" /> CUKUP
                            </span>
                            <span 
                                v-else
                                class="px-3 py-1 rounded-full text-[11px] font-semibold uppercase border bg-red-50 text-red-700 border-red-200/80 inline-flex items-center gap-1.5 shadow-2xs"
                            >
                                <AlertTriangle :size="13" /> PERLU EVALUASI
                            </span>
                        </td>

                        <!-- Action Button: Detail UPT (Matching Pimpasa Worklist Verifikasi Style) -->
                        <td class="py-4 px-5 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                <Button
                                    @click="openDetailModal(item)"
                                    class="h-8 px-3 text-xs font-semibold rounded-md flex items-center gap-1.5 cursor-pointer transition-all shadow-2xs bg-primary hover:bg-[#04407D] text-primary-foreground font-bold"
                                >
                                    <Eye :size="13" />
                                    <span>Detail UPT</span>
                                </Button>
                            </div>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>

        <!-- Integrated Pagination Bar (Exact SLA Control Table Style) -->
        <div v-if="processedScorecards.length > 0" class="p-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 bg-slate-50/50">
            <div>
                Menampilkan <span class="font-bold text-slate-900 tabular-nums">{{ (currentPage - 1) * pageSize + 1 }}</span>
                sampai <span class="font-bold text-slate-900 tabular-nums">{{ Math.min(currentPage * pageSize, processedScorecards.length) }}</span>
                dari <span class="font-bold text-slate-900 tabular-nums">{{ processedScorecards.length }}</span> UPT Imigrasi
            </div>

            <div v-if="totalPages > 1" class="flex items-center gap-1">
                <button
                    @click="currentPage--"
                    :disabled="currentPage === 1"
                    :class="[
                        'px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors cursor-pointer',
                        currentPage === 1
                            ? 'text-slate-300 pointer-events-none'
                            : 'hover:bg-slate-200/80 text-slate-700'
                    ]"
                >
                    Sebelumnya
                </button>

                <button
                    v-for="page in totalPages"
                    :key="page"
                    @click="currentPage = page"
                    :class="[
                        'px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors cursor-pointer',
                        page === currentPage
                            ? 'bg-slate-900 text-white shadow-2xs font-bold'
                            : 'hover:bg-slate-200/80 text-slate-700'
                    ]"
                >
                    {{ page }}
                </button>

                <button
                    @click="currentPage++"
                    :disabled="currentPage >= totalPages"
                    :class="[
                        'px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors cursor-pointer',
                        currentPage >= totalPages
                            ? 'text-slate-300 pointer-events-none'
                            : 'hover:bg-slate-200/80 text-slate-700'
                    ]"
                >
                    Selanjutnya
                </button>
            </div>
        </div>

        <!-- Detail UPT Interactive Modal Dialog (Matching Pimpasa Dialog Style) -->
        <Dialog v-model:open="isModalOpen">
            <DialogContent v-if="selectedUpt" class="sm:max-w-3xl bg-white rounded-2xl p-6 sm:p-7 shadow-2xl space-y-0 font-sans border border-slate-100 max-h-[90vh] overflow-y-auto">
                
                <!-- Modal Header -->
                <DialogHeader class="-mx-6 sm:-mx-7 px-6 sm:px-7 pb-4 border-b border-slate-100 space-y-1 text-left">
                    <DialogTitle class="text-base font-bold text-slate-900 flex items-start gap-2.5 leading-snug">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center border border-blue-200/80 shrink-0 mt-0.5">
                            <Building2 :size="17" />
                        </div>
                        <span class="whitespace-normal break-words">{{ selectedUpt.nama }}</span>
                    </DialogTitle>
                    <DialogDescription class="text-xs text-slate-500 leading-relaxed font-normal pt-0.5">
                        Detail rincian kinerja kepatuhan SLA dan statistik UPT Imigrasi.
                    </DialogDescription>
                </DialogHeader>

                <!-- Modal Body -->
                <div class="space-y-6 pt-4">
                    
                    <!-- Stats Overview Cards inside Modal -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="bg-slate-50/80 p-3.5 rounded-xl border border-slate-200/70">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Desa</p>
                            <p class="text-xl font-bold text-slate-900 mt-0.5 tabular-nums">{{ selectedUpt.desa_count }}</p>
                        </div>
                        <div class="bg-slate-50/80 p-3.5 rounded-xl border border-slate-200/70">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Petugas PIMPASA</p>
                            <p class="text-xl font-bold text-slate-900 mt-0.5 tabular-nums">{{ selectedUpt.pimpasa_count }}</p>
                        </div>
                        <div class="bg-slate-50/80 p-3.5 rounded-xl border border-slate-200/70">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Laporan</p>
                            <p class="text-xl font-bold text-slate-900 mt-0.5 tabular-nums">{{ selectedUpt.total_laporan }}</p>
                        </div>
                        <div class="bg-slate-50/80 p-3.5 rounded-xl border border-slate-200/70">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Completion Rate</p>
                            <p class="text-xl font-bold text-emerald-700 mt-0.5 tabular-nums">{{ selectedUpt.completion_rate }}%</p>
                        </div>
                    </div>

                    <!-- Breakdown: Desa & PIMPASA (Card List di Mobile, Table di Desktop - Tanpa Horizontal Scroll) -->
                    <div class="space-y-2.5">
                        <h4 class="text-xs font-bold uppercase text-slate-800 tracking-wider">Daftar Desa Binaan & Petugas PIMPASA Terkait</h4>
                        
                        <!-- MOBILE VIEW: Stacked Clean Card List (< sm / < 640px) -->
                        <div class="block sm:hidden space-y-3">
                            <div
                                v-for="v in selectedUpt.desa_list || []"
                                :key="v.id"
                                class="p-3.5 rounded-xl border border-slate-200/90 bg-slate-50/60 space-y-2"
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <h5 class="font-bold text-slate-900 text-xs leading-snug">{{ v.nama }}</h5>
                                        <p class="text-[11px] text-slate-600 font-medium mt-0.5">Petugas: {{ v.pimpasa }}</p>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase border bg-emerald-50 text-emerald-800 border-emerald-200/80 shrink-0">
                                        {{ v.status }}
                                    </span>
                                </div>
                                <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between text-[11px]">
                                    <span class="text-slate-500 font-medium">Tiket Diselesaikan:</span>
                                    <span class="font-bold text-slate-900 tabular-nums">{{ v.laporan }} Laporan</span>
                                </div>
                            </div>

                            <div v-if="!selectedUpt.desa_list || selectedUpt.desa_list.length === 0" class="py-6 text-center text-slate-400 italic text-xs bg-slate-50 rounded-xl border border-slate-200">
                                Belum ada Desa Binaan yang terdaftar di UPT Imigrasi ini.
                            </div>
                        </div>

                        <!-- DESKTOP / TABLET VIEW: Full-Width Table (≥ sm / ≥ 640px) -->
                        <div class="hidden sm:block border border-slate-200/80 rounded-xl overflow-hidden shadow-2xs">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-100/80 text-slate-700 text-[10px] font-extrabold uppercase border-b border-slate-200/80 tracking-wider">
                                        <th class="py-3 px-3.5">Nama Desa Binaan</th>
                                        <th class="py-3 px-3.5">Petugas PIMPASA</th>
                                        <th class="py-3 px-3.5 text-center">Status</th>
                                        <th class="py-3 px-3.5 text-right">Tiket Diselesaikan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-xs text-slate-700 bg-white">
                                    <tr v-for="v in selectedUpt.desa_list || []" :key="v.id" class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-3 px-3.5 font-bold text-slate-900 whitespace-normal break-words">{{ v.nama }}</td>
                                        <td class="py-3 px-3.5 text-slate-600 font-medium whitespace-normal break-words">{{ v.pimpasa }}</td>
                                        <td class="py-3 px-3.5 text-center whitespace-nowrap">
                                            <span 
                                                class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase border bg-emerald-50 text-emerald-800 border-emerald-200/80"
                                            >
                                                {{ v.status }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3.5 text-right font-bold text-slate-900 tabular-nums whitespace-nowrap">{{ v.laporan }} Laporan</td>
                                    </tr>

                                    <tr v-if="!selectedUpt.desa_list || selectedUpt.desa_list.length === 0">
                                        <td colspan="4" class="py-8 text-center text-slate-400 italic text-xs">
                                            Belum ada Desa Binaan yang terdaftar di UPT Imigrasi ini.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- Modal Footer -->
                <DialogFooter class="-mx-6 sm:-mx-7 px-6 sm:px-7 pt-4 mt-5 border-t border-slate-100 flex items-center justify-end bg-transparent">
                    <Button
                        type="button"
                        variant="outline"
                        @click="closeModal"
                        class="w-full sm:w-auto h-9 px-4 rounded-xl text-xs font-bold border-slate-300 text-slate-700 hover:bg-slate-50 transition-all cursor-pointer"
                    >
                        Tutup Scorecard
                    </Button>
                </DialogFooter>

            </DialogContent>
        </Dialog>

    </div>
</template>
