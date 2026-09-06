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
        <DialogContent class="sm:max-w-[460px] bg-white rounded-2xl p-6 sm:p-7 shadow-2xl space-y-4">
            
            <!-- Red Destructive Icon & Header -->
            <div class="flex items-start gap-4">
                <div class="w-11 h-11 rounded-2xl bg-red-50 border border-red-200/80 text-red-600 flex items-center justify-center shrink-0 shadow-2xs">
                    <AlertTriangle :size="22" />
                </div>

                <div class="space-y-1 pt-0.5">
                    <DialogTitle class="text-base font-bold text-slate-900 leading-snug">
                        {{ title }}
                    </DialogTitle>
                    <DialogDescription class="text-xs text-slate-600 leading-relaxed font-normal">
                        <span v-if="itemName" class="block font-bold text-slate-900 mb-1">
                            "{{ itemName }}"
                        </span>
                        {{ description }}
                    </DialogDescription>
                </div>
            </div>

            <!-- Modal Footer Actions -->
            <DialogFooter class="pt-3 border-t border-slate-100 flex flex-row items-center justify-end gap-2.5 sm:gap-3">
                <Button
                    type="button"
                    variant="outline"
                    :disabled="loading"
                    @click="handleClose"
                    class="h-9 px-4 rounded-xl border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-50"
                >
                    Batal
                </Button>
                <Button
                    type="button"
                    :disabled="loading"
                    @click="handleConfirm"
                    class="h-9 px-4 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold shadow-xs gap-1.5"
                >
                    <Loader2 v-if="loading" :size="14" class="animate-spin" />
                    <span>{{ loading ? 'Menghapus...' : 'Ya, Hapus Data' }}</span>
                </Button>
            </DialogFooter>

        </DialogContent>
    </Dialog>
</template>
