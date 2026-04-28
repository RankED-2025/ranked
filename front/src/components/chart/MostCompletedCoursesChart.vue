<script setup lang="ts">
import type { MostCompletedCourseSinglePoint } from '@/types/component/chart/most-completed-courses.ts'

type Props = {
  points: MostCompletedCourseSinglePoint[]
}

const props = defineProps<Props>()
</script>

<template>
  <div>
    {{ props.points }}
  </div>
</template>

<style scoped></style>
