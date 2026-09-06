<script setup lang="ts">
import { computed } from 'vue';
import { ShieldAlert, HelpCircle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { notify } from '@/lib/toast';

const props = withDefaults(defineProps<{
    open?: boolean;
    title?: string;
    description?: string;
}>(), {
    open: true,
    title: 'Akses Fitur Sedang Dibatasi',
    description: 'Hak akses untuk fitur ini sedang dinonaktifkan sementara oleh Administrator Kanwil Ditjen Imigrasi Sumatera Utara.',
});

const emit = defineEmits(['update:open']);

const isOpen = computed({
    get: () => props.open,
    set: (val) => emit('update:open', val)
});

const handleHelp = () => {
    isOpen.value = false;
    notify.info('Pusat Bantuan Kanwil', {
        description: 'Silakan hubungi Administrator Kanwil via Helpdesk resmi Ditjen Imigrasi Sumatera Utara.'
    });
};
</script>

<template>
    <!-- MODAL DIALOG SHADCN 403 ACCESS RESTRICTED (CLEAN & MINIMAL) -->
    <Dialog v-model:open="isOpen">
        <DialogContent class="sm:max-w-[420px] bg-white rounded-lg p-6 shadow-xl font-sans border border-slate-100">
            
            <DialogHeader class="space-y-2 text-left">
                <DialogTitle class="text-base font-bold text-slate-900 flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-md bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-200 shrink-0">
                        <ShieldAlert :size="18" />
                    </div>
                    <span>{{ title }}</span>
                </DialogTitle>
                <DialogDescription class="text-xs text-slate-500 leading-relaxed font-normal pt-1">
                    {{ description }}
                </DialogDescription>
            </DialogHeader>

            <DialogFooter class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-end gap-2 bg-transparent">
                <Button
                    type="button"
                    variant="outline"
                    @click="isOpen = false"
                    class="h-8 px-3.5 rounded-md text-xs font-semibold border-slate-300 text-slate-700 hover:bg-slate-50 transition-all cursor-pointer"
                >
                    Tutup
                </Button>
                <Button
                    type="button"
                    @click="handleHelp"
                    class="h-8 px-3.5 bg-primary hover:bg-[#04407D] text-primary-foreground rounded-md text-xs font-semibold shadow-xs transition-all gap-1.5 cursor-pointer"
                >
                    <HelpCircle :size="13" />
                    <span>Hubungi Admin</span>
                </Button>
            </DialogFooter>

        </DialogContent>
    </Dialog>
</template>
