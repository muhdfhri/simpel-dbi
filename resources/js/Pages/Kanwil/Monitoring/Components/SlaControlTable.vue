<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import {
    AlertTriangle,
    Clock,
    Send,
    ArrowUpDown,
    ArrowUp,
    ArrowDown,
    CheckCircle2,
    ShieldAlert
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { notify } from '@/lib/toast';

interface SlaIncident {
    id: number;
    nomor_tiket: string;
    judul: string;
    kategori: string;
    desa_nama: string;
    upt_nama: string;
    pelapor_nama: string;
    status: string;
    created_at_formatted: string;
    hours_elapsed: number;
    hours_remaining: number;
    sla_status: 'terlambat' | 'peringatan' | 'tepat_waktu' | 'breached' | 'warning' | 'on_track';
}

const props = defineProps<{
    incidents: SlaIncident[];
}>();

const currentPage = ref(1);
const pageSize = ref(10);
const sortField = ref<string>('id');
const sortOrder = ref<'asc' | 'desc'>('asc');

// Reset to page 1 whenever incidents change due to filters
watch(() => props.incidents, () => {
    currentPage.value = 1;
});

const toggleSort = (field: string) => {
    if (sortField.value === field) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortOrder.value = 'asc';
    }
};

const processedIncidents = computed(() => {
    let list = [...props.incidents];

    list.sort((a, b) => {
        let valA: any = '';
        let valB: any = '';
        if (sortField.value === 'id') { valA = a.id; valB = b.id; }
        else if (sortField.value === 'tiket') { valA = a.nomor_tiket; valB = b.nomor_tiket; }
        else if (sortField.value === 'judul') { valA = a.judul; valB = b.judul; }
        else if (sortField.value === 'kategori') { valA = a.kategori; valB = b.kategori; }
        else if (sortField.value === 'upt') { valA = a.upt_nama; valB = b.upt_nama; }
        else if (sortField.value === 'desa') { valA = a.desa_nama; valB = b.desa_nama; }
        else if (sortField.value === 'status') { valA = a.sla_status; valB = b.sla_status; }

        if (typeof valA === 'number' && typeof valB === 'number') {
            return sortOrder.value === 'asc' ? valA - valB : valB - valA;
        }

        const cmp = String(valA).localeCompare(String(valB));
        return sortOrder.value === 'asc' ? cmp : -cmp;
    });

    return list;
});

const paginatedIncidents = computed(() => {
    const start = (currentPage.value - 1) * pageSize.value;
    return processedIncidents.value.slice(start, start + pageSize.value);
});

const totalPages = computed(() => Math.ceil(processedIncidents.value.length / pageSize.value) || 1);

const formatSlaHours = (val: number | string | undefined | null) => {
    if (val === undefined || val === null) return 0;
    const num = Math.abs(Math.round(typeof val === 'string' ? parseFloat(val) : val));
    return isNaN(num) ? 0 : num;
};

import { router } from '@inertiajs/vue3';

const kirimTeguranUpt = (inc: SlaIncident) => {
    router.post(`/kanwil/monitoring/tegur-sla/${inc.id}`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            notify.success('Teguran SLA Terkirim!', {
                description: `Peringatan eskalasi SLA untuk tiket ${inc.nomor_tiket} telah dikirim via In-App, ntfy Push & Email ke Petugas PIMPASA ${inc.upt_nama}.`
            });
        },
        onError: () => {
            notify.error('Gagal Mengirim Teguran SLA', { description: 'Petugas PIMPASA penanggung jawab belum terdaftar.' });
        }
    });
};
</script>

<template>
    <div class="bg-white font-sans overflow-hidden">
        
        <!-- Table Body -->
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left text-slate-600 font-sans border-collapse">
                <thead class="bg-slate-100/70 text-slate-700 font-semibold uppercase tracking-wider text-[11px] border-b border-slate-200/80">
                    <tr>
                        <th @click="toggleSort('id')" class="px-4 py-3.5 text-center w-14 cursor-pointer hover:bg-slate-200/60 transition-colors select-none">
                            <div class="flex items-center justify-center gap-1">
                                <span>No</span>
                                <ArrowUp v-if="sortField === 'id' && sortOrder === 'asc'" :size="12" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'id' && sortOrder === 'desc'" :size="12" class="text-slate-900" />
                                <ArrowUpDown v-else :size="12" class="text-slate-300" />
                            </div>
                        </th>

                        <!-- Kolom Kode Tiket -->
                        <th @click="toggleSort('tiket')" class="px-5 py-3.5 cursor-pointer hover:bg-slate-200/60 transition-colors select-none whitespace-nowrap">
                            <div class="flex items-center gap-1.5">
                                <span>Kode Tiket</span>
                                <ArrowUp v-if="sortField === 'tiket' && sortOrder === 'asc'" :size="12" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'tiket' && sortOrder === 'desc'" :size="12" class="text-slate-900" />
                                <ArrowUpDown v-else :size="12" class="text-slate-300" />
                            </div>
                        </th>

                        <!-- Kolom Judul Aduan -->
                        <th @click="toggleSort('judul')" class="px-5 py-3.5 cursor-pointer hover:bg-slate-200/60 transition-colors select-none">
                            <div class="flex items-center gap-1.5">
                                <span>Judul Aduan</span>
                                <ArrowUp v-if="sortField === 'judul' && sortOrder === 'asc'" :size="12" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'judul' && sortOrder === 'desc'" :size="12" class="text-slate-900" />
                                <ArrowUpDown v-else :size="12" class="text-slate-300" />
                            </div>
                        </th>

                        <!-- Kolom Kategori Laporan -->
                        <th @click="toggleSort('kategori')" class="px-5 py-3.5 cursor-pointer hover:bg-slate-200/60 transition-colors select-none whitespace-nowrap">
                            <div class="flex items-center gap-1.5">
                                <span>Kategori Laporan</span>
                                <ArrowUp v-if="sortField === 'kategori' && sortOrder === 'asc'" :size="12" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'kategori' && sortOrder === 'desc'" :size="12" class="text-slate-900" />
                                <ArrowUpDown v-else :size="12" class="text-slate-300" />
                            </div>
                        </th>

                        <th @click="toggleSort('upt')" class="px-5 py-3.5 cursor-pointer hover:bg-slate-200/60 transition-colors select-none whitespace-nowrap">
                            <div class="flex items-center gap-1.5">
                                <span>Satker UPT Pembina</span>
                                <ArrowUp v-if="sortField === 'upt' && sortOrder === 'asc'" :size="12" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'upt' && sortOrder === 'desc'" :size="12" class="text-slate-900" />
                                <ArrowUpDown v-else :size="12" class="text-slate-300" />
                            </div>
                        </th>

                        <th @click="toggleSort('desa')" class="px-5 py-3.5 cursor-pointer hover:bg-slate-200/60 transition-colors select-none whitespace-nowrap">
                            <div class="flex items-center gap-1.5">
                                <span>Desa Binaan & Pelapor</span>
                                <ArrowUp v-if="sortField === 'desa' && sortOrder === 'asc'" :size="12" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'desa' && sortOrder === 'desc'" :size="12" class="text-slate-900" />
                                <ArrowUpDown v-else :size="12" class="text-slate-300" />
                            </div>
                        </th>

                        <th @click="toggleSort('status')" class="px-5 py-3.5 text-center cursor-pointer hover:bg-slate-200/60 transition-colors select-none whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                <span>Status SLA (24 Jam)</span>
                                <ArrowUp v-if="sortField === 'status' && sortOrder === 'asc'" :size="12" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'status' && sortOrder === 'desc'" :size="12" class="text-slate-900" />
                                <ArrowUpDown v-else :size="12" class="text-slate-300" />
                            </div>
                        </th>

                        <th class="px-5 py-3.5 text-center whitespace-nowrap">Aksi Pimpinan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr v-if="paginatedIncidents.length === 0">
                        <td colspan="8" class="p-10 text-center text-slate-400 italic font-medium">
                            <ShieldAlert :size="32" class="mx-auto mb-2 opacity-40 text-slate-400" />
                            <p class="font-bold text-slate-700 text-xs">Tidak ada data insiden SLA yang cocok dengan filter</p>
                        </td>
                    </tr>

                    <tr
                        v-for="inc in paginatedIncidents"
                        :key="inc.id"
                        :class="[
                            'transition-all duration-150',
                            inc.sla_status === 'terlambat' || inc.sla_status === 'breached'
                                ? 'bg-red-50/40 hover:bg-red-100/50 border-l-4 border-l-red-500'
                                : (inc.sla_status === 'peringatan' || inc.sla_status === 'warning'
                                    ? 'bg-amber-50/40 hover:bg-amber-100/50 border-l-4 border-l-amber-500'
                                    : 'hover:bg-slate-50/70')
                        ]"
                    >
                        <!-- Kolom No -->
                        <td class="px-4 py-4 text-center font-sans tabular-nums font-bold text-slate-900">
                            {{ inc.id }}
                        </td>
                        
                        <!-- Kolom Kode Tiket -->
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="font-sans tabular-nums font-bold text-slate-900 text-xs bg-slate-100 px-2 py-0.5 rounded border border-slate-200/80">
                                {{ inc.nomor_tiket }}
                            </span>
                        </td>

                        <!-- Kolom Judul Aduan -->
                        <td class="px-5 py-4 max-w-xs">
                            <div class="font-bold text-slate-900 leading-snug line-clamp-2">{{ inc.judul }}</div>
                        </td>

                        <!-- Kolom Kategori Laporan -->
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="inline-block px-2.5 py-1 rounded text-[11px] font-semibold bg-slate-100 text-slate-800 border border-slate-200/80">
                                {{ inc.kategori }}
                            </span>
                        </td>

                        <td class="px-5 py-4 text-slate-900 font-bold whitespace-nowrap">{{ inc.upt_nama }}</td>

                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="font-bold text-slate-900">{{ inc.desa_nama }}</div>
                            <div class="text-[11px] text-slate-500">Oleh: {{ inc.pelapor_nama }}</div>
                        </td>

                        <!-- Status SLA Badge (Bahasa Indonesia) -->
                        <td class="px-5 py-4 text-center whitespace-nowrap">
                            <span
                                v-if="inc.sla_status === 'terlambat' || inc.sla_status === 'breached'"
                                class="px-3 py-1 rounded-full text-[11px] font-bold uppercase border bg-red-50 text-red-700 border-red-200/80 inline-flex items-center gap-1.5 shadow-2xs"
                            >
                                <AlertTriangle :size="13" /> TERLAMBAT ({{ formatSlaHours(inc.hours_elapsed) }} Jam)
                            </span>
                            <span
                                v-else-if="inc.sla_status === 'peringatan' || inc.sla_status === 'warning'"
                                class="px-3 py-1 rounded-full text-[11px] font-bold uppercase border bg-amber-50 text-amber-800 border-amber-300 inline-flex items-center gap-1.5 shadow-2xs"
                            >
                                <Clock :size="13" /> PERINGATAN (Sisa {{ formatSlaHours(inc.hours_remaining) }} Jam)
                            </span>
                            <span
                                v-else
                                class="px-3 py-1 rounded-full text-[11px] font-semibold uppercase border bg-emerald-50 text-emerald-800 border-emerald-200/80 inline-flex items-center gap-1.5 shadow-2xs"
                            >
                                <CheckCircle2 :size="13" /> TEPAT WAKTU ({{ formatSlaHours(inc.hours_elapsed) }} Jam)
                            </span>
                        </td>

                        <td class="px-5 py-4 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                <Button
                                    @click="kirimTeguranUpt(inc)"
                                    size="sm"
                                    class="h-8 px-3 rounded-md text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200/80 hover:bg-rose-100 hover:text-rose-800 transition-all shadow-2xs gap-1.5 cursor-pointer"
                                >
                                    <Send :size="13" />
                                    <span>Tegur UPT</span>
                                </Button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Integrated Pagination Bar (Matching Pimpasa Worklist Style) -->
        <div v-if="processedIncidents.length > 0" class="p-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 bg-slate-50/50">
            <div>
                Menampilkan <span class="font-bold text-slate-900 tabular-nums">{{ (currentPage - 1) * pageSize + 1 }}</span>
                sampai <span class="font-bold text-slate-900 tabular-nums">{{ Math.min(currentPage * pageSize, processedIncidents.length) }}</span>
                dari <span class="font-bold text-slate-900 tabular-nums">{{ processedIncidents.length }}</span> Insiden SLA
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

    </div>
</template>
