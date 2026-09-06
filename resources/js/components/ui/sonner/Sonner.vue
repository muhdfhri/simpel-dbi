<script lang="ts" setup>
import {
  CircleCheckIcon,
  InfoIcon,
  Loader2Icon,
  OctagonXIcon,
  TriangleAlertIcon,
  XIcon,
} from 'lucide-vue-next'
import { reactiveOmit } from '@vueuse/core'
import { Toaster as Sonner } from 'vue-sonner'
import { cn } from '@/lib/utils'

interface Props {
  invert?: boolean
  theme?: 'light' | 'dark' | 'system'
  position?: 'top-left' | 'top-right' | 'bottom-left' | 'bottom-right' | 'top-center' | 'bottom-center'
  hotkey?: string[]
  richColors?: boolean
  expand?: boolean
  duration?: number
  gap?: number
  visibleToasts?: number
  closeButton?: boolean
  toastOptions?: any
  class?: string
  style?: any
  offset?: string | number
  dir?: 'ltr' | 'rtl' | 'auto'
}

const props = withDefaults(defineProps<Props>(), {
  position: 'bottom-right',
  closeButton: true,
})

const delegatedProps = reactiveOmit(props, 'class', 'toastOptions', 'position', 'expand', 'richColors', 'closeButton')
</script>

<template>
  <Sonner
    :class="cn('toaster group', props.class)"
    :position="position"
    :expand="false"
    :rich-colors="false"
    :close-button="true"
    :toast-options="props.toastOptions ?? {
      classes: {
        toast: 'rounded-2xl shadow-xl font-sans text-xs border border-slate-200/90 bg-white p-4 flex items-start gap-3',
        title: 'font-bold text-xs text-slate-900',
        description: 'text-xs text-slate-600 font-medium mt-0.5',
      },
    }"
    v-bind="delegatedProps"
  >
    <template #success-icon>
      <div class="w-7 h-7 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-200/80">
        <CircleCheckIcon class="size-4 text-emerald-600" />
      </div>
    </template>
    <template #info-icon>
      <div class="w-7 h-7 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-200/80">
        <InfoIcon class="size-4 text-blue-600" />
      </div>
    </template>
    <template #warning-icon>
      <div class="w-7 h-7 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-200/80">
        <TriangleAlertIcon class="size-4 text-amber-600" />
      </div>
    </template>
    <template #error-icon>
      <div class="w-7 h-7 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0 border border-red-200/80">
        <OctagonXIcon class="size-4 text-red-600" />
      </div>
    </template>
    <template #loading-icon>
      <div class="w-7 h-7 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center shrink-0 border border-slate-200">
        <Loader2Icon class="size-4 animate-spin text-slate-600" />
      </div>
    </template>
    <template #close-icon>
      <XIcon class="size-3.5 text-slate-400 hover:text-slate-700 transition-colors" />
    </template>
  </Sonner>
</template>
