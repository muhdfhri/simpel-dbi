<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import { Eye, EyeOff, ShieldCheck, Map } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();

const showPassword = ref(false);
const turnstileSiteKey = import.meta.env.VITE_TURNSTILE_SITE_KEY || '0x4AAAAAAEq_lhhvjQv_W6RC';

const form = useForm({
    email: '',
    password: '',
    remember: false,
    cf_turnstile_response: '',
});

onMounted(() => {
    // Load Cloudflare Turnstile Script dynamically
    if (!document.getElementById('cf-turnstile-script')) {
        const script = document.createElement('script');
        script.id = 'cf-turnstile-script';
        script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?onload=onloadTurnstileCallback';
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);

        (window as any).onloadTurnstileCallback = () => {
            if ((window as any).turnstile) {
                (window as any).turnstile.render('#turnstile-container', {
                    sitekey: turnstileSiteKey,
                    callback: (token: string) => {
                        form.cf_turnstile_response = token;
                    },
                    'expired-callback': () => {
                        form.cf_turnstile_response = '';
                    },
                });
            }
        };
    } else if ((window as any).turnstile) {
        setTimeout(() => {
            (window as any).turnstile.render('#turnstile-container', {
                sitekey: turnstileSiteKey,
                callback: (token: string) => {
                    form.cf_turnstile_response = token;
                },
            });
        }, 100);
    }
});

const submit = () => {
    form.post('/portal-dbi', {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <Head title="Masuk ke Sistem" />

    <!-- Full-Screen Split 60/40 Layout (Ultra Clean Bahasa Indonesia Formal) -->
    <div class="h-screen w-screen grid grid-cols-1 lg:grid-cols-10 overflow-hidden bg-white font-sans text-slate-900 selection:bg-slate-900 selection:text-white">
        
        <!-- KIRI / VISUAL HERO PANEL (60% Width Full Height) -->
        <div class="relative hidden lg:flex lg:col-span-6 flex-col justify-between p-12 bg-slate-950 text-white overflow-hidden">
            
            <!-- Hero Architecture Image (Gedung.jpeg) -->
            <img
                src="/images/Gedung.jpeg"
                alt="Gedung Imigrasi Sumatera Utara"
                loading="lazy"
                decoding="async"
                class="absolute inset-0 w-full h-full object-cover"
            />
            
            <!-- Soft Dark Overlay Gradient -->
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/95 via-slate-950/40 to-slate-950/50 pointer-events-none"></div>

            <!-- Top Left Brand Header (Logo Kanwil + Brand Name) -->
            <div class="relative z-10 flex items-center gap-3">
                <div class="w-11 h-11 rounded-full bg-white flex items-center justify-center p-0.5 shadow-md border border-slate-100/50 shrink-0 overflow-hidden">
                    <img
                        src="/images/logo-kanwil.webp"
                        alt="Logo Kanwil Ditjen Imigrasi Sumut"
                        loading="lazy"
                        decoding="async"
                        class="w-full h-full object-contain scale-125"
                    />
                </div>
                <span class="text-xl font-bold tracking-tight text-white drop-shadow-sm">
                    SIMPEL DBI
                </span>
            </div>

            <!-- Bottom Left Hero Content (Bahasa Indonesia Formal) -->
            <div class="relative z-10 space-y-4 max-w-lg mb-2">
                <!-- Label / Badge Identitas Sistem -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-semibold text-white/90">
                    <Map :size="14" class="text-amber-400" />
                    Kanwil Ditjen Imigrasi Sumatera Utara
                </div>

                <!-- Headline Utama -->
                <h1 class="text-4xl font-extrabold tracking-tight text-white leading-tight drop-shadow-sm">
                    Monitoring & Pengawasan Terintegrasi
                </h1>
                
                <!-- Subtitle -->
                <p class="text-sm text-slate-200 font-normal leading-relaxed drop-shadow-xs">
                    Sistem Informasi Monitoring dan Pelaporan Desa Binaan Imigrasi Sumatera Utara.
                </p>
            </div>

        </div>

        <!-- KANAN / FORM PANEL (40% Width Full Height Clean White) -->
        <div class="lg:col-span-4 flex flex-col justify-between p-8 sm:p-12 lg:p-16 bg-white overflow-y-auto">
            
            <!-- Top Mobile Brand Header (Tampil jika layar kecil) -->
            <div class="flex items-center gap-2.5 lg:hidden">
                <div class="w-9 h-9 rounded-full bg-white flex items-center justify-center p-0.5 shadow-xs border border-slate-200 shrink-0 overflow-hidden">
                    <img
                        src="/images/logo-kanwil.webp"
                        alt="Logo Kanwil Ditjen Imigrasi Sumut"
                        class="w-full h-full object-contain scale-125"
                    />
                </div>
                <span class="font-bold text-slate-900 text-sm">SIMPEL DBI</span>
            </div>

            <!-- Main Form Content Box (Center Focused & Super Clean) -->
            <div class="w-full max-w-sm mx-auto my-auto py-8 space-y-8">
                
                <!-- Main Header Text (Bahasa Indonesia Formal) -->
                <div class="space-y-1.5 text-left">
                    <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">
                        Selamat Datang Kembali
                    </h2>
                    <p class="text-xs text-slate-500 font-normal">
                        Silakan masukkan kredensial akun Anda untuk mengakses sistem.
                    </p>
                </div>

                <!-- Status Banner Notification -->
                <div v-if="status" class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-medium">
                    {{ status }}
                </div>

                <!-- Form Login -->
                <form @submit.prevent="submit" class="space-y-5">
                    
                    <!-- Alamat Email -->
                    <div class="space-y-1.5">
                        <label for="email" class="block text-xs font-semibold text-slate-700">
                            Alamat Email
                        </label>
                        <input
                            id="email"
                            type="email"
                            v-model="form.email"
                            placeholder="nama@imigrasi.go.id"
                            class="w-full px-4 py-3 text-sm text-slate-900 border border-slate-300 rounded-xl focus:border-slate-800 focus:ring-1 focus:ring-slate-800 outline-none transition-all placeholder:text-slate-400 font-medium"
                            required
                            autofocus
                            :disabled="form.processing"
                        />
                        <p v-if="form.errors.email" class="text-xs text-red-500 mt-1 font-medium">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Kata Sandi -->
                    <div class="space-y-1.5">
                        <label for="password" class="block text-xs font-semibold text-slate-700">
                            Kata Sandi
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                v-model="form.password"
                                placeholder="••••••••"
                                class="w-full px-4 py-3 pr-12 text-sm text-slate-900 border border-slate-300 rounded-xl focus:border-slate-800 focus:ring-1 focus:ring-slate-800 outline-none transition-all placeholder:text-slate-400 font-medium"
                                required
                                :disabled="form.processing"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors p-1"
                                tabindex="-1"
                            >
                                <EyeOff v-if="showPassword" class="w-4.5 h-4.5" />
                                <Eye v-else class="w-4.5 h-4.5" />
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="text-xs text-red-500 mt-1 font-medium">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Ingat Saya + Lupa Kata Sandi -->
                    <div class="flex items-center justify-between text-xs pt-1">
                        <label class="flex items-center space-x-2 text-slate-600 cursor-pointer select-none font-medium">
                            <input
                                type="checkbox"
                                v-model="form.remember"
                                class="w-4 h-4 text-slate-900 border-slate-300 rounded focus:ring-slate-800 cursor-pointer"
                            />
                            <span>Ingat Saya</span>
                        </label>
                        <Link
                            href="/forgot-password"
                            class="text-slate-500 hover:text-slate-900 font-semibold transition-colors"
                        >
                            Lupa Kata Sandi?
                        </Link>
                    </div>

                    <!-- Cloudflare Turnstile Verification Widget -->
                    <div class="pt-1 flex justify-center">
                        <div id="turnstile-container"></div>
                    </div>

                    <!-- Tombol Utama Masuk ke Sistem -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-slate-900 hover:bg-slate-800 active:bg-black text-white font-semibold py-3.5 px-4 rounded-xl shadow-md transition-all text-sm disabled:opacity-50 mt-2"
                    >
                        <span v-if="form.processing">Memproses Masuk...</span>
                        <span v-else>Masuk ke Sistem</span>
                    </button>

                </form>

            </div>

            <!-- Footer Text -->
            <div class="text-center text-xs text-slate-400 font-medium">
                V.1.0.0 | Kantor Wilayah Ditjen Imigrasi Sumatera Utara &copy; 2026
            </div>

        </div>

    </div>
</template>
