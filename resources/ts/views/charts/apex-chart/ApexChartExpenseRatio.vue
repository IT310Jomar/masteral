<script lang="ts" setup>
import VueApexCharts from 'vue3-apexcharts'
import { useTheme } from 'vuetify'
import { getDonutChartConfig } from '@core/libs/apex-chart/apexCharConfig'
import axios from "axios";
import { environment } from "@/environments/environment";

const vuetifyTheme = useTheme()
const token = JSON.parse(localStorage.getItem("accessToken") || "{}");
const expenseRationChartConfig = computed(() => getDonutChartConfig(vuetifyTheme.current.value))


const operational= async() => {
  try{
    const response = await axios.get(environment.API_URL + 'auth/get-operational-data', {
      params: {
        token:token,
      },
    });
    if (response.data.success){
      series.value[0] = response.data.request;
      series.value[1] = response.data.payment;
      series.value[2] = response.data.client;
      series.value[3] = response.data.publish;
    }

  }
  catch{

  }
}

onMounted(() => {
  operational()
})


const series = ref([])
</script>

<template>
  <VueApexCharts
    type="donut"
    height="410"
    :options="expenseRationChartConfig"
    :series="series"
  />
</template>
