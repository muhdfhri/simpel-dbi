<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { reactiveOmit } from '@vueuse/core'
import {
  SelectContent,
  SelectPortal,
  SelectViewport,
  useForwardPropsEmits,
} from 'reka-ui'
import { cn } from '@/lib/utils'
import { SelectScrollDownButton, SelectScrollUpButton } from '.'

defineOptions({
  inheritAttrs: false,
})

interface Props {
  forceMount?: boolean
  position?: 'item-aligned' | 'popper'
  bodyLock?: boolean
  side?: 'top' | 'right' | 'bottom' | 'left'
  sideOffset?: number
  align?: 'start' | 'center' | 'end'
  alignOffset?: number
  class?: HTMLAttributes['class']
}

const props = withDefaults(
  defineProps<Props>(),
  {
    position: 'popper',
    side: 'bottom',
    sideOffset: 6,
    align: 'start',
  },
)
const emits = defineEmits<{
  (e: 'closeAutoFocus', event: Event): void
  (e: 'escapeKeyDown', event: KeyboardEvent): void
  (e: 'pointerDownOutside', event: Event): void
}>()

const delegatedProps = reactiveOmit(props, 'class')

const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
  <SelectPortal>
    <SelectContent
      data-slot="select-content"
      :data-align-trigger="position === 'item-aligned'"
      v-bind="{ ...$attrs, ...forwarded }"
      :class="cn(
        'bg-white/95 text-slate-900 data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2 border border-slate-200/90 rounded-2xl shadow-xl backdrop-blur-md p-1.5 z-50 overflow-hidden relative duration-150 box-border',
        position === 'popper'
          && 'w-[var(--reka-select-trigger-width)] min-w-[var(--reka-select-trigger-width)] data-[side=bottom]:translate-y-1.5 data-[side=left]:-translate-x-1 data-[side=right]:translate-x-1 data-[side=top]:-translate-y-1.5',
        props.class,
      )"
    >
      <SelectScrollUpButton />
      <SelectViewport
        :data-position="position"
        :class="cn(
          'p-0.5 space-y-1 w-full box-border',
          position === 'popper' && 'h-[var(--reka-select-trigger-height)] max-h-60',
        )"
      >
        <slot />
      </SelectViewport>
      <SelectScrollDownButton />
    </SelectContent>
  </SelectPortal>
</template>
