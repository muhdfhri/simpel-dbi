<script setup lang="ts">
import { DialogClose, useForwardProps } from 'reka-ui'

interface Props {
  asChild?: boolean
  as?: any
}

const props = defineProps<Props>()
const forwarded = useForwardProps(props)
</script>

<template>
  <DialogClose
    data-slot="dialog-close"
    v-bind="forwarded"
  >
    <slot />
  </DialogClose>
</template>
