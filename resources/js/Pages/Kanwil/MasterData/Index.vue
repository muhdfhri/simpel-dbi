<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import AppLayout from '@/components/layout/AppLayout.vue';
import { Building2, Users, MapPin, UserCheck, ShieldCheck, Plus, Shield, Settings2, Globe2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import DesaTableTab from './Components/DesaTableTab.vue';
import PimpasaTableTab from './Components/PimpasaTableTab.vue';
import UptTableTab from './Components/UptTableTab.vue';
import PerangkatDesaTableTab from './Components/PerangkatDesaTableTab.vue';
import AdminKanwilTableTab from './Components/AdminKanwilTableTab.vue';
import KategoriLaporanTableTab from './Components/KategoriLaporanTableTab.vue';
import PermissionMatrixTab from './Components/PermissionMatrixTab.vue';

interface DesaProps {
    id: number;
    nama: string;
    upt_id: number;
    pimpasa_id: number | null;
    lat?: number | null;
    lng?: number | null;
    upt?: { id: number; nama: string };
    pimpasa?: { id: number; name: string };
    status_terkini: string;
}

interface PimpasaProps {
    id: number;
    name: string;
    email: string;
    nip: string;
    golongan: string;
    upt_id: number;
    upt?: { id: number; nama: string };
}

interface UptProps {
    id: number;
    nama: string;
    tipe: string;
    desa_binaan_list_count: number;
    users_count: number;
}

interface DesaUserProps {
    id: number;
    name: string;
    email: string;
    kontak: string | null;
    is_active: boolean | number;
    desa_id: number | null;
    desa?: { id: number; nama: string };
}

interface KanwilUserProps {
    id: number;
    name: string;
    email: string;
    nip?: string | null;
    golongan?: string | null;
    kontak?: string | null;
    is_active?: boolean | number;
}

interface KategoriItem {
    id: number;
    kode: string;
    nama_kategori: string;
    deskripsi: string | null;
    is_active: boolean;
}

interface MatrixItem {
    key: string;
    label: string;
    desc: string;
    category: string;
    enabled: boolean;
}

interface RoleMatrix {
    role: 'desa' | 'pimpasa';
    role_label: string;
    role_desc: string;
    items: MatrixItem[];
}

const props = defineProps<{
    tab: string;
    desaList: DesaProps[];
    pimpasaList: PimpasaProps[];
    uptList: UptProps[];
    desaUserList: DesaUserProps[];
    kanwilUserList?: KanwilUserProps[];
    kategoriLaporanList?: KategoriItem[];
    kategoriList?: KategoriItem[];
    permissionsMatrix: {
        desa: RoleMatrix;
        pimpasa: RoleMatrix;
    };
}>();

const kategoriItems = computed(() => props.kategoriLaporanList || props.kategoriList || []);
const kanwilUsers = computed(() => props.kanwilUserList || []);

// Category & Subtab State
type MainCategory = 'pengguna' | 'wilayah' | 'pengaturan';
type SubTab = 'admin-kanwil' | 'pimpasa' | 'user-desa' | 'desa' | 'upt' | 'kategori' | 'permissions';

const activeCategory = ref<MainCategory>('pengguna');
const activeSubTab = ref<SubTab>('admin-kanwil');

// Sync Category & Subtab initially
if (props.tab === 'desa' || props.tab === 'upt') {
    activeCategory.value = 'wilayah';
    activeSubTab.value = props.tab as SubTab;
} else if (props.tab === 'kategori' || props.tab === 'permissions') {
    activeCategory.value = 'pengaturan';
    activeSubTab.value = props.tab as SubTab;
} else if (props.tab === 'pimpasa' || props.tab === 'user-desa' || props.tab === 'admin-kanwil') {
    activeCategory.value = 'pengguna';
    activeSubTab.value = props.tab as SubTab;
}

const selectCategory = (category: MainCategory) => {
    activeCategory.value = category;
    if (category === 'pengguna') {
        activeSubTab.value = 'admin-kanwil';
    } else if (category === 'wilayah') {
        activeSubTab.value = 'desa';
    } else if (category === 'pengaturan') {
        activeSubTab.value = 'kategori';
    }
};

const adminKanwilTableRef = ref<InstanceType<typeof AdminKanwilTableTab> | null>(null);
const desaTableRef = ref<InstanceType<typeof DesaTableTab> | null>(null);
const pimpasaTableRef = ref<InstanceType<typeof PimpasaTableTab> | null>(null);
const uptTableRef = ref<InstanceType<typeof UptTableTab> | null>(null);
const desaUserTableRef = ref<InstanceType<typeof PerangkatDesaTableTab> | null>(null);
const kategoriTableRef = ref<InstanceType<typeof KategoriLaporanTableTab> | null>(null);

const handleTambahAction = () => {
    if (activeSubTab.value === 'admin-kanwil') {
        adminKanwilTableRef.value?.openTambahModal();
    } else if (activeSubTab.value === 'pimpasa') {
        pimpasaTableRef.value?.openTambahModal();
    } else if (activeSubTab.value === 'user-desa') {
        desaUserTableRef.value?.openTambahModal();
    } else if (activeSubTab.value === 'desa') {
        desaTableRef.value?.openTambahModal();
    } else if (activeSubTab.value === 'upt') {
        uptTableRef.value?.openTambahModal();
    } else if (activeSubTab.value === 'kategori') {
        kategoriTableRef.value?.openAddModal();
    }
};

const getTambahButtonLabel = computed(() => {
    switch (activeSubTab.value) {
        case 'admin-kanwil': return 'Tambah Admin Kanwil';
        case 'pimpasa': return 'Tambah Petugas PIMPASA';
        case 'user-desa': return 'Tambah User Desa';
        case 'desa': return 'Tambah Desa Binaan';
        case 'upt': return 'Tambah Satker UPT';
        case 'kategori': return 'Tambah Kategori';
        default: return 'Tambah Data';
    }
});
</script>

<template>
    <AppLayout title="Master Data System">
        <div class="space-y-6 font-sans">
            
            <!-- Quick Metrics Summary (5 Grid Layout Presisi) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
                
                <!-- Card 1: Admin Kanwil -->
                <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-slate-300/80 transition-colors">
                        <Shield :size="95" stroke-width="1.0" />
                    </div>
                    <CardContent class="p-4 flex flex-col justify-between h-full min-h-[88px] relative z-10">
                        <div class="space-y-0.5 pr-8">
                            <span class="text-xs font-semibold text-slate-500 block leading-tight">Admin Kanwil</span>
                            <div class="text-2xl font-bold text-slate-900 font-mono tracking-tight leading-none pt-1">
                                {{ kanwilUsers.length }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[10px] text-slate-500 font-medium truncate">Super-Admin Kanwil Sumut</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Card 2: Petugas PIMPASA -->
                <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-slate-300/80 transition-colors">
                        <Users :size="95" stroke-width="1.0" />
                    </div>
                    <CardContent class="p-4 flex flex-col justify-between h-full min-h-[88px] relative z-10">
                        <div class="space-y-0.5 pr-8">
                            <span class="text-xs font-semibold text-slate-500 block leading-tight">Petugas PIMPASA</span>
                            <div class="text-2xl font-bold text-slate-900 font-mono tracking-tight leading-none pt-1">
                                {{ pimpasaList.length }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[10px] text-slate-500 font-medium truncate">Personel UPT Pengampu</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Card 3: Perangkat Desa -->
                <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-slate-300/80 transition-colors">
                        <UserCheck :size="95" stroke-width="1.0" />
                    </div>
                    <CardContent class="p-4 flex flex-col justify-between h-full min-h-[88px] relative z-10">
                        <div class="space-y-0.5 pr-8">
                            <span class="text-xs font-semibold text-slate-500 block leading-tight">Perangkat Desa</span>
                            <div class="text-2xl font-bold text-slate-900 font-mono tracking-tight leading-none pt-1">
                                {{ desaUserList.length }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[10px] text-slate-500 font-medium truncate">Akun Pengelola Desa</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Card 4: Desa Binaan -->
                <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-slate-300/80 transition-colors">
                        <Building2 :size="95" stroke-width="1.0" />
                    </div>
                    <CardContent class="p-4 flex flex-col justify-between h-full min-h-[88px] relative z-10">
                        <div class="space-y-0.5 pr-8">
                            <span class="text-xs font-semibold text-slate-500 block leading-tight">Desa Binaan (DBI)</span>
                            <div class="text-2xl font-bold text-slate-900 font-mono tracking-tight leading-none pt-1">
                                {{ desaList.length }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[10px] text-slate-500 font-medium truncate">Lokasi Binaan se-Sumut</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Card 5: Satker UPT -->
                <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-6 text-slate-200/70 pointer-events-none group-hover:text-slate-300/80 transition-colors">
                        <MapPin :size="95" stroke-width="1.0" />
                    </div>
                    <CardContent class="p-4 flex flex-col justify-between h-full min-h-[88px] relative z-10">
                        <div class="space-y-0.5 pr-8">
                            <span class="text-xs font-semibold text-slate-500 block leading-tight">Satker UPT Imigrasi</span>
                            <div class="text-2xl font-bold text-slate-900 font-mono tracking-tight leading-none pt-1">
                                {{ uptList.length }}
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-[10px] text-slate-500 font-medium truncate">Kanim & Satker Pembina</p>
                        </div>
                    </CardContent>
                </Card>

            </div>

            <!-- Master Data Main Container Card with Clean 2-Tier Categorized Tabs -->
            <Card class="border-slate-200/80 shadow-2xs rounded-lg bg-white overflow-hidden">
                
                <!-- TIER 1: CATEGORY GROUP CONTROL BAR (CLEAN WHITE STYLE) -->
                <div class="p-4 sm:p-5 bg-white border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    
                    <div>
                        <h2 class="text-base font-bold tracking-tight text-slate-900 leading-tight">
                            Tata Kelola Master Data System
                        </h2>
                        <p class="text-xs text-slate-500 leading-tight mt-0.5">
                            Kelola entitas pengguna, wilayah kerja UPT, dan acuan sistem se-Sumatera Utara.
                        </p>
                    </div>

                    <!-- Category Group Segmented Pills (Scrollable on mobile) -->
                    <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-lg border border-slate-200/80 shrink-0 overflow-x-auto max-w-full custom-thin-scrollbar">
                        <button
                            type="button"
                            @click="selectCategory('pengguna')"
                            :class="[
                                'px-3.5 py-1.5 rounded-md text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer whitespace-nowrap',
                                activeCategory === 'pengguna'
                                    ? 'bg-slate-900 text-white shadow-2xs font-bold'
                                    : 'text-slate-600 hover:text-slate-900 font-semibold'
                            ]"
                        >
                            <Users class="w-3.5 h-3.5" />
                            <span>Manajemen Pengguna</span>
                        </button>

                        <button
                            type="button"
                            @click="selectCategory('wilayah')"
                            :class="[
                                'px-3.5 py-1.5 rounded-md text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer whitespace-nowrap',
                                activeCategory === 'wilayah'
                                    ? 'bg-slate-900 text-white shadow-2xs font-bold'
                                    : 'text-slate-600 hover:text-slate-900 font-semibold'
                            ]"
                        >
                            <Building2 class="w-3.5 h-3.5" />
                            <span>Wilayah & Pembinaan</span>
                        </button>

                        <button
                            type="button"
                            @click="selectCategory('pengaturan')"
                            :class="[
                                'px-3.5 py-1.5 rounded-md text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer whitespace-nowrap',
                                activeCategory === 'pengaturan'
                                    ? 'bg-slate-900 text-white shadow-2xs font-bold'
                                    : 'text-slate-600 hover:text-slate-900 font-semibold'
                            ]"
                        >
                            <Settings2 class="w-3.5 h-3.5" />
                            <span>Acuan Sistem</span>
                        </button>
                    </div>

                </div>

                <!-- TIER 2: SUB-TAB NAVIGATION & ACTION BAR -->
                <div class="p-3.5 sm:p-4 border-b border-slate-200/80 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    
                    <!-- Sub-tabs based on Active Category (Scrollable on mobile) -->
                    <div class="flex items-center gap-2 overflow-x-auto max-w-full custom-thin-scrollbar pb-1 sm:pb-0">
                        
                        <!-- Category 1: Manajemen Pengguna -->
                        <template v-if="activeCategory === 'pengguna'">
                            <button
                                @click="activeSubTab = 'admin-kanwil'"
                                :class="[
                                    'px-3.5 py-1.5 rounded-md text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer whitespace-nowrap shrink-0',
                                    activeSubTab === 'admin-kanwil'
                                        ? 'bg-slate-900 text-white shadow-2xs'
                                        : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 hover:text-slate-900'
                                ]"
                            >
                                <Shield class="w-3.5 h-3.5" />
                                <span>Admin Kanwil ({{ kanwilUsers.length }})</span>
                            </button>

                            <button
                                @click="activeSubTab = 'pimpasa'"
                                :class="[
                                    'px-3.5 py-1.5 rounded-md text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer whitespace-nowrap shrink-0',
                                    activeSubTab === 'pimpasa'
                                        ? 'bg-slate-900 text-white shadow-2xs'
                                        : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 hover:text-slate-900'
                                ]"
                            >
                                <Users class="w-3.5 h-3.5" />
                                <span>Petugas PIMPASA ({{ pimpasaList.length }})</span>
                            </button>

                            <button
                                @click="activeSubTab = 'user-desa'"
                                :class="[
                                    'px-3.5 py-1.5 rounded-md text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer whitespace-nowrap shrink-0',
                                    activeSubTab === 'user-desa'
                                        ? 'bg-slate-900 text-white shadow-2xs'
                                        : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 hover:text-slate-900'
                                ]"
                            >
                                <UserCheck class="w-3.5 h-3.5" />
                                <span>Perangkat Desa ({{ desaUserList.length }})</span>
                            </button>
                        </template>

                        <!-- Category 2: Wilayah & UPT -->
                        <template v-else-if="activeCategory === 'wilayah'">
                            <button
                                @click="activeSubTab = 'desa'"
                                :class="[
                                    'px-3.5 py-1.5 rounded-md text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer whitespace-nowrap shrink-0',
                                    activeSubTab === 'desa'
                                        ? 'bg-slate-900 text-white shadow-2xs'
                                        : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 hover:text-slate-900'
                                ]"
                            >
                                <Building2 class="w-3.5 h-3.5" />
                                <span>Desa Binaan ({{ desaList.length }})</span>
                            </button>

                            <button
                                @click="activeSubTab = 'upt'"
                                :class="[
                                    'px-3.5 py-1.5 rounded-md text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer whitespace-nowrap shrink-0',
                                    activeSubTab === 'upt'
                                        ? 'bg-slate-900 text-white shadow-2xs'
                                        : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 hover:text-slate-900'
                                ]"
                            >
                                <MapPin class="w-3.5 h-3.5" />
                                <span>Satker UPT Imigrasi ({{ uptList.length }})</span>
                            </button>
                        </template>

                        <!-- Category 3: Pengaturan System -->
                        <template v-else-if="activeCategory === 'pengaturan'">
                            <button
                                @click="activeSubTab = 'kategori'"
                                :class="[
                                    'px-3.5 py-1.5 rounded-md text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer whitespace-nowrap shrink-0',
                                    activeSubTab === 'kategori'
                                        ? 'bg-slate-900 text-white shadow-2xs'
                                        : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 hover:text-slate-900'
                                ]"
                            >
                                <span>Kategori Laporan ({{ kategoriItems.length }})</span>
                            </button>

                            <button
                                @click="activeSubTab = 'permissions'"
                                :class="[
                                    'px-3.5 py-1.5 rounded-md text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer whitespace-nowrap shrink-0',
                                    activeSubTab === 'permissions'
                                        ? 'bg-slate-900 text-white shadow-2xs'
                                        : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 hover:text-slate-900'
                                ]"
                            >
                                <ShieldCheck class="w-3.5 h-3.5" />
                                <span>Matriks Hak Akses</span>
                            </button>
                        </template>

                    </div>

                    <!-- Action Button (Tambah Data) -->
                    <div v-if="activeSubTab !== 'permissions'" class="w-full sm:w-auto shrink-0">
                        <Button
                            @click="handleTambahAction"
                            class="w-full sm:w-auto bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs rounded-md shadow-xs gap-1.5 shrink-0 cursor-pointer h-9 px-4 justify-center"
                        >
                            <Plus :size="15" />
                            <span>{{ getTambahButtonLabel }}</span>
                        </Button>
                    </div>

                </div>

                <!-- TABLE & CONTENT DISPLAY AREA -->
                <CardContent class="p-0">
                    
                    <AdminKanwilTableTab
                        v-if="activeSubTab === 'admin-kanwil'"
                        ref="adminKanwilTableRef"
                        :kanwilUserList="kanwilUsers"
                    />

                    <PimpasaTableTab
                        v-else-if="activeSubTab === 'pimpasa'"
                        ref="pimpasaTableRef"
                        :pimpasaList="pimpasaList"
                        :uptList="uptList"
                    />

                    <PerangkatDesaTableTab
                        v-else-if="activeSubTab === 'user-desa'"
                        ref="desaUserTableRef"
                        :desaUserList="desaUserList"
                        :desaList="desaList"
                    />

                    <DesaTableTab
                        v-else-if="activeSubTab === 'desa'"
                        ref="desaTableRef"
                        :desaList="desaList"
                        :uptList="uptList"
                        :pimpasaList="pimpasaList"
                    />

                    <UptTableTab
                        v-else-if="activeSubTab === 'upt'"
                        ref="uptTableRef"
                        :uptList="uptList"
                    />

                    <KategoriLaporanTableTab
                        v-else-if="activeSubTab === 'kategori'"
                        ref="kategoriTableRef"
                        :kategoriList="kategoriItems"
                    />

                    <PermissionMatrixTab
                        v-else-if="activeSubTab === 'permissions'"
                        :permissionsMatrix="permissionsMatrix"
                    />

                </CardContent>
            </Card>

        </div>
    </AppLayout>
</template>
