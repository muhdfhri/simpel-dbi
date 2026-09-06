import { toast as sonnerToast } from 'vue-sonner';

export interface ToastOptions {
    description?: string;
    duration?: number;
    action?: {
        label: string;
        onClick: () => void;
    };
}

/**
 * Global Toast Helper System (Shadcn Toast - Nova Style)
 * Layout Horizontal Ultra-Presisi + Tombol Close (X)
 */
export const notify = {
    /**
     * Toast Status Sukses (Hijau Emerald)
     */
    success(title: string, options?: ToastOptions) {
        return sonnerToast.success(title, {
            description: options?.description,
            duration: options?.duration ?? 4000,
            action: options?.action,
            classes: {
                toast: 'bg-white text-slate-900 border border-slate-200/90 shadow-2xl rounded-2xl p-4 font-sans flex items-start gap-3 relative',
                title: 'font-bold text-xs text-slate-900 leading-snug',
                description: 'text-xs text-slate-600 font-medium mt-0.5 leading-relaxed',
                actionButton: 'bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs px-3 py-1.5 rounded-xl shadow-xs',
            },
        });
    },

    /**
     * Toast Status Error / Gagal (Merah Red)
     */
    error(title: string, options?: ToastOptions) {
        return sonnerToast.error(title, {
            description: options?.description,
            duration: options?.duration ?? 5000,
            action: options?.action,
            classes: {
                toast: 'bg-white text-slate-900 border border-slate-200/90 shadow-2xl rounded-2xl p-4 font-sans flex items-start gap-3 relative',
                title: 'font-bold text-xs text-slate-900 leading-snug',
                description: 'text-xs text-slate-600 font-medium mt-0.5 leading-relaxed',
                actionButton: 'bg-red-600 hover:bg-red-700 text-white font-semibold text-xs px-3 py-1.5 rounded-xl shadow-xs',
            },
        });
    },

    /**
     * Toast Status Warning / Peringatan (Amber / Kuning)
     */
    warning(title: string, options?: ToastOptions) {
        return sonnerToast.warning(title, {
            description: options?.description,
            duration: options?.duration ?? 4500,
            action: options?.action,
            classes: {
                toast: 'bg-white text-slate-900 border border-slate-200/90 shadow-2xl rounded-2xl p-4 font-sans flex items-start gap-3 relative',
                title: 'font-bold text-xs text-slate-900 leading-snug',
                description: 'text-xs text-slate-600 font-medium mt-0.5 leading-relaxed',
                actionButton: 'bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs px-3 py-1.5 rounded-xl shadow-xs',
            },
        });
    },

    /**
     * Toast Status Informasi (Biru Sky)
     */
    info(title: string, options?: ToastOptions) {
        return sonnerToast.info(title, {
            description: options?.description,
            duration: options?.duration ?? 4000,
            action: options?.action,
            classes: {
                toast: 'bg-white text-slate-900 border border-slate-200/90 shadow-2xl rounded-2xl p-4 font-sans flex items-start gap-3 relative',
                title: 'font-bold text-xs text-slate-900 leading-snug',
                description: 'text-xs text-slate-600 font-medium mt-0.5 leading-relaxed',
                actionButton: 'bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs px-3 py-1.5 rounded-xl shadow-xs',
            },
        });
    },
};
