<template>
  <v-card :hover="hover" elevation="0" rounded="lg" class="app-card">
    <slot />
  </v-card>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
  hover?: boolean;
}>(), {
  hover: false,
});
</script>

<style scoped>
.app-card {
  box-shadow: var(--shadow-sm) !important;
  transition: box-shadow 0.2s ease, transform 0.2s ease;
}

.app-card:hover {
  box-shadow: var(--shadow-md) !important;
}
</style>
