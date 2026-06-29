<script setup lang="ts">
import type { MyClassRank } from '@/types'
import { computed } from 'vue'
import type { ChartData, ChartOptions } from 'chart.js'
import { getRotatingColors } from '@/constants/chartColors.ts'
import AppChart from './AppChart.vue'

type Props = {
  rank: MyClassRank
}

const props = defineProps<Props>()

const computedData = computed<ChartData<'bar'>>(() => {
  const { bg, border } = getRotatingColors(1)
  return {
    labels: ['Mon percentile'],
    datasets: [
      {
        data: [props.rank.percentile],
        label: 'Percentile',
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
    <AppChart type="bar" :data="computedData" :options="options" />
    <p class="rank-label">
      Rang {{ rank.rank }} / {{ rank.total }} &nbsp;·&nbsp; Moy. {{ rank.myAverage }}%
    </p>
  </div>
</template>

<style scoped>
.rank-label {
  text-align: center;
  margin-top: 0.5rem;
  font-size: 0.9rem;
  opacity: 0.75;
}
</style>
