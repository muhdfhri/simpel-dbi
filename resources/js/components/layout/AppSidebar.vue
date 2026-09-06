<script setup lang="ts">
import { computed, provide } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import type { PageProps, UserRole } from '@/types';
import {
    Home,
    FileText,
    CheckSquare,
    Building2,
    Map,
    ShieldAlert,
    FolderKanban,
    Settings,
    LogOut,
    Search,
    Plus,
    BarChart3,
    FileCheck2,
    BookOpen,
    LayoutDashboard,
    Award,
    Activity
} from 'lucide-vue-next';
import MultiLevelCollapsibleMenu from '@/components/ui/multi-level-collapsible-menu/MultiLevelCollapsibleMenu.vue';

const page = usePage<PageProps>();
const user = computed(() => page.props.auth.user);
const currentRole = computed<UserRole>(() => (user.value?.role as UserRole) || 'desa');

// Label role fungsional utama
const roleLabel = computed(() => {
    switch (currentRole.value) {
        case 'desa': 
            return 'Perangkat Desa Binaan';
        case 'pimpasa': 
            return 'Petugas PIMPASA / UPT';
        case 'kanwil': 
            return 'Kanwil Ditjenim Sumatera Utara';
        default: 
            return 'Pengguna System';
    }
});

// Nama UPT / Nama Desa Binaan spesifik
const locationLabel = computed(() => {
    switch (currentRole.value) {
        case 'desa': 
            return (user.value as any)?.desa_nama 
                ? (user.value as any).desa_nama
                : null;
        case 'pimpasa': 
            return (user.value as any)?.upt_nama 
                ? (user.value as any).upt_nama
                : null;
        default: 
            return null;
    }
});

// Navigation Data Items Structure for MultiLevelCollapsibleMenu
const navigationGroups = computed(() => {
    const role = currentRole.value;

    const essentials = [
        {
            id: 'home',
            name: 'Beranda / Overview',
            href: `/${role}/dashboard`,
            icon: Home,
        },
    ];

    const mainModules: any[] = [];

    if (role === 'desa') {
        mainModules.push(
            { id: 'laporan-create', name: 'Form Laporan Baru', href: '/desa/laporan/create', icon: Plus, permission: 'laporan.create' },
            { id: 'laporan-list', name: 'Daftar Laporan Desa', href: '/desa/laporan', icon: FileText },
            { id: 'riwayat', name: 'Status & Riwayat Tiket', href: '/desa/riwayat', icon: CheckSquare }
        );
    } else if (role === 'pimpasa') {
        mainModules.push(
            { id: 'verifikasi', name: 'Worklist Verifikasi', href: '/pimpasa/verifikasi', icon: CheckSquare, permission: 'laporan.verify' },
            { id: 'tindak-lanjut', name: 'Disposisi & Tindak Lanjut', href: '/pimpasa/tindak-lanjut', icon: FileCheck2, permission: 'laporan.followup' },
            { id: 'kegiatan', name: 'Kegiatan Pembinaan', href: '/pimpasa/kegiatan', icon: FolderKanban, permission: 'kegiatan.create' },
            { id: 'desa-binaan', name: 'Daftar Desa Binaan', href: '/pimpasa/desa-binaan', icon: Building2 },
            { id: 'rekapitulasi', name: 'Rekapitulasi Satker', href: '/pimpasa/rekapitulasi', icon: BarChart3 }
        );
    } else if (role === 'kanwil') {
        mainModules.push(
            { id: 'peta', name: 'Peta Sebaran Desa', href: '/kanwil/peta', icon: Map },
            
            // Executive Monitoring Group with Collapsible Items (Presisi Gambar 1)
            {
                id: 'monitoring',
                name: 'Executive Monitoring',
                icon: BarChart3,
                items: [
                    { id: 'sla-control', name: 'Kendali SLA & Eskalasi', href: '/kanwil/monitoring/sla-control', icon: ShieldAlert },
                    { id: 'upt-scorecard', name: 'Scorecard Kepatuhan UPT', href: '/kanwil/monitoring/upt-scorecard', icon: Award },
                    { id: 'kegiatan-pembinaan', name: 'Monitoring Pembinaan Desa', href: '/kanwil/monitoring/kegiatan-pembinaan', icon: FolderKanban },
                ]
            },

            { id: 'master-data', name: 'Master Data System', href: '/kanwil/master-data', icon: FolderKanban }
        );
    }

    return [
        { name: 'Essentials', items: essentials },
        { name: 'Modul Pelaporan', items: mainModules },
        {
            name: 'Support',
            items: [
                { id: 'manual-book', name: 'Manual Book', href: role === 'desa' ? '/desa/panduan' : (role === 'pimpasa' ? '/pimpasa/panduan' : '/kanwil/panduan'), icon: BookOpen },
                { id: 'settings', name: 'Pengaturan Akun', href: role === 'desa' ? '/desa/settings' : (role === 'pimpasa' ? '/pimpasa/settings' : '/kanwil/settings'), icon: Settings },
            ],
        },
    ];
});

// Provide all menu hrefs for exact matching vs prefix matching in NavMenuItem
const allMenuHrefs = computed(() => {
    const hrefs: string[] = [];
    const extractHrefs = (items: any[]) => {
        items.forEach(item => {
            if (item.href) hrefs.push(item.href.split('?')[0]);
            if (item.items) extractHrefs(item.items);
        });
    };
    navigationGroups.value.forEach(group => {
        if (group.items) extractHrefs(group.items);
    });
    return hrefs;
});

provide('allMenuHrefs', allMenuHrefs);
</script>

<template>
    <!-- AppSidebar Presisi Referensi Gambar 1 (Clean, Professional & Thin Scrollbar) -->
    <aside class="w-64 bg-white border-r border-slate-200/80 flex flex-col justify-between h-screen sticky top-0 z-30 select-none font-sans">
        
        <!-- TOP SECTION: User Profile Box & Search -->
        <div class="p-3.5 space-y-3.5 flex-1 flex flex-col min-h-0">
            
            <!-- User Info Pill Card -->
            <div class="flex items-center justify-between p-2.5 rounded-xl border border-slate-200/70 bg-slate-50/60 shadow-2xs shrink-0">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-slate-900 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                        {{ user?.name?.charAt(0) || 'U' }}
                    </div>
                    <div class="min-w-0">
                        <h4 class="text-xs font-bold text-slate-900 truncate leading-tight">
                            {{ user?.name || 'User SIMPEL DBI' }}
                        </h4>
                        <p class="text-[10px] font-medium text-slate-600 truncate leading-tight mt-0.5">
                            {{ roleLabel }}
                        </p>
                        <p v-if="locationLabel" class="text-[10px] text-slate-400 truncate leading-tight mt-0.5" :title="locationLabel">
                            {{ locationLabel }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- NAVIGATION GROUPS (Standar Industri Modern UX: Clean, Breathable, & Professional Spacing) -->
            <nav class="space-y-6 pt-2 overflow-y-auto flex-1 pr-1 custom-thin-scrollbar">
                <div v-for="group in navigationGroups" :key="group.name">
                    <!-- Group Name Header -->
                    <div class="px-3 mb-2 mt-1 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        <span>{{ group.name }}</span>
                    </div>

                    <!-- MultiLevelCollapsibleMenu -->
                    <MultiLevelCollapsibleMenu :items="group.items" />
                </div>
            </nav>
        </div>

        <!-- BOTTOM SECTION: Quick Logout -->
        <div class="p-3.5 border-t border-slate-100 bg-slate-50/30 shrink-0">
            <Link
                href="/logout"
                method="post"
                as="button"
                class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium text-slate-600 hover:text-red-600 hover:bg-red-50 transition-colors group cursor-pointer"
            >
                <div class="flex items-center gap-2">
                    <LogOut :size="15" class="rotate-180 text-slate-400 group-hover:text-red-600 transition-colors" />
                    <span>Keluar Sistem</span>
                </div>
            </Link>
        </div>

    </aside>
</template>
