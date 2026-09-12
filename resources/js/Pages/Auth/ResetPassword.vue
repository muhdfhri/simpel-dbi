<script setup lang="ts">
import { ref } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import { Key, Eye, EyeOff, ArrowLeft } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import PasswordStrengthMeter from '@/components/common/PasswordStrengthMeter.vue';

const props = defineProps<{
    email: string;
    token: string;
}>();

const showPassword = ref(false);
const showPasswordConfirm = ref(false);

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/reset-password', {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <Head title="Atur Ulang Kata Sandi" />

    <div class="min-h-screen bg-white flex flex-col justify-center items-center p-4 sm:p-6 lg:p-8 font-sans selection:bg-slate-900 selection:text-white relative">
        
        <!-- Top Left Navigation Link -->
        <div class="absolute top-6 left-6 sm:top-8 sm:left-8">
            <Link href="/forgot-password" class="inline-flex items-center text-base font-semibold text-slate-700 hover:text-slate-900 transition-colors gap-2">
                <ArrowLeft :size="20" class="stroke-[2.5]" />
                <span>Kembali ke sebelumnya</span>
            </Link>
        </div>

        <!-- Clean Raised Card with Crisp High-Contrast Outline (Matching ForgotPassword.vue) -->
        <div class="w-full max-w-sm bg-white p-6 sm:p-7 rounded-2xl border border-slate-400 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.07),0_8px_10px_-6px_rgba(0,0,0,0.03)]">
            <div class="space-y-4 mb-5">
                <!-- Logo Kanwil Badge + Label SIMPEL DBI -->
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-full bg-white flex items-center justify-center p-0.5 shadow-xs border border-slate-200/80 shrink-0 overflow-hidden">
                        <img
                            src="/images/logo-kanwil.webp"
                            alt="Logo Kanwil Ditjen Imigrasi Sumut"
                            class="w-full h-full object-contain scale-125"
                        />
                    </div>
                    <span class="font-extrabold text-slate-900 text-lg tracking-tight">SIMPEL DBI</span>
                </div>

                <div class="space-y-1">
                    <h2 class="font-semibold text-lg text-slate-900 tracking-tight">Atur ulang kata sandi</h2>
                    <p class="text-slate-500 text-xs leading-relaxed">
                        Masukkan kata sandi baru untuk akun Anda.
                    </p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <!-- Email (Readonly Display) -->
                <div class="space-y-1.5">
                    <Label for="email" class="text-xs font-semibold text-slate-800">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        v-model="form.email"
                        class="text-xs h-9 rounded-md border-slate-200 bg-slate-50/80 text-slate-600 shadow-2xs font-sans"
                        required
                        readonly
                    />
                    <p v-if="form.errors.email" class="text-xs text-red-500 font-medium pt-0.5">
                        {{ form.errors.email }}
                    </p>
                </div>

                <!-- Password Baru -->
                <div class="space-y-1.5">
                    <Label for="password" class="text-xs font-semibold text-slate-800">Kata sandi baru</Label>
                    <div class="relative">
                        <Input
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            v-model="form.password"
                            placeholder="Minimal 8 karakter"
                            class="text-xs h-9 pr-9 rounded-md border-slate-200 shadow-2xs font-sans placeholder:text-slate-400 focus-visible:ring-slate-400"
                            required
                            autofocus
                            :disabled="form.processing"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 transition-colors"
                            tabindex="-1"
                        >
                            <Eye v-if="!showPassword" :size="15" />
                            <EyeOff v-else :size="15" />
                        </button>
                    </div>
                    <PasswordStrengthMeter :password="form.password" />
                    <p v-if="form.errors.password" class="text-xs text-red-500 font-medium pt-0.5">
                        {{ form.errors.password }}
                    </p>
                </div>

                <!-- Konfirmasi Password Baru -->
                <div class="space-y-1.5">
                    <Label for="password_confirmation" class="text-xs font-semibold text-slate-800">Konfirmasi kata sandi</Label>
                    <div class="relative">
                        <Input
                            id="password_confirmation"
                            :type="showPasswordConfirm ? 'text' : 'password'"
                            v-model="form.password_confirmation"
                            placeholder="Ulangi kata sandi baru"
                            class="text-xs h-9 pr-9 rounded-md border-slate-200 shadow-2xs font-sans placeholder:text-slate-400 focus-visible:ring-slate-400"
                            required
                            :disabled="form.processing"
                        />
                        <button
                            type="button"
                            @click="showPasswordConfirm = !showPasswordConfirm"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 transition-colors"
                            tabindex="-1"
                        >
                            <Eye v-if="!showPasswordConfirm" :size="15" />
                            <EyeOff v-else :size="15" />
                        </button>
                    </div>
                    <p v-if="form.errors.password_confirmation" class="text-xs text-red-500 font-medium pt-0.5">
                        {{ form.errors.password_confirmation }}
                    </p>
                </div>

                <Button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full h-9 bg-slate-900 hover:bg-slate-800 active:bg-black text-white font-medium text-xs rounded-md shadow-xs transition-all cursor-pointer mt-2"
                >
                    <span v-if="form.processing">Menyimpan...</span>
                    <span v-else>Simpan kata sandi baru</span>
                </Button>
            </form>
        </div>

    </div>
</template>
