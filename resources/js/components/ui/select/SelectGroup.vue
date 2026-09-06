<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { reactiveOmit } from '@vueuse/core'
import { SelectGroup } from 'reka-ui'
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
  <SelectGroup
    data-slot="select-group"
    v-bind="delegatedProps"
    :class="cn('scroll-my-1 p-1', props.class)"
  >
    <slot />
  </SelectGroup>
</template>
