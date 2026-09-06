<script setup lang="ts">
import { PopoverContent, PopoverPortal, useForwardPropsEmits } from 'reka-ui'
import { cn } from '@/lib/utils'

interface Props {
  forceMount?: boolean
  trapFocus?: boolean
  side?: 'top' | 'right' | 'bottom' | 'left'
  sideOffset?: number
  align?: 'start' | 'center' | 'end'
  alignOffset?: number
  avoidCollisions?: boolean
  collisionBoundary?: any
  collisionPadding?: number | Record<string, number>
  arrowPadding?: number
  sticky?: 'partial' | 'always'
  hideWhenDetached?: boolean
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  align: 'center',
  sideOffset: 8,
})
const emits = defineEmits<{
  (e: 'escapeKeyDown', event: KeyboardEvent): void
  (e: 'pointerDownOutside', event: Event): void
  (e: 'focusOutside', event: Event): void
  (e: 'interactOutside', event: Event): void
  (e: 'dismiss', event: Event): void
  (e: 'openAutoFocus', event: Event): void
  (e: 'closeAutoFocus', event: Event): void
}>()

const forwarded = useForwardPropsEmits(props, emits)
</script>

<template>
  <PopoverPortal>
    <PopoverContent
      v-bind="forwarded"
      :class="
        cn(
          'z-50 w-72 rounded-2xl border bg-white p-4 shadow-xl outline-none data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2',
          props.class,
        )
      "
    >
      <slot />
    </PopoverContent>
  </PopoverPortal>
</template>
