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
    UserCheck,
    Phone,
    Mail,
    Building2,
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
import PasswordStrengthMeter from '@/components/common/PasswordStrengthMeter.vue';
import { notify } from '@/lib/toast';

interface DesaUserProps {
    id: number;
    name: string;
    email: string;
    kontak: string | null;
    is_active: boolean | number;
    desa_id: number | null;
    desa?: { id: number; nama: string };
}

interface DesaOptionProps {
    id: number;
    nama: string;
}

const props = defineProps<{
    desaUserList: DesaUserProps[];
    desaList: DesaOptionProps[];
}>();

const searchInput = ref('');
const filterDesa = ref<string>('all');

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

const processedDesaUserList = computed(() => {
    let list = [...props.desaUserList];

    if (searchInput.value) {
        const q = searchInput.value.toLowerCase();
        list = list.filter(u =>
            u.name.toLowerCase().includes(q) ||
            u.email.toLowerCase().includes(q) ||
            (u.kontak && u.kontak.toLowerCase().includes(q)) ||
            (u.desa?.nama && u.desa.nama.toLowerCase().includes(q))
        );
    }

    if (filterDesa.value !== 'all') {
        list = list.filter(u => u.desa_id?.toString() === filterDesa.value);
    }

    list.sort((a, b) => {
        let valA: any = '';
        let valB: any = '';
        if (sortField.value === 'id') { valA = a.id; valB = b.id; }
        else if (sortField.value === 'name') { valA = a.name; valB = b.name; }
        else if (sortField.value === 'email') { valA = a.email; valB = b.email; }
        else if (sortField.value === 'desa') { valA = a.desa?.nama || ''; valB = b.desa?.nama || ''; }

        if (typeof valA === 'number' && typeof valB === 'number') {
            return sortOrder.value === 'asc' ? valA - valB : valB - valA;
        }

        const cmp = String(valA).localeCompare(String(valB));
        return sortOrder.value === 'asc' ? cmp : -cmp;
    });

    return list;
});

const paginatedDesaUserList = computed(() => {
    const start = (currentPage.value - 1) * pageSize.value;
    return processedDesaUserList.value.slice(start, start + pageSize.value);
});

const totalDesaUserPages = computed(() => Math.ceil(processedDesaUserList.value.length / pageSize.value) || 1);

const desaFilterComboboxOptions = computed(() => [
    { value: 'all', label: `Semua Desa Binaan (${props.desaUserList.length})` },
    ...props.desaList.map(d => ({
        value: d.id.toString(),
        label: `${d.nama} (${props.desaUserList.filter(u => u.desa_id === d.id).length})`,
    }))
]);

const desaComboboxOptions = computed(() =>
    props.desaList.map(d => ({ value: d.id.toString(), label: d.nama }))
);

// --- MODAL DIALOG CRUD USER PERANGKAT DESA ---
const isDialogOpen = ref(false);
const editingDesaUser = ref<DesaUserProps | null>(null);
const desaUserForm = useForm({
    name: '',
    email: '',
    kontak: '',
    desa_id: '',
    is_active: '1',
    password: '',
});

const openTambahModal = () => {
    editingDesaUser.value = null;
    desaUserForm.reset();
    desaUserForm.desa_id = props.desaList[0]?.id.toString() || '';
    desaUserForm.is_active = '1';
    isDialogOpen.value = true;
};

const openEditModal = (u: DesaUserProps) => {
    editingDesaUser.value = u;
    desaUserForm.name = u.name;
    desaUserForm.email = u.email;
    desaUserForm.kontak = u.kontak || '';
    desaUserForm.desa_id = u.desa_id ? u.desa_id.toString() : '';
    desaUserForm.is_active = Boolean(u.is_active) ? '1' : '0';
    desaUserForm.password = '';
    isDialogOpen.value = true;
};

const submitForm = () => {
    const payload = {
        name: desaUserForm.name,
        email: desaUserForm.email.toLowerCase().trim(),
        kontak: desaUserForm.kontak.trim(),
        desa_id: desaUserForm.desa_id ? parseInt(desaUserForm.desa_id) : null,
        is_active: desaUserForm.is_active === '1',
        password: desaUserForm.password || undefined,
    };

    if (editingDesaUser.value) {
        router.put(`/kanwil/master-data/user-desa/${editingDesaUser.value.id}`, payload, {
            onSuccess: () => {
                isDialogOpen.value = false;
            },
            onError: (errs) => {
                notify.error('Gagal Menyimpan', { description: Object.values(errs)[0] || 'Periksa data input kembali.' });
            }
        });
    } else {
        router.post('/kanwil/master-data/user-desa', payload, {
            onSuccess: () => {
                isDialogOpen.value = false;
            },
            onError: (errs) => {
                notify.error('Gagal Menambahkan', { description: Object.values(errs)[0] || 'Periksa Email kembali.' });
            }
        });
    }
};

// --- CONFIRM DELETE MODAL REUSABLE ---
const isDeleteModalOpen = ref(false);
const desaUserToDelete = ref<DesaUserProps | null>(null);
const isDeleting = ref(false);

const openDeleteModal = (u: DesaUserProps) => {
    desaUserToDelete.value = u;
    isDeleteModalOpen.value = true;
};

const executeDelete = () => {
    if (!desaUserToDelete.value) return;
    isDeleting.value = true;
    router.delete(`/kanwil/master-data/user-desa/${desaUserToDelete.value.id}`, {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            isDeleting.value = false;
            desaUserToDelete.value = null;
        },
        onError: () => {
            isDeleting.value = false;
            notify.error('Gagal Menghapus User Perangkat Desa');
        }
    });
};

defineExpose({ openTambahModal });
const resetFilter = () => {
    searchInput.value = '';
    filterDesa.value = 'all';
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
                    placeholder="Cari user perangkat desa..."
                    class="pl-9 pr-20 text-xs rounded-md bg-white border-slate-200/90 h-9 shadow-2xs"
                />
                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 font-medium pointer-events-none select-none hidden sm:flex items-center gap-1">
                    <kbd class="bg-slate-100 border border-slate-300 text-slate-500 text-[9px] font-sans font-semibold px-1.5 py-0.5 rounded">Enter</kbd>
                </span>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <div class="w-full sm:w-72">
                    <Combobox
                        v-model="filterDesa"
                        :options="desaFilterComboboxOptions"
                        placeholder="Semua Desa Binaan"
                        searchPlaceholder="Cari Desa Binaan..."
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

        <!-- Table Alignment -->
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
                                <span>Nama User Perangkat Desa</span>
                                <ArrowUp v-if="sortField === 'name' && sortOrder === 'asc'" :size="13" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'name' && sortOrder === 'desc'" :size="13" class="text-slate-900" />
                                <ArrowUpDown v-else :size="13" class="text-slate-300" />
                            </div>
                        </th>

                        <th @click="toggleSort('email')" class="px-6 py-3.5 cursor-pointer hover:bg-slate-200/60 transition-colors select-none">
                            <div class="flex items-center gap-1.5">
                                <span>Email Login</span>
                                <ArrowUp v-if="sortField === 'email' && sortOrder === 'asc'" :size="13" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'email' && sortOrder === 'desc'" :size="13" class="text-slate-900" />
                                <ArrowUpDown v-else :size="13" class="text-slate-300" />
                            </div>
                        </th>

                        <th class="px-6 py-3.5">Kontak / WhatsApp</th>

                        <th @click="toggleSort('desa')" class="px-6 py-3.5 cursor-pointer hover:bg-slate-200/60 transition-colors select-none">
                            <div class="flex items-center gap-1.5">
                                <span>Desa Binaan</span>
                                <ArrowUp v-if="sortField === 'desa' && sortOrder === 'asc'" :size="13" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'desa' && sortOrder === 'desc'" :size="13" class="text-slate-900" />
                                <ArrowUpDown v-else :size="13" class="text-slate-300" />
                            </div>
                        </th>

                        <th class="px-6 py-3.5 text-center">Status</th>

                        <th class="px-6 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70 bg-white">
                    <tr v-for="u in paginatedDesaUserList" :key="u.id" class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-4 py-4 text-center font-sans tabular-nums text-slate-900 font-bold">
                            {{ u.id }}
                        </td>
                        <td class="px-6 py-4 font-bold text-slate-900">{{ u.name }}</td>
                        <td class="px-6 py-4 font-mono font-medium text-slate-700">{{ u.email }}</td>
                        <td class="px-6 py-4 font-mono text-slate-600">{{ u.kontak || '-' }}</td>
                        <td class="px-6 py-4 text-slate-700 font-medium">
                            <div class="flex items-center gap-1.5">
                                <Building2 :size="13" class="text-slate-400 shrink-0" />
                                <span>{{ u.desa?.nama || '-' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border"
                                :class="Boolean(u.is_active)
                                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                    : 'bg-rose-50 text-rose-700 border-rose-200'"
                            >
                                {{ Boolean(u.is_active) ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button @click="openEditModal(u)" class="btn-action-edit" title="Edit User Perangkat Desa">
                                    <Edit2 :size="14" />
                                </button>
                                <button @click="openDeleteModal(u)" class="btn-action-delete" title="Hapus User Perangkat Desa">
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
                Menampilkan <span class="font-bold text-slate-900 tabular-nums">{{ (currentPage - 1) * pageSize + 1 }}</span> - <span class="font-bold text-slate-900 tabular-nums">{{ Math.min(currentPage * pageSize, processedDesaUserList.length) }}</span> dari <span class="font-bold text-slate-900 tabular-nums">{{ processedDesaUserList.length }}</span> User Perangkat Desa
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
                <span class="font-sans tabular-nums text-slate-700 px-2 font-bold">{{ currentPage }} / {{ totalDesaUserPages }}</span>
                <Button
                    @click="currentPage++"
                    :disabled="currentPage >= totalDesaUserPages"
                    variant="outline"
                    size="sm"
                    class="h-8 px-3 rounded-md border-slate-300 text-xs font-semibold gap-1"
                >
                    <span>Selanjutnya</span>
                    <ChevronRight :size="14" />
                </Button>
            </div>
        </div>

        <!-- MODAL DIALOG SHADCN CRUD USER PERANGKAT DESA -->
        <Dialog v-model:open="isDialogOpen">
            <DialogContent class="sm:max-w-[520px] bg-white rounded-lg p-6 sm:p-7 shadow-2xl space-y-0 font-sans border border-slate-100">
                
                <DialogHeader class="-mx-6 sm:-mx-7 px-6 sm:px-7 pb-3 border-b border-slate-100 space-y-1 text-left">
                    <DialogTitle class="text-base font-bold text-slate-900 flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-md bg-slate-100 text-slate-700 flex items-center justify-center border border-slate-200/80 shrink-0">
                            <UserCheck :size="17" />
                        </div>
                        <span>{{ editingDesaUser ? 'Edit User Perangkat Desa' : 'Tambah User Perangkat Desa Baru' }}</span>
                    </DialogTitle>
                    <DialogDescription class="text-xs text-slate-500 leading-relaxed font-normal pt-0.5">
                        Isi data akun login Perangkat Desa & penempatan desa binaannya.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitForm" class="space-y-4 pt-3 text-xs">
                    
                    <div>
                        <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">Nama Perangkat Desa / Kontak</Label>
                        <Input
                            v-model="desaUserForm.name"
                            placeholder="Contoh: Kantor Desa Sambirejo / Pak Budi (Kades)"
                            required
                            class="h-9 px-3.5 rounded-md border-slate-300 text-xs focus:ring-2 focus:ring-primary/40 shadow-2xs font-sans"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">Email Login SIMPEL DBI</Label>
                            <Input
                                type="email"
                                v-model="desaUserForm.email"
                                placeholder="desa@simpeldbi.go.id"
                                required
                                class="h-9 px-3.5 rounded-md border-slate-300 text-xs font-mono shadow-2xs"
                            />
                        </div>
                        <div>
                            <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">No. Kontak / WhatsApp</Label>
                            <Input
                                v-model="desaUserForm.kontak"
                                placeholder="08123456789"
                                class="h-9 px-3.5 rounded-md border-slate-300 text-xs font-mono shadow-2xs"
                            />
                        </div>
                    </div>

                    <div>
                        <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">Desa Binaan (Searchable)</Label>
                        <Combobox
                            v-model="desaUserForm.desa_id"
                            :options="desaComboboxOptions"
                            placeholder="Pilih Desa Binaan..."
                            searchPlaceholder="Cari Desa Binaan..."
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">Status Akun</Label>
                            <Select v-model="desaUserForm.is_active">
                                <SelectTrigger class="h-9 text-xs border-slate-300 rounded-md bg-white">
                                    <SelectValue placeholder="Pilih Status" />
                                </SelectTrigger>
                                <SelectContent class="bg-white rounded-lg shadow-xl">
                                    <SelectGroup>
                                        <SelectItem value="1">Aktif</SelectItem>
                                        <SelectItem value="0">Nonaktif</SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </div>
                    <div>
                        <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">Password Login {{ editingDesaUser ? '(Isi jika ubah)' : '' }}</Label>
                        <Input
                            type="password"
                            v-model="desaUserForm.password"
                            :required="!editingDesaUser"
                            placeholder="******"
                            class="h-9 px-3.5 rounded-md border-slate-300 text-xs shadow-2xs font-sans"
                        />
                    </div>

                    <PasswordStrengthMeter :password="desaUserForm.password" />
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
                            Simpan User Perangkat Desa
                        </Button>
                    </DialogFooter>
                </form>

            </DialogContent>
        </Dialog>

        <!-- GLOBAL REUSABLE CONFIRM DELETE MODAL SHADCN -->
        <ConfirmDeleteModal
            v-model:open="isDeleteModalOpen"
            title="Hapus Akun User Perangkat Desa"
            :item-name="desaUserToDelete?.name"
            description="Apakah Anda yakin ingin menghapus akun user perangkat desa ini? Akun tidak akan lagi dapat login ke sistem."
            :loading="isDeleting"
            @confirm="executeDelete"
        />
    </div>
</template>
