<template>
  <div class="loading-element" :class="`spinner-${props.size}`">
    <div class="spinner"></div>
  </div>
</template>

<script setup lang="ts">
const props = withDefaults(defineProps<{
  size?: 'small' | 'medium' | 'large';
}>(), {
  size: 'medium',
});
</script>

<style scoped>
.loading-element {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
}

.spinner {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #3498db;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
}

.spinner-small {
  padding: 0.75rem;
}

.spinner-small .spinner {
  width: 40px;
  height: 40px;
  border-width: 3px;
}

.spinner-medium {
  padding: 1.25rem;
}

.spinner-medium .spinner {
  width: 60px;
  height: 60px;
  border-width: 4px;
}

.spinner-large {
  padding: 2rem;
}

.spinner-large .spinner {
  width: 80px;
  height: 80px;
  border-width: 5px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

</style>
