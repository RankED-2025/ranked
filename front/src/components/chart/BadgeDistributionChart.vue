<script setup lang="ts">
import type { BadgeDistributionPoint } from '@/types'
import { computed } from 'vue'
import { Pie } from 'vue-chartjs'
import type { ChartData, ChartOptions } from 'chart.js'
import { getBadgeColors } from '@/constants/chartColors.ts'

type Props = {
  points: BadgeDistributionPoint[]
}

const props = defineProps<Props>()

const computedData = computed<ChartData<'pie'>>(() => {
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

const options: ChartOptions<'pie'> = {
  responsive: true,
  plugins: { legend: { position: 'right' } },
}
</script>

<template>
  <div>
    <Pie :data="computedData" :options="options" />
  </div>
</template>

<style scoped></style>
