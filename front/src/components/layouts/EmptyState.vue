<template>
  <div class="empty-state text-center py-12 d-flex flex-column align-center">
    <v-icon :size="iconSize" color="grey-lighten-1" class="mb-4">{{ icon }}</v-icon>
    <p class="text-h6 text-grey-darken-1 mb-0">{{ title }}</p>
    <p v-if="description" class="text-body-2 text-grey mt-2 mb-0">{{ description }}</p>
    <div v-if="$slots.action" class="mt-6">
      <slot name="action" />
    </div>
  </div>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
  icon: string;
  title: string;
  description?: string;
  iconSize?: number | string;
}>(), {
  iconSize: 80,
});
</script>
