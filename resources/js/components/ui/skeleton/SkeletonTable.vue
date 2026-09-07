<script setup lang="ts">
withDefaults(defineProps<{
    rows?: number;
    cols?: number;
}>(), {
    rows: 5,
    cols: 6
});
</script>

<template>
    <div class="w-full bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-2xs animate-pulse font-sans">
        <!-- Header Skeleton -->
        <div class="bg-slate-100/70 p-4 border-b border-slate-200/80 flex items-center justify-between gap-4">
            <div class="h-4 bg-slate-200/80 rounded-md w-1/4"></div>
            <div class="h-8 bg-slate-200/80 rounded-lg w-32"></div>
        </div>

        <!-- Rows Skeleton -->
        <div class="divide-y divide-slate-100 p-4 space-y-4">
            <div v-for="r in rows" :key="r" class="flex items-center justify-between gap-4 py-2">
                <div class="h-4 bg-slate-200/70 rounded w-12 shrink-0"></div>
                <div class="h-4 bg-slate-200/70 rounded w-1/3"></div>
                <div class="h-4 bg-slate-200/70 rounded w-1/5"></div>
                <div class="h-6 bg-slate-200/70 rounded-full w-24 shrink-0"></div>
                <div class="h-8 bg-slate-200/70 rounded-lg w-20 shrink-0"></div>
            </div>
        </div>
    </div>
</template>
