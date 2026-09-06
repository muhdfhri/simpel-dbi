<script setup lang="ts">
import { ref } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import { Key, Eye, EyeOff, ArrowLeft } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    email: string;
    token: string;
}>();

const showPassword = ref(false);

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

    <div class="min-h-screen bg-white flex flex-col justify-center items-center p-4 sm:p-6 lg:p-8 font-sans selection:bg-slate-900 selection:text-white">
        
        <!-- Clean Raised Card with Crisp High-Contrast Outline (Matching ForgotPassword.vue) -->
        <div class="w-full max-w-sm bg-white p-6 sm:p-7 rounded-2xl border border-slate-400 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.07),0_8px_10px_-6px_rgba(0,0,0,0.03)]">
            <div class="space-y-4 mb-5">
                <!-- Icon Badge (Key Icon dari Lucide) -->
                <div class="w-9 h-9 rounded-full bg-slate-100/90 border border-slate-200/60 flex items-center justify-center text-slate-700">
                    <Key :size="18" />
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
                    <p v-if="form.errors.password" class="text-xs text-red-500 font-medium pt-0.5">
                        {{ form.errors.password }}
                    </p>
                </div>

                <!-- Konfirmasi Password Baru -->
                <div class="space-y-1.5">
                    <Label for="password_confirmation" class="text-xs font-semibold text-slate-800">Konfirmasi kata sandi</Label>
                    <Input
                        id="password_confirmation"
                        :type="showPassword ? 'text' : 'password'"
                        v-model="form.password_confirmation"
                        placeholder="Ulangi kata sandi baru"
                        class="text-xs h-9 rounded-md border-slate-200 shadow-2xs font-sans placeholder:text-slate-400 focus-visible:ring-slate-400"
                        required
                        :disabled="form.processing"
                    />
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

                <p class="text-center text-slate-500 text-xs pt-1">
                    <Link href="/login" class="inline-flex items-center text-slate-600 font-medium hover:text-slate-900 transition-colors">
                        <ArrowLeft :size="13" class="mr-1" />
                        <span>Batal dan kembali ke halaman masuk</span>
                    </Link>
                </p>
            </form>
        </div>

    </div>
</template>
