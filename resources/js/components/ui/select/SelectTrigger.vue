<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { ChevronDownIcon } from '@lucide/vue'
import { reactiveOmit } from '@vueuse/core'
import { SelectIcon, SelectTrigger, useForwardProps } from 'reka-ui'
import { cn } from '@/lib/utils'

interface Props {
  disabled?: boolean
  asChild?: boolean
  as?: any
  class?: HTMLAttributes['class']
  size?: 'sm' | 'default'
}

const props = withDefaults(defineProps<Props>(), {
  size: 'default',
})

const delegatedProps = reactiveOmit(props, 'class', 'size')
const forwardedProps = useForwardProps(delegatedProps)
</script>

<template>
  <SelectTrigger
    data-slot="select-trigger"
    :data-size="size"
    v-bind="forwardedProps"
    :class="cn(
      'flex w-full items-center justify-between gap-2 rounded-md border border-slate-200/90 bg-white px-3 py-2 text-xs font-semibold text-slate-800 shadow-2xs transition-all outline-none hover:bg-slate-50/80 hover:border-slate-300 focus:ring-2 focus:ring-slate-900 focus:border-slate-900 disabled:cursor-not-allowed disabled:opacity-50 select-none data-[size=default]:h-9 data-[size=sm]:h-8 data-[placeholder]:text-slate-400 [&_svg]:pointer-events-none [&_svg]:shrink-0',
      props.class,
    )"
  >
    <slot />
    <SelectIcon as-child>
      <ChevronDownIcon class="size-4 text-slate-400 shrink-0 transition-transform duration-200" />
    </SelectIcon>
  </SelectTrigger>
</template>
