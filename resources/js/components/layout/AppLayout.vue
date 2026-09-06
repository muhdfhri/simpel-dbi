<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { Head, usePage } from '@inertiajs/vue3';
import AppSidebar from './AppSidebar.vue';
import AppHeader from './AppHeader.vue';
import Toaster from '@/components/ui/sonner/Sonner.vue';
import AccessRestrictedModal from '@/components/common/AccessRestrictedModal.vue';
import { isRestrictedModalOpen, restrictedModalData } from '@/lib/restrictedModal';
import { notify } from '@/lib/toast';

defineProps<{
    title?: string;
}>();

const page = usePage<any>();

// Mobile Drawer Navigation State (< 1024px)
const isMobileMenuOpen = ref(false);

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

// Simpan fungsi unregister dari router.on() agar listener bisa di-cleanup saat unmount
let removeNavigateListener: (() => void) | null = null;

onMounted(() => {
    removeNavigateListener = router.on('navigate', () => {
        // Otomatis tutup mobile menu saat terjadi navigasi halaman
        isMobileMenuOpen.value = false;

        const flash = page.props.flash as any;
        if (!flash) return;

        if (flash.success) {
            notify.success('Berhasil!', { description: flash.success });
        }
        if (flash.error) {
            notify.error('Gagal / Error!', { description: flash.error });
        }
        if (flash.warning) {
            notify.warning('Perhatian!', { description: flash.warning });
        }
        if (flash.info) {
            notify.info('Pemberitahuan Sistem', { description: flash.info });
        }
    });
});

onUnmounted(() => {
    removeNavigateListener?.();
});
</script>

<template>
    <Head :title="title" />

    <!-- Global Toaster Component (Shadcn Nova Style Toast) -->
    <Toaster />

    <!-- Global Access Restricted Modal Dialog (403 Floating Modal) -->
    <AccessRestrictedModal
        v-model:open="isRestrictedModalOpen"
        :title="restrictedModalData.title"
        :description="restrictedModalData.description"
        :featureName="restrictedModalData.featureName"
    />

    <div class="min-h-screen bg-slate-50/70 flex font-sans text-slate-900 antialiased selection:bg-slate-900 selection:text-white relative">
        
        <!-- Desktop AppSidebar (Hidden on mobile < lg, Fixed/Sticky on lg+) -->
        <div class="hidden lg:block shrink-0">
            <AppSidebar />
        </div>

        <!-- Mobile Drawer Backdrop & Overlay Sidebar (< lg) -->
        <div v-if="isMobileMenuOpen" class="fixed inset-0 z-50 lg:hidden flex">
            <!-- Dark Backdrop -->
            <div @click="isMobileMenuOpen = false" class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity"></div>

            <!-- Floating Drawer Sidebar -->
            <div class="relative z-10 w-64 max-w-[80vw] h-full bg-white shadow-2xl flex flex-col animate-in slide-in-from-left duration-200">
                <AppSidebar />
            </div>
        </div>

        <!-- Main Workspace Area -->
        <div class="flex-1 flex flex-col min-w-0 min-h-screen">
            
            <!-- Top Navigation Header with Hamburger Toggle for Mobile -->
            <AppHeader :title="title" @toggle-mobile-menu="toggleMobileMenu" />

            <!-- Content Area (Padding sejajar 100% dengan Top Header: px-4 sm:px-6 md:px-8 py-6) -->
            <main class="flex-1 px-4 sm:px-6 md:px-8 py-6 space-y-6 overflow-y-auto">
                <slot />
            </main>

        </div>

    </div>
</template>
