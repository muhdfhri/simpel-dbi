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
    MapPin,
    Eye,
    Building2,
    Users,
    RotateCcw
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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

interface DesaProps {
    id: number;
    nama: string;
    status_terkini: string;
    pimpasa?: { id: number; name: string };
}

interface UserProps {
    id: number;
    name: string;
    email: string;
    nip: string;
    golongan: string;
}

interface UptProps {
    id: number;
    nama: string;
    tipe: string;
    desa_binaan_list_count: number;
    users_count: number;
    desa_binaan_list?: DesaProps[];
    users?: UserProps[];
}

const props = defineProps<{
    uptList: UptProps[];
}>();

const searchInput = ref('');
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

const filteredUptList = computed(() => {
    let list = [...props.uptList];

    if (searchInput.value) {
        const q = searchInput.value.toLowerCase();
        list = list.filter(u =>
            u.nama.toLowerCase().includes(q) ||
            u.tipe.toLowerCase().includes(q)
        );
    }

    list.sort((a, b) => {
        let valA: any = '';
        let valB: any = '';
        if (sortField.value === 'id') { valA = a.id; valB = b.id; }
        else if (sortField.value === 'nama') { valA = a.nama; valB = b.nama; }
        else if (sortField.value === 'tipe') { valA = a.tipe; valB = b.tipe; }

        if (typeof valA === 'number' && typeof valB === 'number') {
            return sortOrder.value === 'asc' ? valA - valB : valB - valA;
        }

        const cmp = String(valA).localeCompare(String(valB));
        return sortOrder.value === 'asc' ? cmp : -cmp;
    });

    return list;
});

// --- MODAL VIEW DETAIL RINCIAN SATKER UPT ---
const isDetailModalOpen = ref(false);
const selectedUptDetail = ref<UptProps | null>(null);
const activeDetailTab = ref<'desa' | 'pimpasa'>('desa');

const openDetailModal = (u: UptProps) => {
    selectedUptDetail.value = u;
    activeDetailTab.value = 'desa';
    isDetailModalOpen.value = true;
};

// --- MODAL DIALOG CRUD UPT ---
const isDialogOpen = ref(false);
const editingUpt = ref<UptProps | null>(null);
const uptForm = useForm({
    nama: '',
    tipe: 'Kantor Imigrasi',
});

const openTambahModal = () => {
    editingUpt.value = null;
    uptForm.reset();
    isDialogOpen.value = true;
};

const openEditModal = (u: UptProps) => {
    editingUpt.value = u;
    uptForm.nama = u.nama;
    uptForm.tipe = u.tipe;
    isDialogOpen.value = true;
};

const submitForm = () => {
    const payload = {
        nama: uptForm.nama.trim(),
        tipe: uptForm.tipe.trim(),
    };

    if (editingUpt.value) {
        router.put(`/kanwil/master-data/upt/${editingUpt.value.id}`, payload, {
            onSuccess: () => {
                isDialogOpen.value = false;
            },
            onError: (errs) => {
                notify.error('Gagal Menyimpan', { description: Object.values(errs)[0] || 'Periksa kembali isian Satker.' });
            }
        });
    } else {
        router.post('/kanwil/master-data/upt', payload, {
            onSuccess: () => {
                isDialogOpen.value = false;
            },
            onError: (errs) => {
                notify.error('Gagal Menambahkan', { description: Object.values(errs)[0] || 'Periksa kembali isian Satker.' });
            }
        });
    }
};

// --- CONFIRM DELETE MODAL REUSABLE ---
const isDeleteModalOpen = ref(false);
const uptToDelete = ref<UptProps | null>(null);
const isDeleting = ref(false);

const openDeleteModal = (u: UptProps) => {
    uptToDelete.value = u;
    isDeleteModalOpen.value = true;
};

const executeDelete = () => {
    if (!uptToDelete.value) return;
    isDeleting.value = true;
    router.delete(`/kanwil/master-data/upt/${uptToDelete.value.id}`, {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            isDeleting.value = false;
            uptToDelete.value = null;
        },
        onError: () => {
            isDeleting.value = false;
            notify.error('Gagal Menghapus Satker UPT');
        }
    });
};

defineExpose({ openTambahModal });
const resetFilter = () => {
    searchInput.value = '';
};
</script>

<template>
    <div class="space-y-4 font-sans">
        <!-- Search Toolbar -->
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="relative w-full sm:w-80">
                <Search :size="15" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
                <Input
                    type="text"
                    v-model="searchInput"
                    placeholder="Cari Satker UPT..."
                    class="pl-9 pr-20 text-xs rounded-md bg-white border-slate-200/90 h-9 shadow-2xs"
                />
                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 font-medium pointer-events-none select-none hidden sm:flex items-center gap-1">
                    <kbd class="bg-slate-100 border border-slate-300 text-slate-500 text-[9px] font-sans font-semibold px-1.5 py-0.5 rounded">Enter</kbd>
                </span>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-xs text-slate-500 font-medium">
                    Total: <span class="font-bold text-slate-900 tabular-nums">{{ filteredUptList.length }}</span> Satker UPT Imigrasi
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

                        <th @click="toggleSort('nama')" class="px-6 py-3.5 cursor-pointer hover:bg-slate-200/60 transition-colors select-none">
                            <div class="flex items-center gap-1.5">
                                <span>Nama Satker UPT Imigrasi</span>
                                <ArrowUp v-if="sortField === 'nama' && sortOrder === 'asc'" :size="13" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'nama' && sortOrder === 'desc'" :size="13" class="text-slate-900" />
                                <ArrowUpDown v-else :size="13" class="text-slate-300" />
                            </div>
                        </th>

                        <th @click="toggleSort('tipe')" class="px-6 py-3.5 cursor-pointer hover:bg-slate-200/60 transition-colors select-none">
                            <div class="flex items-center gap-1.5">
                                <span>Klasifikasi Tipe</span>
                                <ArrowUp v-if="sortField === 'tipe' && sortOrder === 'asc'" :size="13" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'tipe' && sortOrder === 'desc'" :size="13" class="text-slate-900" />
                                <ArrowUpDown v-else :size="13" class="text-slate-300" />
                            </div>
                        </th>

                        <th class="px-6 py-3.5 text-center">Jumlah Desa Binaan</th>
                        <th class="px-6 py-3.5 text-center">Jumlah Personel PIMPASA</th>
                        <th class="px-6 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70 bg-white">
                    <tr v-for="u in filteredUptList" :key="u.id" class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-4 py-4 text-center font-sans tabular-nums text-slate-900 font-bold">
                            {{ u.id }}
                        </td>
                        <td class="px-6 py-4 font-bold text-slate-900">{{ u.nama }}</td>
                        <td class="px-6 py-4 text-slate-500 font-medium">{{ u.tipe }}</td>
                        <td class="px-6 py-4 text-center font-bold text-slate-900 font-sans tabular-nums">{{ u.desa_binaan_list_count }} Desa</td>
                        <td class="px-6 py-4 text-center font-bold text-slate-900 font-sans tabular-nums">{{ u.users_count }} Orang</td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button @click="openDetailModal(u)" class="btn-action-view" title="Lihat Detail Satker UPT">
                                    <Eye :size="14" />
                                </button>
                                <button @click="openEditModal(u)" class="btn-action-edit" title="Edit Satker UPT">
                                    <Edit2 :size="14" />
                                </button>
                                <button @click="openDeleteModal(u)" class="btn-action-delete" title="Hapus Satker UPT">
                                    <Trash2 :size="14" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- MODAL VIEW DETAIL RINCIAN SATKER UPT SHADCN UI -->
        <Dialog v-model:open="isDetailModalOpen">
            <DialogContent class="sm:max-w-[640px] bg-white rounded-lg p-6 sm:p-7 shadow-2xl space-y-4 max-h-[85vh] flex flex-col font-sans border border-slate-100">
                <DialogHeader class="-mx-6 sm:-mx-7 px-6 sm:px-7 pb-3 border-b border-slate-100 space-y-1 shrink-0 text-left">
                    <div class="flex items-center justify-between gap-4">
                        <DialogTitle class="text-base font-bold text-slate-900 flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-md bg-slate-100 text-slate-700 flex items-center justify-center border border-slate-200/80 shrink-0">
                                <MapPin :size="18" />
                            </div>
                            <div>
                                <span class="block leading-snug">{{ selectedUptDetail?.nama }}</span>
                                <span class="text-[11px] font-medium text-slate-500 block">Tipe: {{ selectedUptDetail?.tipe }}</span>
                            </div>
                        </DialogTitle>
                    </div>
                </DialogHeader>

                <!-- Sub Metric Summary Badges -->
                <div class="grid grid-cols-2 gap-3 shrink-0">
                    <div class="p-3 rounded-md bg-slate-50 border border-slate-200 flex items-center gap-3">
                        <Building2 :size="20" class="text-slate-700 shrink-0" />
                        <div>
                            <span class="text-[10px] uppercase tracking-wider font-bold text-slate-500 block">Desa Binaan (DBI)</span>
                            <span class="text-lg font-bold text-slate-900 font-sans tabular-nums leading-none">{{ selectedUptDetail?.desa_binaan_list_count || 0 }} Desa</span>
                        </div>
                    </div>
                    <div class="p-3 rounded-md bg-slate-50 border border-slate-200 flex items-center gap-3">
                        <Users :size="20" class="text-slate-700 shrink-0" />
                        <div>
                            <span class="text-[10px] uppercase tracking-wider font-bold text-slate-500 block">Personel PIMPASA</span>
                            <span class="text-lg font-bold text-slate-900 font-sans tabular-nums leading-none">{{ selectedUptDetail?.users_count || 0 }} Personel</span>
                        </div>
                    </div>
                </div>

                <!-- Detail Internal Tab Switcher -->
                <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-md shrink-0">
                    <button
                        @click="activeDetailTab = 'desa'"
                        :class="['flex-1 py-1.5 rounded-md text-xs font-semibold transition-all', activeDetailTab === 'desa' ? 'bg-primary text-primary-foreground shadow-2xs' : 'text-slate-600 hover:text-slate-900']"
                    >
                        Daftar Desa Binaan ({{ selectedUptDetail?.desa_binaan_list?.length || 0 }})
                    </button>
                    <button
                        @click="activeDetailTab = 'pimpasa'"
                        :class="['flex-1 py-1.5 rounded-md text-xs font-semibold transition-all', activeDetailTab === 'pimpasa' ? 'bg-primary text-primary-foreground shadow-2xs' : 'text-slate-600 hover:text-slate-900']"
                    >
                        Daftar Personel PIMPASA ({{ selectedUptDetail?.users?.length || 0 }})
                    </button>
                </div>

                <!-- Detail Content Scroll Area -->
                <div class="overflow-y-auto flex-1 max-h-72 border border-slate-200/80 rounded-md divide-y divide-slate-100 text-xs">
                    <template v-if="activeDetailTab === 'desa'">
                        <div v-if="!selectedUptDetail?.desa_binaan_list || selectedUptDetail.desa_binaan_list.length === 0" class="p-6 text-center text-slate-400 italic">
                            Belum ada Desa Binaan yang terdaftar di UPT ini.
                        </div>
                        <div
                            v-for="(d, idx) in selectedUptDetail?.desa_binaan_list"
                            :key="d.id"
                            class="p-3.5 flex items-center justify-between hover:bg-slate-50 transition-colors"
                        >
                            <div class="space-y-0.5">
                                <div class="font-bold text-slate-900 text-xs">{{ idx + 1 }}. {{ d.nama }}</div>
                                <div class="text-[11px] text-slate-500">
                                    Pengampu: <span class="font-semibold text-slate-900">{{ d.pimpasa?.name || 'Belum Di-assign' }}</span>
                                </div>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase border bg-emerald-50 text-emerald-700 border-emerald-200">
                                {{ d.status_terkini }}
                            </span>
                        </div>
                    </template>

                    <template v-else-if="activeDetailTab === 'pimpasa'">
                        <div v-if="!selectedUptDetail?.users || selectedUptDetail.users.length === 0" class="p-6 text-center text-slate-400 italic">
                            Belum ada personel PIMPASA yang ditempatkan di UPT ini.
                        </div>
                        <div
                            v-for="(u, idx) in selectedUptDetail?.users"
                            :key="u.id"
                            class="p-3.5 flex items-center justify-between hover:bg-slate-50 transition-colors"
                        >
                            <div class="space-y-0.5">
                                <div class="font-bold text-slate-900 text-xs">{{ idx + 1 }}. {{ u.name }}</div>
                                <div class="text-[11px] text-slate-500">
                                    Golongan: <span class="font-semibold text-slate-900">{{ u.golongan || '-' }}</span> | Email: <span class="font-mono text-slate-900 font-bold">{{ u.email }}</span>
                                </div>
                            </div>
                            <span class="font-mono text-[11px] font-bold text-slate-900 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200">
                                NIP: {{ u.nip }}
                            </span>
                        </div>
                    </template>
                </div>

                <DialogFooter class="-mx-6 sm:-mx-7 px-6 sm:px-7 pt-3 border-t border-slate-100 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 sm:gap-3 shrink-0 bg-transparent">
                    <Button type="button" variant="outline" @click="isDetailModalOpen = false" class="w-full sm:w-auto h-9 px-4 rounded-xl text-xs font-bold border-slate-300 cursor-pointer">Tutup</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- MODAL DIALOG SHADCN CRUD SATKER UPT -->
        <Dialog v-model:open="isDialogOpen">
            <DialogContent class="sm:max-w-[520px] bg-white rounded-lg p-6 sm:p-7 shadow-2xl space-y-0 font-sans border border-slate-100">
                
                <DialogHeader class="-mx-6 sm:-mx-7 px-6 sm:px-7 pb-3 border-b border-slate-100 space-y-1 text-left">
                    <DialogTitle class="text-base font-bold text-slate-900 flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-md bg-slate-100 text-slate-700 flex items-center justify-center border border-slate-200/80 shrink-0">
                            <MapPin :size="17" />
                        </div>
                        <span>{{ editingUpt ? 'Edit Satker UPT Imigrasi' : 'Tambah Satker UPT Baru' }}</span>
                    </DialogTitle>
                    <DialogDescription class="text-xs text-slate-500 leading-relaxed font-normal pt-0.5">
                        Isi nama lengkap satuan kerja UPT dan klasifikasi tipe Kanim.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitForm" class="space-y-4 pt-3 text-xs">
                    
                    <div>
                        <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">Nama Satker UPT Imigrasi</Label>
                        <Input
                            v-model="uptForm.nama"
                            placeholder="misal: Kantor Imigrasi Kelas I Khusus TPI Medan"
                            required
                            class="h-9 px-3.5 rounded-md border-slate-300 text-xs focus:ring-2 focus:ring-primary/40 shadow-2xs font-sans"
                        />
                    </div>

                    <div>
                        <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">Klasifikasi Tipe Satker</Label>
                        <Input
                            v-model="uptForm.tipe"
                            placeholder="misal: Kelas I Khusus TPI"
                            required
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
                            Simpan Satker UPT
                        </Button>
                    </DialogFooter>
                </form>

            </DialogContent>
        </Dialog>

        <!-- GLOBAL REUSABLE CONFIRM DELETE MODAL SHADCN -->
        <ConfirmDeleteModal
            v-model:open="isDeleteModalOpen"
            title="Hapus Satker UPT Imigrasi"
            :item-name="uptToDelete?.nama"
            description="Apakah Anda yakin ingin menghapus Satker UPT ini? Seluruh korelasi data desa binaan dan personel PIMPASA akan terpengaruh."
            :loading="isDeleting"
            @confirm="executeDelete"
        />
    </div>
</template>
