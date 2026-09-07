<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import NotFoundPage from '@/components/ui/NotFoundPage.vue';
</script>

<template>
    <Head title="404 - Halaman Tidak Ditemukan" />

    <NotFoundPage
        title="404"
        description="Halaman Tidak Ditemukan"
        helperText="Halaman yang Anda tuju tidak tersedia atau URL yang dimasukkan salah. Silakan kembali ke beranda SIMPEL DBI."
        backLabel="Kembali ke Beranda"
    />
</template>
