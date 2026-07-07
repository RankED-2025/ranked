<script setup lang="ts">
import type { MyBadgePoint } from '@/types'
import { computed } from 'vue'
import type { ChartData, ChartOptions } from 'chart.js'
import { getBadgeColors } from '@/constants/chartColors.ts'
import AppChart from './AppChart.vue'

type Props = {
  points: MyBadgePoint[]
}

const props = defineProps<Props>()

const computedData = computed<ChartData<'doughnut'>>(() => {
  const { bg, border } = getBadgeColors(props.points.map((p) => p.type))
  return {
    labels: props.points.map((p) => p.type),
    datasets: [
      {
        data: props.points.map((p) => p.count),
        label: 'Badges',
        backgroundColor: bg,
        borderColor: border,
        borderWidth: 2,
      },
    ],
  }
})

const options: ChartOptions<'doughnut'> = {
  responsive: true,
  plugins: { legend: { position: 'right' } },
}
</script>

<template>
  <div>
    <AppChart type="doughnut" :data="computedData" :options="options" />
  </div>
</template>

<style scoped></style>
