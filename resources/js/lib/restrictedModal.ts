import { ref } from 'vue';

export const isRestrictedModalOpen = ref(false);

export const restrictedModalData = ref({
    title: 'Akses Fitur Sedang Dibatasi oleh Kanwil',
    description: 'Hak akses untuk fitur ini sedang dinonaktifkan sementara oleh Administrator Kanwil Ditjen Imigrasi Sumatera Utara.',
    featureName: 'Fitur Utama System'
});

export function openRestrictedModal(data?: { title?: string; description?: string; featureName?: string }) {
    if (data) {
        restrictedModalData.value = {
            title: data.title || 'Akses Fitur Sedang Dibatasi oleh Kanwil',
            description: data.description || 'Hak akses untuk fitur ini sedang dinonaktifkan sementara oleh Administrator Kanwil Ditjen Imigrasi Sumatera Utara.',
            featureName: data.featureName || 'Fitur Utama System'
        };
    }
    isRestrictedModalOpen.value = true;
}
