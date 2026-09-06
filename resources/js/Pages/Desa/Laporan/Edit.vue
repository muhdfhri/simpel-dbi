<script setup lang="ts">
import { ref } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/components/layout/AppLayout.vue';
import {
    UploadCloud,
    FileText,
    X,
    AlertCircle,
    Send,
    MapPin,
    FilePlus2,
    AlertTriangle,
    Calendar,
    Users,
    AlignLeft,
    Tag,
    ArrowLeft,
    Paperclip,
    ExternalLink,
    FileEdit,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { notify } from '@/lib/toast';

interface LampiranItem {
    id: number;
    nama_file_asli: string;
    path: string;
    tipe_file: string;
    ukuran_bytes: number;
}

interface LaporanProps {
    id: number;
    kode_tiket: string;
    judul: string;
    kategori_id?: number | null;
    status: string;
    tanggal_kejadian: string;
    lokasi_detail: string;
    kronologi: string;
    estimasi_jumlah_orang: number | null;
    verifikasi?: {
        catatan: string | null;
        pimpasa?: { name: string };
    };
    lampiran_list?: LampiranItem[];
}

const props = defineProps<{
    laporan: LaporanProps;
    kategoriOptions: Array<{ id: number; value: number; label: string; kode?: string }>;
}>();

const form = useForm({
    kategori_id: props.laporan.kategori_id ?? undefined as number | undefined,
    judul: props.laporan.judul,
    tanggal_kejadian: props.laporan.tanggal_kejadian
        ? props.laporan.tanggal_kejadian.replace(' ', 'T').slice(0, 16)
        : '',
    lokasi_detail: props.laporan.lokasi_detail,
    kronologi: props.laporan.kronologi,
    estimasi_jumlah_orang: props.laporan.estimasi_jumlah_orang ?? undefined as number | undefined,
    lampiran: [] as File[],
});

// File Upload Handler
const fileInput = ref<HTMLInputElement | null>(null);
const fileErrors = ref<string[]>([]);

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        addFiles(Array.from(target.files));
    }
};

const handleFileDrop = (event: DragEvent) => {
    event.preventDefault();
    if (event.dataTransfer?.files) {
        addFiles(Array.from(event.dataTransfer.files));
    }
};

const addFiles = (files: File[]) => {
    fileErrors.value = [];
    const validFiles: File[] = [];
    const currentTotalFiles = (props.laporan.lampiran_list?.length || 0) + form.lampiran.length;

    for (const file of files) {
        if (file.size > 2 * 1024 * 1024) {
            const msg = `File "${file.name}" melebihi batas maksimal 2MB.`;
            fileErrors.value.push(msg);
            notify.error('Ukuran File Terlalu Besar', { description: msg });
            continue;
        }

        const ext = file.name.split('.').pop()?.toLowerCase();
        if (!['jpg', 'jpeg', 'png', 'pdf'].includes(ext || '')) {
            const msg = `Format file "${file.name}" harus berupa JPG, PNG, atau PDF.`;
            fileErrors.value.push(msg);
            notify.error('Format File Tidak Sesuai', { description: msg });
            continue;
        }

        if (currentTotalFiles + validFiles.length >= 2) {
            const msg = `Maksimal 2 file lampiran dalam satu pengajuan.`;
            fileErrors.value.push(msg);
            notify.warning('Batas Maksimal File', { description: msg });
            break;
        }

        validFiles.push(file);
    }

    form.lampiran.push(...validFiles);
};

const removeFile = (index: number) => {
    form.lampiran.splice(index, 1);
};

// Submit with full client-side validation (matching Create.vue)
const submit = () => {
    if (!form.kategori_id) {
        notify.error('Kategori Belum Dipilih', {
            description: 'Silakan pilih Kategori Laporan terlebih dahulu.'
        });
        return;
    }

    if (!form.judul || !form.judul.trim()) {
        notify.error('Judul Belum Diisi', {
            description: 'Silakan isi Judul Ringkas Kejadian terlebih dahulu.'
        });
        return;
    }

    if (!form.tanggal_kejadian) {
        notify.error('Tanggal Belum Diisi', {
            description: 'Silakan pilih Tanggal & Waktu Kejadian.'
        });
        return;
    }

    if (!form.estimasi_jumlah_orang || form.estimasi_jumlah_orang < 1) {
        notify.error('Estimasi Jumlah Orang Belum Diisi', {
            description: 'Silakan isi Estimasi Jumlah Orang Terlibat (minimal 1).'
        });
        return;
    }

    if (!form.lokasi_detail || !form.lokasi_detail.trim()) {
        notify.error('Lokasi Belum Diisi', {
            description: 'Silakan isi Detail Alamat / Patokan Lokasi Kejadian.'
        });
        return;
    }

    if (!form.kronologi || !form.kronologi.trim()) {
        notify.error('Kronologi Belum Diisi', {
            description: 'Silakan isi Rincian Kronologi Kejadian.'
        });
        return;
    }

    form.post(`/desa/laporan/${props.laporan.id}/update`);
};
</script>

<template>
    <AppLayout :title="`Perbaiki Laporan ${laporan.kode_tiket}`">

        <!-- Amber Alert Card: Catatan Perbaikan dari PIMPASA -->
        <div class="p-5 rounded-2xl bg-amber-50 border-2 border-amber-300 text-amber-950 space-y-2 shadow-2xs">
            <div class="flex items-center gap-2 font-bold text-sm text-orange-900">
                <AlertTriangle :size="18" class="text-orange-600 shrink-0" />
                <span>Instruksi Perbaikan Data dari Petugas PIMPASA</span>
            </div>
            <p class="text-xs text-amber-900 font-medium leading-relaxed pl-7">
                "{{ laporan.verifikasi?.catatan || 'Mohon periksa dan lengkapi kembali fakta kejadian serta dokumen pendukung.' }}"
            </p>
            <div class="text-[11px] text-amber-700 pl-7 font-mono">
                Petugas Verifikator: {{ laporan.verifikasi?.pimpasa?.name || 'Petugas PIMPASA UPT' }}
            </div>
        </div>

        <!-- Form Edit & Re-submit Grid (matching Create: 8/4 col) -->
        <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- LEFT MAIN PANEL (8 Cols) -->
            <div class="lg:col-span-8 space-y-6">

                <!-- SECTION 1: Klasifikasi & Judul -->
                <Card class="border-slate-200/80 shadow-2xs rounded-2xl bg-white overflow-hidden">
                    <CardHeader class="pb-3.5 border-b border-slate-100 px-6 py-4">
                        <CardTitle class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <Tag :size="16" class="text-slate-700" />
                            Klasifikasi &amp; Judul Laporan
                        </CardTitle>
                        <CardDescription class="text-xs text-slate-500">
                            Perbarui kategori dan judul laporan sesuai instruksi verifikator.
                        </CardDescription>
                    </CardHeader>

                    <CardContent class="p-6 space-y-6">

                        <!-- Kategori Laporan -->
                        <div class="space-y-2">
                            <Label for="kategori_id" class="text-xs font-bold text-slate-700 uppercase tracking-wider block">
                                Kategori Laporan <span class="text-red-500">*</span>
                            </Label>
                            <Select v-model="form.kategori_id">
                                <SelectTrigger class="w-full h-10 px-4 bg-white border border-slate-300 rounded-xl text-sm font-medium text-slate-900 shadow-2xs focus:ring-2 focus:ring-slate-900">
                                    <SelectValue placeholder="-- Pilih Kategori Laporan --" />
                                </SelectTrigger>
                                <SelectContent class="rounded-xl border-slate-200 shadow-lg">
                                    <SelectItem
                                        v-for="cat in kategoriOptions"
                                        :key="cat.id"
                                        :value="cat.id"
                                        class="text-sm rounded-lg"
                                    >
                                        {{ cat.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.kategori_id" class="text-xs text-red-500 font-medium pt-0.5">
                                {{ form.errors.kategori_id }}
                            </p>
                        </div>

                        <!-- Judul Laporan -->
                        <div class="space-y-2">
                            <Label for="judul" class="text-xs font-bold text-slate-700 uppercase tracking-wider block">
                                Judul Ringkas Kejadian <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="judul"
                                type="text"
                                v-model="form.judul"
                                placeholder="Contoh: Terdeteksi 3 WNA Asing Tanpa Dokumen Resmi di Dusun I"
                                class="text-sm rounded-xl py-2.5 px-4 border-slate-300 shadow-2xs focus:ring-2 focus:ring-slate-900"
                                required
                            />
                            <p v-if="form.errors.judul" class="text-xs text-red-500 font-medium pt-0.5">
                                {{ form.errors.judul }}
                            </p>
                        </div>

                    </CardContent>
                </Card>

                <!-- SECTION 2: Waktu, Lokasi & Kronologi -->
                <Card class="border-slate-200/80 shadow-2xs rounded-2xl bg-white overflow-hidden">
                    <CardHeader class="pb-3.5 border-b border-slate-100 px-6 py-4">
                        <CardTitle class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <AlignLeft :size="16" class="text-slate-700" />
                            Lokasi &amp; Rincian Kronologi Kejadian
                        </CardTitle>
                        <CardDescription class="text-xs text-slate-500">
                            Perbarui lokasi dan kronologi sesuai fakta lapangan terbaru.
                        </CardDescription>
                    </CardHeader>

                    <CardContent class="p-6 space-y-6">

                        <!-- Grid 2 Kolom: Tanggal Kejadian & Estimasi Jumlah Orang -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <!-- Tanggal Kejadian -->
                            <div class="space-y-2">
                                <Label for="tanggal_kejadian" class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                    <Calendar :size="13" class="text-slate-500" />
                                    <span>Tanggal &amp; Waktu</span>
                                    <span class="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="tanggal_kejadian"
                                    type="datetime-local"
                                    v-model="form.tanggal_kejadian"
                                    class="text-sm rounded-xl py-2.5 px-4 border-slate-300 shadow-2xs"
                                    required
                                />
                                <p v-if="form.errors.tanggal_kejadian" class="text-xs text-red-500 font-medium pt-0.5">
                                    {{ form.errors.tanggal_kejadian }}
                                </p>
                            </div>

                            <!-- Estimasi Jumlah Orang -->
                            <div class="space-y-2">
                                <Label for="estimasi_jumlah_orang" class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                    <Users :size="13" class="text-slate-500" />
                                    <span>Estimasi Orang Terlibat</span>
                                    <span class="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="estimasi_jumlah_orang"
                                    type="number"
                                    min="1"
                                    v-model="form.estimasi_jumlah_orang"
                                    placeholder="Misal: 5"
                                    class="text-sm rounded-xl py-2.5 px-4 border-slate-300 shadow-2xs"
                                    required
                                />
                                <p v-if="form.errors.estimasi_jumlah_orang" class="text-xs text-red-500 font-medium pt-0.5">
                                    {{ form.errors.estimasi_jumlah_orang }}
                                </p>
                            </div>
                        </div>

                        <!-- Lokasi Detail -->
                        <div class="space-y-2">
                            <Label for="lokasi_detail" class="text-xs font-bold text-slate-700 uppercase tracking-wider block">
                                Detail Alamat / Patokan Lokasi <span class="text-red-500">*</span>
                            </Label>
                            <div class="relative">
                                <MapPin :size="16" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
                                <Input
                                    id="lokasi_detail"
                                    type="text"
                                    v-model="form.lokasi_detail"
                                    placeholder="Dusun II, RT 03/RW 01, Dekat Pasar Tradisional"
                                    class="pl-10 text-sm rounded-xl py-2.5 border-slate-300 shadow-2xs"
                                    required
                                />
                            </div>
                            <p v-if="form.errors.lokasi_detail" class="text-xs text-red-500 font-medium pt-0.5">
                                {{ form.errors.lokasi_detail }}
                            </p>
                        </div>

                        <!-- Kronologi Kejadian -->
                        <div class="space-y-2">
                            <Label for="kronologi" class="text-xs font-bold text-slate-700 uppercase tracking-wider block">
                                Rincian Kronologi Kejadian <span class="text-red-500">*</span>
                            </Label>
                            <Textarea
                                id="kronologi"
                                rows="6"
                                v-model="form.kronologi"
                                placeholder="Jelaskan secara rinci waktu kejadian, fakta di lapangan, identitas terduga, aktivitas yang dicurigai, dan informasi pendukung lainnya..."
                                class="text-sm rounded-xl p-4 border-slate-300 shadow-2xs font-normal leading-relaxed focus:ring-2 focus:ring-slate-900"
                                required
                            />
                            <p v-if="form.errors.kronologi" class="text-xs text-red-500 font-medium pt-0.5">
                                {{ form.errors.kronologi }}
                            </p>
                        </div>

                    </CardContent>
                </Card>

            </div>

            <!-- RIGHT SIDEBAR PANEL (4 Cols) -->
            <div class="lg:col-span-4 space-y-6">

                <!-- Lampiran Lama (Existing Files) -->
                <Card v-if="laporan.lampiran_list && laporan.lampiran_list.length > 0" class="border-slate-200/80 shadow-2xs rounded-2xl bg-white overflow-hidden">
                    <CardHeader class="pb-3.5 border-b border-slate-100 px-5 py-4">
                        <CardTitle class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <Paperclip :size="15" class="text-slate-600" />
                            <span>Lampiran Sebelumnya</span>
                        </CardTitle>
                        <CardDescription class="text-xs text-slate-500">
                            File yang sudah terlampir pada laporan ini.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="p-4 space-y-2">
                        <a
                            v-for="file in laporan.lampiran_list"
                            :key="file.id"
                            :href="`/storage/${file.path}`"
                            target="_blank"
                            class="flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-xs text-slate-800 transition-colors group"
                        >
                            <div class="flex items-center gap-2 min-w-0">
                                <FileText :size="14" class="text-slate-400 shrink-0" />
                                <span class="truncate font-medium">{{ file.nama_file_asli }}</span>
                            </div>
                            <ExternalLink :size="13" class="text-slate-400 group-hover:text-slate-700 shrink-0 transition-colors" />
                        </a>
                    </CardContent>
                </Card>

                <!-- Upload Lampiran Tambahan Card -->
                <Card class="border-slate-200/80 shadow-2xs rounded-2xl bg-white overflow-hidden">
                    <CardHeader class="pb-3.5 border-b border-slate-100 px-5 py-4">
                        <CardTitle class="text-sm font-bold text-slate-900 flex items-center justify-between">
                            <span>Tambah Lampiran Susulan</span>
                            <span class="text-[10px] font-mono font-bold text-slate-600 bg-slate-200/70 px-2 py-0.5 rounded-full">
                                {{ form.lampiran.length }}/2 File
                            </span>
                        </CardTitle>
                        <CardDescription class="text-xs text-slate-500">
                            Upload file pendukung tambahan untuk perbaikan laporan.
                        </CardDescription>
                    </CardHeader>

                    <CardContent class="p-5 space-y-4">

                        <!-- Drag & Drop Upload Zone -->
                        <div
                            @dragover.prevent
                            @drop.prevent="handleFileDrop"
                            @click="fileInput?.click()"
                            class="border-2 border-dashed border-slate-200 hover:border-amber-400 rounded-xl p-6 text-center bg-slate-50/40 hover:bg-amber-50/20 transition-all cursor-pointer space-y-2.5 group"
                        >
                            <input
                                ref="fileInput"
                                type="file"
                                multiple
                                accept=".jpg,.jpeg,.png,.pdf"
                                class="hidden"
                                @change="handleFileSelect"
                            />
                            <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-700 group-hover:scale-110 group-hover:bg-amber-500 group-hover:text-white flex items-center justify-center mx-auto transition-all shadow-2xs">
                                <UploadCloud :size="20" />
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-800">
                                    Klik untuk pilih file atau drag &amp; drop
                                </p>
                                <p class="text-[10px] text-slate-400 mt-1">
                                    Format: JPG, PNG, PDF (Max 2MB/file)
                                </p>
                            </div>
                        </div>

                        <!-- Backend Validation Error Lampiran -->
                        <div v-if="form.errors.lampiran" class="pt-1">
                            <p class="text-xs text-red-500 font-medium flex items-center gap-1">
                                <AlertCircle :size="13" />
                                {{ form.errors.lampiran }}
                            </p>
                        </div>

                        <!-- Error Messages Lampiran (Client Side) -->
                        <div v-if="fileErrors.length > 0" class="space-y-1 pt-1">
                            <p v-for="err in fileErrors" :key="err" class="text-xs text-red-500 font-medium flex items-center gap-1">
                                <AlertCircle :size="13" />
                                {{ err }}
                            </p>
                        </div>

                        <!-- Selected File List Preview -->
                        <div v-if="form.lampiran.length > 0" class="space-y-2 pt-1">
                            <div
                                v-for="(file, i) in form.lampiran"
                                :key="i"
                                class="flex items-center justify-between p-3 rounded-xl border border-amber-200 bg-amber-50/50 text-xs"
                            >
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <FileText :size="16" class="text-amber-600 shrink-0" />
                                    <span class="font-medium text-slate-800 truncate">{{ file.name }}</span>
                                </div>
                                <button
                                    type="button"
                                    @click.stop="removeFile(i)"
                                    class="text-slate-400 hover:text-red-600 p-1 rounded-lg hover:bg-red-50 transition-colors"
                                >
                                    <X :size="14" />
                                </button>
                            </div>
                        </div>

                        <!-- Action Submit Box -->
                        <div class="pt-4 border-t border-slate-100 space-y-2.5">
                            <Button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 rounded-xl text-xs shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer"
                            >
                                <Send :size="14" />
                                <span>{{ form.processing ? 'Mengirim Perbaikan...' : 'Kirimkan Perbaikan Laporan' }}</span>
                            </Button>

                            <Link
                                href="/desa/laporan"
                                class="w-full flex items-center justify-center py-2 text-xs font-medium text-slate-500 hover:text-slate-900 transition-colors"
                            >
                                Batal &amp; Kembali ke Daftar
                            </Link>
                        </div>

                    </CardContent>
                </Card>

            </div>

        </form>

    </AppLayout>
</template>
