<script setup lang="ts">
import { useTheme } from 'vuetify'
import { getLineChartConfig } from '@core/libs/chartjs/chartjsConfig'
import LineChart from '@core/libs/chartjs/components/LineChart'

import type { ChartJsCustomColors } from '@/pages/Dashboard/chartjs/types'

interface Props {
  colors: ChartJsCustomColors
}

const props = defineProps<Props>()

const vuetifyTheme = useTheme()

const data = {
  labels: [0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 110, 120, 130, 140],
  datasets: [
    {
      fill: false,
      tension: 0.5,
      pointRadius: 1,
      label: 'Absent',
      pointHoverRadius: 5,
      pointStyle: 'circle',
      borderColor: props.colors.scatterChartWarning,
      backgroundColor: props.colors.scatterChartWarning,
      pointHoverBorderWidth: 5,
      pointHoverBorderColor: props.colors.white,
      pointBorderColor: 'transparent',
      pointHoverBackgroundColor: props.colors.scatterChartWarning,
      data: [80, 150, 180, 270, 210, 160, 160, 202, 265, 210, 270, 255, 290, 360, 375],
    },
    {
      fill: false,
      tension: 0.5,
      label: 'Late',
      pointRadius: 1,
      pointHoverRadius: 5,
      pointStyle: 'circle',
      borderColor: props.colors.areaChartBlueLight,
      backgroundColor: props.colors.areaChartBlueLight,
      pointHoverBorderWidth: 5,
      pointHoverBorderColor: props.colors.white,
      pointBorderColor: 'transparent',
      pointHoverBackgroundColor: props.colors.areaChartBlueLight,
      data: [80, 125, 105, 130, 215, 195, 140, 160, 230, 300, 220, 170, 210, 200, 280],
    },
  ],
}

const chartConfig = computed(() => getLineChartConfig(vuetifyTheme.current.value))
</script>

<template>
  <LineChart
    :chart-options="chartConfig"
    :height="400"
    :chart-data="data"
  />
</template>
