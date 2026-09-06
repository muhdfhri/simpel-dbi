<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { ShieldCheck, Lock, Home, Users, CheckCircle2, RefreshCw, AlertTriangle } from 'lucide-vue-next';
import { notify } from '@/lib/toast';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

interface MatrixItem {
    key: string;
    label: string;
    desc: string;
    category: string;
    enabled: boolean;
}

interface RoleMatrix {
    role: 'desa' | 'pimpasa';
    role_label: string;
    role_desc: string;
    items: MatrixItem[];
}

const props = defineProps<{
    permissionsMatrix: {
        desa: RoleMatrix;
        pimpasa: RoleMatrix;
    };
}>();

const activeRoleTab = ref<'desa' | 'pimpasa'>('desa');
const isSubmitting = ref(false);

const currentRoleData = computed(() => {
    return activeRoleTab.value === 'desa'
        ? props.permissionsMatrix.desa
        : props.permissionsMatrix.pimpasa;
});

// --- CONFIRM MODAL DIALOG STATE ---
const isConfirmOpen = ref(false);
const pendingToggle = ref<{
    role: 'pimpasa' | 'desa';
    roleLabel: string;
    item: MatrixItem;
    nextStatus: boolean;
} | null>(null);

const requestToggleConfirm = (role: 'pimpasa' | 'desa', item: MatrixItem) => {
    const roleLabel = role === 'pimpasa' ? 'Petugas PIMPASA' : 'Perangkat Desa';
    pendingToggle.value = {
        role,
        roleLabel,
        item,
        nextStatus: !item.enabled,
    };
    isConfirmOpen.value = true;
};

const executeToggle = () => {
    if (!pendingToggle.value) return;

    const { role, item, nextStatus } = pendingToggle.value;
    isSubmitting.value = true;

    router.post('/kanwil/master-data/permissions/toggle', {
        role,
        permission: item.key,
        enable: nextStatus,
    }, {
        preserveScroll: true,
        onFinish: () => {
            isSubmitting.value = false;
            isConfirmOpen.value = false;
            pendingToggle.value = null;
        },
        onError: () => {
            isSubmitting.value = false;
            notify.error('Gagal Memperbarui Hak Akses');
        }
    });
};
</script>

<template>
    <div class="space-y-6 font-sans p-4 sm:p-6">
        
        <!-- Header Guidance Banner -->
        <div class="p-4 bg-slate-50 border border-slate-200/80 rounded-lg flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-md bg-primary/10 text-primary flex items-center justify-center shrink-0 mt-0.5 sm:mt-0">
                    <ShieldCheck :size="20" />
                </div>
                <div class="space-y-1">
                    <h3 class="text-sm font-bold text-slate-900 flex flex-wrap items-center gap-2">
                        <span>Matriks Kontrol Hak Akses (Role Permission Matrix)</span>
                        <span class="shrink-0 whitespace-nowrap px-2.5 py-0.5 text-[10px] font-semibold bg-emerald-100 text-emerald-800 rounded-full border border-emerald-200">Real-Time Sync</span>
                    </h3>
                    <p class="text-xs text-slate-500 leading-relaxed max-w-3xl">
                        Atur toggle sakelar di bawah ini untuk memberi atau mencabut izin fitur & CRUD secara terpisah untuk Role <strong>Perangkat Desa Binaan</strong> dan <strong>Petugas PIMPASA</strong>.
                    </p>
                </div>
            </div>
        </div>

        <!-- Role Sub-Tab Switcher -->
        <div class="flex items-center gap-2 p-1 bg-slate-100 rounded-lg w-full sm:w-fit overflow-x-auto border border-slate-200/60">
            <button
                type="button"
                @click="activeRoleTab = 'desa'"
                :class="[
                    'px-4 py-2 rounded-md text-xs font-bold transition-all flex items-center gap-2.5 whitespace-nowrap shrink-0 cursor-pointer',
                    activeRoleTab === 'desa'
                        ? 'bg-white text-slate-900 shadow-2xs border border-slate-200/80'
                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50'
                ]"
            >
                <Home :size="15" :class="activeRoleTab === 'desa' ? 'text-primary' : 'text-slate-500'" />
                <span>Role Perangkat Desa Binaan</span>
                <span :class="['px-2 py-0.5 rounded-full text-[10px] font-mono font-bold border', activeRoleTab === 'desa' ? 'bg-slate-100 text-slate-800 border-slate-200' : 'bg-slate-200/70 text-slate-600 border-transparent']">
                    {{ permissionsMatrix.desa.items.length }} Fitur
                </span>
            </button>

            <button
                type="button"
                @click="activeRoleTab = 'pimpasa'"
                :class="[
                    'px-4 py-2 rounded-md text-xs font-bold transition-all flex items-center gap-2.5 whitespace-nowrap shrink-0 cursor-pointer',
                    activeRoleTab === 'pimpasa'
                        ? 'bg-white text-slate-900 shadow-2xs border border-slate-200/80'
                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50'
                ]"
            >
                <Users :size="15" :class="activeRoleTab === 'pimpasa' ? 'text-primary' : 'text-slate-500'" />
                <span>Role Petugas PIMPASA / UPT</span>
                <span :class="['px-2 py-0.5 rounded-full text-[10px] font-mono font-bold border', activeRoleTab === 'pimpasa' ? 'bg-slate-100 text-slate-800 border-slate-200' : 'bg-slate-200/70 text-slate-600 border-transparent']">
                    {{ permissionsMatrix.pimpasa.items.length }} Fitur
                </span>
            </button>
        </div>

        <!-- Dedicated Role Permission Card Container -->
        <div class="border border-slate-200/90 rounded-lg overflow-hidden bg-white shadow-2xs">
            
            <!-- Role Header Banner -->
            <div class="bg-slate-50/90 p-4 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <p class="text-xs text-slate-600 font-medium">
                        {{ currentRoleData.role_desc }}
                    </p>
                </div>

                <div class="text-xs font-medium text-slate-500 bg-white px-3 py-1.5 rounded-md border border-slate-200/80 shrink-0 w-fit">
                    Status: <span class="font-bold text-emerald-700 font-mono">{{ currentRoleData.items.filter(i => i.enabled).length }} / {{ currentRoleData.items.length }} Fitur Aktif</span>
                </div>
            </div>

            <!-- Permission Items List Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left text-slate-600 font-sans border-collapse">
                    <thead class="bg-slate-100/70 text-slate-700 font-semibold uppercase text-[10px] tracking-wider border-b border-slate-200/70">
                        <tr>
                            <th class="px-5 py-3 text-center w-12">No</th>
                            <th class="px-5 py-3">Nama Fitur</th>
                            <th class="px-5 py-3">Deskripsi Penggunaan & Dampak</th>
                            <th class="px-6 py-3 text-center w-56">Kontrol Sakelar (Toggle)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <tr v-for="(item, index) in currentRoleData.items" :key="item.key" class="hover:bg-slate-50/80 transition-colors">
                            
                            <!-- Index -->
                            <td class="px-5 py-4 text-center font-bold text-slate-900 font-mono">
                                {{ index + 1 }}
                            </td>

                            <!-- Label -->
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900 text-xs">{{ item.label }}</div>
                            </td>

                            <!-- Description -->
                            <td class="px-5 py-4 text-xs text-slate-500 font-normal leading-relaxed">
                                {{ item.desc }}
                            </td>

                            <!-- Interactive Toggle Switch (Trigger Confirmation Dialog) -->
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-3">
                                    <button
                                        type="button"
                                        @click="requestToggleConfirm(currentRoleData.role, item)"
                                        :class="[
                                            'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-hidden',
                                            item.enabled ? 'bg-emerald-600' : 'bg-slate-300'
                                        ]"
                                        :title="item.enabled ? 'Klik untuk mematikan hak akses fitur ini' : 'Klik untuk mengaktifkan hak akses fitur ini'"
                                    >
                                        <span
                                            :class="[
                                                'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out flex items-center justify-center text-[9px]',
                                                item.enabled ? 'translate-x-5' : 'translate-x-0'
                                            ]"
                                        />
                                    </button>

                                    <span
                                        :class="[
                                            'inline-flex items-center px-2.5 py-0.5 rounded text-[11px] font-semibold min-w-[90px] justify-center border',
                                            item.enabled ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-200'
                                        ]"
                                    >
                                        {{ item.enabled ? 'Izin Diberikan' : 'Akses Dikunci' }}
                                    </span>
                                </div>
                            </td>

                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

        <!-- MODAL DIALOG KONFIRMASI UBAH HAK AKSES -->
        <Dialog v-model:open="isConfirmOpen">
            <DialogContent class="sm:max-w-[460px] bg-white rounded-lg p-6 shadow-2xl space-y-0 font-sans border border-slate-100">
                
                <DialogHeader class="-mx-6 px-6 pb-3 border-b border-slate-100 space-y-1 text-left">
                    <DialogTitle class="text-base font-bold text-slate-900 flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-md bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-200 shrink-0">
                            <AlertTriangle :size="18" />
                        </div>
                        <span>Konfirmasi Ubah Hak Akses</span>
                    </DialogTitle>
                    <DialogDescription class="text-xs text-slate-500 leading-relaxed font-normal pt-0.5">
                        Perubahan hak akses ini akan langsung berdampak pada seluruh pengguna aktif role tersebut.
                    </DialogDescription>
                </DialogHeader>

                <div v-if="pendingToggle" class="py-4 space-y-3 text-xs text-slate-700 leading-relaxed">
                    <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-md space-y-1">
                        <div class="text-slate-500 text-[11px] font-medium">Fitur Target:</div>
                        <div class="font-bold text-slate-900 text-sm">{{ pendingToggle.item.label }}</div>
                        <div class="text-slate-500 text-[11px]">Role: <span class="font-semibold text-slate-800">{{ pendingToggle.roleLabel }}</span></div>
                    </div>

                    <p>
                        Apakah Anda yakin ingin 
                        <strong :class="pendingToggle.nextStatus ? 'text-emerald-700' : 'text-rose-700'">
                            {{ pendingToggle.nextStatus ? 'MENGAKTIFKAN' : 'MENONAKTIFKAN' }}
                        </strong> 
                        hak akses fitur ini untuk <strong>Role {{ pendingToggle.roleLabel }}</strong>?
                    </p>
                </div>

                <DialogFooter class="-mx-6 px-6 pt-4 mt-2 border-t border-slate-100 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 sm:gap-3 bg-transparent">
                    <Button
                        type="button"
                        variant="outline"
                        @click="isConfirmOpen = false"
                        :disabled="isSubmitting"
                        class="w-full sm:w-auto h-9 px-4 rounded-xl text-xs font-bold border-slate-300 text-slate-700 hover:bg-slate-50 transition-all cursor-pointer"
                    >
                        Batal
                    </Button>
                    <Button
                        type="button"
                        @click="executeToggle"
                        :disabled="isSubmitting"
                        :class="[
                            'w-full sm:w-auto h-9 px-4 text-white rounded-xl text-xs font-bold shadow-xs transition-all flex items-center justify-center gap-2 cursor-pointer',
                            pendingToggle?.nextStatus ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700'
                        ]"
                    >
                        <RefreshCw v-if="isSubmitting" :size="14" class="animate-spin" />
                        <span>{{ isSubmitting ? 'Memproses...' : (pendingToggle?.nextStatus ? 'Ya, Aktifkan Hak Akses' : 'Ya, Nonaktifkan Hak Akses') }}</span>
                    </Button>
                </DialogFooter>

            </DialogContent>
        </Dialog>

    </div>
</template>
