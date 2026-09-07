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
    ShieldAlert,
    Calendar,
    Users,
    AlignLeft,
    Tag,
    ArrowLeft,
    CheckCircle2
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

const props = defineProps<{
    kategoriOptions: Array<{ id: number; value: number; label: string; kode?: string }>;
}>();

const form = useForm({
    kategori_id: undefined as number | undefined,
    judul: '',
    tanggal_kejadian: '',
    lokasi_detail: '',
    kronologi: '',
    estimasi_jumlah_orang: undefined as number | undefined,
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

import { notify } from '@/lib/toast';

const addFiles = (files: File[]) => {
    fileErrors.value = [];
    const validFiles: File[] = [];

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

        if (form.lampiran.length + validFiles.length >= 2) {
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

    if (!form.lampiran || form.lampiran.length === 0) {
        notify.error('Wajib Upload Dokumen', {
            description: 'Silakan lampirkan minimal 1 berkas foto/dokumen bukti pendukung.'
        });
        return;
    }

    notify.info('Mengirim Laporan...', {
        description: 'Laporan Anda sedang diproses dan notifikasi asinkron sedang dikirimkan ke Petugas PIMPASA UPT & Kanwil.'
    });

    form.post('/desa/laporan');
};
</script>

<template>
    <AppLayout title="Form Pengajuan Laporan Baru">
        

        <!-- Form Content Grid (2/3 & 1/3 Spacing) -->
        <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- LEFT MAIN PANEL (8 Cols) -->
            <div class="lg:col-span-8 space-y-6">
                
                <!-- SECTION 1: Detail Pelaporan -->
                <Card class="border-slate-200/80 shadow-2xs rounded-2xl bg-white overflow-hidden">
                    <CardHeader class="pb-3.5 border-b border-slate-100 px-6 py-4">
                        <CardTitle class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <Tag :size="16" class="text-slate-700" />
                            Klasifikasi & Judul Laporan
                        </CardTitle>
                        <CardDescription class="text-xs text-slate-500">
                            Pilih kategori yang tepat untuk mempercepat verifikasi oleh Petugas PIMPASA.
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
                            Lokasi & Rincian Kronologi Kejadian
                        </CardTitle>
                        <CardDescription class="text-xs text-slate-500">
                            Fakta lokasi dan kronologi rinci di lapangan.
                        </CardDescription>
                    </CardHeader>

                    <CardContent class="p-6 space-y-6">
                        
                        <!-- Grid 2 Kolom: Tanggal Kejadian & Estimasi Jumlah Orang -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <!-- Tanggal Kejadian -->
                            <div class="space-y-2">
                                <Label for="tanggal_kejadian" class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                    <Calendar :size="13" class="text-slate-500" />
                                    <span>Tanggal & Waktu</span>
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
                
                <!-- Upload Dropzone Card -->
                <Card class="border-slate-200/80 shadow-2xs rounded-2xl bg-white overflow-hidden">
                    <CardHeader class="pb-3.5 border-b border-slate-100 px-5 py-4">
                        <CardTitle class="text-sm font-bold text-slate-900 flex items-center justify-between">
                            <span>Upload Lampiran Berkas <span class="text-red-500">*</span></span>
                            <span class="text-[10px] font-mono font-bold text-slate-600 bg-slate-200/70 px-2 py-0.5 rounded-full">
                                {{ form.lampiran.length }}/2 File
                            </span>
                        </CardTitle>
                        <CardDescription class="text-xs text-slate-500">
                            Wajib melampirkan minimal 1 berkas dokumen/foto bukti pendukung.
                        </CardDescription>
                    </CardHeader>

                    <CardContent class="p-5 space-y-4">
                        
                        <!-- Drag & Drop Upload Zone -->
                        <div
                            @dragover.prevent
                            @drop.prevent="handleFileDrop"
                            @click="fileInput?.click()"
                            class="border-2 border-dashed border-slate-200 hover:border-slate-400 rounded-xl p-6 text-center bg-slate-50/40 hover:bg-slate-50 transition-all cursor-pointer space-y-2.5 group"
                        >
                            <input
                                ref="fileInput"
                                type="file"
                                multiple
                                accept=".jpg,.jpeg,.png,.pdf"
                                class="hidden"
                                @change="handleFileSelect"
                            />
                            <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-600 group-hover:scale-110 group-hover:bg-slate-900 group-hover:text-white flex items-center justify-center mx-auto transition-all shadow-2xs">
                                <UploadCloud :size="20" />
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-800">
                                    Klik untuk pilih file atau drag & drop
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
                                class="flex items-center justify-between p-3 rounded-xl border border-slate-200 bg-slate-50/80 text-xs"
                            >
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <FileText :size="16" class="text-slate-500 shrink-0" />
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
                                class="w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold py-3 rounded-xl text-xs shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer"
                            >
                                <Send :size="14" />
                                <span>{{ form.processing ? 'Mengirim Laporan...' : 'Kirim Laporan Sekarang' }}</span>
                            </Button>

                            <Link
                                href="/desa/laporan"
                                class="w-full flex items-center justify-center py-2 text-xs font-medium text-slate-500 hover:text-slate-900 transition-colors"
                            >
                                Batal & Kembali ke Daftar
                            </Link>
                        </div>

                    </CardContent>
                </Card>

                <!-- Information & Confidentiality Alert Card -->
                <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-100 text-blue-950 space-y-2">
                    <div class="flex items-center gap-2 font-bold text-xs">
                        <ShieldAlert :size="16" class="text-blue-600 shrink-0" />
                        <span>Kerahasiaan Pelapor Terjamin</span>
                    </div>
                    <p class="text-[11px] text-blue-800 leading-relaxed">
                        Data pelaporan Anda terlindungi dan hanya diproses oleh UPT Imigrasi serta Kantor Wilayah Ditjen Imigrasi Sumatera Utara.
                    </p>
                </div>

            </div>

        </form>

    </AppLayout>
</template>

