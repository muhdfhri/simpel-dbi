<script setup lang="ts">
import NavMenuItem, { type NavItem } from './NavMenuItem.vue';

const props = withDefaults(defineProps<{
    items: NavItem[];
    defaultOpenIds?: string[];
}>(), {
    defaultOpenIds: () => [],
});
</script>

<template>
    <div class="flex flex-col gap-1.5 w-full font-sans">
        <NavMenuItem
            v-for="item in items"
            :key="item.id"
            :item="item"
            :level="0"
            :default-open-ids="defaultOpenIds"
        />
    </div>
</template>
