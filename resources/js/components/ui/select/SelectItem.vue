<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { CheckIcon } from '@lucide/vue'
import { reactiveOmit } from '@vueuse/core'
import {
  SelectItem,
  SelectItemIndicator,
  SelectItemText,
  useForwardProps,
} from 'reka-ui'
import { cn } from '@/lib/utils'

interface Props {
  value: string | number | Record<string, any>
  disabled?: boolean
  textValue?: string
  asChild?: boolean
  as?: any
  class?: HTMLAttributes['class']
}

const props = defineProps<Props>()

const delegatedProps = reactiveOmit(props, 'class')
const forwardedProps = useForwardProps(delegatedProps)
</script>

<template>
  <SelectItem
    data-slot="select-item"
    v-bind="forwardedProps"
    :class="
      cn(
        'relative flex w-full box-border cursor-pointer items-center justify-between rounded-xl px-3 py-2 text-xs font-medium text-slate-700 outline-none select-none transition-colors hover:bg-slate-100/90 focus:bg-slate-100 focus:text-slate-900 data-[disabled]:pointer-events-none data-[disabled]:opacity-50 [&_svg]:pointer-events-none [&_svg]:shrink-0',
        props.class,
      )
    "
  >
    <SelectItemText class="truncate font-medium flex-1 text-left">
      <slot />
    </SelectItemText>

    <!-- Indicator Centang Presisi dengan Margin Simetris Kanan -->
    <SelectItemIndicator class="shrink-0 ml-2 inline-flex items-center justify-center text-slate-900">
      <slot name="indicator-icon">
        <CheckIcon class="size-4 text-slate-900" />
      </slot>
    </SelectItemIndicator>
  </SelectItem>
</template>
