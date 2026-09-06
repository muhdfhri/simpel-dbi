<script setup lang="ts">
import { DialogTrigger, useForwardProps } from 'reka-ui'

interface Props {
  asChild?: boolean
  as?: any
}

const props = defineProps<Props>()
const forwarded = useForwardProps(props)
</script>

<template>
  <DialogTrigger
    data-slot="dialog-trigger"
    v-bind="forwarded"
  >
    <slot />
  </DialogTrigger>
</template>
