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
    Tag,
    CheckCircle2,
    XCircle,
    RotateCcw
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import ConfirmDeleteModal from '@/components/common/ConfirmDeleteModal.vue';
import { notify } from '@/lib/toast';

interface KategoriItem {
    id: number;
    kode: string;
    nama_kategori: string;
    deskripsi: string | null;
    is_active: boolean | number;
}

const props = defineProps<{
    kategoriList: KategoriItem[];
}>();

const searchInput = ref('');
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

// Filtered & Sorted List
const processedKategoriList = computed(() => {
    let list = [...props.kategoriList];

    if (searchInput.value.trim()) {
        const q = searchInput.value.toLowerCase();
        list = list.filter(
            (k) =>
                k.nama_kategori.toLowerCase().includes(q) ||
                k.kode.toLowerCase().includes(q) ||
                (k.deskripsi && k.deskripsi.toLowerCase().includes(q))
        );
    }

    list.sort((a, b) => {
        let valA: any = '';
        let valB: any = '';
        if (sortField.value === 'id') { valA = a.id; valB = b.id; }
        else if (sortField.value === 'kode') { valA = a.kode; valB = b.kode; }
        else if (sortField.value === 'nama') { valA = a.nama_kategori; valB = b.nama_kategori; }
        else if (sortField.value === 'status') { valA = Boolean(a.is_active) ? 1 : 0; valB = Boolean(b.is_active) ? 1 : 0; }

        if (typeof valA === 'number' && typeof valB === 'number') {
            return sortOrder.value === 'asc' ? valA - valB : valB - valA;
        }

        const cmp = String(valA).localeCompare(String(valB));
        return sortOrder.value === 'asc' ? cmp : -cmp;
    });

    return list;
});

const paginatedKategoriList = computed(() => {
    const start = (currentPage.value - 1) * pageSize.value;
    return processedKategoriList.value.slice(start, start + pageSize.value);
});

const totalKategoriPages = computed(() => Math.ceil(processedKategoriList.value.length / pageSize.value) || 1);

// --- MODAL FORM CRUD ---
const isDialogOpen = ref(false);
const editingKategori = ref<KategoriItem | null>(null);

const form = useForm({
    kode: '',
    nama_kategori: '',
    deskripsi: '',
    is_active: true,
});

const openAddModal = () => {
    editingKategori.value = null;
    form.reset();
    form.clearErrors();
    form.is_active = true;
    isDialogOpen.value = true;
};

const openEditModal = (item: KategoriItem) => {
    editingKategori.value = item;
    form.clearErrors();
    form.kode = item.kode;
    form.nama_kategori = item.nama_kategori;
    form.deskripsi = item.deskripsi || '';
    form.is_active = Boolean(item.is_active);
    isDialogOpen.value = true;
};

const submitForm = () => {
    if (editingKategori.value) {
        form.put(`/kanwil/master-data/kategori/${editingKategori.value.id}`, {
            onSuccess: () => {
                isDialogOpen.value = false;
            },
            onError: (errs) => {
                notify.error('Gagal Menyimpan', { description: Object.values(errs)[0] || 'Periksa kembali isian data.' });
            }
        });
    } else {
        form.post('/kanwil/master-data/kategori', {
            onSuccess: () => {
                isDialogOpen.value = false;
            },
            onError: (errs) => {
                notify.error('Gagal Menambahkan', { description: Object.values(errs)[0] || 'Periksa kembali isian data.' });
            }
        });
    }
};

const toggleStatus = (item: KategoriItem) => {
    router.post(`/kanwil/master-data/kategori/${item.id}/toggle`, {}, {
        preserveScroll: true,
    });
};

// --- CONFIRM DELETE MODAL ---
const isDeleteModalOpen = ref(false);
const kategoriToDelete = ref<KategoriItem | null>(null);
const isDeleting = ref(false);

const openDeleteModal = (item: KategoriItem) => {
    kategoriToDelete.value = item;
    isDeleteModalOpen.value = true;
};

const executeDelete = () => {
    if (!kategoriToDelete.value) return;
    isDeleting.value = true;
    router.delete(`/kanwil/master-data/kategori/${kategoriToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            isDeleting.value = false;
            kategoriToDelete.value = null;
        },
        onError: () => {
            isDeleting.value = false;
            notify.error('Gagal Menghapus Kategori');
        }
    });
};

defineExpose({
    openAddModal,
    openTambahModal: openAddModal,
});
const resetFilter = () => {
    searchInput.value = '';
};
</script>

<template>
    <div class="space-y-4 font-sans">
        
        <!-- Search & Filter Toolbar -->
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="relative w-full sm:w-80">
                <Search :size="15" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
                <Input
                    type="text"
                    v-model="searchInput"
                    placeholder="Cari kategori atau kode..."
                    class="pl-9 pr-20 text-xs rounded-md bg-white border-slate-200/90 h-9 shadow-2xs"
                />
                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 font-medium pointer-events-none select-none hidden sm:flex items-center gap-1">
                    <kbd class="bg-slate-100 border border-slate-300 text-slate-500 text-[9px] font-sans font-semibold px-1.5 py-0.5 rounded">Enter</kbd>
                </span>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-xs text-slate-500 font-medium">
                    Total: <span class="font-bold text-slate-900 tabular-nums">{{ processedKategoriList.length }}</span> Kategori Laporan
                </div>
                <button
                    v-if="searchInput"
                    type="button"
                    @click="resetFilter"
                    class="h-9 px-2.5 bg-white border border-slate-200/90 rounded-md text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-all flex items-center gap-1.5 shadow-2xs cursor-pointer"
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

                        <th @click="toggleSort('kode')" class="px-6 py-3.5 cursor-pointer hover:bg-slate-200/60 transition-colors select-none">
                            <div class="flex items-center gap-1.5">
                                <span>Kode System</span>
                                <ArrowUp v-if="sortField === 'kode' && sortOrder === 'asc'" :size="13" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'kode' && sortOrder === 'desc'" :size="13" class="text-slate-900" />
                                <ArrowUpDown v-else :size="13" class="text-slate-300" />
                            </div>
                        </th>

                        <th @click="toggleSort('nama')" class="px-6 py-3.5 cursor-pointer hover:bg-slate-200/60 transition-colors select-none">
                            <div class="flex items-center gap-1.5">
                                <span>Nama Kategori Laporan</span>
                                <ArrowUp v-if="sortField === 'nama' && sortOrder === 'asc'" :size="13" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'nama' && sortOrder === 'desc'" :size="13" class="text-slate-900" />
                                <ArrowUpDown v-else :size="13" class="text-slate-300" />
                            </div>
                        </th>

                        <th class="px-6 py-3.5">Deskripsi Bantuan</th>

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
                    <tr v-if="paginatedKategoriList.length === 0">
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            <Tag :size="32" class="mx-auto mb-2 opacity-40" />
                            <p class="font-bold text-slate-700 text-xs">Belum ada kategori laporan</p>
                        </td>
                    </tr>

                    <tr v-for="(row, idx) in paginatedKategoriList" :key="row.id" class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-4 py-4 text-center font-sans tabular-nums text-slate-900 font-bold">
                            {{ (currentPage - 1) * pageSize + idx + 1 }}
                        </td>
                        <td class="px-6 py-4 font-mono font-bold text-slate-900">
                            <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200 text-[11px]">
                                {{ row.kode }}
                            </span>
                        </td>
                        <td class="px-6 py-4 font-bold text-slate-900">
                            {{ row.nama_kategori }}
                        </td>
                        <td class="px-6 py-4 text-slate-500 font-medium max-w-sm line-clamp-2">
                            {{ row.deskripsi || '-' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button
                                type="button"
                                @click="toggleStatus(row)"
                                class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase border transition-colors cursor-pointer inline-flex items-center gap-1"
                                :class="Boolean(row.is_active) ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-200 hover:bg-slate-200'"
                            >
                                <CheckCircle2 v-if="Boolean(row.is_active)" :size="12" />
                                <XCircle v-else :size="12" />
                                <span>{{ Boolean(row.is_active) ? 'Aktif' : 'Non-Aktif' }}</span>
                            </button>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button @click="openEditModal(row)" class="btn-action-edit" title="Edit Kategori Laporan">
                                    <Edit2 :size="14" />
                                </button>
                                <button @click="openDeleteModal(row)" class="btn-action-delete" title="Hapus Kategori Laporan">
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
                Menampilkan <span class="font-bold text-slate-900 tabular-nums">{{ (currentPage - 1) * pageSize + 1 }}</span> - <span class="font-bold text-slate-900 tabular-nums">{{ Math.min(currentPage * pageSize, processedKategoriList.length) }}</span> dari <span class="font-bold text-slate-900 tabular-nums">{{ processedKategoriList.length }}</span> Kategori Laporan
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
                <span class="font-sans tabular-nums text-slate-700 px-2 font-bold">{{ currentPage }} / {{ totalKategoriPages }}</span>
                <Button
                    @click="currentPage++"
                    :disabled="currentPage >= totalKategoriPages"
                    variant="outline"
                    size="sm"
                    class="h-8 px-3 rounded-md border-slate-300 text-xs font-semibold gap-1"
                >
                    <span>Selanjutnya</span>
                    <ChevronRight :size="14" />
                </Button>
            </div>
        </div>

        <!-- Modal Dialog Form Tambah / Edit Kategori -->
        <Dialog v-model:open="isDialogOpen">
            <DialogContent class="sm:max-w-lg rounded-xl">
                <DialogHeader>
                    <DialogTitle class="text-sm font-bold text-slate-900">
                        {{ editingKategori ? 'Edit Master Kategori Laporan' : 'Tambah Kategori Laporan Baru' }}
                    </DialogTitle>
                    <DialogDescription class="text-xs text-slate-500">
                        Kelola data kategori laporan yang akan dapat dipilih oleh Perangkat Desa.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitForm" class="space-y-4 pt-2">
                    <div class="space-y-1.5">
                        <Label for="nama_kategori" class="font-semibold text-slate-800 text-xs tracking-tight block">Nama Kategori <span class="text-red-500">*</span></Label>
                        <Input
                            id="nama_kategori"
                            v-model="form.nama_kategori"
                            type="text"
                            placeholder="Contoh: Indikasi TPPO / PMI Non-Prosedural"
                            class="text-xs rounded-md border-slate-300 h-9"
                            required
                        />
                        <p v-if="form.errors.nama_kategori" class="text-xs text-red-500">{{ form.errors.nama_kategori }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="kode" class="font-semibold text-slate-800 text-xs tracking-tight block">Kode Identifier System <span class="text-red-500">*</span></Label>
                        <Input
                            id="kode"
                            v-model="form.kode"
                            type="text"
                            placeholder="Contoh: tppo_pmi (huruf kecil & underscore)"
                            class="text-xs font-mono rounded-md border-slate-300 h-9"
                            required
                        />
                        <p v-if="form.errors.kode" class="text-xs text-red-500">{{ form.errors.kode }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="deskripsi" class="font-semibold text-slate-800 text-xs tracking-tight block">Deskripsi Bantuan (Opsional)</Label>
                        <Textarea
                            id="deskripsi"
                            v-model="form.deskripsi"
                            rows="3"
                            placeholder="Penjelasan ringkas mengenai kriteria laporan ini..."
                            class="text-xs rounded-md border-slate-300"
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
                            :disabled="form.processing"
                            class="w-full sm:w-auto h-9 px-4 bg-primary hover:bg-[#04407D] text-primary-foreground rounded-xl text-xs font-bold shadow-xs transition-all cursor-pointer"
                        >
                            {{ editingKategori ? 'Simpan Perubahan' : 'Tambah Kategori' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- GLOBAL REUSABLE CONFIRM DELETE MODAL SHADCN -->
        <ConfirmDeleteModal
            v-model:open="isDeleteModalOpen"
            title="Hapus Kategori Laporan"
            :item-name="kategoriToDelete?.nama_kategori"
            description="Apakah Anda yakin ingin menghapus data kategori laporan ini? Kategori yang dihapus tidak akan dapat dipilih lagi dalam pengajuan baru."
            :loading="isDeleting"
            @confirm="executeDelete"
        />

    </div>
</template>
