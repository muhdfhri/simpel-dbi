<script setup lang="ts">
import { AlertDialogTrigger } from 'reka-ui'

interface Props {
  asChild?: boolean
  as?: any
}

const props = defineProps<Props>()
</script>

<template>
  <AlertDialogTrigger data-slot="alert-dialog-trigger" v-bind="props">
    <slot />
  </AlertDialogTrigger>
</template>
