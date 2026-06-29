<template>
  <component :is="chartComponents[type]" :data="data" :options="options" />
</template>

<script setup lang="ts">
import type { Component } from 'vue'
import { Bar, Line, Doughnut, Pie, Radar } from 'vue-chartjs'
import type { ChartData, ChartOptions } from 'chart.js'

defineProps<{
  /** Type de chart Chart.js à rendre */
  type: 'bar' | 'line' | 'doughnut' | 'pie' | 'radar'
  data: ChartData<any>
  options?: ChartOptions<any>
}>()

const chartComponents: Record<string, Component> = {
  bar: Bar,
  line: Line,
  doughnut: Doughnut,
  pie: Pie,
  radar: Radar,
}
</script>
