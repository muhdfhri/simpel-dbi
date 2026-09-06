<script setup lang="ts">
import { SelectValue } from 'reka-ui'

interface Props {
  placeholder?: string
  asChild?: boolean
  as?: any
}

const props = defineProps<Props>()
</script>

<template>
  <SelectValue
    data-slot="select-value"
    v-bind="props"
    class="text-left text-start block truncate"
  >
    <slot />
  </SelectValue>
</template>
