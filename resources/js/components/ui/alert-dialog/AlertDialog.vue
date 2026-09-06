<script setup lang="ts">
import { AlertDialogRoot, useForwardPropsEmits } from 'reka-ui'

interface Props {
  defaultOpen?: boolean
  open?: boolean
}

const props = defineProps<Props>()
const emits = defineEmits(['update:open'])

const forwarded = useForwardPropsEmits(props, emits)
</script>

<template>
  <AlertDialogRoot v-slot="slotProps" data-slot="alert-dialog" v-bind="forwarded">
    <slot v-bind="slotProps" />
  </AlertDialogRoot>
</template>
