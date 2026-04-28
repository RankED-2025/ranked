<script setup lang="ts">
import type { BadgeDistributionPoint } from '@/types/component/chart/badge-distribution.ts'
import { computed } from 'vue'
import { Pie } from 'vue-chartjs'
import type { ChartData, ChartOptions } from 'chart.js'

type Props = {
  points: BadgeDistributionPoint[]
}

const props = defineProps<Props>()

const computedData = computed<ChartData<'pie'>>(() => ({
  labels: props.points.map((p) => p.type),
  datasets: [
    {
      data: props.points.map((p) => p.count),
      label: 'Badges',
    },
  ],
}))

const options: ChartOptions<'pie'> = {
  responsive: true,
  plugins: {
    legend: { position: 'right' },
  },
}
</script>

<template>
  <div>
    <Pie :data="computedData" :options="options" />
  </div>
</template>

<style scoped></style>
