<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { reactiveOmit } from '@vueuse/core'
import { DialogDescription, useForwardProps } from 'reka-ui'
import { cn } from '@/lib/utils'

interface Props {
  asChild?: boolean
  as?: any
  class?: HTMLAttributes['class']
}

const props = defineProps<Props>()

const delegatedProps = reactiveOmit(props, 'class')
const forwardedProps = useForwardProps(delegatedProps)
</script>

<template>
  <DialogDescription
    data-slot="dialog-description"
    v-bind="forwardedProps"
    :class="cn('text-muted-foreground *:[a]:hover:text-foreground text-sm *:[a]:underline *:[a]:underline-offset-3', props.class)"
  >
    <slot />
  </DialogDescription>
</template>
