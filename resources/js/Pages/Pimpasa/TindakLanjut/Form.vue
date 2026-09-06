<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/components/layout/AppLayout.vue';
import {
    ArrowLeft,
    Building2,
    CheckCircle2,
    Send,
    FileText,
    ShieldAlert,
    FileCheck2,
    Hash,
    Layers,
    AlignLeft,
    CheckSquare2,
    ChevronRight
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

interface LaporanProps {
    id: number;
    kode_tiket: string;
    judul: string;
    kategori: string;
    status: string;
    tanggal_kejadian: string;
    lokasi_detail: string;
    desa?: { nama: string };
    tindak_lanjut?: {
        nomor_registrasi: string;
        seksi_penanggung_jawab: string;
        bentuk_intervensi: string;
        ringkasan_hasil: string;
        status_akhir: string;
    } | null;
}

const props = defineProps<{
    laporan: LaporanProps;
}>();

const backHref = '/pimpasa/tindak-lanjut';
const backLabel = 'Kembali ke Disposisi & Tindak Lanjut';

const form = useForm({
    nomor_registrasi: props.laporan.tindak_lanjut?.nomor_registrasi || '',
    seksi_penanggung_jawab: props.laporan.tindak_lanjut?.seksi_penanggung_jawab || 'inteldak',
    bentuk_intervensi: props.laporan.tindak_lanjut?.bentuk_intervensi || 'pemeriksaan_lapangan',
    ringkasan_hasil: props.laporan.tindak_lanjut?.ringkasan_hasil || '',
    is_selesai: props.laporan.tindak_lanjut?.status_akhir === 'selesai' || true,
});

const submitTindakLanjut = () => {
    form.post(`/pimpasa/tindak-lanjut/${props.laporan.id}`, {
        onSuccess: () => {
            notify.success('Tindak Lanjut Disimpan!', {
                description: `Hasil penanganan UPT untuk Kode Tiket ${props.laporan.kode_tiket} berhasil diperbarui.`
            });
        },
    });
};
</script>

<template>
    <AppLayout :title="`Tindak Lanjut UPT — ${laporan.kode_tiket}`">
        <div class="space-y-6 font-sans">
            
            <!-- TOP NAVIGATION & ACTION BAR -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                
                <!-- Breadcrumb & Back Link -->
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <Link :href="backHref">
                        <Button variant="outline" class="h-9 px-3 text-xs font-semibold rounded-xl border-slate-200 hover:bg-slate-100 text-slate-700 flex items-center gap-1.5 shadow-2xs transition-all cursor-pointer shrink-0">
                            <ArrowLeft :size="15" />
                            <span>{{ backLabel }}</span>
                        </Button>
                    </Link>
                    <div class="flex items-center gap-1.5 text-xs text-slate-400 min-w-0">
                        <ChevronRight :size="14" class="shrink-0" />
                        <span class="text-slate-700 font-bold truncate">
                            Form Tindak Lanjut UPT #{{ laporan.kode_tiket }}
                        </span>
                    </div>
                </div>

            </div>

            <!-- Form Content Grid (8 Cols Left, 4 Cols Right) -->
            <form @submit.prevent="submitTindakLanjut" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                
                <!-- LEFT MAIN PANEL (8 Cols) -->
                <div class="lg:col-span-8 space-y-6">
                    
                    <!-- SECTION 1: Form Penanganan Lapangan -->
                    <Card class="border-slate-200/80 shadow-2xs rounded-2xl bg-white overflow-hidden">
                        <CardHeader class="pb-3.5 border-b border-slate-100 px-6 py-4">
                            <CardTitle class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <Building2 :size="16" class="text-primary" />
                                Form Penanganan Lapangan UPT Imigrasi
                            </CardTitle>
                            <CardDescription class="text-xs text-slate-500">
                                Catat nomor registrasi UPT, seksi penanggung jawab, dan ringkasan berita acara penanganan.
                            </CardDescription>
                        </CardHeader>

                        <CardContent class="p-6 space-y-6">
                            
                            <!-- Nomor Registrasi UPT -->
                            <div class="space-y-2">
                                <Label for="nomor_registrasi" class="text-xs font-bold text-slate-700 uppercase tracking-wider block">
                                    Nomor Registrasi Penanganan UPT (Opsional / Auto-Generated)
                                </Label>
                                <div class="relative">
                                    <Hash :size="16" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
                                    <Input
                                        id="nomor_registrasi"
                                        type="text"
                                        v-model="form.nomor_registrasi"
                                        placeholder="Contoh: REG-UPT-2026-0012 (Kosongkan untuk pembuatan otomatis)"
                                        class="pl-10 font-mono text-xs rounded-xl h-10 border-slate-300 shadow-2xs focus:ring-2 focus:ring-primary"
                                    />
                                </div>
                            </div>

                            <!-- Grid 2 Kolom: Seksi PJ & Bentuk Intervensi -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                
                                <!-- Seksi Penanggung Jawab -->
                                <div class="space-y-2">
                                    <Label class="text-xs font-bold text-slate-700 uppercase tracking-wider block">
                                        Seksi Penanggung Jawab UPT <span class="text-red-500">*</span>
                                    </Label>
                                    <Select v-model="form.seksi_penanggung_jawab">
                                        <SelectTrigger class="w-full h-10 py-2 px-3 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-900 shadow-2xs focus:ring-2 focus:ring-primary text-left justify-between items-center">
                                            <SelectValue placeholder="-- Pilih Seksi PJ --" />
                                        </SelectTrigger>
                                        <SelectContent class="rounded-xl border-slate-200 shadow-lg bg-white">
                                            <SelectItem value="inteldak" class="text-xs rounded-lg font-medium">Inteldakim (Intelijen dan Penindakan Keimigrasian)</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <!-- Bentuk Intervensi Lapangan -->
                                <div class="space-y-2">
                                    <Label class="text-xs font-bold text-slate-700 uppercase tracking-wider block">
                                        Bentuk Intervensi Lapangan <span class="text-red-500">*</span>
                                    </Label>
                                    <Select v-model="form.bentuk_intervensi">
                                        <SelectTrigger class="w-full h-10 py-2 px-3 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-900 shadow-2xs focus:ring-2 focus:ring-primary text-left justify-between items-center">
                                            <SelectValue placeholder="-- Pilih Bentuk Intervensi --" />
                                        </SelectTrigger>
                                        <SelectContent class="rounded-xl border-slate-200 shadow-lg bg-white">
                                            <SelectItem value="pemeriksaan_lapangan" class="text-xs rounded-lg font-medium">Pemeriksaan & Inspeksi Lapangan</SelectItem>
                                            <SelectItem value="sosialisasi" class="text-xs rounded-lg font-medium">Sosialisasi & Penyuluhan Edukasi</SelectItem>
                                            <SelectItem value="operasi_gabungan" class="text-xs rounded-lg font-medium">Operasi Gabungan Keimigrasian</SelectItem>
                                            <SelectItem value="proses_hukum" class="text-xs rounded-lg font-medium">Proses Hukum & Projustitia</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                            </div>

                            <!-- Ringkasan Hasil Berita Acara -->
                            <div class="space-y-2">
                                <Label for="ringkasan_hasil" class="text-xs font-bold text-slate-700 uppercase tracking-wider block">
                                    Ringkasan Berita Acara & Hasil Lapangan <span class="text-red-500">*</span>
                                </Label>
                                <Textarea
                                    id="ringkasan_hasil"
                                    rows="6"
                                    v-model="form.ringkasan_hasil"
                                    placeholder="Jelaskan secara rinci tindakan petugas di lapangan, hasil pemeriksaan dokumen, dan kesimpulan berita acara..."
                                    class="text-xs rounded-xl p-4 border-slate-300 shadow-2xs font-normal leading-relaxed focus:ring-2 focus:ring-primary"
                                    required
                                />
                                <p v-if="form.errors.ringkasan_hasil" class="text-xs text-red-500 font-medium pt-0.5">
                                    {{ form.errors.ringkasan_hasil }}
                                </p>
                            </div>

                        </CardContent>
                    </Card>

                </div>

                <!-- RIGHT SIDEBAR PANEL (4 Cols) -->
                <div class="lg:col-span-4 space-y-6">
                    
                    <!-- Status Penyelesaian Card -->
                    <Card class="border-slate-200/80 shadow-2xs rounded-2xl bg-white sticky top-20 overflow-hidden">
                        <CardHeader class="pb-3.5 border-b border-slate-100 px-5 py-4">
                            <CardTitle class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <FileCheck2 :size="16" class="text-emerald-600" />
                                <span>Status Penyelesaian Tiket</span>
                            </CardTitle>
                            <CardDescription class="text-xs text-slate-500">
                                Konfirmasi status akhir dari hasil penanganan UPT.
                            </CardDescription>
                        </CardHeader>

                        <CardContent class="p-5 space-y-5">
                            
                            <div class="p-4 rounded-xl bg-emerald-50/80 border border-emerald-200/80 text-emerald-950 space-y-2">
                                <label class="flex items-start gap-2.5 cursor-pointer font-bold text-xs text-emerald-900 select-none">
                                    <input
                                        type="checkbox"
                                        v-model="form.is_selesai"
                                        class="rounded border-emerald-400 text-emerald-600 focus:ring-emerald-500 mt-0.5"
                                    />
                                    <span>Tandai Laporan Selesai 100%</span>
                                </label>
                                <p class="text-[11px] text-emerald-800 leading-relaxed font-normal">
                                    Mengonfirmasi bahwa seluruh prosedur penanganan lapangan telah dilaksanakan dan diarsipkan di UPT.
                                </p>
                            </div>

                            <!-- Action Submit Box -->
                            <div class="pt-4 border-t border-slate-100 space-y-2.5">
                                <Button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="w-full bg-primary hover:bg-[#04407D] text-primary-foreground font-bold py-3 rounded-xl text-xs shadow-xs transition-all flex items-center justify-center gap-2 cursor-pointer"
                                >
                                    <Send :size="15" />
                                    <span v-if="form.processing">Menyimpan Penanganan...</span>
                                    <span v-else>Simpan Hasil Penanganan UPT</span>
                                </Button>

                                <Link
                                    :href="backHref"
                                    class="w-full flex items-center justify-center py-2 text-xs font-semibold text-slate-500 hover:text-slate-900 transition-colors"
                                >
                                    Batal & Kembali
                                </Link>
                            </div>

                        </CardContent>
                    </Card>

                </div>

            </form>

        </div>
    </AppLayout>
</template>


