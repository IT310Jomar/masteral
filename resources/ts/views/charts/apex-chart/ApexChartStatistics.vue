<script lang="ts" setup>
import VueApexCharts from 'vue3-apexcharts'
import { useTheme } from 'vuetify'
import { getRadialBarChartConfig } from '@core/libs/apex-chart/apexCharConfig'
import axios from "axios";
import { environment } from "@/environments/environment";

const vuetifyTheme = useTheme()
const token = JSON.parse(localStorage.getItem("accessToken") || "{}");
const statisticsChartConfig = computed(() => getRadialBarChartConfig(vuetifyTheme.current.value))


const jorunalCount= async() => {
  try{
    const response = await axios.get(environment.API_URL + 'auth/get-request-data', {
      params: {
        token:token,
      },
    });
    if (response.data.success){
      series.value[0] = response.data.pending;
      series.value[1] = response.data.approved;
      series.value[2] = response.data.rejected;
    }

  }
  catch{

  }
}

onMounted(() => {
  jorunalCount()
})

const series = ref([]);
</script>

<template>
  <VueApexCharts
    type="radialBar"
    height="400"
    :options="statisticsChartConfig"
    :series="series"
  />
</template>
