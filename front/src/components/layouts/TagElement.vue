<template>
  <span class="tag-element" :class="`size-${props.size}`" :style="tagStyle">
    {{ props.text }}
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { type UiColor, type UiSize } from '@/types/ui';

const props = withDefaults(defineProps<{
  text: string;
  color?: UiColor;
  textColor?: UiColor;
  size?: UiSize;
}>(), {
  color: 'secondary',
  textColor: 'black',
  size: 'medium',
});

const tagStyle = computed(() => ({
  backgroundColor: props.color,
  color: props.textColor,
}));
</script>

<style scoped>
.tag-element {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  font-weight: 600;
  white-space: nowrap;
  user-select: none;
}

.size-small {
  font-size: 0.7rem;
  padding: 0.15rem 0.5rem;
}

.size-medium {
  font-size: 0.8rem;
  padding: 0.25rem 0.65rem;
}

.size-large {
  font-size: 0.95rem;
  padding: 0.35rem 0.85rem;
}
</style>
