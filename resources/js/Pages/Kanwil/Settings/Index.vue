<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { notify } from '@/lib/toast';
import PasswordStrengthMeter from '@/components/common/PasswordStrengthMeter.vue';
import AppLayout from '@/components/layout/AppLayout.vue';
import {
    User,
    Building2,
    Lock,
    Bell,
    Building,
    ShieldAlert,
    Users,
    FolderKanban
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
        nip: string | null;
        golongan: string | null;
        role: string;
        role_label: string;
        created_at?: string;
    };
    kanwilInfo?: {
        nama: string;
        provinsi: string;
        total_upt: number;
        total_desa: number;
        total_pimpasa: number;
    } | null;
}>();

// Active Tab State
const activeTab = ref<'profil' | 'wilayah' | 'password' | 'notifications'>('profil');

// Form 1: Profil Pengguna Executive Kanwil
const profileForm = useForm({
    name: props.user.name,
    email: props.user.email,
    kontak: props.user.kontak || '',
    nip: props.user.nip || '',
    golongan: props.user.golongan || '',
});

const submitProfile = () => {
    profileForm.put('/kanwil/settings/profile', {
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
    passwordForm.put('/kanwil/settings/password', {
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
    notifForm.put('/kanwil/settings/notifications', {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Pengaturan Akun Executive Kanwil">
        <div class="space-y-6 font-sans text-slate-800">
            
            <!-- HEADER PAGE CARD (MATCHING PIMPASA & DESA SETTINGS STYLE) -->
            <div class="bg-white border border-slate-200 rounded-lg p-5 sm:p-6">
                <div class="space-y-1">
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                        <User class="w-5 h-5 text-slate-700 shrink-0" />
                        Pengaturan Akun - Executive & Administrator Kanwil
                    </h1>
                    <p class="text-xs text-slate-500">
                        Kelola identitas pimpinan, kontak WhatsApp alert operasional, keamanan kata sandi, dan preferensi notifikasi pengawasan wilayah.
                    </p>
                </div>
            </div>

            <!-- TABS NAVIGATION BAR -->
            <div class="grid grid-cols-2 md:grid-cols-4 bg-slate-100 p-1 rounded-lg gap-1 border border-slate-200/60">
                <button
                    type="button"
                    @click="activeTab = 'profil'"
                    :class="[
                        'py-2 px-3 text-xs font-semibold rounded-md transition-colors flex items-center justify-center gap-2 cursor-pointer',
                        activeTab === 'profil'
                            ? 'bg-white text-slate-900 shadow-2xs font-bold'
                            : 'text-slate-600 hover:text-slate-900'
                    ]"
                >
                    <User class="w-3.5 h-3.5" />
                    <span>Profil Executive</span>
                </button>

                <button
                    type="button"
                    @click="activeTab = 'wilayah'"
                    :class="[
                        'py-2 px-3 text-xs font-semibold rounded-md transition-colors flex items-center justify-center gap-2 cursor-pointer',
                        activeTab === 'wilayah'
                            ? 'bg-white text-slate-900 shadow-2xs font-bold'
                            : 'text-slate-600 hover:text-slate-900'
                    ]"
                >
                    <Building2 class="w-3.5 h-3.5" />
                    <span>Struktur & Wilayah</span>
                </button>

                <button
                    type="button"
                    @click="activeTab = 'password'"
                    :class="[
                        'py-2 px-3 text-xs font-semibold rounded-md transition-colors flex items-center justify-center gap-2 cursor-pointer',
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
                        'py-2 px-3 text-xs font-semibold rounded-md transition-colors flex items-center justify-center gap-2 cursor-pointer',
                        activeTab === 'notifications'
                            ? 'bg-white text-slate-900 shadow-2xs font-bold'
                            : 'text-slate-600 hover:text-slate-900'
                    ]"
                >
                    <Bell class="w-3.5 h-3.5" />
                    <span>Notifikasi Monitoring</span>
                </button>
            </div>

            <!-- TAB CONTENT AREA -->
            <div class="space-y-6">

                <!-- TAB 1: PROFIL PENGGUNA KANWIL -->
                <div v-show="activeTab === 'profil'" class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-2xs">
                    <div class="p-5 sm:p-6 border-b border-slate-100">
                        <h2 class="text-base font-bold text-slate-900 tracking-tight">
                            Profil & Informasi Kontak Executive Kanwil
                        </h2>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            Perbarui identitas pejabat, email akun, dan nomor WhatsApp operasional untuk penerimaan alert eskalasi SLA UPT Imigrasi.
                        </p>
                    </div>

                    <form @submit.prevent="submitProfile" class="p-5 sm:p-6 space-y-6">
                        <!-- Input 1: Nama Lengkap -->
                        <div class="space-y-2">
                            <Label for="name" class="text-xs font-bold text-slate-800 block leading-normal">
                                Nama Lengkap Pimpinan / Admin Kanwil
                            </Label>
                            <Input
                                id="name"
                                v-model="profileForm.name"
                                type="text"
                                placeholder="Masukkan nama lengkap pimpinan"
                                class="h-9.5 text-xs bg-white border-slate-200 text-slate-900 focus-visible:ring-slate-900 px-3.5 w-full"
                            />
                            <span v-if="profileForm.errors.name" class="text-[11px] text-red-600 block mt-1">
                                {{ profileForm.errors.name }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Input 2: Email Resmi -->
                            <div class="space-y-2">
                                <Label for="email" class="text-xs font-bold text-slate-800 block leading-normal">
                                    Email Resmi Akun Kanwil
                                </Label>
                                <Input
                                    id="email"
                                    v-model="profileForm.email"
                                    type="email"
                                    placeholder="kanwil.sumut@imigrasi.go.id"
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
                                <span v-if="profileForm.errors.kontak" class="text-[11px] text-red-600 block mt-1">
                                    {{ profileForm.errors.kontak }}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Input 4: NIP -->
                            <div class="space-y-2">
                                <Label for="nip" class="text-xs font-bold text-slate-800 block leading-normal">
                                    NIP (Nomor Identitas Pegawai)
                                </Label>
                                <Input
                                    id="nip"
                                    v-model="profileForm.nip"
                                    type="text"
                                    placeholder="198501012010011002"
                                    class="h-9.5 text-xs bg-white border-slate-200 text-slate-900 focus-visible:ring-slate-900 px-3.5 w-full"
                                />
                            </div>

                            <!-- Input 5: Pangkat / Golongan -->
                            <div class="space-y-2">
                                <Label for="golongan" class="text-xs font-bold text-slate-800 block leading-normal">
                                    Pangkat / Golongan Fungsional
                                </Label>
                                <Input
                                    id="golongan"
                                    v-model="profileForm.golongan"
                                    type="text"
                                    placeholder="Pembina Utama Muda (IV/c)"
                                    class="h-9.5 text-xs bg-white border-slate-200 text-slate-900 focus-visible:ring-slate-900 px-3.5 w-full"
                                />
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex justify-end">
                            <Button
                                type="submit"
                                size="sm"
                                :disabled="profileForm.processing"
                                class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold h-9.5 px-5 cursor-pointer"
                            >
                                {{ profileForm.processing ? 'Menyimpan...' : 'Simpan Perubahan Profil' }}
                            </Button>
                        </div>
                    </form>
                </div>

                <!-- TAB 2: STRUKTUR & WILAYAH PENGAWASAN KANWIL -->
                <div v-show="activeTab === 'wilayah'" class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-2xs">
                    <div class="p-5 sm:p-6 border-b border-slate-100">
                        <h2 class="text-base font-bold text-slate-900 tracking-tight">
                            Struktur & Cakupan Wilayah Pengawasan Kanwil
                        </h2>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            Rincian wilayah pengampuan eksekutif Kantor Wilayah Ditjen Imigrasi Sumatera Utara.
                        </p>
                    </div>

                    <div class="p-5 sm:p-6 space-y-6">
                        <div class="space-y-3">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
                                <Building class="w-3.5 h-3.5" />
                                Data Wilayah & Pengampuan Sistem
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="p-4 border border-slate-200 rounded-lg bg-slate-50/50 space-y-1">
                                    <span class="text-[11px] text-slate-500 block font-medium">Nama Instansi</span>
                                    <span class="text-xs font-bold text-slate-900 block leading-snug">
                                        {{ kanwilInfo?.nama || 'Kantor Wilayah Ditjen Imigrasi Sumut' }}
                                    </span>
                                </div>

                                <div class="p-4 border border-slate-200 rounded-lg bg-slate-50/50 space-y-1">
                                    <span class="text-[11px] text-slate-500 block font-medium">Provinsi Pengawasan</span>
                                    <span class="text-xs font-bold text-slate-900 block leading-snug">
                                        {{ kanwilInfo?.provinsi || 'Sumatera Utara' }}
                                    </span>
                                </div>

                                <div class="p-4 border border-slate-200 rounded-lg bg-slate-50/50 space-y-1">
                                    <span class="text-[11px] text-slate-500 block font-medium">Total Satker UPT Imigrasi</span>
                                    <span class="text-xs font-bold text-slate-900 block leading-snug">
                                        {{ kanwilInfo?.total_upt || 0 }} Satker UPT
                                    </span>
                                </div>

                                <div class="p-4 border border-slate-200 rounded-lg bg-slate-50/50 space-y-1">
                                    <span class="text-[11px] text-slate-500 block font-medium">Total Desa Binaan</span>
                                    <span class="text-xs font-bold text-slate-900 block leading-snug">
                                        {{ kanwilInfo?.total_desa || 0 }} Desa Binaan
                                    </span>
                                </div>

                                <div class="p-4 border border-slate-200 rounded-lg bg-slate-50/50 space-y-1">
                                    <span class="text-[11px] text-slate-500 block font-medium">Total Petugas PIMPASA UPT</span>
                                    <span class="text-xs font-bold text-slate-900 block leading-snug">
                                        {{ kanwilInfo?.total_pimpasa || 0 }} Petugas PIMPASA
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
                            Ubah Kata Sandi Akun Executive Kanwil
                        </h2>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            Ganti kata sandi secara berkala untuk menjaga keamanan akses dashboard executive monitoring dan master data sistem.
                        </p>
                    </div>

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
                                <PasswordStrengthMeter :password="passwordForm.new_password" />
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
                                class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold h-9.5 px-5 cursor-pointer"
                            >
                                {{ passwordForm.processing ? 'Menyimpan...' : 'Perbarui Kata Sandi' }}
                            </Button>
                        </div>
                    </form>
                </div>

                <!-- TAB 4: PREFERENSI NOTIFIKASI EXECUTIVE -->
                <div v-show="activeTab === 'notifications'" class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-2xs">
                    <div class="p-5 sm:p-6 border-b border-slate-100">
                        <h2 class="text-base font-bold text-slate-900 tracking-tight">
                            Preferensi Kanal Notifikasi & Alert Executive Monitoring
                        </h2>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            Atur kanal notifikasi untuk memantau pengajuan aduan baru dari seluruh Desa Binaan dan eskalasi peneguran SLA UPT Imigrasi > 24 jam.
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
                                    <span class="font-bold text-slate-900 block">Notifikasi Executive Monitoring (In-App)</span>
                                    <p class="text-slate-500 leading-relaxed">Tampilkan lencana dan pesan real-time di lonceng sistem saat ada aduan baru atau peneguran SLA UPT > 24 jam.</p>
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
                                    <p class="text-slate-500 leading-relaxed">Kirimkan spanduk push notification instan ke layar seluler pimpinan saat terjadi pengajuan laporan baru atau teguran SLA.</p>
                                </div>
                            </label>

                            <label class="flex items-start gap-3.5 p-4 border border-slate-200/80 rounded-lg bg-slate-50/50 hover:bg-slate-50 transition-colors cursor-pointer">
                                <input
                                    type="checkbox"
                                    v-model="notifForm.notify_email"
                                    class="mt-0.5 rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                                />
                                <div class="space-y-1 text-xs">
                                    <span class="font-bold text-slate-900 block">Surat Elektronik Kedinasan Executive (Email SMTP)</span>
                                    <p class="text-slate-500 leading-relaxed">Kirimkan salinan surat pemberitahuan resmi dari Kanwil Ditjenim Sumatera Utara ke alamat email pimpinan.</p>
                                </div>
                            </label>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex justify-end">
                            <Button
                                type="submit"
                                size="sm"
                                :disabled="notifForm.processing"
                                class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold h-9.5 px-5 cursor-pointer"
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
