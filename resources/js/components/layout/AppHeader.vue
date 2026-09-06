<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import {
    Search,
    Bell,
    CheckCheck,
    Info,
    AlertTriangle,
    AlertCircle,
    CheckCircle2,
    FilePlus2,
    FileText,
    History,
    Settings,
    BookOpen,
    ArrowRight,
    Command,
    X,
    LayoutDashboard,
    FileCheck2,
    FolderKanban,
    Calendar,
    Building2,
    BarChart3,
    Map,
    Menu
} from 'lucide-vue-next';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

defineProps<{
    title?: string;
}>();

const emit = defineEmits<{
    (e: 'toggle-mobile-menu'): void;
}>();

const page = usePage<any>();
const unreadCount = computed(() => page.props.auth.user?.unreadNotificationsCount || 0);
const notifications = computed(() => page.props.auth.user?.recentNotifications || []);
const userRole = computed(() => page.props.auth.user?.role || 'desa');

// Command Palette State
const isCommandOpen = ref(false);
const searchQuery = ref('');

// Item Aksi Per Role dengan Tema Warna Primary Konsisten
const quickActions = computed(() => {
    const role = userRole.value;

    if (role === 'pimpasa') {
        return [
            { id: 'pimpasa-dashboard', label: 'Dashboard Overview', desc: 'Pantau matriks verifikasi, statistik insiden, dan peta wilayah UPT', url: '/pimpasa/dashboard', icon: LayoutDashboard, category: 'Navigasi Utama', color: 'bg-slate-100 text-slate-800 border-slate-200 group-hover:bg-slate-900 group-hover:text-white group-hover:border-slate-900' },
            { id: 'verifikasi-worklist', label: 'Worklist Verifikasi Laporan', desc: 'Periksa & verifikasi kelengkapan berkas laporan masuk dari Perangkat Desa', url: '/pimpasa/verifikasi', icon: FileCheck2, category: 'Verifikasi & Disposisi', color: 'bg-slate-100 text-slate-800 border-slate-200 group-hover:bg-slate-900 group-hover:text-white group-hover:border-slate-900' },
            { id: 'tindak-lanjut', label: 'Disposisi & Tindak Lanjut UPT', desc: 'Kelola penugasan seksi teknis UPT, bentuk intervensi & status akhir', url: '/pimpasa/tindak-lanjut', icon: FolderKanban, category: 'Verifikasi & Disposisi', color: 'bg-slate-100 text-slate-800 border-slate-200 group-hover:bg-slate-900 group-hover:text-white group-hover:border-slate-900' },
            { id: 'kegiatan-pembinaan', label: 'Kegiatan Pembinaan Desa', desc: 'Catat & kelola agenda penyuluhan TPPO, layanan simpatik & edukasi desa', url: '/pimpasa/kegiatan', icon: Calendar, category: 'Kegiatan & Wilayah', color: 'bg-slate-100 text-slate-800 border-slate-200 group-hover:bg-slate-900 group-hover:text-white group-hover:border-slate-900' },
            { id: 'desa-binaan', label: 'Direktori & Profil Desa Binaan', desc: 'Direktori desa pengampuan UPT, perangkat desa & indeks kerawanan', url: '/pimpasa/desa-binaan', icon: Building2, category: 'Kegiatan & Wilayah', color: 'bg-slate-100 text-slate-800 border-slate-200 group-hover:bg-slate-900 group-hover:text-white group-hover:border-slate-900' },
            { id: 'rekapitulasi-satker', label: 'Rekapitulasi Kinerja Satker UPT', desc: 'Rekapitulasi agregat insiden, resolution rate, & laporan kedinasan', url: '/pimpasa/rekapitulasi', icon: BarChart3, category: 'Kegiatan & Wilayah', color: 'bg-slate-100 text-slate-800 border-slate-200 group-hover:bg-slate-900 group-hover:text-white group-hover:border-slate-900' },
            { id: 'pimpasa-settings', label: 'Pengaturan Akun PIMPASA', desc: 'Kelola profil petugas PIMPASA, NIP, email & kata sandi', url: '/pimpasa/settings', icon: Settings, category: 'Pengaturan & Bantuan', color: 'bg-slate-100 text-slate-800 border-slate-200 group-hover:bg-slate-900 group-hover:text-white group-hover:border-slate-900' },
            { id: 'pimpasa-panduan', label: 'Buku Panduan PIMPASA', desc: 'Petunjuk operasional verifikasi & alur tindak lanjut UPT', url: '/pimpasa/panduan', icon: BookOpen, category: 'Pengaturan & Bantuan', color: 'bg-slate-100 text-slate-800 border-slate-200 group-hover:bg-slate-900 group-hover:text-white group-hover:border-slate-900' },
        ];
    } else if (role === 'kanwil') {
        return [
            { id: 'kanwil-monitoring', label: 'Executive Monitoring Kanwil', desc: 'Pantau grafik analitik, resolution rate, & rekapitulasi UPT se-Sumut', url: '/kanwil/monitoring', icon: BarChart3, category: 'Navigasi Utama', color: 'bg-slate-100 text-slate-800 border-slate-200 group-hover:bg-slate-900 group-hover:text-white group-hover:border-slate-900' },
            { id: 'kanwil-master-data', label: 'Master Data System', desc: 'Kelola master desa binaan, petugas PIMPASA, UPT, & matriks hak akses', url: '/kanwil/master-data', icon: Building2, category: 'Manajemen Data', color: 'bg-slate-100 text-slate-800 border-slate-200 group-hover:bg-slate-900 group-hover:text-white group-hover:border-slate-900' },
            { id: 'kanwil-peta', label: 'Peta Spasial Keimigrasian', desc: 'Visualisasi pemetaan spasial sebaran desa binaan & zona kerawanan', url: '/kanwil/peta', icon: Map, category: 'Navigasi Utama', color: 'bg-slate-100 text-slate-800 border-slate-200 group-hover:bg-slate-900 group-hover:text-white group-hover:border-slate-900' },
            { id: 'kanwil-settings', label: 'Pengaturan System Kanwil', desc: 'Kelola profil pimpinan & konfigurasi global sistem', url: '/kanwil/settings', icon: Settings, category: 'Pengaturan & Bantuan', color: 'bg-slate-100 text-slate-800 border-slate-200 group-hover:bg-slate-900 group-hover:text-white group-hover:border-slate-900' },
        ];
    } else {
        return [
            { id: 'desa-dashboard', label: 'Dashboard Utama Desa', desc: 'Ringkasan laporan desa, status terkini & statistik insiden', url: '/desa/dashboard', icon: LayoutDashboard, category: 'Navigasi Utama', color: 'bg-slate-100 text-slate-800 border-slate-200 group-hover:bg-slate-900 group-hover:text-white group-hover:border-slate-900' },
            { id: 'create-laporan', label: 'Buat Laporan Baru', desc: 'Pengajuan laporan isu keimigrasian & ketenagakerjaan baru', url: '/desa/laporan/create', icon: FilePlus2, category: 'Aksi Laporan', color: 'bg-slate-100 text-slate-800 border-slate-200 group-hover:bg-slate-900 group-hover:text-white group-hover:border-slate-900' },
            { id: 'list-laporan', label: 'Daftar Laporan Desa', desc: 'Lihat seluruh status tiket laporan desa binaan', url: '/desa/laporan', icon: FileText, category: 'Aksi Laporan', color: 'bg-slate-100 text-slate-800 border-slate-200 group-hover:bg-slate-900 group-hover:text-white group-hover:border-slate-900' },
            { id: 'audit-trail', label: 'Status & Riwayat Tiket', desc: 'Jejak audit trail perubahan status & verifikasi tiket', url: '/desa/riwayat', icon: History, category: 'Aksi Laporan', color: 'bg-slate-100 text-slate-800 border-slate-200 group-hover:bg-slate-900 group-hover:text-white group-hover:border-slate-900' },
            { id: 'settings', label: 'Pengaturan Akun Desa', desc: 'Kelola profil perangkat desa, email & password', url: '/desa/settings', icon: Settings, category: 'Pengaturan & Bantuan', color: 'bg-slate-100 text-slate-800 border-slate-200 group-hover:bg-slate-900 group-hover:text-white group-hover:border-slate-900' },
            { id: 'panduan', label: 'Buku Panduan SIMPEL DBI', desc: 'Petunjuk lengkap penggunaan sistem & panduan operasional', url: '/desa/panduan', icon: BookOpen, category: 'Pengaturan & Bantuan', color: 'bg-slate-100 text-slate-800 border-slate-200 group-hover:bg-slate-900 group-hover:text-white group-hover:border-slate-900' },
        ];
    }
});

const searchPlaceholder = computed(() => {
    switch (userRole.value) {
        case 'pimpasa': return 'Ketik perintah atau cari menu PIMPASA (verifikasi, kegiatan, rekap, dll)...';
        case 'kanwil': return 'Ketik perintah atau cari menu Kanwil (monitoring, master data, peta, dll)...';
        default: return 'Ketik perintah atau cari menu Desa (laporan, riwayat, panduan, dll)...';
    }
});

const emptySearchHint = computed(() => {
    switch (userRole.value) {
        case 'pimpasa': return "Coba ketik 'verifikasi', 'kegiatan', atau 'rekapitulasi'.";
        case 'kanwil': return "Coba ketik 'monitoring', 'master data', atau 'peta'.";
        default: return "Coba ketik 'laporan', 'buat', atau 'panduan'.";
    }
});

const filteredActions = computed(() => {
    if (!searchQuery.value.trim()) return quickActions.value;
    const q = searchQuery.value.toLowerCase();
    return quickActions.value.filter(act =>
        act.label.toLowerCase().includes(q) ||
        act.desc.toLowerCase().includes(q) ||
        act.category.toLowerCase().includes(q)
    );
});

const navigateTo = (url: string) => {
    isCommandOpen.value = false;
    searchQuery.value = '';
    router.visit(url);
};

// Global Keyboard Shortcut (Ctrl+K / Cmd+K)
const handleKeyDown = (e: KeyboardEvent) => {
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        isCommandOpen.value = !isCommandOpen.value;
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
});

// Helper Format Waktu Tanggal Standar Indonesia
const formatTime = (dateStr: string) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    const dateFormatted = d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
    const timeFormatted = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace('.', ':');
    return `${dateFormatted}, ${timeFormatted} WIB`;
};

// Handle Mark Single Notification as Read & Redirect
const handleNotificationClick = (item: any) => {
    router.post(`/notifications/${item.id}/read`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            if (item.url && item.url !== '#') {
                router.visit(item.url);
            }
        },
    });
};

// Handle Mark All as Read
const handleMarkAllRead = () => {
    router.post('/notifications/read-all', {}, {
        preserveScroll: true,
    });
};

// Icon & Color Helper berdasarkan tipe notifikasi
const getNotificationTheme = (type: string) => {
    switch (type) {
        case 'warning':
            return { icon: AlertTriangle, bg: 'bg-amber-50 text-amber-600 border-amber-200/90' };
        case 'success':
            return { icon: CheckCircle2, bg: 'bg-emerald-50 text-emerald-600 border-emerald-200/90' };
        case 'error':
            return { icon: AlertCircle, bg: 'bg-red-50 text-red-600 border-red-200/90' };
        case 'purple':
            return { icon: Info, bg: 'bg-purple-50 text-purple-600 border-purple-200/90' };
        default:
            return { icon: Info, bg: 'bg-blue-50 text-blue-600 border-blue-200/90' };
    }
};
</script>

<template>
    <!-- AppHeader Minimalis Modern Presisi DESIGN.md -->
    <header class="h-16 bg-white border-b border-slate-200/80 px-6 md:px-8 flex items-center justify-between sticky top-0 z-20 font-sans">
        
        <!-- Left: Pure Menu Title & Hamburger Menu for Mobile -->
        <div class="flex items-center gap-3">
            <!-- Mobile Hamburger Toggle Button (< lg) -->
            <button
                @click="emit('toggle-mobile-menu')"
                type="button"
                class="lg:hidden p-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer"
                title="Buka Navigasi System"
            >
                <Menu :size="18" />
            </button>

            <h1 class="text-sm md:text-base font-bold text-slate-900 leading-none">
                {{ title ? title.split('—')[0].trim() : 'Dashboard' }}
            </h1>
        </div>

        <!-- Right: Search Command Trigger & Notification Bell Group -->
        <div class="flex items-center gap-2.5">
            
            <!-- Command Palette Trigger Button (Desktop View - Styled with System Primary Navy Color) -->
            <button
                @click="isCommandOpen = true"
                type="button"
                class="hidden sm:flex items-center gap-2.5 h-9 px-3.5 bg-slate-50 hover:bg-slate-100/90 border border-slate-200 hover:border-slate-300 rounded-xl text-xs transition-all shadow-2xs group cursor-pointer"
            >
                <Search :size="15" class="text-slate-700 group-hover:text-slate-900 transition-colors" />
                <span class="font-bold text-slate-800 group-hover:text-slate-950 transition-colors">Cari menu & perintah...</span>
                <kbd class="ml-2 font-mono text-[10px] bg-white border border-slate-200/90 px-1.5 py-0.5 rounded-md font-bold text-slate-700 shadow-2xs">
                    Ctrl K
                </kbd>
            </button>

            <!-- Command Palette Trigger Button (Mobile View Icon Only) -->
            <button
                @click="isCommandOpen = true"
                type="button"
                class="flex sm:hidden w-9 h-9 rounded-xl border border-slate-200/80 items-center justify-center text-slate-800 hover:bg-slate-100 transition-colors shadow-2xs"
            >
                <Search :size="18" />
            </button>

            <!-- Notification Bell Popover -->
            <Popover>
                <PopoverTrigger as-child>
                    <button class="relative w-9 h-9 rounded-xl border border-slate-200/80 flex items-center justify-center text-slate-700 hover:bg-slate-100 transition-colors shadow-2xs cursor-pointer">
                        <Bell :size="18" />
                        <!-- Red Dot Unread Badge -->
                        <span v-if="unreadCount > 0" class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 bg-red-600 text-white font-mono text-[10px] font-bold rounded-full flex items-center justify-center border-2 border-white shadow-2xs">
                            {{ unreadCount > 9 ? '9+' : unreadCount }}
                        </span>
                    </button>
                </PopoverTrigger>

                <PopoverContent class="w-80 sm:w-96 p-0 rounded-2xl border-slate-200/90 shadow-xl bg-white overflow-hidden" align="end">
                    
                    <!-- Popover Header -->
                    <div class="p-3.5 sm:p-4 border-b border-slate-100 flex items-center justify-between gap-2 bg-slate-50/70">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="font-bold text-xs text-slate-900 shrink-0">Notifikasi Inbox</span>
                            <span v-if="unreadCount > 0" class="inline-flex items-center px-2 py-0.5 rounded-full bg-red-50 border border-red-200/80 text-red-600 font-mono text-[10px] font-bold shrink-0 whitespace-nowrap">
                                {{ unreadCount }} Baru
                            </span>
                        </div>

                        <button
                            v-if="unreadCount > 0"
                            @click="handleMarkAllRead"
                            class="text-[11px] font-semibold text-primary hover:underline flex items-center gap-1 transition-colors cursor-pointer shrink-0 leading-tight"
                        >
                            <CheckCheck :size="14" class="shrink-0" />
                            <span class="whitespace-nowrap">Tandai Dibaca</span>
                        </button>
                    </div>

                    <!-- Popover Notification Item List -->
                    <div class="max-h-80 overflow-y-auto custom-thin-scrollbar divide-y divide-slate-100/80 text-xs">
                        
                        <div v-if="notifications.length === 0" class="p-8 text-center text-slate-400 space-y-2">
                            <Bell :size="24" class="mx-auto text-slate-300" />
                            <p class="text-xs font-medium">Belum ada notifikasi baru.</p>
                        </div>

                        <div
                            v-for="item in notifications"
                            :key="item.id"
                            @click="handleNotificationClick(item)"
                            :class="[
                                'p-3.5 flex items-start gap-3 transition-colors cursor-pointer relative group',
                                !item.read_at ? 'bg-slate-50/70' : 'bg-white opacity-70 hover:bg-slate-50'
                            ]"
                        >
                            <div :class="['w-8 h-8 rounded-lg border flex items-center justify-center shrink-0 shadow-2xs', getNotificationTheme(item.type).bg]">
                                <component :is="getNotificationTheme(item.type).icon" :size="16" />
                            </div>

                            <div class="space-y-0.5 min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <h5 class="font-bold text-xs text-slate-900 truncate">
                                        {{ item.title }}
                                    </h5>

                                    <div class="flex items-center gap-2 shrink-0">
                                        <span class="text-[10px] font-mono text-slate-400">
                                            {{ formatTime(item.created_at) }}
                                        </span>
                                        <span v-if="!item.read_at" class="w-2 h-2 rounded-full bg-red-500 shadow-2xs shrink-0"></span>
                                    </div>
                                </div>
                                <p class="text-[11px] text-slate-600 leading-snug line-clamp-2 font-normal">
                                    {{ item.message }}
                                </p>
                            </div>
                        </div>

                    </div>

                    <!-- Popover Footer -->
                    <div class="p-2.5 border-t border-slate-100 bg-slate-50/70 text-center">
                        <span class="text-[10px] font-medium text-slate-400">
                            Pemberitahuan Sistem SIMPEL DBI
                        </span>
                    </div>

                </PopoverContent>
            </Popover>

        </div>

    </header>

    <!-- CLEAN & MODERN COMMAND PALETTE MODAL DIALOG -->
    <Dialog v-model:open="isCommandOpen">
        <DialogContent class="p-0 sm:max-w-xl rounded-2xl overflow-hidden border-slate-200/90 shadow-2xl bg-white gap-0 [&>button]:hidden">
            
            <DialogHeader class="sr-only">
                <DialogTitle>Command Palette Pencarian Menu SIMPEL DBI</DialogTitle>
            </DialogHeader>

            <!-- Search Input Box Header (Dengan Tombol X Tutup Bersih) -->
            <div class="p-4 border-b border-slate-100 flex items-center gap-3 bg-white">
                <Search :size="18" class="text-slate-800 shrink-0" />
                <input
                    v-model="searchQuery"
                    type="text"
                    :placeholder="searchPlaceholder"
                    class="w-full text-sm font-semibold text-slate-900 placeholder-slate-400 bg-transparent focus:outline-none"
                    autoFocus
                />
                <!-- Tombol Close (X) Bersih (Menggantikan ESC) -->
                <button
                    @click="isCommandOpen = false"
                    type="button"
                    title="Tutup Modal"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition-colors shrink-0 cursor-pointer"
                >
                    <X :size="18" />
                </button>
            </div>

            <!-- Command Action List (Ultra Thin Custom Scrollbar) -->
            <div class="max-h-80 overflow-y-auto custom-thin-scrollbar p-2 space-y-1 divide-y-0">
                
                <div v-if="filteredActions.length > 0">
                    <div
                        v-for="act in filteredActions"
                        :key="act.id"
                        @click="navigateTo(act.url)"
                        class="p-3 rounded-xl flex items-center justify-between gap-3 hover:bg-slate-100/80 transition-all cursor-pointer group"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- Icon Badge dengan Warna Primary Sistem -->
                            <div :class="['w-9 h-9 rounded-xl border flex items-center justify-center shrink-0 shadow-2xs transition-all', act.color]">
                                <component :is="act.icon" :size="18" />
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-xs font-bold text-slate-900 truncate group-hover:text-slate-950 transition-colors">
                                        {{ act.label }}
                                    </h4>
                                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200/60">
                                        {{ act.category }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 truncate pt-0.5 font-normal">
                                    {{ act.desc }}
                                </p>
                            </div>
                        </div>

                        <ArrowRight :size="15" class="text-slate-400 group-hover:text-slate-900 group-hover:translate-x-0.5 transition-all shrink-0" />
                    </div>
                </div>

                <!-- Empty Search Result -->
                <div v-else class="py-10 text-center text-slate-400 space-y-2">
                    <Command :size="28" class="mx-auto text-slate-400" />
                    <p class="text-xs font-medium text-slate-700">Tidak ada perintah yang sesuai dengan "{{ searchQuery }}"</p>
                    <p class="text-[11px] text-slate-400">{{ emptySearchHint }}</p>
                </div>

            </div>

            <!-- Footer Hints -->
            <div class="px-4 py-2.5 border-t border-slate-100 bg-slate-50/80 flex items-center justify-between text-[11px] text-slate-500 font-medium">
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1">
                        <kbd class="font-mono text-[9px] bg-white border border-slate-200 px-1 rounded shadow-2xs font-bold">↵</kbd> pilih
                    </span>
                    <span class="flex items-center gap-1">
                        <kbd class="font-mono text-[9px] bg-white border border-slate-200 px-1 rounded shadow-2xs font-bold">ESC</kbd> tutup
                    </span>
                </div>
                <div class="flex items-center gap-1 font-mono text-[10px] font-bold text-slate-600">
                    <Command :size="12" class="text-slate-500" /> SIMPEL DBI
                </div>
            </div>

        </DialogContent>
    </Dialog>
</template>


