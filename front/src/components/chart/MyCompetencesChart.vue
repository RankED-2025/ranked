<script setup lang="ts">
import type { MyCompetencePoint } from '@/types/component/chart/my-competences.ts'
import { computed } from 'vue'
import { Radar } from 'vue-chartjs'
import type { ChartData, ChartOptions } from 'chart.js'
import { getRotatingColors } from '@/constants/chartColors.ts'

type Props = {
  points: MyCompetencePoint[]
}

const props = defineProps<Props>()

const computedData = computed<ChartData<'radar'>>(() => {
  const { bg, border } = getRotatingColors(1)
  return {
    labels: props.points.map((p) => p.matiere),
    datasets: [
      {
        data: props.points.map((p) => p.percentage),
        label: 'Compétences acquises (%)',
        backgroundColor: bg[0],
        borderColor: border[0],
        fill: true,
      },
    ],
  }
})

const options: ChartOptions<'radar'> = {
  responsive: true,
  scales: { r: { min: 0, max: 100, ticks: { stepSize: 20 } } },
}
</script>

<template>
  <div>
    <Radar :data="computedData" :options="options" />
  </div>
</template>

<style scoped></style>
