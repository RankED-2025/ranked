<template>
  <div class="loading-modal">
    <div class="loading-backdrop"></div>
    <div class="loading-modal-panel" :class="`size-${props.size}`">
      <LoadingElement :size="props.size" />

      <p class="loading-message">{{ props.message }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import LoadingElement from './LoadingElement.vue';

const props = withDefaults(defineProps<{
  message?: string;
  size?: 'small' | 'medium' | 'large';
}>(), {
  size: 'medium',
  message: "Chargement en cours, merci de patienter..."
});
</script>

<style scoped>
.loading-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 9999;
  display: grid;
  place-items: center;
}

.loading-backdrop {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
}

.loading-modal-panel {
  position: relative;
  z-index: 1;
  background-color: #fff;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  display: flex;
  justify-content: center;
  align-items: center;
  flex-flow: column nowrap;
  text-align: center;
  padding: 1rem 1.25rem 1.5rem;
}

.size-small {
  width: min(280px, calc(100vw - 2rem));
  min-height: 200px;
}

.size-medium {
  width: min(380px, calc(100vw - 2rem));
  min-height: 250px;
}

.size-large {
  width: min(520px, calc(100vw - 2rem));
  min-height: 300px;
}

.loading-message {
  margin: 0;
  color: #4b5563;
}
</style>
