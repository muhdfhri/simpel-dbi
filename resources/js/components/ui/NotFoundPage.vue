<script setup lang="ts">
import { computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { ArrowLeft, Frown } from 'lucide-vue-next';

interface NotFoundPageProps {
    className?: string;
    homeHref?: string;
    title?: string;
    description?: string;
    helperText?: string;
    backLabel?: string;
    buttonClassName?: string;
}

const props = withDefaults(defineProps<NotFoundPageProps>(), {
    className: '',
    homeHref: '/',
    title: '404',
    description: 'Oops! Page not found',
    helperText: 'The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.',
    backLabel: 'Back to Home',
    buttonClassName: '',
});

const page = usePage();

const computedHomeUrl = computed(() => {
    if (props.homeHref && props.homeHref !== '/') return props.homeHref;
    
    const user = (page.props as any).auth?.user;
    if (user) {
        const role = user.role?.value || user.role || 'desa';
        return `/${role}/dashboard`;
    }
    return '/portal-dbi';
});
</script>

<template>
    <div class="min-h-screen w-full flex items-center justify-center p-6 bg-white font-sans">
        
        <!-- Clean White Content Container -->
        <div class="w-full max-w-2xl flex flex-col items-center justify-center text-center space-y-6">
            
            <!-- Animated Frown Icon (Swaying side to side) -->
            <div class="inline-block animate-sway">
                <slot name="icon">
                    <Frown class="mx-auto h-24 w-24 text-slate-500 stroke-[1.5]" />
                </slot>
            </div>

            <!-- Big 404 Title -->
            <h1 class="font-extrabold text-4xl text-slate-900 tracking-tight">
                {{ title }}
            </h1>

            <!-- Sub-description -->
            <p class="text-slate-600 text-xl font-medium">
                {{ description }}
            </p>

            <!-- Helper text -->
            <p class="mx-auto max-w-md text-slate-500 text-sm leading-relaxed font-normal">
                {{ helperText }}
            </p>

            <!-- Back to Home Button (Rounded dikurangi/disesuaikan) -->
            <div class="pt-2">
                <Link
                    :href="computedHomeUrl"
                    class="mt-4 inline-flex items-center rounded-full bg-slate-900 hover:bg-slate-800 px-6 py-2.5 font-semibold text-white text-sm transition-all shadow-md hover:scale-[1.02] active:scale-[0.98] cursor-pointer gap-2"
                >
                    <ArrowLeft class="h-4 w-4 text-white" />
                    <span>{{ backLabel }}</span>
                </Link>
            </div>

        </div>

    </div>
</template>

<style scoped>
@keyframes sway {
    0%, 100% {
        transform: rotate(0deg) translateX(0px);
    }
    25% {
        transform: rotate(-8deg) translateX(-6px);
    }
    75% {
        transform: rotate(8deg) translateX(6px);
    }
}

.animate-sway {
    animation: sway 4s ease-in-out infinite;
}
</style>
