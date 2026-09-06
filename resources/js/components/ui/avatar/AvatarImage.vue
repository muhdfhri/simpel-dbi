<script setup lang="ts">
import { AvatarImage } from 'reka-ui'

interface Props {
  src?: string
  alt?: string
  asChild?: boolean
  as?: any
}

const props = defineProps<Props>()
</script>

<template>
  <AvatarImage
    data-slot="avatar-image"
    v-bind="props"
    class="rounded-full aspect-square size-full object-cover"
  >
    <slot />
  </AvatarImage>
</template>
