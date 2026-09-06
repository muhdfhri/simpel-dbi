<script setup lang="ts">
import { ref, computed, inject } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronRight, Lock } from 'lucide-vue-next';
import { openRestrictedModal } from '@/lib/restrictedModal';

export interface NavItem {
    id: string;
    name: string;
    href?: string;
    icon?: any;
    permission?: string;
    items?: NavItem[];
}

const props = withDefaults(defineProps<{
    item: NavItem;
    level?: number;
    defaultOpenIds?: string[];
}>(), {
    level: 0,
    defaultOpenIds: () => [],
});

const page = usePage<any>();
const allMenuHrefs = inject<any>('allMenuHrefs', computed(() => []));
const isFolder = computed(() => !!props.item.items && props.item.items.length > 0);

// Helper untuk memeriksa apakah anak folder cocok dengan URL aktif
const isChildActive = (items?: NavItem[]): boolean => {
    if (!items) return false;
    const currentUrl = page.url.split('?')[0];
    return items.some(child => {
        if (child.href) {
            const childHref = child.href.split('?')[0];
            if (currentUrl === childHref || (childHref !== '/' && currentUrl.startsWith(childHref + '/'))) {
                return true;
            }
        }
        if (child.items) {
            return isChildActive(child.items);
        }
        return false;
    });
};

// Open state for folder: HANYA terbuka jika dipaksa via defaultOpenIds ATAU salah satu anak/sub-anaknya sedang diakses
const isOpen = ref(
    props.defaultOpenIds.includes(props.item.id) || isChildActive(props.item.items)
);

const toggleOpen = () => {
    if (isFolder.value) {
        isOpen.value = !isOpen.value;
    }
};

const isSelected = computed(() => {
    if (!props.item.href) return false;
    const currentUrl = page.url.split('?')[0]; // Ignore query string
    const targetHref = props.item.href.split('?')[0];
    
    // 1. Exact URL Match (Direct Links)
    if (currentUrl === targetHref) return true;
    
    // 2. Folder Sub-path Match (Only for Collapsible Folder Groups)
    if (isFolder.value && targetHref !== '/' && currentUrl.startsWith(targetHref + '/')) {
        return true;
    }
    
    return false;
});

const userPermissions = computed<string[]>(() => page.props.auth?.user?.permissions || []);

const isLocked = computed(() => {
    if (props.item.permission) {
        return !userPermissions.value.includes(props.item.permission);
    }
    return false;
});

const handleLockedClick = () => {
    openRestrictedModal({
        title: 'Akses Fitur Sedang Dibatasi',
        description: `Hak akses untuk fitur '${props.item.name}' sedang dinonaktifkan sementara oleh Administrator Kanwil Ditjen Imigrasi Sumatera Utara.`,
        featureName: props.item.name,
    });
};
</script>

<template>
    <!-- Folder Item (Collapsible Parent Group - Presisi DESIGN.md) -->
    <div v-if="isFolder" class="group/collapsible w-full space-y-1">
        <!-- Trigger Button -->
        <button
            type="button"
            @click="toggleOpen"
            :style="{ paddingLeft: level > 0 ? `${level * 12 + 10}px` : '10px' }"
            :class="[
                'w-full flex items-center justify-between py-2.5 pr-3 rounded-lg text-xs cursor-pointer transition-colors hover:bg-slate-100 group/item select-none',
                isOpen ? 'text-primary font-bold' : 'text-slate-700 font-semibold'
            ]"
        >
            <div class="flex items-center gap-3 min-w-0">
                <div v-if="item.icon" class="size-4 text-slate-500 group-hover/item:text-primary shrink-0 flex items-center justify-center">
                    <component :is="item.icon" :size="18" />
                </div>
                <span class="truncate text-xs tracking-tight leading-none">{{ item.name }}</span>
            </div>

            <ChevronRight
                :class="[
                    'ml-auto size-3.5 text-slate-400 transition-transform duration-200 shrink-0',
                    isOpen ? 'rotate-90 text-primary' : ''
                ]"
            />
        </button>

        <!-- Collapsible Children Content -->
        <div v-show="isOpen" class="overflow-hidden flex flex-col gap-1 pt-1">
            <NavMenuItem
                v-for="child in item.items"
                :key="child.id"
                :item="child"
                :level="level + 1"
                :default-open-ids="defaultOpenIds"
            />
        </div>
    </div>

    <!-- Leaf Item (Direct Link or Locked Item) -->
    <template v-else>
        <!-- Item Terkunci (Trigger Floating Modal Instant) -->
        <button
            v-if="isLocked"
            type="button"
            @click="handleLockedClick"
            :style="{ paddingLeft: level > 0 ? `${level * 12 + 10}px` : '10px' }"
            class="group/item w-full flex items-center justify-between py-2.5 pr-3 rounded-lg text-xs text-amber-700 bg-amber-50/60 hover:bg-amber-100/80 transition-all cursor-pointer select-none font-medium border border-amber-200/60"
            :title="`Fitur '${item.name}' sedang dinonaktifkan oleh Kanwil`"
        >
            <div class="flex items-center gap-3 min-w-0">
                <div v-if="item.icon" class="size-4 shrink-0 flex items-center justify-center text-amber-600">
                    <component :is="item.icon" :size="16" />
                </div>
                <span class="truncate tracking-tight leading-none font-medium text-amber-900">{{ item.name }}</span>
            </div>
            <Lock :size="13" class="text-amber-600 shrink-0" />
        </button>

        <!-- Item Normal Aktif (Direct Link) -->
        <Link
            v-else-if="item.href"
            :href="item.href"
            :style="{ paddingLeft: level > 0 ? `${level * 12 + 10}px` : '10px' }"
            :class="[
                'group/item flex items-center gap-3 py-2.5 pr-3 rounded-lg text-xs transition-all cursor-pointer select-none',
                isSelected
                    ? 'bg-primary text-primary-foreground font-semibold shadow-2xs'
                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium'
            ]"
        >
            <div v-if="item.icon" class="size-4 shrink-0 flex items-center justify-center">
                <component
                    :is="item.icon"
                    :size="16"
                    :class="isSelected ? 'text-primary-foreground' : 'text-slate-400 group-hover/item:text-slate-700'"
                />
            </div>
            <span class="truncate tracking-tight leading-none">{{ item.name }}</span>
        </Link>

        <button
            v-else
            type="button"
            :style="{ paddingLeft: level > 0 ? `${level * 12 + 10}px` : '10px' }"
            :class="[
                'group/item w-full flex items-center gap-3 py-2.5 pr-3 rounded-lg text-xs text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors cursor-pointer select-none font-medium'
            ]"
        >
            <div v-if="item.icon" class="size-4 shrink-0 flex items-center justify-center">
                <component :is="item.icon" :size="16" class="text-slate-400 group-hover/item:text-slate-700" />
            </div>
            <span class="truncate tracking-tight leading-none">{{ item.name }}</span>
        </button>
    </template>
</template>
