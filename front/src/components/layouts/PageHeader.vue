<template>
  <v-card elevation="1" rounded="lg" class="page-header-card mb-6">
    <v-card-text class="py-3 px-4 d-flex align-center justify-space-between flex-wrap ga-3">
      <div class="d-flex align-center ga-3">
        <v-btn v-if="showBack" icon variant="text" size="small" @click="emit('back')">
          <v-icon>mdi-arrow-left</v-icon>
        </v-btn>
        <v-avatar v-if="icon" size="34" color="primary" variant="tonal" rounded="lg">
          <v-icon size="16">{{ icon }}</v-icon>
        </v-avatar>
        <div>
          <p class="text-body-2 font-weight-bold mb-0">{{ title }}</p>
          <p v-if="subtitle" class="text-caption text-medium-emphasis mb-0">{{ subtitle }}</p>
        </div>
      </div>

      <div v-if="$slots.actions" class="d-flex align-center ga-2">
        <slot name="actions" />
      </div>
    </v-card-text>
  </v-card>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
  title: string;
  subtitle?: string;
  icon?: string;
  showBack?: boolean;
}>(), {
  showBack: false,
});

const emit = defineEmits<{
  back: [];
}>();
</script>

<style scoped>
.page-header-card {
  border-left: 3px solid var(--primary-color) !important;
}
</style>
