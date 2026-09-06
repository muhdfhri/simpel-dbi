<script setup lang="ts">
import { ref, computed } from 'vue';
import { Activity, FileText, CheckCircle2, Clock, AlertTriangle, Building2, MapPin, Inbox, Filter } from 'lucide-vue-next';
import { Card, CardHeader, CardContent } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface FeedItem {
    id: number;
    nomor_tiket: string;
    judul: string;
    kategori: string;
    desa_nama: string;
    upt_nama: string;
    status: string;
    updated_at_relative: string;
}

interface UptOption {
    id: number;
    nama: string;
}

const props = defineProps<{
    feed: FeedItem[];
    uptOptions?: UptOption[];
}>();

// Selected UPT filter state for Live Feed
const selectedUptFilter = ref<string>('all');

// Computed list of UPT options (uses passed uptOptions or extracts unique UPT names from feed items)
const availableUptList = computed(() => {
    if (props.uptOptions && props.uptOptions.length > 0) {
        return props.uptOptions;
    }
    if (!props.feed) return [];
    const map = new Map<string, string>();
    props.feed.forEach(item => {
        if (item.upt_nama && !map.has(item.upt_nama)) {
            map.set(item.upt_nama, item.upt_nama);
        }
    });
    return Array.from(map.values()).map((nama, idx) => ({
        id: idx + 1,
        nama: nama
    }));
});

// Computed filtered activity feed
const filteredFeed = computed(() => {
    if (!props.feed) return [];
    if (selectedUptFilter.value === 'all') return props.feed;
    
    const targetUptObj = availableUptList.value.find(u => u.id.toString() === selectedUptFilter.value || u.nama === selectedUptFilter.value);
    const targetUptName = targetUptObj ? targetUptObj.nama.toLowerCase() : selectedUptFilter.value.toLowerCase();

    return props.feed.filter(item => {
        return item.upt_nama.toLowerCase().includes(targetUptName);
    });
});

const getStatusBadgeClass = (status: string) => {
    switch (status?.toLowerCase()) {
        case 'diajukan':
        case 'baru':
            return 'bg-sky-50 text-sky-700 border-sky-200/80';
        case 'diverifikasi':
        case 'proses':
            return 'bg-amber-50 text-amber-700 border-amber-200/80';
        case 'ditindaklanjuti':
        case 'selesai':
            return 'bg-emerald-50 text-emerald-700 border-emerald-200/80';
        case 'ditolak':
            return 'bg-rose-50 text-rose-700 border-rose-200/80';
        default:
            return 'bg-slate-100 text-slate-700 border-slate-200';
    }
};

const getNodeIconClass = (status: string) => {
    switch (status?.toLowerCase()) {
        case 'diajukan':
        case 'baru':
            return 'bg-sky-100 text-sky-700 border-sky-200';
        case 'diverifikasi':
        case 'proses':
            return 'bg-amber-100 text-amber-700 border-amber-200';
        case 'ditindaklanjuti':
        case 'selesai':
            return 'bg-emerald-100 text-emerald-700 border-emerald-200';
        case 'ditolak':
            return 'bg-rose-100 text-rose-700 border-rose-200';
        default:
            return 'bg-slate-100 text-slate-700 border-slate-200';
    }
};
</script>

<template>
    <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white overflow-hidden font-sans">
        <CardHeader class="py-3.5 px-5 border-b border-slate-100 flex flex-row items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-2">
                <Activity :size="16" class="text-slate-700 shrink-0" />
                <span class="text-xs font-bold text-slate-900">Live Feed Transaksi</span>
            </div>
            
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 flex items-center gap-1.5 shadow-2xs shrink-0">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Real-Time Stream
            </span>
        </CardHeader>

        <!-- Dedicated Filter Sub-Toolbar (Spacious & No Wrap Distortion) -->
        <div class="p-3 sm:p-3.5 border-b border-slate-100 bg-slate-50/30">
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Filter Satker UPT Imigrasi</label>
                <Select v-model="selectedUptFilter">
                    <SelectTrigger class="h-9 w-full text-xs px-3 border-slate-300 rounded-md bg-white shadow-2xs font-semibold text-slate-900 [&>span]:line-clamp-1 [&>span]:truncate [&>span]:text-left [&>span]:block [&>span]:w-full">
                        <SelectValue placeholder="Semua Satker UPT Imigrasi" />
                    </SelectTrigger>
                    <SelectContent class="bg-white rounded-md shadow-xl text-xs w-[340px] max-w-[90vw] max-h-[300px]">
                        <SelectGroup>
                            <SelectItem value="all" class="font-bold text-slate-900 py-2">Semua Satker UPT Imigrasi</SelectItem>
                            <SelectItem v-for="u in availableUptList" :key="u.id" :value="u.id.toString()" class="text-xs font-medium py-2 leading-snug">
                                {{ u.nama }}
                            </SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
            </div>
        </div>

        <CardContent class="p-5 font-sans">
            <div v-if="!filteredFeed || filteredFeed.length === 0" class="text-center py-8 text-xs text-slate-400 font-medium space-y-1">
                <Filter :size="18" class="mx-auto text-slate-300 mb-1" />
                <div>Belum ada transaksi real-time untuk Satker ini.</div>
                <button
                    v-if="selectedUptFilter !== 'all'"
                    @click="selectedUptFilter = 'all'"
                    class="text-primary font-semibold hover:underline text-[11px]"
                >
                    Reset Filter UPT
                </button>
            </div>

            <div v-else class="relative pl-6 space-y-5 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200/80">
                <div
                    v-for="item in filteredFeed"
                    :key="item.id"
                    class="relative flex flex-col gap-1 text-xs group"
                >
                    <!-- Timeline Node Icon -->
                    <div
                        :class="[
                            'absolute -left-[27px] top-0.5 w-6 h-6 rounded-full flex items-center justify-center border text-xs shadow-2xs transition-transform group-hover:scale-110',
                            getNodeIconClass(item.status)
                        ]"
                    >
                        <Inbox v-if="item.status === 'diajukan'" :size="12" />
                        <Clock v-else-if="item.status === 'diverifikasi'" :size="12" />
                        <CheckCircle2 v-else-if="item.status === 'ditindaklanjuti'" :size="12" />
                        <AlertTriangle v-else-if="item.status === 'ditolak'" :size="12" />
                        <FileText v-else :size="12" />
                    </div>

                    <!-- Header Row: Ticket Code, Status Badge, Timestamp -->
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-slate-900 font-bold text-xs">{{ item.nomor_tiket }}</span>
                            <span :class="['px-2 py-0.5 rounded-md text-[10px] font-bold uppercase border shadow-2xs', getStatusBadgeClass(item.status)]">
                                {{ item.status }}
                            </span>
                        </div>
                        <span class="text-[11px] text-slate-400 font-medium font-sans tabular-nums shrink-0 whitespace-nowrap">
                            {{ item.updated_at_relative }}
                        </span>
                    </div>

                    <!-- Judul Transaksi -->
                    <div class="font-bold text-slate-900 leading-snug text-xs">
                        {{ item.judul }}
                    </div>

                    <!-- Context Attributes (Desa & UPT) -->
                    <div class="text-[11px] text-slate-500 font-medium flex items-center gap-3 pt-0.5">
                        <span class="flex items-center gap-1">
                            <MapPin :size="11" class="text-slate-400 shrink-0" />
                            <strong class="text-slate-700 font-semibold">{{ item.desa_nama }}</strong>
                        </span>
                        <span class="text-slate-300">•</span>
                        <span class="flex items-center gap-1">
                            <Building2 :size="11" class="text-slate-400 shrink-0" />
                            <span>{{ item.upt_nama }}</span>
                        </span>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>

