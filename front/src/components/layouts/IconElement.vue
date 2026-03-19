<template>
  <i
    class="icon-element mdi"
    :class="normalizedName"
    :style="iconStyle"
    :title="props.title"
    :aria-label="props.ariaLabel ?? props.title"
    @click="$emit('click', $event)"
    @keydown.enter="$emit('click', $event)"
    @keydown.space.prevent="$emit('click', $event)"
  />
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { type UiColor, type UiSize } from '@/types/ui';

const props = withDefaults(defineProps<{
  name: string;
  size?: UiSize;
  color?: UiColor;
  title?: string;
  ariaLabel?: string;
}>(), {
  size: 'medium',
  color: 'black',
});

defineEmits<{
  (e: 'click', event: MouseEvent | KeyboardEvent): void;
}>();

const normalizedName = computed(() => {
  if (props.name.startsWith('mdi-')) {
    return props.name;
  }

  return `mdi-${props.name}`;
});

const iconSize = computed(() => {
  if (typeof props.size === 'number') {
    return `${props.size}px`;
  }

  const sizes: Record<UiSize, string> = {
    small: '16px',
    medium: '20px',
    large: '24px',
  };

  return sizes[props.size];
});

const iconStyle = computed(() => {
  const style: Record<string, string> = {
    fontSize: iconSize.value,
    color: props.color,
  };

  return style;
});
</script>

<style scoped>
.icon-element {
  display: inline-flex;
  line-height: 1;
  transition: color 0.2s ease;
}
</style>
