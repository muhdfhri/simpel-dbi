<script setup lang="ts">
import { PopoverRoot, useForwardPropsEmits } from 'reka-ui'

interface Props {
  defaultOpen?: boolean
  open?: boolean
  modal?: boolean
}

const props = defineProps<Props>()
const emits = defineEmits<{
  (e: 'update:open', value: boolean): void
}>()

const forwarded = useForwardPropsEmits(props, emits)
</script>

<template>
  <PopoverRoot v-bind="forwarded">
    <slot />
  </PopoverRoot>
</template>
