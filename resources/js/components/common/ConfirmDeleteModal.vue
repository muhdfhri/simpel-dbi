<script setup lang="ts">
import { AlertTriangle, Loader2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

interface Props {
    open: boolean;
    title?: string;
    description?: string;
    itemName?: string;
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Konfirmasi Hapus Data',
    description: 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini permanen dan tidak dapat dibatalkan.',
    loading: false,
});

const emit = defineEmits(['update:open', 'confirm', 'cancel']);

const handleClose = () => {
    emit('update:open', false);
    emit('cancel');
};

const handleConfirm = () => {
    emit('confirm');
};
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="sm:max-w-[460px] bg-white rounded-3xl p-0 overflow-hidden shadow-2xl space-y-0 font-sans border-0">
            
            <!-- Header Section with Light Red Warning Icon -->
            <div class="p-6 sm:p-7 pb-5 flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 text-rose-500 flex items-center justify-center shrink-0 shadow-2xs">
                    <AlertTriangle :size="24" stroke-width="2.2" />
                </div>

                <div class="space-y-1.5 pt-0.5">
                    <DialogTitle class="text-base font-bold text-slate-900 leading-tight">
                        {{ title }}
                    </DialogTitle>
                    <DialogDescription class="text-xs text-slate-500 leading-relaxed font-normal">
                        <span v-if="itemName" class="block font-bold text-slate-900 my-0.5">
                            "{{ itemName }}"
                        </span>
                        {{ description }}
                    </DialogDescription>
                </div>
            </div>

            <!-- Modal Footer Actions with Soft Grey Background Bar -->
            <div class="bg-slate-50/70 p-5 px-6 flex items-center justify-end gap-3 border-t border-slate-100">
                <Button
                    type="button"
                    variant="outline"
                    :disabled="loading"
                    @click="handleClose"
                    class="h-10 px-6 rounded-full border-slate-200 bg-white text-slate-700 text-xs font-bold hover:bg-slate-100 transition-all shadow-2xs"
                >
                    Batal
                </Button>
                <Button
                    type="button"
                    :disabled="loading"
                    @click="handleConfirm"
                    class="h-10 px-6 rounded-full bg-red-600 hover:bg-red-700 text-white text-xs font-bold shadow-xs gap-2 transition-all cursor-pointer"
                >
                    <Loader2 v-if="loading" :size="14" class="animate-spin" />
                    <span>{{ loading ? 'Menghapus...' : 'Ya, Hapus Data' }}</span>
                </Button>
            </div>

        </DialogContent>
    </Dialog>
</template>
