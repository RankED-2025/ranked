<template>
  <v-dialog :model-value="props.modelValue" max-width="420" @update:model-value="close">
    <v-card rounded="lg" class="confirmation-card">
      <v-card-title class="confirmation-title">{{ props.title }}</v-card-title>
      <v-card-text class="confirmation-message">{{ props.message }}</v-card-text>
      <v-card-actions class="confirmation-actions">
        <v-spacer />
        <v-btn variant="text" @click="onCancel">{{ props.cancelText }}</v-btn>
        <v-btn
          color="error"
          variant="elevated"
          :loading="props.isLoading"
          :disabled="props.isLoading"
          @click="onConfirm"
        >
          {{ props.isLoading ? props.loadingText : props.confirmText }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup lang="ts">

interface Props {
  modelValue: boolean;
  title?: string;
  message: string;
  confirmText?: string;
  cancelText?: string;
  isLoading?: boolean;
  loadingText?: string;
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: false,
  title: 'Confirmation',
  confirmText: 'Confirmer',
  cancelText: 'Annuler',
  isLoading: false,
  loadingText: 'Suppression...',
});

const emit = defineEmits<{
  'update:modelValue': [value: boolean];
  confirm: [];
  cancel: [];
}>();

const close = () => {
  emit('update:modelValue', false);
};

const onConfirm = () => {
  emit('confirm');
};

const onCancel = () => {
  close();
  emit('cancel');
};
</script>

<style scoped>
.confirmation-card {
  box-shadow: var(--shadow-lg) !important;
}

.confirmation-title {
  font-size: var(--font-size-lg);
  font-weight: 700;
  color: var(--text-color);
  padding: var(--spacing-lg) var(--spacing-lg) var(--spacing-sm);
}

.confirmation-message {
  color: var(--text-muted-color);
  padding: 0 var(--spacing-lg) var(--spacing-md);
}

.confirmation-actions {
  padding: 0 var(--spacing-md) var(--spacing-md);
}
</style>
