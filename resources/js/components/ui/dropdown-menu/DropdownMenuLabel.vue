<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { reactiveOmit } from '@vueuse/core'
import { DropdownMenuLabel, useForwardProps } from 'reka-ui'
import { cn } from '@/lib/utils'

interface Props {
  asChild?: boolean
  as?: any
  inset?: boolean
  class?: HTMLAttributes['class']
}

const props = defineProps<Props>()

const delegatedProps = reactiveOmit(props, 'class', 'inset')
const forwardedProps = useForwardProps(delegatedProps)
</script>

<template>
  <DropdownMenuLabel
    data-slot="dropdown-menu-label"
    :data-inset="inset ? '' : undefined"
    v-bind="forwardedProps"
    :class="cn('text-muted-foreground px-1.5 py-1 text-xs font-medium data-inset:pl-7', props.class)"
  >
    <slot />
  </DropdownMenuLabel>
</template>
