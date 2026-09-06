<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { reactiveOmit } from '@vueuse/core'
import { SelectSeparator } from 'reka-ui'
import { cn } from '@/lib/utils'

interface Props {
  asChild?: boolean
  as?: any
  class?: HTMLAttributes['class']
}

const props = defineProps<Props>()

const delegatedProps = reactiveOmit(props, 'class')
</script>

<template>
  <SelectSeparator
    data-slot="select-separator"
    v-bind="delegatedProps"
    :class="cn('bg-border -mx-1 my-1 h-px pointer-events-none', props.class)"
  />
</template>
