<script setup lang="ts">
import { PopoverTrigger } from 'reka-ui'

interface Props {
  asChild?: boolean
  as?: string
}

const props = defineProps<Props>()
</script>

<template>
  <PopoverTrigger v-bind="props">
    <slot />
  </PopoverTrigger>
</template>
