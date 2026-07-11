<template>
  <div :style="{ position: 'relative', height: `${height}px` }">
    <component :is="chartComponents[type]" :data="data" :options="mergedOptions" />
  </div>
</template>

<script setup lang="ts">
import type { Component } from 'vue'
import { computed } from 'vue'
import { Bar, Line, Doughnut, Pie, Radar } from 'vue-chartjs'
import type { ChartData, ChartOptions, ChartType } from 'chart.js'

const props = defineProps<{
  /** Type de chart Chart.js à rendre */
  type: 'bar' | 'line' | 'doughnut' | 'pie' | 'radar'
  data: ChartData<ChartType>
  options?: ChartOptions<ChartType>
  /** Hauteur fixée du canvas en px (défaut : 220) */
  height?: number
}>()

const chartComponents: Record<string, Component> = {
  bar: Bar,
  line: Line,
  doughnut: Doughnut,
  pie: Pie,
  radar: Radar,
}

const mergedOptions = computed(() => ({
  maintainAspectRatio: false,
  ...props.options,
}))

const height = computed(() => props.height ?? 220)
</script>
