<script setup lang="ts">
import { computed } from 'vue';
import { Check, Dot } from 'lucide-vue-next';

const props = defineProps<{
    password: string;
}>();

const hasMinLength = computed(() => props.password.length >= 8);
const hasUpperAndLower = computed(() => /[a-z]/.test(props.password) && /[A-Z]/.test(props.password));
const hasNumber = computed(() => /\d/.test(props.password));
const hasSymbol = computed(() => /[^a-zA-Z0-9]/.test(props.password));

const fulfilledCount = computed(() => {
    let count = 0;
    if (hasMinLength.value) count++;
    if (hasUpperAndLower.value) count++;
    if (hasNumber.value) count++;
    if (hasSymbol.value) count++;
    return count;
});

const isFullyValid = computed(() => fulfilledCount.value === 4);

const strengthText = computed(() => {
    if (!props.password) return 'Empty';
    if (fulfilledCount.value <= 1) return 'Sangat Lemah';
    if (fulfilledCount.value === 2) return 'Lemah';
    if (fulfilledCount.value === 3) return 'Sedang';
    return 'Kuat (Sesuai Standar)';
});

const strengthTextColor = computed(() => {
    if (!props.password) return 'text-slate-400';
    if (fulfilledCount.value <= 1) return 'text-rose-600';
    if (fulfilledCount.value === 2) return 'text-amber-600';
    if (fulfilledCount.value === 3) return 'text-blue-600';
    return 'text-emerald-600';
});

defineExpose({
    isFullyValid,
    fulfilledCount,
    hasMinLength,
    hasUpperAndLower,
    hasNumber,
    hasSymbol,
});
</script>

<template>
    <div class="space-y-3.5 mt-4 text-xs font-sans">
        <!-- 4-Segment Strength Bar -->
        <div class="space-y-2">
            <div class="grid grid-cols-4 gap-2 h-2">
                <div
                    class="rounded-full transition-all duration-300 h-full"
                    :class="[
                        fulfilledCount >= 1
                            ? (fulfilledCount === 1 ? 'bg-rose-500' : fulfilledCount === 2 ? 'bg-amber-500' : fulfilledCount === 3 ? 'bg-blue-500' : 'bg-emerald-500')
                            : 'bg-slate-200/80'
                    ]"
                ></div>
                <div
                    class="rounded-full transition-all duration-300 h-full"
                    :class="[
                        fulfilledCount >= 2
                            ? (fulfilledCount === 2 ? 'bg-amber-500' : fulfilledCount === 3 ? 'bg-blue-500' : 'bg-emerald-500')
                            : 'bg-slate-200/80'
                    ]"
                ></div>
                <div
                    class="rounded-full transition-all duration-300 h-full"
                    :class="[
                        fulfilledCount >= 3
                            ? (fulfilledCount === 3 ? 'bg-blue-500' : 'bg-emerald-500')
                            : 'bg-slate-200/80'
                    ]"
                ></div>
                <div
                    class="rounded-full transition-all duration-300 h-full"
                    :class="[
                        fulfilledCount >= 4 ? 'bg-emerald-500' : 'bg-slate-200/80'
                    ]"
                ></div>
            </div>
            <div class="flex items-center justify-between text-[11px] pt-0.5">
                <span class="text-slate-500 font-medium">Kekuatan Password:</span>
                <span :class="['font-bold tracking-tight', strengthTextColor]">{{ strengthText }}</span>
            </div>
        </div>

        <!-- Checklist Criteria -->
        <div class="space-y-1.5 bg-slate-50/80 p-3 rounded-lg border border-slate-200/80 text-[11px]">
            <!-- 8+ Karakter -->
            <div class="flex items-center gap-2 transition-colors" :class="hasMinLength ? 'text-emerald-700 font-medium' : 'text-slate-500'">
                <div class="w-4 h-4 rounded-full flex items-center justify-center shrink-0" :class="hasMinLength ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-200/80 text-slate-400'">
                    <Check v-if="hasMinLength" :size="11" class="stroke-[3]" />
                    <Dot v-else :size="16" />
                </div>
                <span>Minimal 8 karakter</span>
            </div>

            <!-- Upper and Lower Case -->
            <div class="flex items-center gap-2 transition-colors" :class="hasUpperAndLower ? 'text-emerald-700 font-medium' : 'text-slate-500'">
                <div class="w-4 h-4 rounded-full flex items-center justify-center shrink-0" :class="hasUpperAndLower ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-200/80 text-slate-400'">
                    <Check v-if="hasUpperAndLower" :size="11" class="stroke-[3]" />
                    <Dot v-else :size="16" />
                </div>
                <span>Kombinasi huruf besar dan kecil (A-Z, a-z)</span>
            </div>

            <!-- Number -->
            <div class="flex items-center gap-2 transition-colors" :class="hasNumber ? 'text-emerald-700 font-medium' : 'text-slate-500'">
                <div class="w-4 h-4 rounded-full flex items-center justify-center shrink-0" :class="hasNumber ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-200/80 text-slate-400'">
                    <Check v-if="hasNumber" :size="11" class="stroke-[3]" />
                    <Dot v-else :size="16" />
                </div>
                <span>Mengandung angka (0-9)</span>
            </div>

            <!-- Symbol -->
            <div class="flex items-center gap-2 transition-colors" :class="hasSymbol ? 'text-emerald-700 font-medium' : 'text-slate-500'">
                <div class="w-4 h-4 rounded-full flex items-center justify-center shrink-0" :class="hasSymbol ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-200/80 text-slate-400'">
                    <Check v-if="hasSymbol" :size="11" class="stroke-[3]" />
                    <Dot v-else :size="16" />
                </div>
                <span>Mengandung karakter unik/simbol (@#$%...)</span>
            </div>
        </div>
    </div>
</template>
