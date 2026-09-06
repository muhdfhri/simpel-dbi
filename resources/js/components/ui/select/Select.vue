<script setup lang="ts">
import { SelectRoot, useForwardPropsEmits } from 'reka-ui'

interface Props {
  open?: boolean
  defaultOpen?: boolean
  defaultValue?: any
  modelValue?: any
  dir?: 'ltr' | 'rtl'
  name?: string
  autocomplete?: string
  disabled?: boolean
  required?: boolean
}

const props = defineProps<Props>()
const emits = defineEmits<{
  (e: 'update:modelValue', value: any): void
  (e: 'update:open', value: boolean): void
}>()

const forwarded = useForwardPropsEmits(props, emits)
</script>

<template>
  <SelectRoot
    v-slot="slotProps"
    data-slot="select"
    v-bind="forwarded"
  >
    <slot v-bind="slotProps" />
  </SelectRoot>
</template>
