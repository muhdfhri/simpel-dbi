<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import {
    Search,
    Edit2,
    Trash2,
    ArrowUpDown,
    ArrowUp,
    ArrowDown,
    ChevronLeft,
    ChevronRight,
    Building2,
    MapPin,
    RotateCcw
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import Combobox from '@/components/ui/combobox/Combobox.vue';
import ConfirmDeleteModal from '@/components/common/ConfirmDeleteModal.vue';
import { notify } from '@/lib/toast';

interface DesaProps {
    id: number;
    nama: string;
    upt_id: number;
    pimpasa_id: number | null;
    lat?: number | null;
    lng?: number | null;
    upt?: { id: number; nama: string };
    pimpasa?: { id: number; name: string };
    status_terkini: string;
}

interface UptProps {
    id: number;
    nama: string;
}

interface PimpasaProps {
    id: number;
    name: string;
    nip: string;
    upt_id: number;
}

const props = defineProps<{
    desaList: DesaProps[];
    uptList: UptProps[];
    pimpasaList: PimpasaProps[];
}>();

const searchInput = ref('');
const filterUpt = ref<string>('all');

const currentPage = ref(1);
const pageSize = ref(50);
const sortField = ref<string>('id');
const sortOrder = ref<'asc' | 'desc'>('asc');

const toggleSort = (field: string) => {
    if (sortField.value === field) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortOrder.value = 'asc';
    }
};

const processedDesaList = computed(() => {
    let list = [...props.desaList];

    if (searchInput.value) {
        const q = searchInput.value.toLowerCase();
        list = list.filter(d =>
            d.nama.toLowerCase().includes(q) ||
            (d.upt?.nama && d.upt.nama.toLowerCase().includes(q)) ||
            (d.pimpasa?.name && d.pimpasa.name.toLowerCase().includes(q))
        );
    }

    if (filterUpt.value !== 'all') {
        list = list.filter(d => d.upt_id.toString() === filterUpt.value);
    }

    list.sort((a, b) => {
        let valA: any = '';
        let valB: any = '';
        if (sortField.value === 'id') { valA = a.id; valB = b.id; }
        else if (sortField.value === 'nama') { valA = a.nama; valB = b.nama; }
        else if (sortField.value === 'upt') { valA = a.upt?.nama || ''; valB = b.upt?.nama || ''; }
        else if (sortField.value === 'pimpasa') { valA = a.pimpasa?.name || ''; valB = b.pimpasa?.name || ''; }
        else if (sortField.value === 'status') { valA = a.status_terkini; valB = b.status_terkini; }

        if (typeof valA === 'number' && typeof valB === 'number') {
            return sortOrder.value === 'asc' ? valA - valB : valB - valA;
        }

        const cmp = String(valA).localeCompare(String(valB));
        return sortOrder.value === 'asc' ? cmp : -cmp;
    });

    return list;
});

const paginatedDesaList = computed(() => {
    const start = (currentPage.value - 1) * pageSize.value;
    return processedDesaList.value.slice(start, start + pageSize.value);
});

const totalDesaPages = computed(() => Math.ceil(processedDesaList.value.length / pageSize.value) || 1);

const uptFilterComboboxOptions = computed(() => [
    { value: 'all', label: `Semua Satker UPT Imigrasi (${props.desaList.length})` },
    ...props.uptList.map(u => ({
        value: u.id.toString(),
        label: `${u.nama} (${props.desaList.filter(d => d.upt_id === u.id).length})`,
    }))
]);

const uptComboboxOptions = computed(() =>
    props.uptList.map(u => ({ value: u.id.toString(), label: u.nama }))
);

const getUptDesaCount = (uptId: number) => {
    return props.desaList.filter(d => d.upt_id === uptId).length;
};

const pimpasaComboboxOptions = computed(() => [
    { value: '', label: '-- Belum Di-assign (Kosong) --' },
    ...props.pimpasaList.map(p => ({
        value: p.id.toString(),
        label: p.name,
        description: `NIP: ${p.nip}`
    }))
]);

// --- MODAL DIALOG CRUD DESA BINAAN ---
const isDialogOpen = ref(false);
const editingDesa = ref<DesaProps | null>(null);
const desaForm = useForm({
    nama: '',
    upt_id: '',
    pimpasa_id: '',
    lat: '' as string | number,
    lng: '' as string | number,
    status_terkini: 'aman',
});

const openTambahModal = () => {
    editingDesa.value = null;
    desaForm.reset();
    desaForm.upt_id = props.uptList[0]?.id.toString() || '';
    desaForm.lat = '3.5952';
    desaForm.lng = '98.6722';
    isDialogOpen.value = true;
};

const openEditModal = (d: DesaProps) => {
    editingDesa.value = d;
    desaForm.nama = d.nama;
    desaForm.upt_id = d.upt_id.toString();
    desaForm.pimpasa_id = d.pimpasa_id ? d.pimpasa_id.toString() : '';
    desaForm.lat = d.lat !== null && d.lat !== undefined ? d.lat : '';
    desaForm.lng = d.lng !== null && d.lng !== undefined ? d.lng : '';
    desaForm.status_terkini = d.status_terkini;
    isDialogOpen.value = true;
};

const submitForm = () => {
    const payload = {
        nama: desaForm.nama,
        upt_id: parseInt(desaForm.upt_id),
        pimpasa_id: desaForm.pimpasa_id ? parseInt(desaForm.pimpasa_id) : null,
        lat: desaForm.lat !== '' && desaForm.lat !== null ? parseFloat(desaForm.lat.toString()) : null,
        lng: desaForm.lng !== '' && desaForm.lng !== null ? parseFloat(desaForm.lng.toString()) : null,
        status_terkini: desaForm.status_terkini,
    };

    if (editingDesa.value) {
        router.put(`/kanwil/master-data/desa/${editingDesa.value.id}`, payload, {
            onSuccess: () => {
                isDialogOpen.value = false;
            },
            onError: (errs) => {
                notify.error('Gagal Menyimpan', { description: Object.values(errs)[0] || 'Periksa kembali isian data.' });
            }
        });
    } else {
        router.post('/kanwil/master-data/desa', payload, {
            onSuccess: () => {
                isDialogOpen.value = false;
            },
            onError: (errs) => {
                notify.error('Gagal Menambahkan', { description: Object.values(errs)[0] || 'Periksa kembali isian data.' });
            }
        });
    }
};

// --- CONFIRM DELETE MODAL REUSABLE ---
const isDeleteModalOpen = ref(false);
const desaToDelete = ref<DesaProps | null>(null);
const isDeleting = ref(false);

const openDeleteModal = (d: DesaProps) => {
    desaToDelete.value = d;
    isDeleteModalOpen.value = true;
};

const executeDelete = () => {
    if (!desaToDelete.value) return;
    isDeleting.value = true;
    router.delete(`/kanwil/master-data/desa/${desaToDelete.value.id}`, {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            isDeleting.value = false;
            desaToDelete.value = null;
        },
        onError: () => {
            isDeleting.value = false;
            notify.error('Gagal Menghapus Desa Binaan');
        }
    });
};

defineExpose({ openTambahModal });
const resetFilter = () => {
    searchInput.value = '';
    filterUpt.value = 'all';
};
</script>

<template>
    <div class="space-y-4 font-sans">
        <!-- Search & Filter Toolbar -->
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="relative w-full md:w-72">
                <Search :size="15" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
                <Input
                    type="text"
                    v-model="searchInput"
                    placeholder="Cari desa binaan..."
                    class="pl-9 pr-20 text-xs rounded-md bg-white border-slate-200/90 h-9 shadow-2xs w-full"
                />
                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 font-medium pointer-events-none select-none hidden sm:flex items-center gap-1">
                    <kbd class="bg-slate-100 border border-slate-300 text-slate-500 text-[9px] font-sans font-semibold px-1.5 py-0.5 rounded">Enter</kbd>
                </span>
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
                <div class="w-full md:w-72">
                    <Combobox
                        v-model="filterUpt"
                        :options="uptFilterComboboxOptions"
                        placeholder="Semua Satker UPT Imigrasi"
                        searchPlaceholder="Cari Kanim / UPT..."
                        class="w-full h-9 bg-white border-slate-200/90 rounded-md text-xs font-semibold text-slate-800 shadow-2xs"
                    />
                </div>

                <button
                    type="button"
                    @click="resetFilter"
                    class="h-9 px-3 bg-white border border-slate-200/90 rounded-md text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-all flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer shrink-0"
                >
                    <RotateCcw :size="13" class="text-slate-400 shrink-0" />
                    <span>Reset</span>
                </button>
            </div>
        </div>

        <!-- Table Alignment dengan Dynamic ArrowUp / ArrowDown Sorting Header -->
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left text-slate-600 font-sans border-collapse">
                <thead class="bg-slate-100/70 text-slate-700 font-semibold uppercase tracking-wider text-[11px] border-b border-slate-200/80">
                    <tr>
                        <th @click="toggleSort('id')" class="px-4 py-3.5 text-center w-16 cursor-pointer hover:bg-slate-200/60 transition-colors select-none">
                            <div class="flex items-center justify-center gap-1">
                                <span>No</span>
                                <ArrowUp v-if="sortField === 'id' && sortOrder === 'asc'" :size="12" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'id' && sortOrder === 'desc'" :size="12" class="text-slate-900" />
                                <ArrowUpDown v-else :size="12" class="text-slate-300" />
                            </div>
                        </th>

                        <th @click="toggleSort('nama')" class="px-6 py-3.5 cursor-pointer hover:bg-slate-200/60 transition-colors select-none">
                            <div class="flex items-center gap-1.5">
                                <span>Nama Desa Binaan</span>
                                <ArrowUp v-if="sortField === 'nama' && sortOrder === 'asc'" :size="13" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'nama' && sortOrder === 'desc'" :size="13" class="text-slate-900" />
                                <ArrowUpDown v-else :size="13" class="text-slate-300" />
                            </div>
                        </th>

                        <th @click="toggleSort('upt')" class="px-6 py-3.5 cursor-pointer hover:bg-slate-200/60 transition-colors select-none">
                            <div class="flex items-center gap-1.5">
                                <span>Satker UPT Pembina</span>
                                <ArrowUp v-if="sortField === 'upt' && sortOrder === 'asc'" :size="13" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'upt' && sortOrder === 'desc'" :size="13" class="text-slate-900" />
                                <ArrowUpDown v-else :size="13" class="text-slate-300" />
                            </div>
                        </th>

                        <th @click="toggleSort('status')" class="px-6 py-3.5 text-center cursor-pointer hover:bg-slate-200/60 transition-colors select-none">
                            <div class="flex items-center justify-center gap-1.5">
                                <span>Status</span>
                                <ArrowUp v-if="sortField === 'status' && sortOrder === 'asc'" :size="13" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'status' && sortOrder === 'desc'" :size="13" class="text-slate-900" />
                                <ArrowUpDown v-else :size="13" class="text-slate-300" />
                            </div>
                        </th>

                        <th class="px-6 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70 bg-white">
                    <tr v-for="d in paginatedDesaList" :key="d.id" class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-4 py-4 text-center font-sans tabular-nums text-slate-900 font-bold">
                            {{ d.id }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-900">{{ d.nama }}</div>
                            <div v-if="d.lat !== null && d.lat !== undefined && d.lng !== null && d.lng !== undefined" class="text-[11px] text-slate-400 font-sans tabular-nums flex items-center gap-1 mt-0.5">
                                <MapPin :size="11" class="text-slate-400 shrink-0" />
                                <span>Lat: {{ d.lat }}, Lng: {{ d.lng }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-500 font-medium">{{ d.upt?.nama || '-' }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase border bg-emerald-50 text-emerald-700 border-emerald-200">
                                {{ d.status_terkini }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button @click="openEditModal(d)" class="btn-action-edit" title="Edit Desa Binaan">
                                    <Edit2 :size="14" />
                                </button>
                                <button @click="openDeleteModal(d)" class="btn-action-delete" title="Hapus Desa Binaan">
                                    <Trash2 :size="14" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <div>
                Menampilkan <span class="font-bold text-slate-900 tabular-nums">{{ (currentPage - 1) * pageSize + 1 }}</span> - <span class="font-bold text-slate-900 tabular-nums">{{ Math.min(currentPage * pageSize, processedDesaList.length) }}</span> dari <span class="font-bold text-slate-900 tabular-nums">{{ processedDesaList.length }}</span> Desa Binaan
            </div>
            <div class="flex items-center gap-2">
                <Button
                    @click="currentPage--"
                    :disabled="currentPage === 1"
                    variant="outline"
                    size="sm"
                    class="h-8 px-3 rounded-md border-slate-300 text-xs font-semibold gap-1"
                >
                    <ChevronLeft :size="14" />
                    <span>Sebelumnya</span>
                </Button>
                <span class="font-sans tabular-nums text-slate-700 px-2 font-bold">{{ currentPage }} / {{ totalDesaPages }}</span>
                <Button
                    @click="currentPage++"
                    :disabled="currentPage >= totalDesaPages"
                    variant="outline"
                    size="sm"
                    class="h-8 px-3 rounded-md border-slate-300 text-xs font-semibold gap-1"
                >
                    <span>Selanjutnya</span>
                    <ChevronRight :size="14" />
                </Button>
            </div>
        </div>

        <!-- MODAL DIALOG SHADCN CRUD DESA BINAAN -->
        <Dialog v-model:open="isDialogOpen">
            <DialogContent class="sm:max-w-[520px] bg-white rounded-lg p-6 sm:p-7 shadow-2xl space-y-0 font-sans border border-slate-100">
                
                <DialogHeader class="-mx-6 sm:-mx-7 px-6 sm:px-7 pb-3 border-b border-slate-100 space-y-1 text-left">
                    <DialogTitle class="text-base font-bold text-slate-900 flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-md bg-slate-100 text-slate-700 flex items-center justify-center border border-slate-200/80 shrink-0">
                            <Building2 :size="17" />
                        </div>
                        <span>{{ editingDesa ? 'Edit Desa Binaan' : 'Tambah Desa Binaan Baru' }}</span>
                    </DialogTitle>
                    <DialogDescription class="text-xs text-slate-500 leading-relaxed font-normal pt-0.5">
                        Isi rincian nama desa, koordinat geospasial, dan tentukan Satker UPT Pembina.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitForm" class="space-y-4 pt-3 text-xs">
                    
                    <div>
                        <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">Nama Desa Binaan</Label>
                        <Input
                            v-model="desaForm.nama"
                            placeholder="misal: Desa Sukamaju"
                            required
                            class="h-9 px-3.5 rounded-md border-slate-300 text-xs focus:ring-2 focus:ring-primary/40 shadow-2xs font-sans"
                        />
                    </div>

                    <!-- Koordinat Geospasial (Lat, Lng) -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">Latitude (Lintang)</Label>
                            <Input
                                type="number"
                                step="any"
                                v-model="desaForm.lat"
                                placeholder="misal: 3.5952"
                                class="h-9 px-3.5 rounded-md border-slate-300 text-xs focus:ring-2 focus:ring-primary/40 shadow-2xs font-sans"
                            />
                        </div>
                        <div>
                            <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">Longitude (Bujur)</Label>
                            <Input
                                type="number"
                                step="any"
                                v-model="desaForm.lng"
                                placeholder="misal: 98.6722"
                                class="h-9 px-3.5 rounded-md border-slate-300 text-xs focus:ring-2 focus:ring-primary/40 shadow-2xs font-sans"
                            />
                        </div>
                    </div>

                    <div>
                        <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">Satker UPT Pembina (Searchable)</Label>
                        <Combobox
                            v-model="desaForm.upt_id"
                            :options="uptComboboxOptions"
                            placeholder="Pilih Satker UPT Imigrasi..."
                            searchPlaceholder="Cari Kanim UPT..."
                        />
                    </div>

                    <DialogFooter class="-mx-6 sm:-mx-7 px-6 sm:px-7 pt-4 mt-5 border-t border-slate-100 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 sm:gap-3 bg-transparent">
                        <Button
                            type="button"
                            variant="outline"
                            @click="isDialogOpen = false"
                            class="w-full sm:w-auto h-9 px-4 rounded-xl text-xs font-bold border-slate-300 text-slate-700 hover:bg-slate-50 transition-all cursor-pointer"
                        >
                            Batal
                        </Button>
                        <Button
                            type="submit"
                            class="w-full sm:w-auto h-9 px-4 bg-primary hover:bg-[#04407D] text-primary-foreground rounded-xl text-xs font-bold shadow-xs transition-all cursor-pointer"
                        >
                            Simpan Desa Binaan
                        </Button>
                    </DialogFooter>
                </form>

            </DialogContent>
        </Dialog>

        <!-- GLOBAL REUSABLE CONFIRM DELETE MODAL SHADCN -->
        <ConfirmDeleteModal
            v-model:open="isDeleteModalOpen"
            title="Hapus Desa Binaan"
            :item-name="desaToDelete?.nama"
            description="Apakah Anda yakin ingin menghapus data desa binaan ini? Seluruh riwayat akan dihapus dari sistem."
            :loading="isDeleting"
            @confirm="executeDelete"
        />
    </div>
</template>
