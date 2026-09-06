<script setup lang="ts">
import { ref, computed } from 'vue';
import { Check, ChevronsUpDown, Search } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';

interface OptionItem {
    value: string;
    label: string;
    description?: string;
}

const props = withDefaults(defineProps<{
    modelValue?: string;
    options: OptionItem[];
    placeholder?: string;
    searchPlaceholder?: string;
    disabled?: boolean;
    class?: string;
}>(), {
    placeholder: 'Pilih item...',
    searchPlaceholder: 'Cari kata kunci...',
    disabled: false,
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const searchQuery = ref('');

const selectedLabel = computed(() => {
    const found = props.options.find(o => o.value === props.modelValue);
    return found ? found.label : '';
});

const filteredOptions = computed(() => {
    if (!searchQuery.value) return props.options;
    const q = searchQuery.value.toLowerCase();
    return props.options.filter(o =>
        o.label.toLowerCase().includes(q) ||
        (o.description && o.description.toLowerCase().includes(q))
    );
});

const handleSelect = (val: string) => {
    emit('update:modelValue', val);
    isOpen.value = false;
    searchQuery.value = '';
};
</script>

<template>
    <Popover v-model:open="isOpen">
        <PopoverTrigger as-child>
            <Button
                type="button"
                variant="outline"
                role="combobox"
                :aria-expanded="isOpen"
                :disabled="disabled"
                :class="cn('w-full justify-between font-semibold text-xs h-9 rounded-md border-slate-200/90 bg-white px-3 shadow-2xs hover:bg-slate-50 transition-all font-sans', !modelValue && 'text-slate-400 font-normal', props.class)"
            >
                <span class="truncate text-slate-800">{{ selectedLabel || placeholder }}</span>
                <ChevronsUpDown :size="14" class="ml-2 shrink-0 text-slate-400" />
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-[var(--reka-popper-anchor-width)] p-0 rounded-xl shadow-xl bg-white border border-slate-200 z-50">
            <!-- Search Input Header -->
            <div class="p-2 border-b border-slate-100 flex items-center gap-2 bg-slate-50/50">
                <Search :size="14" class="text-slate-400 ml-1.5 shrink-0" />
                <Input
                    type="text"
                    v-model="searchQuery"
                    :placeholder="searchPlaceholder"
                    class="h-8 text-xs border-none shadow-none focus-visible:ring-0 px-1 bg-transparent"
                />
            </div>

            <!-- Options Scroll List with Custom Thin Scrollbar -->
            <div class="max-h-56 overflow-y-auto p-1 text-xs custom-thin-scrollbar pr-1.5">
                <div v-if="filteredOptions.length === 0" class="py-3 px-2 text-center text-slate-400 italic">
                    Tidak ditemukan data
                </div>
                <button
                    v-for="opt in filteredOptions"
                    :key="opt.value"
                    type="button"
                    @click="handleSelect(opt.value)"
                    :class="[
                        'w-full text-left px-2.5 py-2 rounded-lg flex items-center justify-between transition-colors hover:bg-slate-100/80 cursor-pointer',
                        modelValue === opt.value ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-700'
                    ]"
                >
                    <div class="truncate">
                        <div class="font-semibold text-xs leading-snug">{{ opt.label }}</div>
                        <div v-if="opt.description" class="text-[10px] text-slate-400 font-mono mt-0.5">{{ opt.description }}</div>
                    </div>
                    <Check v-if="modelValue === opt.value" :size="14" class="text-blue-600 shrink-0 ml-1" />
                </button>
            </div>
        </PopoverContent>
    </Popover>
</template>
