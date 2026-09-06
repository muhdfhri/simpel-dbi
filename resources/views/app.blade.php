<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Title dihandle oleh Inertia di app.ts (title callback) --}}
    <title inertia>SIMPEL DBI</title>

    {{-- Meta SEO dasar --}}
    <meta name="description" content="Sistem Informasi Monitoring dan Pelaporan Desa Binaan Imigrasi — Kantor Wilayah Ditjen Imigrasi Sumatera Utara">
    <meta name="robots" content="noindex, nofollow">{{-- Sistem internal, tidak perlu diindex --}}

    {{-- Google Fonts sudah dimuat via @import di app.css --}}

    {{-- Vite: app.css (design tokens + Tailwind) + app.ts (Inertia + Vue) --}}
    @vite(['resources/css/app.css', 'resources/js/app.ts'])

    {{-- Inertia head (title, meta dari komponen Vue) --}}
    @inertiaHead
</head>
<body class="min-h-screen bg-background text-foreground font-sans antialiased">
    {{-- Inertia app root --}}
    @inertia
</body>
</html>
