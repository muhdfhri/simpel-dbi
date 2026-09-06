<script setup lang="ts">
import { ref, computed } from 'vue';
import { Check, ChevronsUpDown, Search, X } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';

interface Option {
    value: string;
    label: string;
    description?: string;
}

interface Props {
    modelValue: string[]; // Array of selected values
    options: Option[];
    placeholder?: string;
    searchPlaceholder?: string;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: () => [],
    placeholder: 'Pilih beberapa item...',
    searchPlaceholder: 'Cari pilihan...',
});

const emit = defineEmits(['update:modelValue']);

const open = ref(false);
const searchQuery = ref('');

const filteredOptions = computed(() => {
    if (!searchQuery.value) return props.options;
    const q = searchQuery.value.toLowerCase();
    return props.options.filter(
        o => o.label.toLowerCase().includes(q) || (o.description && o.description.toLowerCase().includes(q))
    );
});

const isSelected = (val: string) => props.modelValue.includes(val);

const toggleOption = (val: string) => {
    const updated = isSelected(val)
        ? props.modelValue.filter(v => v !== val)
        : [...props.modelValue, val];
    emit('update:modelValue', updated);
};

const removeOption = (val: string, e: Event) => {
    e.stopPropagation();
    const updated = props.modelValue.filter(v => v !== val);
    emit('update:modelValue', updated);
};

const clearAll = (e: Event) => {
    e.stopPropagation();
    emit('update:modelValue', []);
};

const selectedLabels = computed(() => {
    return props.options.filter(o => props.modelValue.includes(o.value));
});
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <Button
                variant="outline"
                role="combobox"
                :aria-expanded="open"
                class="w-full min-h-[38px] h-auto py-1.5 px-3 justify-between bg-white border-slate-300 rounded-xl text-xs hover:bg-slate-50 transition-all font-sans"
            >
                <div class="flex flex-wrap items-center gap-1.5 max-w-[88%] text-left">
                    <template v-if="selectedLabels.length > 0">
                        <Badge
                            v-for="item in selectedLabels"
                            :key="item.value"
                            variant="secondary"
                            class="h-6 px-2 text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/80 rounded-lg flex items-center gap-1 shrink-0"
                        >
                            <span class="truncate max-w-[120px]">{{ item.label }}</span>
                            <X
                                :size="12"
                                class="cursor-pointer text-blue-500 hover:text-blue-900 transition-colors"
                                @click="(e) => removeOption(item.value, e)"
                            />
                        </Badge>
                    </template>
                    <span v-else class="text-slate-400 font-normal">
                        {{ placeholder }}
                    </span>
                </div>

                <div class="flex items-center gap-1 shrink-0 ml-1">
                    <X
                        v-if="selectedLabels.length > 0"
                        :size="14"
                        class="text-slate-400 hover:text-slate-700 transition-colors mr-0.5 cursor-pointer"
                        @click="clearAll"
                    />
                    <ChevronsUpDown :size="14" class="text-slate-400" />
                </div>
            </Button>
        </PopoverTrigger>

        <PopoverContent class="w-[320px] p-0 bg-white rounded-xl shadow-2xl border border-slate-200/90 z-50">
            <!-- Search Bar Input -->
            <div class="p-2 border-b border-slate-100 flex items-center gap-2">
                <Search :size="14" class="text-slate-400 shrink-0 ml-2" />
                <input
                    type="text"
                    v-model="searchQuery"
                    :placeholder="searchPlaceholder"
                    class="w-full text-xs outline-none bg-transparent py-1 text-slate-800 placeholder:text-slate-400"
                />
            </div>

            <!-- Options List -->
            <div class="max-h-60 overflow-y-auto p-1 divide-y divide-slate-50">
                <div v-if="filteredOptions.length === 0" class="py-6 text-center text-xs text-slate-400 italic">
                    Tidak ada item ditemukan.
                </div>

                <div
                    v-for="opt in filteredOptions"
                    :key="opt.value"
                    @click="toggleOption(opt.value)"
                    :class="[
                        'px-2.5 py-2 rounded-lg text-xs cursor-pointer flex items-center justify-between transition-colors font-sans',
                        isSelected(opt.value) ? 'bg-blue-50/80 text-blue-900 font-semibold' : 'text-slate-700 hover:bg-slate-100/70'
                    ]"
                >
                    <div class="space-y-0.5 pr-2">
                        <div class="leading-snug">{{ opt.label }}</div>
                        <div v-if="opt.description" class="text-[10px] text-slate-400 font-normal">
                            {{ opt.description }}
                        </div>
                    </div>

                    <div :class="[
                        'w-4 h-4 rounded-md border flex items-center justify-center shrink-0 transition-colors',
                        isSelected(opt.value) ? 'bg-blue-600 border-blue-600 text-white' : 'border-slate-300 bg-white'
                    ]">
                        <Check v-if="isSelected(opt.value)" :size="12" />
                    </div>
                </div>
            </div>

            <!-- Footer Toolbar Clear All -->
            <div v-if="modelValue.length > 0" class="p-2 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between text-[11px] text-slate-500">
                <span>Terpilih: <strong class="text-slate-900">{{ modelValue.length }}</strong> item</span>
                <button @click="emit('update:modelValue', [])" class="text-red-600 hover:underline font-bold">
                    Hapus Semua
                </button>
            </div>
        </PopoverContent>
    </Popover>
</template>
