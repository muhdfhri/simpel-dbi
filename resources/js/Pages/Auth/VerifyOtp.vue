<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import { Key, ArrowLeft, RotateCcw, CheckCircle2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

const props = defineProps<{
    email?: string;
    token?: string;
    status?: string;
}>();

// Form Inertia
const form = useForm({
    email: props.email || '',
    token: props.token || '',
    otp: '',
});

// Array 6 box input OTP
const otpDigits = ref<string[]>(['', '', '', '', '', '']);
const inputRefs = ref<HTMLInputElement[]>([]);

// Sync otpDigits array into form.otp string
const updateOtpString = () => {
    form.otp = otpDigits.value.join('');
};

const handleInput = (index: number, event: Event) => {
    const target = event.target as HTMLInputElement;
    const value = target.value.replace(/\D/g, ''); // Hanya angka

    if (value.length > 0) {
        // Simpan 1 angka terakhir diketik
        otpDigits.value[index] = value[value.length - 1];
        updateOtpString();

        // Pindah otomatis ke input box berikutnya
        if (index < 5 && inputRefs.value[index + 1]) {
            inputRefs.value[index + 1].focus();
        }
    } else {
        otpDigits.value[index] = '';
        updateOtpString();
    }
};

const handleKeyDown = (index: number, event: KeyboardEvent) => {
    if (event.key === 'Backspace' && !otpDigits.value[index] && index > 0) {
        // Mundur ke box sebelumnya jika menekan Backspace saat box kosong
        if (inputRefs.value[index - 1]) {
            inputRefs.value[index - 1].focus();
        }
    }
};

const handlePaste = (event: ClipboardEvent) => {
    event.preventDefault();
    const pastedData = event.clipboardData?.getData('text') || '';
    const digits = pastedData.replace(/\D/g, '').slice(0, 6).split('');

    digits.forEach((digit, i) => {
        if (i < 6) {
            otpDigits.value[i] = digit;
        }
    });
    updateOtpString();

    // Focus ke box setelah digit terakhir yang terisi
    const nextIndex = Math.min(digits.length, 5);
    if (inputRefs.value[nextIndex]) {
        inputRefs.value[nextIndex].focus();
    }
};

const submit = () => {
    updateOtpString();
    form.post('/verify-otp');
};

const handleResend = () => {
    form.post('/forgot-password', {
        onSuccess: () => {
            startCountdown();
        },
    });
};

// Countdown Timer 5 menit (300 detik)
const timer = ref(300);
let intervalId: any = null;

const startCountdown = () => {
    timer.value = 300;
    if (intervalId) clearInterval(intervalId);
    intervalId = setInterval(() => {
        if (timer.value > 0) {
            timer.value--;
        } else {
            clearInterval(intervalId);
        }
    }, 1000);
};

const formattedTime = computed(() => {
    const minutes = Math.floor(timer.value / 60);
    const seconds = timer.value % 60;
    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
});

onMounted(() => {
    startCountdown();
    // Auto-focus ke box pertama
    if (inputRefs.value[0]) {
        inputRefs.value[0].focus();
    }
});

onUnmounted(() => {
    if (intervalId) clearInterval(intervalId);
});
</script>

<template>
    <Head title="Verifikasi Kode OTP" />

    <div class="min-h-screen bg-white flex flex-col justify-center items-center p-4 sm:p-6 lg:p-8 font-sans selection:bg-slate-900 selection:text-white relative">
        
        <!-- Top Left Navigation Link -->
        <div class="absolute top-6 left-6 sm:top-8 sm:left-8">
            <Link href="/forgot-password" class="inline-flex items-center text-base font-semibold text-slate-700 hover:text-slate-900 transition-colors gap-2">
                <ArrowLeft :size="20" class="stroke-[2.5]" />
                <span>Ganti alamat email</span>
            </Link>
        </div>

        <!-- Raised Card with Crisp High-Contrast Outline (100% Selaras dengan ForgotPassword.vue) -->
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
                    <h2 class="font-semibold text-lg text-slate-900 tracking-tight">Verifikasi Kode OTP</h2>
                    <p class="text-slate-500 text-xs leading-relaxed">
                        Masukkan 6 digit kode verifikasi yang telah kami kirimkan ke <span class="font-medium text-slate-900 font-mono underline underline-offset-2 decoration-slate-300 break-all">{{ form.email || 'email Anda' }}</span>
                    </p>
                </div>
            </div>

            <!-- Status Alert jika ada resend success -->
            <div v-if="status" class="mb-4 p-2.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center gap-1.5">
                <CheckCircle2 :size="14" class="shrink-0 text-emerald-600" />
                <span>{{ status }}</span>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <!-- 6-Digit Box Input Grid -->
                <div class="space-y-2">
                    <label class="text-xs font-semibold text-slate-800 block text-center">Kode OTP 6-Digit</label>
                    <div class="flex justify-between items-center gap-1.5" @paste="handlePaste">
                        <input
                            v-for="(digit, index) in otpDigits"
                            :key="index"
                            :ref="(el) => { if (el) inputRefs[index] = el as HTMLInputElement }"
                            type="text"
                            inputmode="numeric"
                            maxlength="1"
                            v-model="otpDigits[index]"
                            @input="handleInput(index, $event)"
                            @keydown="handleKeyDown(index, $event)"
                            class="w-11 h-12 text-center text-lg font-bold font-mono rounded-lg border border-slate-300 bg-slate-50/50 text-slate-900 focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 transition-all"
                            :disabled="form.processing"
                        />
                    </div>
                    <p v-if="form.errors.otp" class="text-xs text-red-500 font-medium text-center pt-1">
                        {{ form.errors.otp }}
                    </p>
                </div>

                <!-- Timer & Resend Option -->
                <div class="text-center text-xs text-slate-500 space-y-1">
                    <p v-if="timer > 0">
                        Kode kedaluwarsa dalam <span class="font-semibold text-slate-900 font-mono">{{ formattedTime }}</span>
                    </p>
                    <p v-else class="text-amber-600 font-medium">
                        Kode telah kedaluwarsa. Silakan minta kode baru.
                    </p>

                    <button
                        type="button"
                        @click="handleResend"
                        :disabled="form.processing || timer > 240"
                        class="inline-flex items-center text-xs font-medium text-slate-700 hover:text-slate-900 underline underline-offset-2 disabled:opacity-50 disabled:no-underline transition-colors cursor-pointer mt-1"
                    >
                        <RotateCcw :size="12" class="mr-1 text-slate-400" />
                        <span>Kirim Ulang Kode OTP</span>
                    </button>
                </div>

                <!-- Submit Button -->
                <Button
                    type="submit"
                    :disabled="form.processing || form.otp.length !== 6"
                    class="w-full h-9 bg-slate-900 hover:bg-slate-800 active:bg-black text-white font-medium text-xs rounded-md shadow-xs transition-all cursor-pointer"
                >
                    <span v-if="form.processing">Memverifikasi...</span>
                    <span v-else>Verifikasi & Lanjutkan</span>
                </Button>
            </form>
        </div>

    </div>
</template>
