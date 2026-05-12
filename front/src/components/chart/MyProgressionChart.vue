<script setup lang="ts">
import type { MyProgressionPoint } from '@/types'
import { computed } from 'vue'
import { Bar } from 'vue-chartjs'
import type { ChartData, ChartOptions } from 'chart.js'
import { getRotatingColors } from '@/constants/chartColors.ts'

type Props = {
  points: MyProgressionPoint[]
}

const props = defineProps<Props>()

const computedData = computed<ChartData<'bar'>>(() => {
  const { bg, border } = getRotatingColors(props.points.length)
  return {
    labels: props.points.map((p) => p.title),
    datasets: [
      {
        data: props.points.map((p) => p.percentage),
        label: 'Complétion (%)',
        backgroundColor: bg,
        borderColor: border,
        borderWidth: 1,
      },
    ],
  }
})

const options: ChartOptions<'bar'> = {
  indexAxis: 'y',
  responsive: true,
  plugins: { legend: { display: false } },
  scales: { x: { min: 0, max: 100 } },
}
</script>

<template>
  <div>
    <Bar :data="computedData" :options="options" />
  </div>
</template>

<style scoped></style>
