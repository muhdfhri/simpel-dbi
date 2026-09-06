<script setup lang="ts">
import { DropdownMenuRoot, useForwardPropsEmits } from 'reka-ui'

interface Props {
  defaultOpen?: boolean
  open?: boolean
  dir?: 'ltr' | 'rtl'
  modal?: boolean
}

const props = defineProps<Props>()
const emits = defineEmits(['update:open'])

const forwarded = useForwardPropsEmits(props, emits)
</script>

<template>
  <DropdownMenuRoot
    v-slot="slotProps"
    data-slot="dropdown-menu"
    v-bind="forwarded"
  >
    <slot v-bind="slotProps" />
  </DropdownMenuRoot>
</template>
