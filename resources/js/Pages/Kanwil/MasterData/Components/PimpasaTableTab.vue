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
    Users,
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

interface PimpasaProps {
    id: number;
    name: string;
    email: string;
    nip: string;
    golongan: string;
    upt_id: number;
    upt?: { id: number; nama: string };
}

interface UptProps {
    id: number;
    nama: string;
}

const props = defineProps<{
    pimpasaList: PimpasaProps[];
    uptList: UptProps[];
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

const processedPimpasaList = computed(() => {
    let list = [...props.pimpasaList];

    if (searchInput.value) {
        const q = searchInput.value.toLowerCase();
        list = list.filter(p =>
            p.name.toLowerCase().includes(q) ||
            p.nip.toLowerCase().includes(q) ||
            p.email.toLowerCase().includes(q) ||
            (p.upt?.nama && p.upt.nama.toLowerCase().includes(q))
        );
    }

    if (filterUpt.value !== 'all') {
        list = list.filter(p => p.upt_id.toString() === filterUpt.value);
    }

    list.sort((a, b) => {
        let valA: any = '';
        let valB: any = '';
        if (sortField.value === 'id') { valA = a.id; valB = b.id; }
        else if (sortField.value === 'name') { valA = a.name; valB = b.name; }
        else if (sortField.value === 'nip') { valA = a.nip; valB = b.nip; }
        else if (sortField.value === 'upt') { valA = a.upt?.nama || ''; valB = b.upt?.nama || ''; }

        if (typeof valA === 'number' && typeof valB === 'number') {
            return sortOrder.value === 'asc' ? valA - valB : valB - valA;
        }

        const cmp = String(valA).localeCompare(String(valB));
        return sortOrder.value === 'asc' ? cmp : -cmp;
    });

    return list;
});

const paginatedPimpasaList = computed(() => {
    const start = (currentPage.value - 1) * pageSize.value;
    return processedPimpasaList.value.slice(start, start + pageSize.value);
});

const totalPimpasaPages = computed(() => Math.ceil(processedPimpasaList.value.length / pageSize.value) || 1);

const uptFilterComboboxOptions = computed(() => [
    { value: 'all', label: `Semua Satker UPT Imigrasi (${props.pimpasaList.length})` },
    ...props.uptList.map(u => ({
        value: u.id.toString(),
        label: `${u.nama} (${props.pimpasaList.filter(p => p.upt_id === u.id).length})`,
    }))
]);

const uptComboboxOptions = computed(() =>
    props.uptList.map(u => ({ value: u.id.toString(), label: u.nama }))
);

// --- MODAL DIALOG CRUD PETUGAS PIMPASA ---
const isDialogOpen = ref(false);
const editingPimpasa = ref<PimpasaProps | null>(null);
const pimpasaForm = useForm({
    name: '',
    email: '',
    nip: '',
    golongan: '',
    upt_id: '',
    password: '',
});

const openTambahModal = () => {
    editingPimpasa.value = null;
    pimpasaForm.reset();
    pimpasaForm.upt_id = props.uptList[0]?.id.toString() || '';
    isDialogOpen.value = true;
};

const openEditModal = (p: PimpasaProps) => {
    editingPimpasa.value = p;
    pimpasaForm.name = p.name;
    pimpasaForm.email = p.email;
    pimpasaForm.nip = p.nip;
    pimpasaForm.golongan = p.golongan;
    pimpasaForm.upt_id = p.upt_id.toString();
    pimpasaForm.password = '';
    isDialogOpen.value = true;
};

const submitForm = () => {
    const payload = {
        name: pimpasaForm.name,
        email: pimpasaForm.email.toLowerCase().trim(),
        nip: pimpasaForm.nip.trim(),
        golongan: pimpasaForm.golongan.trim(),
        upt_id: parseInt(pimpasaForm.upt_id),
        password: pimpasaForm.password || undefined,
    };

    if (editingPimpasa.value) {
        router.put(`/kanwil/master-data/pimpasa/${editingPimpasa.value.id}`, payload, {
            onSuccess: () => {
                isDialogOpen.value = false;
            },
            onError: (errs) => {
                notify.error('Gagal Menyimpan', { description: Object.values(errs)[0] || 'Periksa NIP / Email kembali.' });
            }
        });
    } else {
        router.post('/kanwil/master-data/pimpasa', payload, {
            onSuccess: () => {
                isDialogOpen.value = false;
            },
            onError: (errs) => {
                notify.error('Gagal Menambahkan', { description: Object.values(errs)[0] || 'Periksa NIP / Email kembali.' });
            }
        });
    }
};

// --- CONFIRM DELETE MODAL REUSABLE ---
const isDeleteModalOpen = ref(false);
const pimpasaToDelete = ref<PimpasaProps | null>(null);
const isDeleting = ref(false);

const openDeleteModal = (p: PimpasaProps) => {
    pimpasaToDelete.value = p;
    isDeleteModalOpen.value = true;
};

const executeDelete = () => {
    if (!pimpasaToDelete.value) return;
    isDeleting.value = true;
    router.delete(`/kanwil/master-data/pimpasa/${pimpasaToDelete.value.id}`, {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            isDeleting.value = false;
            pimpasaToDelete.value = null;
        },
        onError: () => {
            isDeleting.value = false;
            notify.error('Gagal Menghapus Petugas PIMPASA');
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
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="relative w-full sm:w-72">
                <Search :size="15" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
                <Input
                    type="text"
                    v-model="searchInput"
                    placeholder="Cari personel PIMPASA..."
                    class="pl-9 pr-20 text-xs rounded-md bg-white border-slate-200/90 h-9 shadow-2xs"
                />
                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 font-medium pointer-events-none select-none hidden sm:flex items-center gap-1">
                    <kbd class="bg-slate-100 border border-slate-300 text-slate-500 text-[9px] font-sans font-semibold px-1.5 py-0.5 rounded">Enter</kbd>
                </span>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <div class="w-full sm:w-72">
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
                    class="h-9 px-2.5 bg-white border border-slate-200/90 rounded-md text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-all flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer shrink-0"
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

                        <th @click="toggleSort('name')" class="px-6 py-3.5 cursor-pointer hover:bg-slate-200/60 transition-colors select-none">
                            <div class="flex items-center gap-1.5">
                                <span>Nama Personel PIMPASA</span>
                                <ArrowUp v-if="sortField === 'name' && sortOrder === 'asc'" :size="13" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'name' && sortOrder === 'desc'" :size="13" class="text-slate-900" />
                                <ArrowUpDown v-else :size="13" class="text-slate-300" />
                            </div>
                        </th>

                        <th @click="toggleSort('nip')" class="px-6 py-3.5 cursor-pointer hover:bg-slate-200/60 transition-colors select-none">
                            <div class="flex items-center gap-1.5">
                                <span>NIP Resmi</span>
                                <ArrowUp v-if="sortField === 'nip' && sortOrder === 'asc'" :size="13" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'nip' && sortOrder === 'desc'" :size="13" class="text-slate-900" />
                                <ArrowUpDown v-else :size="13" class="text-slate-300" />
                            </div>
                        </th>

                        <th class="px-6 py-3.5">Pangkat / Golongan</th>

                        <th @click="toggleSort('upt')" class="px-6 py-3.5 cursor-pointer hover:bg-slate-200/60 transition-colors select-none">
                            <div class="flex items-center gap-1.5">
                                <span>Satker UPT Imigrasi</span>
                                <ArrowUp v-if="sortField === 'upt' && sortOrder === 'asc'" :size="13" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'upt' && sortOrder === 'desc'" :size="13" class="text-slate-900" />
                                <ArrowUpDown v-else :size="13" class="text-slate-300" />
                            </div>
                        </th>

                        <th class="px-6 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70 bg-white">
                    <tr v-for="p in paginatedPimpasaList" :key="p.id" class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-4 py-4 text-center font-sans tabular-nums text-slate-900 font-bold">
                            {{ p.id }}
                        </td>
                        <td class="px-6 py-4 font-bold text-slate-900">{{ p.name }}</td>
                        <td class="px-6 py-4 font-mono font-bold text-slate-900">{{ p.nip || '-' }}</td>
                        <td class="px-6 py-4 text-slate-500 font-medium">{{ p.golongan || '-' }}</td>
                        <td class="px-6 py-4 text-slate-500 font-medium">{{ p.upt?.nama || '-' }}</td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button @click="openEditModal(p)" class="btn-action-edit" title="Edit Petugas PIMPASA">
                                    <Edit2 :size="14" />
                                </button>
                                <button @click="openDeleteModal(p)" class="btn-action-delete" title="Hapus Petugas PIMPASA">
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
                Menampilkan <span class="font-bold text-slate-900 tabular-nums">{{ (currentPage - 1) * pageSize + 1 }}</span> - <span class="font-bold text-slate-900 tabular-nums">{{ Math.min(currentPage * pageSize, processedPimpasaList.length) }}</span> dari <span class="font-bold text-slate-900 tabular-nums">{{ processedPimpasaList.length }}</span> Personel PIMPASA
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
                <span class="font-sans tabular-nums text-slate-700 px-2 font-bold">{{ currentPage }} / {{ totalPimpasaPages }}</span>
                <Button
                    @click="currentPage++"
                    :disabled="currentPage >= totalPimpasaPages"
                    variant="outline"
                    size="sm"
                    class="h-8 px-3 rounded-md border-slate-300 text-xs font-semibold gap-1"
                >
                    <span>Selanjutnya</span>
                    <ChevronRight :size="14" />
                </Button>
            </div>
        </div>

        <!-- MODAL DIALOG SHADCN CRUD PETUGAS PIMPASA -->
        <Dialog v-model:open="isDialogOpen">
            <DialogContent class="sm:max-w-[520px] bg-white rounded-lg p-6 sm:p-7 shadow-2xl space-y-0 font-sans border border-slate-100">
                
                <DialogHeader class="-mx-6 sm:-mx-7 px-6 sm:px-7 pb-3 border-b border-slate-100 space-y-1 text-left">
                    <DialogTitle class="text-base font-bold text-slate-900 flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-md bg-slate-100 text-slate-700 flex items-center justify-center border border-slate-200/80 shrink-0">
                            <Users :size="17" />
                        </div>
                        <span>{{ editingPimpasa ? 'Edit Petugas PIMPASA' : 'Tambah User PIMPASA Baru' }}</span>
                    </DialogTitle>
                    <DialogDescription class="text-xs text-slate-500 leading-relaxed font-normal pt-0.5">
                        Isi rincian NIP, Pangkat/Golongan, dan Satker UPT penempatan.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitForm" class="space-y-4 pt-3 text-xs">
                    
                    <div>
                        <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">Nama Personel PIMPASA</Label>
                        <Input
                            v-model="pimpasaForm.name"
                            placeholder="Nama Lengkap & Gelar"
                            required
                            class="h-9 px-3.5 rounded-md border-slate-300 text-xs focus:ring-2 focus:ring-primary/40 shadow-2xs font-sans"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">NIP Resmi</Label>
                            <Input
                                v-model="pimpasaForm.nip"
                                placeholder="1995xxxx"
                                required
                                class="h-9 px-3.5 rounded-md border-slate-300 text-xs font-mono shadow-2xs"
                            />
                        </div>
                        <div>
                            <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">Pangkat / Golongan</Label>
                            <Input
                                v-model="pimpasaForm.golongan"
                                placeholder="Penata Muda / III/a"
                                required
                                class="h-9 px-3.5 rounded-md border-slate-300 text-xs shadow-2xs font-sans"
                            />
                        </div>
                    </div>

                    <div>
                        <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">Email Login SIMPEL DBI</Label>
                        <Input
                            type="email"
                            v-model="pimpasaForm.email"
                            placeholder="nama@simpeldbi.go.id"
                            required
                            class="h-9 px-3.5 rounded-md border-slate-300 text-xs font-mono shadow-2xs"
                        />
                    </div>

                    <div>
                        <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">Satker UPT Imigrasi (Searchable)</Label>
                        <Combobox
                            v-model="pimpasaForm.upt_id"
                            :options="uptComboboxOptions"
                            placeholder="Pilih UPT Imigrasi..."
                            searchPlaceholder="Cari Kanim UPT..."
                        />
                    </div>

                    <div>
                        <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">Password Login {{ editingPimpasa ? '(Isi jika ubah)' : '' }}</Label>
                        <Input
                            type="password"
                            v-model="pimpasaForm.password"
                            :required="!editingPimpasa"
                            placeholder="******"
                            class="h-9 px-3.5 rounded-md border-slate-300 text-xs shadow-2xs font-sans"
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
                            Simpan User PIMPASA
                        </Button>
                    </DialogFooter>
                </form>

            </DialogContent>
        </Dialog>

        <!-- GLOBAL REUSABLE CONFIRM DELETE MODAL SHADCN -->
        <ConfirmDeleteModal
            v-model:open="isDeleteModalOpen"
            title="Hapus Akun PIMPASA"
            :item-name="pimpasaToDelete?.name"
            description="Apakah Anda yakin ingin menghapus akun personel PIMPASA ini? Akun tidak akan lagi dapat login ke sistem."
            :loading="isDeleting"
            @confirm="executeDelete"
        />
    </div>
</template>
