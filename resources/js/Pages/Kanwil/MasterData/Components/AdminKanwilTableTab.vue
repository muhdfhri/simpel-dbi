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
    ShieldCheck,
    RotateCcw
} from 'lucide-vue-next';

const resetFilter = () => {
    searchInput.value = '';
};
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
import PasswordStrengthMeter from '@/components/common/PasswordStrengthMeter.vue';
import { notify } from '@/lib/toast';

interface KanwilUserProps {
    id: number;
    name: string;
    email: string;
    nip?: string | null;
    golongan?: string | null;
    kontak?: string | null;
    is_active?: boolean | number;
}

const props = defineProps<{
    kanwilUserList: KanwilUserProps[];
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

const processedKanwilList = computed(() => {
    let list = [...props.kanwilUserList];

    if (searchInput.value) {
        const q = searchInput.value.toLowerCase();
        list = list.filter(u =>
            u.name.toLowerCase().includes(q) ||
            u.email.toLowerCase().includes(q) ||
            (u.nip && u.nip.toLowerCase().includes(q)) ||
            (u.kontak && u.kontak.toLowerCase().includes(q))
        );
    }

    list.sort((a, b) => {
        let valA: any = '';
        let valB: any = '';
        if (sortField.value === 'id') { valA = a.id; valB = b.id; }
        else if (sortField.value === 'name') { valA = a.name; valB = b.name; }
        else if (sortField.value === 'email') { valA = a.email; valB = b.email; }
        else if (sortField.value === 'nip') { valA = a.nip || ''; valB = b.nip || ''; }

        if (typeof valA === 'number' && typeof valB === 'number') {
            return sortOrder.value === 'asc' ? valA - valB : valB - valA;
        }

        const cmp = String(valA).localeCompare(String(valB));
        return sortOrder.value === 'asc' ? cmp : -cmp;
    });

    return list;
});

const paginatedKanwilList = computed(() => {
    const start = (currentPage.value - 1) * pageSize.value;
    return processedKanwilList.value.slice(start, start + pageSize.value);
});

const totalKanwilPages = computed(() => Math.ceil(processedKanwilList.value.length / pageSize.value) || 1);

// --- MODAL DIALOG CRUD ADMIN KANWIL ---
const isDialogOpen = ref(false);
const editingKanwilUser = ref<KanwilUserProps | null>(null);
const kanwilForm = useForm({
    name: '',
    email: '',
    nip: '',
    golongan: '',
    kontak: '',
    password: '',
});

const openTambahModal = () => {
    editingKanwilUser.value = null;
    kanwilForm.reset();
    isDialogOpen.value = true;
};

const openEditModal = (user: KanwilUserProps) => {
    editingKanwilUser.value = user;
    kanwilForm.name = user.name;
    kanwilForm.email = user.email;
    kanwilForm.nip = user.nip || '';
    kanwilForm.golongan = user.golongan || '';
    kanwilForm.kontak = user.kontak || '';
    kanwilForm.password = '';
    isDialogOpen.value = true;
};

const submitForm = () => {
    const payload = {
        name: kanwilForm.name,
        email: kanwilForm.email.toLowerCase().trim(),
        nip: kanwilForm.nip.trim() || undefined,
        golongan: kanwilForm.golongan.trim() || undefined,
        kontak: kanwilForm.kontak.trim() || undefined,
        password: kanwilForm.password || undefined,
    };

    if (editingKanwilUser.value) {
        router.put(`/kanwil/master-data/user-kanwil/${editingKanwilUser.value.id}`, payload, {
            onSuccess: () => {
                isDialogOpen.value = false;
                notify.success('Data Admin Kanwil berhasil diperbarui');
            },
            onError: (errs) => {
                notify.error('Gagal Menyimpan', { description: Object.values(errs)[0] || 'Periksa NIP / Email kembali.' });
            }
        });
    } else {
        router.post('/kanwil/master-data/user-kanwil', payload, {
            onSuccess: () => {
                isDialogOpen.value = false;
                notify.success('Akun Admin Kanwil berhasil ditambahkan');
            },
            onError: (errs) => {
                notify.error('Gagal Menambahkan', { description: Object.values(errs)[0] || 'Periksa NIP / Email kembali.' });
            }
        });
    }
};

// --- CONFIRM DELETE MODAL ---
const isDeleteModalOpen = ref(false);
const kanwilUserToDelete = ref<KanwilUserProps | null>(null);
const isDeleting = ref(false);

const openDeleteModal = (user: KanwilUserProps) => {
    kanwilUserToDelete.value = user;
    isDeleteModalOpen.value = true;
};

const executeDelete = () => {
    if (!kanwilUserToDelete.value) return;
    isDeleting.value = true;
    router.delete(`/kanwil/master-data/user-kanwil/${kanwilUserToDelete.value.id}`, {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            isDeleting.value = false;
            kanwilUserToDelete.value = null;
            notify.success('Akun Admin Kanwil berhasil dihapus');
        },
        onError: (errs) => {
            isDeleting.value = false;
            notify.error('Gagal Menghapus Admin Kanwil', { description: Object.values(errs)[0] || 'Terjadi kesalahan.' });
        }
    });
};

defineExpose({ openTambahModal });
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
                    placeholder="Cari nama, email, atau NIP admin Kanwil..."
                    class="pl-9 pr-20 text-xs rounded-md bg-white border-slate-200/90 h-9 shadow-2xs"
                />
                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 font-medium pointer-events-none select-none hidden sm:flex items-center gap-1">
                    <kbd class="bg-slate-100 border border-slate-300 text-slate-500 text-[9px] font-sans font-semibold px-1.5 py-0.5 rounded">Enter</kbd>
                </span>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-xs text-slate-500 font-medium">
                    Total: <span class="font-bold text-slate-900 tabular-nums">{{ processedKanwilList.length }}</span> User Executive / Admin Kanwil
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

        <!-- Data Table -->
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
                                <span>Nama Administrator Kanwil</span>
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
                        <th @click="toggleSort('email')" class="px-6 py-3.5 cursor-pointer hover:bg-slate-200/60 transition-colors select-none">
                            <div class="flex items-center gap-1.5">
                                <span>Email Akun Login</span>
                                <ArrowUp v-if="sortField === 'email' && sortOrder === 'asc'" :size="13" class="text-slate-900" />
                                <ArrowDown v-else-if="sortField === 'email' && sortOrder === 'desc'" :size="13" class="text-slate-900" />
                                <ArrowUpDown v-else :size="13" class="text-slate-300" />
                            </div>
                        </th>
                        <th class="px-6 py-3.5">No. WhatsApp Operasional</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70 bg-white">
                    <tr v-for="u in paginatedKanwilList" :key="u.id" class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-4 py-4 text-center font-sans tabular-nums text-slate-900 font-bold">
                            {{ u.id }}
                        </td>
                        <td class="px-6 py-4 font-bold text-slate-900">{{ u.name }}</td>
                        <td class="px-6 py-4 font-mono font-bold text-slate-900">{{ u.nip || '-' }}</td>
                        <td class="px-6 py-4 text-slate-500 font-medium">{{ u.golongan || '-' }}</td>
                        <td class="px-6 py-4 font-mono text-slate-700 font-medium">{{ u.email }}</td>
                        <td class="px-6 py-4 text-slate-600 font-mono">{{ u.kontak || '-' }}</td>
                        <td class="px-6 py-4 text-center">
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold tracking-tight"
                                :class="u.is_active !== false ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200'"
                            >
                                {{ u.is_active !== false ? 'Aktif' : 'Non-Aktif' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button @click="openEditModal(u)" class="btn-action-edit" title="Edit Admin Kanwil">
                                    <Edit2 :size="14" />
                                </button>
                                <button @click="openDeleteModal(u)" class="btn-action-delete" title="Hapus Admin Kanwil">
                                    <Trash2 :size="14" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="paginatedKanwilList.length === 0">
                        <td colspan="8" class="px-6 py-8 text-center text-slate-400">
                            Tidak ada data Administrator Kanwil ditemukan.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <div>
                Menampilkan <span class="font-bold text-slate-900 tabular-nums">{{ (currentPage - 1) * pageSize + 1 }}</span> - <span class="font-bold text-slate-900 tabular-nums">{{ Math.min(currentPage * pageSize, processedKanwilList.length) }}</span> dari <span class="font-bold text-slate-900 tabular-nums">{{ processedKanwilList.length }}</span> Admin Kanwil
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
                <span class="font-sans tabular-nums text-slate-700 px-2 font-bold">{{ currentPage }} / {{ totalKanwilPages }}</span>
                <Button
                    @click="currentPage++"
                    :disabled="currentPage >= totalKanwilPages"
                    variant="outline"
                    size="sm"
                    class="h-8 px-3 rounded-md border-slate-300 text-xs font-semibold gap-1"
                >
                    <span>Selanjutnya</span>
                    <ChevronRight :size="14" />
                </Button>
            </div>
        </div>

        <!-- MODAL DIALOG SHADCN CRUD ADMIN KANWIL -->
        <Dialog v-model:open="isDialogOpen">
            <DialogContent class="sm:max-w-[520px] bg-white rounded-lg p-6 sm:p-7 shadow-2xl space-y-0 font-sans border border-slate-100">
                
                <DialogHeader class="-mx-6 sm:-mx-7 px-6 sm:px-7 pb-3 border-b border-slate-100 space-y-1 text-left">
                    <DialogTitle class="text-base font-bold text-slate-900 flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-md bg-slate-100 text-slate-700 flex items-center justify-center border border-slate-200/80 shrink-0">
                            <ShieldCheck :size="17" />
                        </div>
                        <span>{{ editingKanwilUser ? 'Edit Administrator Kanwil' : 'Tambah User Admin Kanwil Baru' }}</span>
                    </DialogTitle>
                    <DialogDescription class="text-xs text-slate-500 leading-relaxed font-normal pt-0.5">
                        Isi rincian NIP, Pangkat/Golongan, dan identitas pimpinan/admin Kanwil.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitForm" class="space-y-4 pt-3 text-xs">
                    
                    <div>
                        <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">Nama Lengkap Administrator</Label>
                        <Input
                            v-model="kanwilForm.name"
                            placeholder="Nama Lengkap & Gelar Pejabat"
                            required
                            class="h-9 px-3.5 rounded-md border-slate-300 text-xs focus:ring-2 focus:ring-primary/40 shadow-2xs font-sans"
                        />
                    </div>

                    <div>
                        <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">Email Resmi Akun Login</Label>
                        <Input
                            type="email"
                            v-model="kanwilForm.email"
                            placeholder="nama@imigrasi.go.id"
                            required
                            class="h-9 px-3.5 rounded-md border-slate-300 text-xs font-mono shadow-2xs"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">NIP Resmi (Opsional)</Label>
                            <Input
                                v-model="kanwilForm.nip"
                                placeholder="1985xxxx"
                                class="h-9 px-3.5 rounded-md border-slate-300 text-xs font-mono shadow-2xs"
                            />
                        </div>
                        <div>
                            <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">Pangkat / Golongan (Opsional)</Label>
                            <Input
                                v-model="kanwilForm.golongan"
                                placeholder="Pembina / IV/a"
                                class="h-9 px-3.5 rounded-md border-slate-300 text-xs shadow-2xs font-sans"
                            />
                        </div>
                    </div>

                    <div>
                        <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">No. WhatsApp Operasional</Label>
                        <Input
                            v-model="kanwilForm.kontak"
                            placeholder="081234567890"
                            class="h-9 px-3.5 rounded-md border-slate-300 text-xs font-mono shadow-2xs"
                        />
                    </div>

                    <div>
                        <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">Password Login {{ editingKanwilUser ? '(Isi jika ubah)' : '' }}</Label>
                        <Input
                            type="password"
                            v-model="kanwilForm.password"
                            :required="!editingKanwilUser"
                            placeholder="******"
                            class="h-9 px-3.5 rounded-md border-slate-300 text-xs shadow-2xs font-sans"
                        />
                        <PasswordStrengthMeter :password="kanwilForm.password" />
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
                            Simpan Admin Kanwil
                        </Button>
                    </DialogFooter>
                </form>

            </DialogContent>
        </Dialog>

        <!-- CONFIRM DELETE MODAL -->
        <ConfirmDeleteModal
            v-model:open="isDeleteModalOpen"
            title="Hapus Akun Admin Kanwil"
            :item-name="kanwilUserToDelete?.name"
            description="Apakah Anda yakin ingin menghapus akun Administrator Kanwil ini? Akun tidak akan lagi memiliki hak akses Super-Admin."
            :loading="isDeleting"
            @confirm="executeDelete"
        />
    </div>
</template>
