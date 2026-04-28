<script setup lang="ts">
import type { MyBadgePoint } from '@/types/component/chart/my-badges.ts'
import { computed } from 'vue'
import { Doughnut } from 'vue-chartjs'
import type { ChartData, ChartOptions } from 'chart.js'
import { getBadgeColors } from '@/constants/chartColors.ts'

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
    <Doughnut :data="computedData" :options="options" />
  </div>
</template>

<style scoped></style>
