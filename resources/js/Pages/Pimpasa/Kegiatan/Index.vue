<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/components/layout/AppLayout.vue';
import {
    Calendar,
    Users,
    MapPin,
    Plus,
    Building2,
    FileText,
    FileSpreadsheet,
    Search,
    RotateCcw,
    Eye,
    Edit2,
    Trash2,
    AlertCircle,
    Paperclip,
    ExternalLink
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import Combobox from '@/components/ui/combobox/Combobox.vue';
import ConfirmDeleteModal from '@/components/common/ConfirmDeleteModal.vue';
import { notify } from '@/lib/toast';

interface LampiranItem {
    id: number;
    file_name: string;
    file_path: string;
    mime_type: string;
}

interface KegiatanItem {
    id: number;
    pimpasa_id?: number;
    desa_id: number;
    judul: string;
    jenis_pembinaan: string;
    tanggal: string;
    jumlah_peserta: number;
    status: string;
    lokasi?: string;
    ringkasan_materi: string;
    desa_nama?: string;
    petugas_nama?: string;
    lampiran?: LampiranItem[];
}

interface DesaOption {
    id: number;
    nama: string;
    kegiatan_pembinaan_list_count?: number;
}

interface StatsData {
    total_kegiatan: number;
    total_peserta: number;
    total_desa: number;
    count_selesai?: number;
    count_terjadwal?: number;
    count_dibatalkan?: number;
    count_penyuluhan?: number;
    count_simpatik?: number;
    count_pemuda?: number;
    count_tppo?: number;
    count_inspeksi?: number;
}

const props = defineProps<{
    kegiatanList: KegiatanItem[];
    desaList: DesaOption[];
    stats: StatsData;
    filters: {
        search?: string;
        desa_id?: string;
        jenis_pembinaan?: string;
        status?: string;
    };
}>();

// Search & Filter state
const search = ref(props.filters.search || '');
const selectedDesa = ref(props.filters.desa_id || 'all');
const selectedJenis = ref(props.filters.jenis_pembinaan || 'all');
const selectedStatus = ref(props.filters.status || 'all');

const onDesaChange = (val: any) => {
    selectedDesa.value = String(val || 'all');
    applyFilter();
};

const onJenisChange = (val: any) => {
    selectedJenis.value = String(val || 'all');
    applyFilter();
};

const desaComboboxOptions = computed(() => [
    { value: 'all', label: `Semua Desa Binaan (${props.stats.total_kegiatan ?? props.kegiatanList.length})` },
    ...(props.desaList || []).map(d => ({
        value: String(d.id),
        label: `${d.nama} (${d.kegiatan_pembinaan_list_count ?? 0})`
    }))
]);

const jenisComboboxOptions = computed(() => [
    { value: 'all', label: `Semua Jenis (${props.stats.total_kegiatan ?? props.kegiatanList.length})` },
    { value: 'Penyuluhan Hukum', label: `Penyuluhan Hukum (${props.stats.count_penyuluhan ?? 0})` },
    { value: 'Layanan Simpatik', label: `Layanan Simpatik (${props.stats.count_simpatik ?? 0})` },
    { value: 'Pembinaan Pemuda', label: `Pembinaan Pemuda (${props.stats.count_pemuda ?? 0})` },
    { value: 'Sosialisasi TPPO', label: `Sosialisasi TPPO (${props.stats.count_tppo ?? 0})` },
    { value: 'Inspeksi Lapangan', label: `Inspeksi Lapangan (${props.stats.count_inspeksi ?? 0})` },
]);

// Pagination (10 Data Per Halaman)
const currentPage = ref(1);
const perPage = ref(10);

const paginatedKegiatanList = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    const end = start + perPage.value;
    return props.kegiatanList.slice(start, end);
});

const totalPages = computed(() => Math.ceil(props.kegiatanList.length / perPage.value) || 1);

const prevPage = () => {
    if (currentPage.value > 1) currentPage.value--;
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) currentPage.value++;
};

const applyFilter = () => {
    currentPage.value = 1;
    router.get('/pimpasa/kegiatan', {
        search: search.value || undefined,
        desa_id: selectedDesa.value !== 'all' ? selectedDesa.value : undefined,
        jenis_pembinaan: selectedJenis.value !== 'all' ? selectedJenis.value : undefined,
        status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
    }, { preserveState: true, replace: true });
};

const resetFilter = () => {
    search.value = '';
    selectedDesa.value = 'all';
    selectedJenis.value = 'all';
    selectedStatus.value = 'all';
    currentPage.value = 1;
    applyFilter();
};

// Export Handlers
const handleExportPdf = () => {
    const params = new URLSearchParams();
    if (selectedDesa.value !== 'all') params.append('desa_id', selectedDesa.value);
    window.open(`/pimpasa/kegiatan/export-pdf?${params.toString()}`, '_blank');
};

const handleExportExcel = () => {
    const params = new URLSearchParams();
    if (selectedDesa.value !== 'all') params.append('desa_id', selectedDesa.value);
    window.open(`/pimpasa/kegiatan/export-excel?${params.toString()}`, '_blank');
};

// Modals State
const isFormModalOpen = ref(false);
const isEditMode = ref(false);
const editingId = ref<number | null>(null);

const isDetailModalOpen = ref(false);
const selectedDetail = ref<KegiatanItem | null>(null);

const isDeleteModalOpen = ref(false);
const itemToDelete = ref<KegiatanItem | null>(null);
const isDeleting = ref(false);

// Form Management
const form = useForm({
    judul: '',
    desa_id: '' as string | number,
    jenis_pembinaan: '',
    tanggal: new Date().toISOString().split('T')[0],
    jumlah_peserta: '' as string | number,
    status: '',
    lokasi: '',
    ringkasan_materi: '',
    lampiran_files: [] as File[],
});

const openCreateModal = () => {
    isEditMode.value = false;
    editingId.value = null;
    form.reset();
    form.clearErrors();
    form.judul = '';
    form.desa_id = '';
    form.jenis_pembinaan = '';
    form.tanggal = new Date().toISOString().split('T')[0];
    form.jumlah_peserta = '';
    form.status = '';
    form.lokasi = '';
    form.ringkasan_materi = '';
    form.lampiran_files = [];
    isFormModalOpen.value = true;
};

const openEditModal = (item: KegiatanItem) => {
    isEditMode.value = true;
    editingId.value = item.id;
    form.clearErrors();
    form.judul = item.judul;
    form.desa_id = item.desa_id;
    form.jenis_pembinaan = item.jenis_pembinaan;
    form.tanggal = item.tanggal;
    form.jumlah_peserta = item.jumlah_peserta;
    form.status = item.status;
    form.lokasi = item.lokasi || '';
    form.ringkasan_materi = item.ringkasan_materi;
    form.lampiran_files = [];
    isFormModalOpen.value = true;
};

const submitForm = () => {
    if (isEditMode.value && editingId.value) {
        form.post(`/pimpasa/kegiatan/${editingId.value}`, {
            headers: { 'X-HTTP-Method-Override': 'PUT' },
            onSuccess: () => {
                isFormModalOpen.value = false;
                notify.success('Berhasil Diperbarui', { description: 'Data agenda kegiatan pembinaan telah diperbarui.' });
            },
            onError: (errs) => {
                notify.error('Gagal Menyimpan', { description: Object.values(errs)[0] || 'Periksa kembali isian form.' });
            }
        });
    } else {
        form.post('/pimpasa/kegiatan', {
            onSuccess: () => {
                isFormModalOpen.value = false;
                notify.success('Berhasil Ditambahkan', { description: 'Agenda kegiatan pembinaan baru berhasil disimpan.' });
            },
            onError: (errs) => {
                notify.error('Gagal Menambahkan', { description: Object.values(errs)[0] || 'Periksa kembali isian form.' });
            }
        });
    }
};

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files) {
        const files = Array.from(target.files);
        const oversized = files.filter(f => f.size > 2 * 1024 * 1024);
        if (oversized.length > 0) {
            notify.error('File Terlalu Besar', {
                description: `Ukuran file "${oversized[0].name}" melebihi batas maksimal 2MB.`
            });
            target.value = '';
            form.lampiran_files = [];
            return;
        }
        form.lampiran_files = files;
    }
};

const openDetailModal = (item: KegiatanItem) => {
    selectedDetail.value = item;
    isDetailModalOpen.value = true;
};

const confirmDelete = (item: KegiatanItem) => {
    itemToDelete.value = item;
    isDeleteModalOpen.value = true;
};

const executeDelete = () => {
    if (!itemToDelete.value) return;
    isDeleting.value = true;
    router.delete(`/pimpasa/kegiatan/${itemToDelete.value.id}`, {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            isDeleting.value = false;
            itemToDelete.value = null;
            notify.success('Berhasil Dihapus', { description: 'Agenda kegiatan pembinaan berhasil dihapus.' });
        },
        onError: () => {
            isDeleting.value = false;
            notify.error('Gagal Menghapus', { description: 'Terjadi kesalahan saat menghapus data kegiatan.' });
        }
    });
};

const getStatusBadge = (status: string) => {
    switch (status.toLowerCase()) {
        case 'selesai':
            return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        case 'terjadwal':
            return 'bg-blue-50 text-blue-700 border-blue-200';
        case 'dibatalkan':
            return 'bg-rose-50 text-rose-700 border-rose-200';
        default:
            return 'bg-slate-50 text-slate-700 border-slate-200';
    }
};
</script>

<template>
    <AppLayout title="Kegiatan Pembinaan Desa Binaan Imigrasi">
        <div class="space-y-6 font-sans">
            
            <!-- Quick Metric KPI Cards (Matching Presisi Daftar Desa Binaan) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Card 1: Total Agenda Kegiatan -->
                <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group hover:border-slate-400 transition-all">
                    <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-slate-300/80 transition-colors">
                        <Calendar :size="105" stroke-width="1.0" />
                    </div>
                    <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                        <div class="space-y-0.5 pr-12">
                            <span class="text-xs font-semibold text-slate-500 block leading-tight">Total Agenda Kegiatan</span>
                            <div class="text-3xl font-bold text-slate-900 font-sans tabular-nums tracking-tight leading-none pt-1">
                                {{ stats.total_kegiatan }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[11px] text-slate-500 font-medium">Agenda pembinaan keimigrasian</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Card 2: Total Peserta Terbina -->
                <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group hover:border-slate-400 transition-all">
                    <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-slate-300/80 transition-colors">
                        <Users :size="105" stroke-width="1.0" />
                    </div>
                    <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                        <div class="space-y-0.5 pr-12">
                            <span class="text-xs font-semibold text-slate-500 block leading-tight">Total Peserta Terbina</span>
                            <div class="text-3xl font-bold text-slate-900 font-sans tabular-nums tracking-tight leading-none pt-1">
                                {{ stats.total_peserta }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[11px] text-slate-500 font-medium">Masyarakat desa teredukasi</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Card 3: Cakupan Desa Binaan -->
                <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group hover:border-slate-400 transition-all">
                    <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-slate-300/80 transition-colors">
                        <Building2 :size="105" stroke-width="1.0" />
                    </div>
                    <CardContent class="p-4 sm:p-5 flex flex-col justify-between h-full min-h-[92px] relative z-10">
                        <div class="space-y-0.5 pr-12">
                            <span class="text-xs font-semibold text-slate-500 block leading-tight">Cakupan Desa Binaan</span>
                            <div class="text-3xl font-bold text-slate-900 font-sans tabular-nums tracking-tight leading-none pt-1">
                                {{ stats.total_desa }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[11px] text-slate-500 font-medium">Desa sasaran di wilayah UPT</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Card Utama Filter & Actions -->
            <Card class="border-slate-200/80 shadow-2xs rounded-2xl bg-white">
                <CardHeader class="pb-4 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div class="space-y-0.5">
                        <CardTitle class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <Calendar :size="18" class="text-primary shrink-0" />
                            <span>Agenda & Laporan Kegiatan Pembinaan Desa</span>
                        </CardTitle>
                        <CardDescription class="text-xs text-slate-500">
                            Pencatatan kegiatan proaktif (Penyuluhan TPPO, Paspor Simpatik Masuk Desa, & Pembinaan Karang Taruna).
                        </CardDescription>
                    </div>

                    <div class="flex items-center gap-2 shrink-0 flex-wrap w-full lg:w-auto">
                        <Button
                            @click="handleExportExcel"
                            variant="outline"
                            size="sm"
                            class="flex-1 sm:flex-none justify-center h-9 px-3.5 border-emerald-200 bg-emerald-50/70 hover:bg-emerald-100/90 text-emerald-800 font-semibold rounded-md text-xs flex items-center gap-2 shadow-2xs transition-all cursor-pointer"
                        >
                            <FileSpreadsheet :size="15" class="text-emerald-700 shrink-0" />
                            <span>Export Excel</span>
                        </Button>

                        <Button
                            @click="handleExportPdf"
                            variant="outline"
                            size="sm"
                            class="flex-1 sm:flex-none justify-center h-9 px-3.5 border-rose-200 bg-rose-50/70 hover:bg-rose-100/90 text-rose-800 font-semibold rounded-md text-xs flex items-center gap-2 shadow-2xs transition-all cursor-pointer"
                        >
                            <FileText :size="15" class="text-rose-700 shrink-0" />
                            <span>Export PDF</span>
                        </Button>
                        <Button
                            class="w-full sm:w-auto justify-center h-9 px-4 bg-primary hover:bg-[#04407D] text-primary-foreground rounded-md text-xs font-semibold shadow-xs transition-all gap-1.5 cursor-pointer"
                            @click="openCreateModal"
                        >
                            <Plus :size="15" />
                            <span>Tambah Agenda Pembinaan</span>
                        </Button>
                    </div>
                </CardHeader>

                <!-- Filter Controls Toolbar Style (Worklist & Disposisi Presisi Style) -->
                <div class="p-4 bg-slate-50/50 border-b border-slate-100 grid grid-cols-1 sm:grid-cols-12 gap-3">
                    
                    <!-- Search Input -->
                    <div class="sm:col-span-6 lg:col-span-3 relative">
                        <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" :size="15" />
                        <Input
                            v-model="search"
                            type="text"
                            placeholder="Cari judul, jenis, materi..."
                            class="pl-9 pr-24 h-9 text-xs rounded-md border-slate-200/90 shadow-2xs bg-white font-sans"
                            @keyup.enter="applyFilter"
                        />
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 font-medium pointer-events-none select-none hidden sm:flex items-center gap-1">
                            <kbd class="bg-slate-100 border border-slate-300 text-slate-500 text-[9px] font-sans font-semibold px-1.5 py-0.5 rounded">Enter</kbd>
                        </span>
                    </div>

                    <!-- Select Filter Status -->
                    <div class="sm:col-span-6 lg:col-span-2">
                        <Select v-model="selectedStatus" @update:model-value="applyFilter">
                            <SelectTrigger class="w-full h-9 text-xs font-semibold text-slate-800 border-slate-200/90 rounded-md bg-white shadow-2xs">
                                <SelectValue placeholder="Status" />
                            </SelectTrigger>
                            <SelectContent class="bg-white rounded-lg shadow-xl border-slate-200">
                                <SelectGroup>
                                    <SelectItem value="all" class="text-xs font-semibold text-slate-900">Semua Status ({{ stats.total_kegiatan }})</SelectItem>
                                    <SelectItem value="selesai" class="text-xs font-semibold text-emerald-700">SELESAI ({{ stats.count_selesai ?? 0 }})</SelectItem>
                                    <SelectItem value="terjadwal" class="text-xs font-semibold text-blue-700">TERJADWAL ({{ stats.count_terjadwal ?? 0 }})</SelectItem>
                                    <SelectItem value="dibatalkan" class="text-xs font-semibold text-rose-700">DIBATALKAN ({{ stats.count_dibatalkan ?? 0 }})</SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Combobox Filter Desa Binaan (Searchable) -->
                    <div class="sm:col-span-6 lg:col-span-3">
                        <Combobox
                            :options="desaComboboxOptions"
                            :model-value="selectedDesa"
                            @update:model-value="onDesaChange"
                            placeholder="Pilih Desa Binaan..."
                            search-placeholder="Cari desa binaan..."
                            class="w-full h-9 bg-white border-slate-200/90 text-xs font-semibold text-slate-800 shadow-2xs"
                        />
                    </div>

                    <!-- Combobox Filter Jenis Pembinaan (Searchable) -->
                    <div class="sm:col-span-6 lg:col-span-3">
                        <Combobox
                            :options="jenisComboboxOptions"
                            :model-value="selectedJenis"
                            @update:model-value="onJenisChange"
                            placeholder="Jenis Pembinaan..."
                            search-placeholder="Cari jenis pembinaan..."
                            class="w-full h-9 bg-white border-slate-200/90 text-xs font-semibold text-slate-800 shadow-2xs"
                        />
                    </div>

                    <!-- Reset Filter Button -->
                    <div class="sm:col-span-12 lg:col-span-1 flex items-center">
                        <button
                            type="button"
                            @click="resetFilter"
                            class="w-full h-9 px-2.5 bg-white border border-slate-200/90 rounded-md text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-all flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer"
                        >
                            <RotateCcw :size="13" class="text-slate-400 shrink-0" />
                            <span>Reset</span>
                        </button>
                    </div>

                </div>

                <!-- Grid List Card Agenda Kegiatan -->
                <CardContent class="pt-6">
                    <div v-if="kegiatanList.length === 0" class="py-12 text-center text-slate-400 font-medium">
                        <AlertCircle class="mx-auto mb-2 text-slate-300" :size="32" />
                        <p class="text-sm">Tidak ada data kegiatan pembinaan yang ditemukan.</p>
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div
                            v-for="item in paginatedKegiatanList"
                            :key="item.id"
                            class="p-5 rounded-xl border border-slate-200/80 bg-white hover:border-slate-300 hover:shadow-xs transition-all space-y-3 shadow-2xs flex flex-col justify-between"
                        >
                            <div class="space-y-3">
                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                    <span class="text-[11px] font-bold text-slate-700 bg-slate-100 px-2.5 py-0.5 rounded-full border border-slate-200/80">
                                        {{ item.jenis_pembinaan }}
                                    </span>
                                    <span :class="['px-2.5 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider', getStatusBadge(item.status)]">
                                        {{ item.status }}
                                    </span>
                                </div>

                                <h4 class="font-bold text-slate-900 text-sm leading-snug line-clamp-2 hover:text-primary cursor-pointer" @click="openDetailModal(item)">
                                    {{ item.judul }}
                                </h4>

                                <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">
                                    {{ item.ringkasan_materi }}
                                </p>
                            </div>

                            <div class="pt-3 border-t border-slate-100 space-y-2">
                                <div class="grid grid-cols-2 gap-2 text-xs text-slate-600">
                                    <div class="flex items-center gap-1.5">
                                        <MapPin :size="14" class="text-slate-400 shrink-0" />
                                        <span class="truncate font-semibold text-slate-800">{{ item.desa_nama }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <Users :size="14" class="text-blue-600 shrink-0" />
                                        <span class="font-medium tabular-nums">{{ item.jumlah_peserta }} Peserta</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <Calendar :size="14" class="text-slate-400 shrink-0" />
                                        <span class="font-medium tabular-nums text-slate-600">{{ item.tanggal }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <Building2 :size="14" class="text-slate-400 shrink-0" />
                                        <span class="truncate font-medium text-slate-500">{{ item.lokasi || '-' }}</span>
                                    </div>
                                </div>

                                <!-- Action Buttons Style Kanwil MasterData Tab -->
                                <div class="flex items-center justify-between pt-2 border-t border-slate-50">
                                    <span class="text-[10px] text-slate-400 font-medium">Oleh: {{ item.petugas_nama }}</span>
                                    <div class="flex items-center gap-1.5">
                                        <button
                                            @click="openDetailModal(item)"
                                            class="w-7 h-7 rounded-md bg-slate-100 text-slate-700 border border-slate-200/80 flex items-center justify-center hover:bg-slate-200 transition-colors"
                                            title="Lihat Detail"
                                        >
                                            <Eye :size="13" />
                                        </button>
                                        <button
                                            @click="openEditModal(item)"
                                            class="w-7 h-7 rounded-md bg-blue-50 text-blue-700 border border-blue-200/80 flex items-center justify-center hover:bg-blue-100 transition-colors"
                                            title="Edit Kegiatan"
                                        >
                                            <Edit2 :size="13" />
                                        </button>
                                        <button
                                            @click="confirmDelete(item)"
                                            class="w-7 h-7 rounded-md bg-red-50 text-red-600 border border-red-200/80 flex items-center justify-center hover:bg-red-100 transition-colors"
                                            title="Hapus Kegiatan"
                                        >
                                            <Trash2 :size="13" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>

                <!-- Integrated Pagination Bar (10 Data Per Halaman) -->
                <div v-if="kegiatanList.length > 0" class="p-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
                    <div>
                        Menampilkan <span class="font-bold text-slate-900">{{ (currentPage - 1) * perPage + 1 }}</span>
                        sampai <span class="font-bold text-slate-900">{{ Math.min(currentPage * perPage, kegiatanList.length) }}</span>
                        dari <span class="font-bold text-slate-900">{{ kegiatanList.length }}</span> Data Kegiatan Pembinaan
                    </div>

                    <div v-if="totalPages > 1" class="flex items-center gap-1.5">
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="currentPage === 1"
                            @click="prevPage"
                            class="h-8 px-2.5 text-xs font-semibold rounded-md border-slate-200"
                        >
                            Sebelumnya
                        </Button>

                        <span class="px-2 font-semibold text-slate-700">Halaman {{ currentPage }} dari {{ totalPages }}</span>

                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="currentPage === totalPages"
                            @click="nextPage"
                            class="h-8 px-2.5 text-xs font-semibold rounded-md border-slate-200"
                        >
                            Selanjutnya
                        </Button>
                    </div>
                </div>
            </Card>

            <!-- MODAL DIALOG SHADCN CRUD (PERFECTLY PARALLEL 2-COLUMN LAYOUT) -->
            <Dialog v-model:open="isFormModalOpen">
                <DialogContent class="sm:max-w-3xl bg-white rounded-xl p-6 sm:p-7 shadow-2xl space-y-0 font-sans border border-slate-100 max-h-[90vh] overflow-y-auto">
                    
                    <DialogHeader class="-mx-6 sm:-mx-7 px-6 sm:px-7 pb-3 border-b border-slate-100 space-y-1 text-left">
                        <DialogTitle class="text-base font-bold text-slate-900 flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-md bg-slate-100 text-slate-700 flex items-center justify-center border border-slate-200/80 shrink-0">
                                <Calendar :size="17" />
                            </div>
                            <span>{{ isEditMode ? 'Edit Agenda Pembinaan' : 'Tambah Agenda Pembinaan Baru' }}</span>
                        </DialogTitle>
                        <DialogDescription class="text-xs text-slate-500 leading-relaxed font-normal pt-0.5">
                            Isi rincian nama kegiatan, desa sasaran, jenis pembinaan, dan informasi pendukung agenda.
                        </DialogDescription>
                    </DialogHeader>

                    <form @submit.prevent="submitForm" class="space-y-4 pt-3 text-xs">
                        
                        <!-- 2-Column Grid Layout (Simetris 4-Baris Pararel) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            
                            <!-- Kolom Kiri: Identitas & Lokasi Agenda -->
                            <div class="space-y-3.5">
                                <div>
                                    <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">Judul Kegiatan <span class="text-rose-500">*</span></Label>
                                    <Input
                                        id="judul"
                                        v-model="form.judul"
                                        type="text"
                                        placeholder="misal: Sosialisasi Pencegahan TPPO bagi Pemuda Desa"
                                        class="h-9 px-3.5 rounded-md border-slate-300 text-xs focus:ring-2 focus:ring-primary/40 shadow-2xs font-sans"
                                        required
                                    />
                                    <p v-if="form.errors.judul" class="text-[11px] text-rose-500 mt-1">{{ form.errors.judul }}</p>
                                </div>

                                <div>
                                    <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">Desa Sasaran <span class="text-rose-500">*</span></Label>
                                    <Select v-model="form.desa_id">
                                        <SelectTrigger class="h-9 text-xs border-slate-300 rounded-md bg-white font-sans">
                                            <SelectValue placeholder="Pilih Desa Binaan" />
                                        </SelectTrigger>
                                        <SelectContent class="bg-white rounded-lg shadow-xl">
                                            <SelectItem v-for="d in desaList" :key="d.id" :value="d.id">
                                                {{ d.nama }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="form.errors.desa_id" class="text-[11px] text-rose-500 mt-1">{{ form.errors.desa_id }}</p>
                                </div>

                                <div>
                                    <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">Jenis Pembinaan <span class="text-rose-500">*</span></Label>
                                    <Input
                                        v-model="form.jenis_pembinaan"
                                        type="text"
                                        placeholder="misal: Penyuluhan Hukum"
                                        class="h-9 px-3.5 rounded-md border-slate-300 text-xs focus:ring-2 focus:ring-primary/40 shadow-2xs font-sans"
                                        required
                                    />
                                    <p v-if="form.errors.jenis_pembinaan" class="text-[11px] text-rose-500 mt-1">{{ form.errors.jenis_pembinaan }}</p>
                                </div>

                                <div>
                                    <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">Lokasi Pelaksanaan</Label>
                                    <Input
                                        v-model="form.lokasi"
                                        type="text"
                                        placeholder="misal: Balai Desa"
                                        class="h-9 px-3.5 rounded-md border-slate-300 text-xs focus:ring-2 focus:ring-primary/40 shadow-2xs font-sans"
                                    />
                                </div>
                            </div>

                            <!-- Kolom Kanan: Waktu, Materi & File Dokumentasi -->
                            <div class="space-y-3.5">
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">Tanggal <span class="text-rose-500">*</span></Label>
                                        <Input
                                            v-model="form.tanggal"
                                            type="date"
                                            class="h-9 px-3 text-xs rounded-md border-slate-300 focus:ring-2 focus:ring-primary/40 shadow-2xs font-sans"
                                            required
                                        />
                                        <p v-if="form.errors.tanggal" class="text-[11px] text-rose-500 mt-1">{{ form.errors.tanggal }}</p>
                                    </div>

                                    <div>
                                        <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">Peserta <span class="text-rose-500">*</span></Label>
                                        <Input
                                            v-model.number="form.jumlah_peserta"
                                            type="number"
                                            min="1"
                                            placeholder="misal: 30"
                                            class="h-9 px-3.5 rounded-md border-slate-300 text-xs focus:ring-2 focus:ring-primary/40 shadow-2xs font-sans"
                                            required
                                        />
                                        <p v-if="form.errors.jumlah_peserta" class="text-[11px] text-rose-500 mt-1">{{ form.errors.jumlah_peserta }}</p>
                                    </div>
                                </div>

                                <div>
                                    <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">Status <span class="text-rose-500">*</span></Label>
                                    <Select v-model="form.status">
                                        <SelectTrigger class="h-9 text-xs border-slate-300 rounded-md bg-white font-sans">
                                            <SelectValue placeholder="Pilih Status" />
                                        </SelectTrigger>
                                        <SelectContent class="bg-white rounded-lg shadow-xl">
                                            <SelectItem value="selesai">SELESAI</SelectItem>
                                            <SelectItem value="terjadwal">TERJADWAL</SelectItem>
                                            <SelectItem value="dibatalkan">DIBATALKAN</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="form.errors.status" class="text-[11px] text-rose-500 mt-1">{{ form.errors.status }}</p>
                                </div>

                                <div>
                                    <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">Ringkasan Materi / Catatan <span class="text-rose-500">*</span></Label>
                                    <Textarea
                                        v-model="form.ringkasan_materi"
                                        rows="3"
                                        placeholder="Tuliskan poin-poin materi, pembahasan, atau hasil pelaksanaan..."
                                        class="p-3 rounded-md border-slate-300 text-xs focus:ring-2 focus:ring-primary/40 shadow-2xs font-sans resize-none"
                                        required
                                    />
                                    <p v-if="form.errors.ringkasan_materi" class="text-[11px] text-rose-500 mt-1">{{ form.errors.ringkasan_materi }}</p>
                                </div>

                                <div>
                                    <Label class="font-semibold text-slate-800 text-xs tracking-tight block mb-1.5">Upload Foto Dokumentasi / Lampiran</Label>
                                    <Input
                                        type="file"
                                        multiple
                                        accept="image/*,.pdf"
                                        class="h-9 px-3 text-xs rounded-md border-slate-300 file:mr-3 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200"
                                        @change="handleFileChange"
                                    />
                                    <p class="text-[10px] text-slate-400 mt-1">Format: JPG, PNG, PDF (Maksimal 2MB per file)</p>
                                </div>
                            </div>

                        </div>

                        <DialogFooter class="-mx-6 sm:-mx-7 px-6 sm:px-7 pt-4 mt-5 border-t border-slate-100 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 sm:gap-3 bg-transparent">
                            <Button
                                type="button"
                                variant="outline"
                                @click="isFormModalOpen = false"
                                class="w-full sm:w-auto h-9 px-4 rounded-xl text-xs font-bold border-slate-300 text-slate-700 hover:bg-slate-50 transition-all cursor-pointer"
                            >
                                Batal
                            </Button>
                            <Button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full sm:w-auto h-9 px-4 bg-primary hover:bg-[#04407D] text-primary-foreground rounded-xl text-xs font-bold shadow-xs transition-all cursor-pointer"
                            >
                                {{ form.processing ? 'Menyimpan...' : (isEditMode ? 'Simpan Perubahan' : 'Simpan Agenda Pembinaan') }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- MODAL DETAIL KEGIATAN (STYLE SHADCN MATCHING) -->
            <Dialog v-model:open="isDetailModalOpen">
                <DialogContent v-if="selectedDetail" class="sm:max-w-xl bg-white rounded-2xl p-6 sm:p-7 shadow-2xl space-y-0 font-sans border border-slate-100 max-h-[90vh] overflow-y-auto">
                    <DialogHeader class="-mx-6 sm:-mx-7 px-6 sm:px-7 pb-3.5 border-b border-slate-100 space-y-1 text-left">
                        <DialogTitle class="text-base font-bold text-slate-900 flex items-start gap-2.5 leading-snug">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center border border-blue-200/80 shrink-0 mt-0.5">
                                <Eye :size="17" />
                            </div>
                            <span class="whitespace-normal break-words">{{ selectedDetail.judul }}</span>
                        </DialogTitle>
                        <DialogDescription class="text-xs text-slate-500 leading-relaxed font-normal pt-0.5">
                            Detail rincian pelaksanaan agenda kegiatan pembinaan keimigrasian.
                        </DialogDescription>
                    </DialogHeader>

                    <div class="space-y-5 text-xs text-slate-600 pt-4">
                        
                        <!-- Status Badges -->
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-bold text-slate-700 bg-slate-100 px-3 py-0.5 rounded-full border border-slate-200/80">
                                {{ selectedDetail.jenis_pembinaan }}
                            </span>
                            <span :class="['px-3 py-0.5 rounded-full text-xs font-bold border uppercase tracking-wider', getStatusBadge(selectedDetail.status)]">
                                {{ selectedDetail.status }}
                            </span>
                        </div>

                        <!-- Grid Meta Information -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 bg-slate-50/80 p-4 rounded-xl border border-slate-200/70">
                            <div class="space-y-0.5">
                                <span class="text-slate-400 font-semibold block text-[10px] uppercase tracking-wider">Desa Sasaran</span>
                                <span class="font-bold text-slate-900 text-xs">{{ selectedDetail.desa_nama }}</span>
                            </div>
                            <div class="space-y-0.5">
                                <span class="text-slate-400 font-semibold block text-[10px] uppercase tracking-wider">Lokasi Kegiatan</span>
                                <span class="font-bold text-slate-900 text-xs">{{ selectedDetail.lokasi || '-' }}</span>
                            </div>
                            <div class="space-y-0.5">
                                <span class="text-slate-400 font-semibold block text-[10px] uppercase tracking-wider">Tanggal Pelaksanaan</span>
                                <span class="font-bold text-slate-800 text-xs">{{ selectedDetail.tanggal }}</span>
                            </div>
                            <div class="space-y-0.5">
                                <span class="text-slate-400 font-semibold block text-[10px] uppercase tracking-wider">Jumlah Peserta</span>
                                <span class="font-bold text-blue-700 text-xs">{{ selectedDetail.jumlah_peserta }} Orang</span>
                            </div>
                        </div>

                        <!-- Ringkasan Materi -->
                        <div class="space-y-1.5">
                            <h5 class="font-bold text-slate-900 text-xs">Ringkasan Materi & Pembahasan:</h5>
                            <p class="leading-relaxed whitespace-pre-line text-slate-800 bg-slate-50/60 p-4 rounded-xl border border-slate-200/70 text-xs font-normal">
                                {{ selectedDetail.ringkasan_materi }}
                            </p>
                        </div>

                        <!-- Lampiran Dokumentasi -->
                        <div v-if="selectedDetail.lampiran && selectedDetail.lampiran.length > 0" class="space-y-2">
                            <h5 class="font-bold text-slate-900 text-xs flex items-center gap-1.5">
                                <Paperclip :size="14" class="text-primary" />
                                <span>Dokumentasi & Berkas Lampiran ({{ selectedDetail.lampiran.length }} File):</span>
                            </h5>
                            <div class="grid grid-cols-1 gap-2">
                                <div v-for="l in selectedDetail.lampiran" :key="l.id" class="p-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition-all flex items-center justify-between shadow-2xs">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
                                            <FileText :size="14" />
                                        </div>
                                        <span class="truncate font-semibold text-slate-800 text-xs">{{ l.file_name }}</span>
                                    </div>
                                    <a :href="l.file_path" target="_blank" class="text-primary hover:text-[#04407D] font-bold text-xs flex items-center gap-1 shrink-0 ml-2">
                                        <span>Buka</span>
                                        <ExternalLink :size="12" />
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="pt-2 text-[11px] text-slate-500 font-medium">
                            Dicatat oleh: <strong class="text-slate-900 font-bold">{{ selectedDetail.petugas_nama }}</strong>
                        </div>
                    </div>

                    <DialogFooter class="-mx-6 sm:-mx-7 px-6 sm:px-7 pt-4 mt-5 border-t border-slate-100 flex items-center justify-end bg-transparent">
                        <Button
                            type="button"
                            variant="outline"
                            @click="isDetailModalOpen = false"
                            class="h-9 px-4 rounded-xl text-xs font-bold border-slate-300 text-slate-700 hover:bg-slate-50 transition-all cursor-pointer"
                        >
                            Tutup Detail
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <!-- CONFIRM DELETE MODAL REUSABLE (EXACT MATCHING MASTER DATA ADMIN) -->
            <ConfirmDeleteModal
                v-model:open="isDeleteModalOpen"
                title="Hapus Agenda Pembinaan"
                :item-name="itemToDelete?.judul"
                description="Apakah Anda yakin ingin menghapus data kegiatan pembinaan ini? Seluruh riwayat akan dihapus dari sistem."
                :loading="isDeleting"
                @confirm="executeDelete"
            />

        </div>
    </AppLayout>
</template>
