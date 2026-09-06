<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { reactiveOmit } from '@vueuse/core'
import { DialogTitle, useForwardProps } from 'reka-ui'
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
  <DialogTitle
    data-slot="dialog-title"
    v-bind="forwardedProps"
    :class="cn('text-base leading-none font-medium cn-font-heading', props.class)"
  >
    <slot />
  </DialogTitle>
</template>
