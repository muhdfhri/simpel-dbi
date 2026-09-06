<script setup lang="ts">
import {
    Building2,
    FileText,
    TrendingUp,
    AlertTriangle,
    CheckCircle2
} from 'lucide-vue-next';
import { Card, CardContent } from '@/components/ui/card';

interface KpiProps {
    total_desa: number;
    total_laporan: number;
    laporan_proses: number;
    laporan_selesai: number;
    resolution_rate: number;
    sla_breached_count: number;
}

const props = defineProps<{
    kpi: KpiProps;
}>();
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 font-sans">
        
        <!-- Card 1: Total Desa Binaan -->
        <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-slate-300/80 transition-colors">
                <Building2 :size="105" stroke-width="1.0" />
            </div>

            <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                <div class="space-y-0.5 pr-12">
                    <span class="text-xs font-semibold text-slate-500 block leading-tight">Total Desa Binaan (DBI)</span>
                    <div class="text-3xl font-bold text-slate-900 font-mono tracking-tight leading-none pt-1">
                        {{ kpi.total_desa }}
                    </div>
                </div>
                <div class="pt-2">
                    <p class="text-[11px] text-slate-500 font-medium">Terdaftar se-Sumatera Utara</p>
                </div>
            </CardContent>
        </Card>

        <!-- Card 2: Total Laporan Masuk -->
        <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-slate-300/80 transition-colors">
                <FileText :size="105" stroke-width="1.0" />
            </div>

            <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                <div class="space-y-0.5 pr-12">
                    <span class="text-xs font-semibold text-slate-500 block leading-tight">Total Laporan Masuk</span>
                    <div class="text-3xl font-bold text-slate-900 font-mono tracking-tight leading-none pt-1">
                        {{ kpi.total_laporan }}
                    </div>
                </div>
                <div class="pt-2">
                    <p class="text-[11px] text-slate-500 font-medium">
                        Proses: <strong class="font-mono text-slate-900">{{ kpi.laporan_proses }}</strong> | Selesai: <strong class="font-mono text-slate-900">{{ kpi.laporan_selesai }}</strong>
                    </p>
                </div>
            </CardContent>
        </Card>

        <!-- Card 3: SLA Resolution Rate -->
        <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-slate-300/80 transition-colors">
                <TrendingUp :size="105" stroke-width="1.0" />
            </div>

            <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                <div class="space-y-0.5 pr-12">
                    <span class="text-xs font-semibold text-slate-500 block leading-tight">Rate Penyelesaian SLA</span>
                    <div class="text-3xl font-bold text-slate-900 font-mono tracking-tight leading-none pt-1">
                        {{ kpi.resolution_rate }}%
                    </div>
                </div>
                <div class="pt-2">
                    <p class="text-[11px] text-slate-500 font-medium flex items-center gap-1">
                        <CheckCircle2 :size="12" class="text-slate-500" /> Target SLA Terpenuhi
                    </p>
                </div>
            </CardContent>
        </Card>

        <!-- Card 4: Red Flag SLA Alerts -->
        <Card :class="['border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group', kpi.sla_breached_count > 0 ? 'border-red-200 bg-red-50/20' : '']">
            <div class="absolute -right-4 -bottom-6 text-red-200/60 pointer-events-none group-hover:text-red-300/80 transition-colors">
                <AlertTriangle :size="105" stroke-width="1.0" />
            </div>

            <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                <div class="space-y-0.5 pr-12">
                    <span class="text-xs font-bold text-red-700 block leading-tight">Red Flag SLA Alerts</span>
                    <div class="text-3xl font-bold text-red-700 font-mono tracking-tight leading-none pt-1">
                        {{ kpi.sla_breached_count }}
                    </div>
                </div>
                <div class="pt-2">
                    <p class="text-[11px] text-red-600 font-medium">Melewati SLA 2x24 Jam</p>
                </div>
            </CardContent>
        </Card>

    </div>
</template>
