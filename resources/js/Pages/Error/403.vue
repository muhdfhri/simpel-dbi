<script setup lang="ts">
import AppLayout from '@/components/layout/AppLayout.vue';
import AccessRestrictedModal from '@/components/common/AccessRestrictedModal.vue';

defineProps<{
    status?: number;
    message?: string;
}>();
</script>

<template>
    <AppLayout title="Akses Fitur Dibatasi">
        <AccessRestrictedModal
            :description="message || 'Hak akses untuk fitur ini sedang dinonaktifkan sementara oleh Administrator Kanwil Ditjen Imigrasi Sumatera Utara.'"
        />
    </AppLayout>
</template>
