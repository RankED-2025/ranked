<template>
  <div v-if="isOpen" class="modal-overlay" @click="onBackdropClick">
    <div class="modal-content" @click.stop>
      <h2>{{ props.title }}</h2>
      <p>{{ props.message }}</p>
      <div class="modal-actions">
        <button @click="onCancel" class="cancel-button">{{ props.cancelText }}</button>
        <button @click="onConfirm" :disabled="props.isLoading" class="confirm-button" :class="{ loading: props.isLoading }">
          {{ props.isLoading ? props.loadingText : props.confirmText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

interface Props {
  title?: string;
  message: string;
  confirmText?: string;
  cancelText?: string;
  isLoading?: boolean;
  loadingText?: string;
}

const props = withDefaults(defineProps<Props>(), {
  title: 'Confirmation',
  confirmText: 'Confirmer',
  cancelText: 'Annuler',
  isLoading: false,
  loadingText: 'Suppression...',
});

const emit = defineEmits<{
  confirm: [];
  cancel: [];
}>();

const isOpen = ref(false);

const open = () => {
  isOpen.value = true;
};

const close = () => {
  isOpen.value = false;
};

const onConfirm = () => {
  emit('confirm');
};

const onCancel = () => {
  close();
  emit('cancel');
};

const onBackdropClick = () => {
  close();
};

defineExpose({
  open,
  close,
});
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 8px;
  padding: 24px;
  max-width: 400px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.modal-content h2 {
  margin-top: 0;
  margin-bottom: 12px;
  font-size: 1.25rem;
}

.modal-content p {
  margin: 12px 0 24px;
  color: #666;
}

.modal-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

.cancel-button,
.confirm-button {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.cancel-button {
  background-color: #e0e0e0;
  color: #333;
}

.cancel-button:hover {
  background-color: #d0d0d0;
}

.confirm-button {
  background-color: #d32f2f;
  color: white;
}

.confirm-button:hover:not(:disabled) {
  background-color: #b71c1c;
}

.confirm-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.confirm-button.loading {
  opacity: 0.8;
}
</style>
