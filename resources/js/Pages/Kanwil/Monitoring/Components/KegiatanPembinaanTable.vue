<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import {
    Calendar,
    Paperclip,
    Eye,
    Building2,
    Users,
    MapPin,
    ArrowUpDown,
    ArrowUp,
    ArrowDown,
    FileText,
    X,
    FolderKanban
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

interface LampiranItem {
    id: number;
    nama_file_asli: string;
    path: string;
    tipe_file: string;
    ukuran_bytes: number;
}

interface KegiatanItem {
    id: number;
    judul: string;
    jenis_pembinaan: string;
    tanggal: string | null;
    tanggal_formatted: string;
    jumlah_peserta: number;
    status: string;
    lokasi: string;
    ringkasan_materi: string;
    desa_nama: string;
    upt_nama: string;
    pimpasa_nama: string;
    lampiran_count: number;
    lampiran_list: LampiranItem[];
}

const props = defineProps<{
    kegiatanList: KegiatanItem[];
}>();

const currentPage = ref(1);
const pageSize = ref(10);
const sortField = ref<string>('id');
const sortOrder = ref<'asc' | 'desc'>('asc');

// Modal Detail State
const selectedKegiatanModal = ref<KegiatanItem | null>(null);

const openDetailModal = (item: KegiatanItem) => {
    selectedKegiatanModal.value = item;
};

const closeDetailModal = () => {
    selectedKegiatanModal.value = null;
};

watch(() => props.kegiatanList, () => {
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

const processedList = computed(() => {
    let list = [...props.kegiatanList];

    list.sort((a, b) => {
        let valA: any = '';
        let valB: any = '';
        if (sortField.value === 'id') { valA = a.id; valB = b.id; }
        else if (sortField.value === 'tanggal') { valA = a.tanggal || ''; valB = b.tanggal || ''; }
        else if (sortField.value === 'judul') { valA = a.judul; valB = b.judul; }
        else if (sortField.value === 'jenis') { valA = a.jenis_pembinaan; valB = b.jenis_pembinaan; }
        else if (sortField.value === 'upt') { valA = a.upt_nama; valB = b.upt_nama; }
        else if (sortField.value === 'desa') { valA = a.desa_nama; valB = b.desa_nama; }
        else if (sortField.value === 'peserta') { valA = a.jumlah_peserta; valB = b.jumlah_peserta; }

        if (typeof valA === 'number' && typeof valB === 'number') {
            return sortOrder.value === 'asc' ? valA - valB : valB - valA;
        }

        const cmp = String(valA).localeCompare(String(valB));
        return sortOrder.value === 'asc' ? cmp : -cmp;
    });

    return list;
});

const paginatedList = computed(() => {
    const start = (currentPage.value - 1) * pageSize.value;
    return processedList.value.slice(start, start + pageSize.value);
});

const totalPages = computed(() => Math.ceil(processedList.value.length / pageSize.value) || 1);
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

                        <!-- Kolom Tanggal Execution -->
                        <th @click="toggleSort('tanggal')" class="px-5 py-3.5 cursor-pointer hover:bg-slate-200/60 transition-colors select-none whitespace-nowrap">
                            <div class="flex items-center gap-1.5">
                                <span>Tanggal</span>
                                <ArrowUp v-if="sortField === 'tanggal' && sortOrder === 'asc'" :size="12" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'tanggal' && sortOrder === 'desc'" :size="12" class="text-slate-900" />
                                <ArrowUpDown v-else :size="12" class="text-slate-300" />
                            </div>
                        </th>

                        <!-- Kolom Judul Kegiatan -->
                        <th @click="toggleSort('judul')" class="px-5 py-3.5 cursor-pointer hover:bg-slate-200/60 transition-colors select-none">
                            <div class="flex items-center gap-1.5">
                                <span>Judul Kegiatan</span>
                                <ArrowUp v-if="sortField === 'judul' && sortOrder === 'asc'" :size="12" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'judul' && sortOrder === 'desc'" :size="12" class="text-slate-900" />
                                <ArrowUpDown v-else :size="12" class="text-slate-300" />
                            </div>
                        </th>

                        <!-- Kolom Jenis Pembinaan -->
                        <th @click="toggleSort('jenis')" class="px-5 py-3.5 cursor-pointer hover:bg-slate-200/60 transition-colors select-none whitespace-nowrap">
                            <div class="flex items-center gap-1.5">
                                <span>Jenis Pembinaan</span>
                                <ArrowUp v-if="sortField === 'jenis' && sortOrder === 'asc'" :size="12" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'jenis' && sortOrder === 'desc'" :size="12" class="text-slate-900" />
                                <ArrowUpDown v-else :size="12" class="text-slate-300" />
                            </div>
                        </th>

                        <!-- Kolom Satker UPT Pembina -->
                        <th @click="toggleSort('upt')" class="px-5 py-3.5 cursor-pointer hover:bg-slate-200/60 transition-colors select-none whitespace-nowrap">
                            <div class="flex items-center gap-1.5">
                                <span>Satker UPT Pembina</span>
                                <ArrowUp v-if="sortField === 'upt' && sortOrder === 'asc'" :size="12" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'upt' && sortOrder === 'desc'" :size="12" class="text-slate-900" />
                                <ArrowUpDown v-else :size="12" class="text-slate-300" />
                            </div>
                        </th>

                        <!-- Kolom Desa Binaan & Petugas PIMPASA -->
                        <th @click="toggleSort('desa')" class="px-5 py-3.5 cursor-pointer hover:bg-slate-200/60 transition-colors select-none whitespace-nowrap">
                            <div class="flex items-center gap-1.5">
                                <span>Desa & Petugas PIMPASA</span>
                                <ArrowUp v-if="sortField === 'desa' && sortOrder === 'asc'" :size="12" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'desa' && sortOrder === 'desc'" :size="12" class="text-slate-900" />
                                <ArrowUpDown v-else :size="12" class="text-slate-300" />
                            </div>
                        </th>

                        <!-- Kolom Lokasi & Peserta -->
                        <th @click="toggleSort('peserta')" class="px-5 py-3.5 cursor-pointer hover:bg-slate-200/60 transition-colors select-none whitespace-nowrap">
                            <div class="flex items-center gap-1.5">
                                <span>Lokasi & Peserta</span>
                                <ArrowUp v-if="sortField === 'peserta' && sortOrder === 'asc'" :size="12" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'peserta' && sortOrder === 'desc'" :size="12" class="text-slate-900" />
                                <ArrowUpDown v-else :size="12" class="text-slate-300" />
                            </div>
                        </th>

                        <th class="px-5 py-3.5 text-center whitespace-nowrap">Berkas Dokumen</th>
                        <th class="px-5 py-3.5 text-center whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr v-if="paginatedList.length === 0">
                        <td colspan="9" class="p-10 text-center text-slate-400 italic font-medium">
                            <FolderKanban :size="32" class="mx-auto mb-2 opacity-40 text-slate-400" />
                            <p class="font-bold text-slate-700 text-xs">Belum ada data kegiatan pembinaan yang cocok</p>
                        </td>
                    </tr>

                    <tr
                        v-for="keg in paginatedList"
                        :key="keg.id"
                        class="hover:bg-slate-50/70 transition-all duration-150"
                    >
                        <!-- Kolom No -->
                        <td class="px-4 py-4 text-center font-sans tabular-nums font-bold text-slate-900">
                            {{ keg.id }}
                        </td>

                        <!-- Kolom Tanggal -->
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-1.5 font-semibold text-slate-900">
                                <Calendar :size="13" class="text-slate-400" />
                                <span>{{ keg.tanggal_formatted }}</span>
                            </div>
                        </td>

                        <!-- Kolom Judul Kegiatan -->
                        <td class="px-5 py-4 max-w-xs">
                            <div class="font-bold text-slate-900 leading-snug line-clamp-2">{{ keg.judul }}</div>
                        </td>

                        <!-- Kolom Jenis Pembinaan -->
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="inline-block px-2.5 py-1 rounded text-[11px] font-semibold bg-blue-50 text-blue-800 border border-blue-200/80">
                                {{ keg.jenis_pembinaan }}
                            </span>
                        </td>

                        <!-- Kolom Satker UPT Pembina -->
                        <td class="px-5 py-4 text-slate-900 font-bold whitespace-nowrap">
                            <div class="flex items-center gap-1.5">
                                <Building2 :size="13" class="text-slate-400" />
                                <span>{{ keg.upt_nama }}</span>
                            </div>
                        </td>

                        <!-- Kolom Desa & PIMPASA -->
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="font-bold text-slate-900">{{ keg.desa_nama }}</div>
                            <div class="text-[11px] text-slate-500 font-medium">Petugas: {{ keg.pimpasa_nama }}</div>
                        </td>

                        <!-- Kolom Lokasi & Peserta -->
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-1 text-slate-800 font-medium">
                                <MapPin :size="12" class="text-slate-400 shrink-0" />
                                <span class="truncate max-w-[120px]">{{ keg.lokasi }}</span>
                            </div>
                            <div class="flex items-center gap-1 text-[11px] text-slate-600 font-bold mt-0.5">
                                <Users :size="12" class="text-slate-400 shrink-0" />
                                <span>{{ keg.jumlah_peserta }} Warga</span>
                            </div>
                        </td>

                        <!-- Kolom Berkas Dokumen -->
                        <td class="px-5 py-4 text-center whitespace-nowrap">
                            <span
                                v-if="keg.lampiran_count > 0"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-100 text-slate-800 text-[11px] font-bold border border-slate-200/80"
                            >
                                <Paperclip :size="12" class="text-slate-500" />
                                <span>{{ keg.lampiran_count }} Berkas</span>
                            </span>
                            <span v-else class="text-slate-300 text-[11px]">-</span>
                        </td>

                        <!-- Aksi -->
                        <td class="px-5 py-4 text-center whitespace-nowrap">
                            <Button
                                @click="openDetailModal(keg)"
                                size="sm"
                                class="h-8 px-3 rounded-md text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 transition-all shadow-2xs gap-1.5 cursor-pointer"
                            >
                                <Eye :size="13" />
                                <span>Detail Kegiatan</span>
                            </Button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Integrated Pagination Bar -->
        <div v-if="processedList.length > 0" class="p-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 bg-slate-50/50">
            <div>
                Menampilkan <span class="font-bold text-slate-900 tabular-nums">{{ (currentPage - 1) * pageSize + 1 }}</span>
                sampai <span class="font-bold text-slate-900 tabular-nums">{{ Math.min(currentPage * pageSize, processedList.length) }}</span>
                dari <span class="font-bold text-slate-900 tabular-nums">{{ processedList.length }}</span> Data Kegiatan
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

        <!-- Detail Modal Dialog -->
        <div
            v-if="selectedKegiatanModal"
            class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
            @click.self="closeDetailModal"
        >
            <div class="bg-white rounded-2xl max-w-xl w-full p-6 space-y-4 shadow-2xl border border-slate-200 relative animate-in fade-in zoom-in duration-150">
                
                <div class="flex items-start justify-between pb-3 border-b border-slate-100">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Detail Kegiatan Pembinaan</span>
                        <h3 class="text-base font-bold text-slate-900 leading-snug mt-0.5">
                            {{ selectedKegiatanModal.judul }}
                        </h3>
                    </div>
                    <Button @click="closeDetailModal" variant="ghost" size="sm" class="h-7 w-7 p-0 rounded-lg text-slate-400 hover:text-slate-700">
                        <X :size="16" />
                    </Button>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="grid grid-cols-2 gap-3 bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <div>
                            <span class="text-slate-500 font-medium block">Satker UPT:</span>
                            <span class="font-bold text-slate-900">{{ selectedKegiatanModal.upt_nama }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 font-medium block">Desa Binaan:</span>
                            <span class="font-bold text-slate-900">{{ selectedKegiatanModal.desa_nama }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 font-medium block">Petugas PIMPASA:</span>
                            <span class="font-bold text-slate-900">{{ selectedKegiatanModal.pimpasa_nama }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 font-medium block">Tanggal & Peserta:</span>
                            <span class="font-bold text-slate-900">{{ selectedKegiatanModal.tanggal_formatted }} ({{ selectedKegiatanModal.jumlah_peserta }} Warga)</span>
                        </div>
                    </div>

                    <div>
                        <span class="text-slate-500 font-semibold block mb-1">Ringkasan Materi & Kegiatan:</span>
                        <p class="p-3 bg-white border border-slate-200/80 rounded-xl text-slate-700 leading-relaxed font-normal">
                            {{ selectedKegiatanModal.ringkasan_materi }}
                        </p>
                    </div>

                    <div v-if="selectedKegiatanModal.lampiran_list.length > 0">
                        <span class="text-slate-500 font-semibold block mb-1.5">Dokumentasi Foto Lapangan:</span>
                        <div class="flex items-center gap-2 overflow-x-auto pb-1">
                            <a
                                v-for="l in selectedKegiatanModal.lampiran_list"
                                :key="l.id"
                                :href="`/${l.path}`"
                                target="_blank"
                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 text-xs font-semibold shrink-0"
                            >
                                <Paperclip :size="13" class="text-slate-500" />
                                <span class="truncate max-w-[150px]">{{ l.nama_file_asli }}</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-end">
                    <Button @click="closeDetailModal" size="sm" class="h-8 px-4 rounded-md bg-slate-900 text-white font-semibold text-xs">
                        Tutup Detail
                    </Button>
                </div>

            </div>
        </div>

    </div>
</template>
