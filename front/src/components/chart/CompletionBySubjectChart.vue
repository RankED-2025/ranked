<script setup lang="ts">
import type { CompletionBySubjectPoint } from '@/types/component/chart/completion-by-subject.ts'
import { computed } from 'vue'
import { Doughnut } from 'vue-chartjs'
import type { ChartData, ChartOptions } from 'chart.js'

type Props = {
  points: CompletionBySubjectPoint[]
}

const props = defineProps<Props>()

const computedData = computed<ChartData<'doughnut'>>(() => ({
  labels: props.points.map((p) => p.subject),
  datasets: [
    {
      data: props.points.map((p) => p.average),
      label: 'Avg completion (%)',
    },
  ],
}))

const options: ChartOptions<'doughnut'> = {
  responsive: true,
  plugins: {
    legend: { position: 'right' },
  },
}
</script>

<template>
  <div>
    <Doughnut :data="computedData" :options="options" />
  </div>
</template>

<style scoped></style>
