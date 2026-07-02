<template>
  <v-alert
    v-if="resolved"
    :type="resolved.type"
    variant="tonal"
    class="mb-4"
    closable
    rounded="lg"
    :data-testid="testId"
    @click:close="emit('update:error', null)"
  >
    {{ resolved.message }}
  </v-alert>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { resolveStatusMessage } from '@/utils'
import type { StatusMessageOverride } from '@/types'

interface Props {
  error?: unknown
  overrides?: StatusMessageOverride[]
  testId?: string
}

const props = withDefaults(defineProps<Props>(), {
  overrides: () => [],
  testId: 'error-message',
})

const emit = defineEmits<{
  'update:error': [value: unknown]
}>()

const resolved = computed(() => {
  if (props.error === null || props.error === undefined) return null
  return resolveStatusMessage(props.error, props.overrides)
})
</script>
