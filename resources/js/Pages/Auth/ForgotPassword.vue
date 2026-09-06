<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import { Mail, ArrowLeft, RotateCcw, CheckCircle2, Key } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const isSubmitted = ref(false);

const submit = () => {
    form.post('/forgot-password');
};

const handleResend = () => {
    isSubmitted.value = false;
    form.post('/forgot-password');
};

const isSent = computed(() => Boolean(props.status) || isSubmitted.value);
</script>

<template>
    <Head title="Lupa Kata Sandi" />

    <div class="min-h-screen bg-white flex flex-col justify-center items-center p-4 sm:p-6 lg:p-8 font-sans selection:bg-slate-900 selection:text-white">
        
        <!-- Success / Check Inbox State -->
        <div v-if="isSent" class="flex w-full max-w-sm flex-col items-center gap-4 rounded-2xl border border-slate-400 bg-white p-6 sm:p-8 text-center shadow-[0_10px_25px_-5px_rgba(0,0,0,0.07),0_8px_10px_-6px_rgba(0,0,0,0.03)] font-sans animate-in fade-in zoom-in-95 duration-200">
            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-slate-700">
                <Mail :size="20" />
            </div>

            <div class="space-y-1 text-center">
                <h3 class="font-semibold text-base text-slate-900">Periksa Inbox Email Anda</h3>
                <p class="text-slate-500 text-xs leading-relaxed">
                    Kami telah mengirimkan tautan reset kata sandi ke
                    <span class="font-medium text-slate-900 font-mono block mt-0.5">{{ form.email || 'email Anda' }}</span>
                </p>
            </div>

            <div v-if="status" class="p-2.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-center gap-1.5 w-full">
                <CheckCircle2 :size="14" class="shrink-0 text-emerald-600" />
                <span>{{ status }}</span>
            </div>

            <div class="w-full space-y-3 pt-1">
                <Button
                    @click="handleResend"
                    :disabled="form.processing"
                    size="sm"
                    type="button"
                    variant="outline"
                    class="w-full h-9 rounded-md text-xs font-medium border-slate-200 text-slate-700 hover:bg-slate-50 transition-all cursor-pointer"
                >
                    <RotateCcw :size="13" class="mr-1.5 text-slate-400" />
                    <span>{{ form.processing ? 'Mengirim Ulang...' : 'Kirim Ulang Email' }}</span>
                </Button>

                <div class="text-center">
                    <Link href="/login" class="inline-flex items-center text-xs font-medium text-slate-500 hover:text-slate-900 transition-colors">
                        <ArrowLeft :size="13" class="mr-1" />
                        <span>Kembali ke Halaman Masuk</span>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Initial Form State (Clean White Background + Raised Card with Crisp High-Contrast Outline) -->
        <div v-else class="w-full max-w-sm bg-white p-6 sm:p-7 rounded-2xl border border-slate-400 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.07),0_8px_10px_-6px_rgba(0,0,0,0.03)]">
            <div class="space-y-4 mb-5">
                <!-- Icon Badge (Key Icon dari Lucide) -->
                <div class="w-9 h-9 rounded-full bg-slate-100/90 border border-slate-200/60 flex items-center justify-center text-slate-700">
                    <Key :size="18" />
                </div>

                <div class="space-y-1">
                    <h2 class="font-semibold text-lg text-slate-900 tracking-tight">Lupa kata sandi Anda?</h2>
                    <p class="text-slate-500 text-xs leading-relaxed">
                        Masukkan email akun Anda dan kami akan mengirimkan tautan untuk memilih kata sandi baru.
                    </p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-1.5">
                    <Label for="email" class="text-xs font-semibold text-slate-800">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        v-model="form.email"
                        placeholder="you@example.com"
                        class="text-xs h-9 rounded-md border-slate-200 shadow-2xs font-sans placeholder:text-slate-400 focus-visible:ring-slate-400"
                        required
                        autofocus
                        :disabled="form.processing"
                    />
                    <p v-if="form.errors.email" class="text-xs text-red-500 font-medium pt-0.5">
                        {{ form.errors.email }}
                    </p>
                </div>

                <Button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full h-9 bg-slate-900 hover:bg-slate-800 active:bg-black text-white font-medium text-xs rounded-md shadow-xs transition-all cursor-pointer"
                >
                    <span v-if="form.processing">Mengirimkan...</span>
                    <span v-else>Kirim tautan reset</span>
                </Button>

                <p class="text-center text-slate-500 text-xs pt-1">
                    Ingat kata sandi?
                    <Link href="/login" class="text-slate-900 font-medium underline underline-offset-2 hover:text-slate-700 ml-1">
                        Kembali ke halaman masuk
                    </Link>
                </p>
            </form>
        </div>

    </div>
</template>
