<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/components/layout/AppLayout.vue';
import {
    User,
    Building2,
    Lock,
    Bell,
    Building
} from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
        kontak: string | null;
        role: string;
        role_label: string;
        created_at?: string;
    };
    desaInfo?: {
        id: number;
        nama: string;
        wilayah?: {
            kecamatan: string;
            kabupaten: string;
            provinsi: string;
        };
        upt_pembina?: {
            nama: string;
            kode: string;
        };
        pimpasa_pembina?: {
            name: string;
            kontak: string;
            email: string;
        };
    } | null;
}>();

// Active Tab State
const activeTab = ref<'profil' | 'desa' | 'password' | 'notifications'>('profil');

// Form 1: Profil Pengguna
const profileForm = useForm({
    name: props.user.name,
    email: props.user.email,
    kontak: props.user.kontak || '',
});

const submitProfile = () => {
    profileForm.put('/desa/settings/profile', {
        preserveScroll: true,
    });
};

// Form 2: Keamanan / Password
const passwordForm = useForm({
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
});

const submitPassword = () => {
    passwordForm.put('/desa/settings/password', {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
        },
    });
};

// Form 3: Preferensi Notifikasi
const notifForm = useForm({
    notify_in_app: true,
    notify_whatsapp: true,
    notify_email: true,
});

const submitNotifications = () => {
    notifForm.put('/desa/settings/notifications', {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Pengaturan Akun">
        <div class="space-y-6 font-sans text-slate-800">
            
            <!-- HEADER PAGE CARD (CLEAN STYLE MATCHING INDEX PAGES) -->
            <div class="bg-white border border-slate-200 rounded-lg p-5 sm:p-6">
                <div class="space-y-1">
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                        <User class="w-5 h-5 text-slate-700 shrink-0" />
                        Pengaturan Akun - Perangkat Desa
                    </h1>
                    <p class="text-xs text-slate-500">
                        Kelola data profil pengguna, nomor WhatsApp operasional, keamanan kata sandi, dan preferensi notifikasi.
                    </p>
                </div>
            </div>

            <!-- TABS NAVIGATION BAR -->
            <div class="grid grid-cols-2 md:grid-cols-4 bg-slate-100 p-1 rounded-lg gap-1 border border-slate-200/60">
                <button
                    type="button"
                    @click="activeTab = 'profil'"
                    :class="[
                        'py-2 px-3 text-xs font-semibold rounded-md transition-colors flex items-center justify-center gap-2',
                        activeTab === 'profil'
                            ? 'bg-white text-slate-900 shadow-2xs font-bold'
                            : 'text-slate-600 hover:text-slate-900'
                    ]"
                >
                    <User class="w-3.5 h-3.5" />
                    <span>Profil Pengguna</span>
                </button>

                <button
                    type="button"
                    @click="activeTab = 'desa'"
                    :class="[
                        'py-2 px-3 text-xs font-semibold rounded-md transition-colors flex items-center justify-center gap-2',
                        activeTab === 'desa'
                            ? 'bg-white text-slate-900 shadow-2xs font-bold'
                            : 'text-slate-600 hover:text-slate-900'
                    ]"
                >
                    <Building2 class="w-3.5 h-3.5" />
                    <span>Informasi Desa</span>
                </button>

                <button
                    type="button"
                    @click="activeTab = 'password'"
                    :class="[
                        'py-2 px-3 text-xs font-semibold rounded-md transition-colors flex items-center justify-center gap-2',
                        activeTab === 'password'
                            ? 'bg-white text-slate-900 shadow-2xs font-bold'
                            : 'text-slate-600 hover:text-slate-900'
                    ]"
                >
                    <Lock class="w-3.5 h-3.5" />
                    <span>Keamanan & Password</span>
                </button>

                <button
                    type="button"
                    @click="activeTab = 'notifications'"
                    :class="[
                        'py-2 px-3 text-xs font-semibold rounded-md transition-colors flex items-center justify-center gap-2',
                        activeTab === 'notifications'
                            ? 'bg-white text-slate-900 shadow-2xs font-bold'
                            : 'text-slate-600 hover:text-slate-900'
                    ]"
                >
                    <Bell class="w-3.5 h-3.5" />
                    <span>Notifikasi</span>
                </button>
            </div>

            <!-- TAB CONTENT AREA -->
            <div class="space-y-6">

                <!-- TAB 1: PROFIL PENGGUNA -->
                <div v-show="activeTab === 'profil'" class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-2xs">
                    <div class="p-5 sm:p-6 border-b border-slate-100">
                        <h2 class="text-base font-bold text-slate-900 tracking-tight">
                            Profil & Informasi Kontak
                        </h2>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            Perbarui identitas diri dan nomor WhatsApp aktif untuk koordinasi langsung dengan Petugas Imigrasi.
                        </p>
                    </div>

                    <!-- PROPORSI GRID PADDING SIMETRIS PRESISI -->
                    <form @submit.prevent="submitProfile" class="p-5 sm:p-6 space-y-6">
                        <!-- Input 1: Nama Lengkap -->
                        <div class="space-y-2">
                            <Label for="name" class="text-xs font-bold text-slate-800 block leading-normal">
                                Nama Lengkap Perangkat Desa
                            </Label>
                            <Input
                                id="name"
                                v-model="profileForm.name"
                                type="text"
                                placeholder="Masukkan nama lengkap"
                                class="h-9.5 text-xs bg-white border-slate-200 text-slate-900 focus-visible:ring-slate-900 px-3.5 w-full"
                            />
                            <span v-if="profileForm.errors.name" class="text-[11px] text-red-600 block mt-1">
                                {{ profileForm.errors.name }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Input 2: Email Desa -->
                            <div class="space-y-2">
                                <Label for="email" class="text-xs font-bold text-slate-800 block leading-normal">
                                    Email Akun Desa
                                </Label>
                                <Input
                                    id="email"
                                    v-model="profileForm.email"
                                    type="email"
                                    placeholder="contoh@desa.go.id"
                                    class="h-9.5 text-xs bg-white border-slate-200 text-slate-900 focus-visible:ring-slate-900 px-3.5 w-full"
                                />
                                <span v-if="profileForm.errors.email" class="text-[11px] text-red-600 block mt-1">
                                    {{ profileForm.errors.email }}
                                </span>
                            </div>

                            <!-- Input 3: WhatsApp Operasional -->
                            <div class="space-y-2">
                                <Label for="kontak" class="text-xs font-bold text-slate-800 block leading-normal">
                                    Nomor WhatsApp Operasional (Aktif)
                                </Label>
                                <Input
                                    id="kontak"
                                    v-model="profileForm.kontak"
                                    type="text"
                                    placeholder="081234567890"
                                    class="h-9.5 text-xs bg-white border-slate-200 text-slate-900 focus-visible:ring-slate-900 px-3.5 w-full"
                                />
                                <p class="text-[11px] text-slate-500 leading-normal pt-0.5">
                                    Nomor WhatsApp ini digunakan untuk koordinasi penanganan laporan di desa.
                                </p>
                                <span v-if="profileForm.errors.kontak" class="text-[11px] text-red-600 block mt-1">
                                    {{ profileForm.errors.kontak }}
                                </span>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex justify-end">
                            <Button
                                type="submit"
                                size="sm"
                                :disabled="profileForm.processing"
                                class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold h-9.5 px-5"
                            >
                                {{ profileForm.processing ? 'Menyimpan...' : 'Simpan Perubahan Profil' }}
                            </Button>
                        </div>
                    </form>
                </div>

                <!-- TAB 2: INFORMASI DESA BINAAN (READ-ONLY CLEAN) -->
                <div v-show="activeTab === 'desa'" class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-2xs">
                    <div class="p-5 sm:p-6 border-b border-slate-100">
                        <h2 class="text-base font-bold text-slate-900 tracking-tight">
                            Informasi Desa Binaan & Kantor Imigrasi Pembina
                        </h2>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            Data wilayah desa bertugas dan rincian Kantor Imigrasi (UPT) Pembina.
                        </p>
                    </div>

                    <div class="p-5 sm:p-6 space-y-6">
                        <div class="space-y-3">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
                                <Building class="w-3.5 h-3.5" />
                                Data Wilayah & Satker Pembina
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="p-4 border border-slate-200 rounded-lg bg-slate-50/50 space-y-1">
                                    <span class="text-[11px] text-slate-500 block font-medium">Nama Desa Binaan</span>
                                    <span class="text-xs font-bold text-slate-900 block leading-snug">
                                        {{ desaInfo?.nama || 'Belum dihubungkan' }}
                                    </span>
                                </div>

                                <div class="p-4 border border-slate-200 rounded-lg bg-slate-50/50 space-y-1">
                                    <span class="text-[11px] text-slate-500 block font-medium">Provinsi Wilayah</span>
                                    <span class="text-xs font-bold text-slate-900 block leading-snug">
                                        {{ desaInfo?.wilayah?.provinsi || 'Sumatera Utara' }}
                                    </span>
                                </div>

                                <div class="p-4 border border-slate-200 rounded-lg bg-slate-50/50 space-y-1">
                                    <span class="text-[11px] text-slate-500 block font-medium">Kantor Imigrasi Pembina (UPT)</span>
                                    <span class="text-xs font-bold text-slate-900 block leading-snug">
                                        {{ desaInfo?.upt_pembina?.nama || 'Kantor Imigrasi Pembina' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: KEAMANAN & KATA SANDI -->
                <div v-show="activeTab === 'password'" class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-2xs">
                    <div class="p-5 sm:p-6 border-b border-slate-100">
                        <h2 class="text-base font-bold text-slate-900 tracking-tight">
                            Ubah Kata Sandi Akun
                        </h2>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            Ganti kata sandi secara berkala untuk menjaga kerahasiaan akses akun Perangkat Desa.
                        </p>
                    </div>

                    <!-- PROPORSI GRID PADDING SIMETRIS PRESISI -->
                    <form @submit.prevent="submitPassword" class="p-5 sm:p-6 space-y-6">
                        <!-- Input 1: Password Saat Ini -->
                        <div class="space-y-2">
                            <Label for="current_password" class="text-xs font-bold text-slate-800 block leading-normal">
                                Kata Sandi Saat Ini
                            </Label>
                            <Input
                                id="current_password"
                                v-model="passwordForm.current_password"
                                type="password"
                                placeholder="••••••••"
                                class="h-9.5 text-xs bg-white border-slate-200 text-slate-900 focus-visible:ring-slate-900 px-3.5 w-full"
                            />
                            <span v-if="passwordForm.errors.current_password" class="text-[11px] text-red-600 block mt-1">
                                {{ passwordForm.errors.current_password }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Input 2: Password Baru -->
                            <div class="space-y-2">
                                <Label for="new_password" class="text-xs font-bold text-slate-800 block leading-normal">
                                    Kata Sandi Baru (Minimal 8 Karakter)
                                </Label>
                                <Input
                                    id="new_password"
                                    v-model="passwordForm.new_password"
                                    type="password"
                                    placeholder="••••••••"
                                    class="h-9.5 text-xs bg-white border-slate-200 text-slate-900 focus-visible:ring-slate-900 px-3.5 w-full"
                                />
                                <span v-if="passwordForm.errors.new_password" class="text-[11px] text-red-600 block mt-1">
                                    {{ passwordForm.errors.new_password }}
                                </span>
                            </div>

                            <!-- Input 3: Konfirmasi Password Baru -->
                            <div class="space-y-2">
                                <Label for="new_password_confirmation" class="text-xs font-bold text-slate-800 block leading-normal">
                                    Ulangi Kata Sandi Baru
                                </Label>
                                <Input
                                    id="new_password_confirmation"
                                    v-model="passwordForm.new_password_confirmation"
                                    type="password"
                                    placeholder="••••••••"
                                    class="h-9.5 text-xs bg-white border-slate-200 text-slate-900 focus-visible:ring-slate-900 px-3.5 w-full"
                                />
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex justify-end">
                            <Button
                                type="submit"
                                size="sm"
                                :disabled="passwordForm.processing"
                                class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold h-9.5 px-5"
                            >
                                {{ passwordForm.processing ? 'Menyimpan...' : 'Perbarui Kata Sandi' }}
                            </Button>
                        </div>
                    </form>
                </div>

                <!-- TAB 4: PREFERENSI NOTIFIKASI -->
                <div v-show="activeTab === 'notifications'" class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-2xs">
                    <div class="p-5 sm:p-6 border-b border-slate-100">
                        <h2 class="text-base font-bold text-slate-900 tracking-tight">
                            Preferensi Kanal Notifikasi Tiket Laporan
                        </h2>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            Atur kanal notifikasi untuk menerima pemberitahuan saat terjadi pembaruan status laporan desa (Diajukan, Minta Perbaikan, Diverifikasi, Ditindaklanjuti UPT, Selesai, Ditolak).
                        </p>
                    </div>

                    <form @submit.prevent="submitNotifications" class="p-5 sm:p-6 space-y-6">
                        <div class="space-y-4">
                            <label class="flex items-start gap-3.5 p-4 border border-slate-200/80 rounded-lg bg-slate-50/50 hover:bg-slate-50 transition-colors cursor-pointer">
                                <input
                                    type="checkbox"
                                    v-model="notifForm.notify_in_app"
                                    class="mt-0.5 rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                                />
                                <div class="space-y-1 text-xs">
                                    <span class="font-bold text-slate-900 block">Notifikasi Inbox Sistem (In-App)</span>
                                    <p class="text-slate-500 leading-relaxed">Tampilkan lencana dan pesan pemberitahuan di menu lonceng aplikasi saat status tiket laporan berubah.</p>
                                </div>
                            </label>

                            <label class="flex items-start gap-3.5 p-4 border border-slate-200/80 rounded-lg bg-slate-50/50 hover:bg-slate-50 transition-colors cursor-pointer">
                                <input
                                    type="checkbox"
                                    v-model="notifForm.notify_whatsapp"
                                    class="mt-0.5 rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                                />
                                <div class="space-y-1 text-xs">
                                    <span class="font-bold text-slate-900 block">Push Notification Layar HP (ntfy)</span>
                                    <p class="text-slate-500 leading-relaxed">Kirimkan spanduk notifikasi push instan ke layar kunci perangkat seluler saat status laporan diperbarui oleh PIMPASA/UPT.</p>
                                </div>
                            </label>

                            <label class="flex items-start gap-3.5 p-4 border border-slate-200/80 rounded-lg bg-slate-50/50 hover:bg-slate-50 transition-colors cursor-pointer">
                                <input
                                    type="checkbox"
                                    v-model="notifForm.notify_email"
                                    class="mt-0.5 rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                                />
                                <div class="space-y-1 text-xs">
                                    <span class="font-bold text-slate-900 block">Salinan Surat Elektronik Resmi (Email SMTP)</span>
                                    <p class="text-slate-500 leading-relaxed">Kirimkan pemberitahuan resmi berperekat dari Kanwil Ditjenim Sumatera Utara ke alamat email akun desa.</p>
                                </div>
                            </label>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex justify-end">
                            <Button
                                type="submit"
                                size="sm"
                                :disabled="notifForm.processing"
                                class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold h-9.5 px-5"
                            >
                                Simpan Preferensi Notifikasi
                            </Button>
                        </div>
                    </form>
                </div>

            </div>

        </div>
    </AppLayout>
</template>
