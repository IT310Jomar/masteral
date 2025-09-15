<script setup lang="ts">
import { useTheme } from 'vuetify'
import { getLineChartConfig } from '@core/libs/chartjs/chartjsConfig'
import LineChart from '@core/libs/chartjs/components/LineChart'
import type { ChartJsCustomColors } from '@/views/charts/chartjs/types'
import axios from "axios";
import { environment } from "@/environments/environment";

interface Props {
  colors: ChartJsCustomColors
}
const token = JSON.parse(localStorage.getItem("accessToken") || "{}");
const publicSchool = ref()
const privateSchool = ref()
const props = defineProps<Props>()
const vuetifyTheme = useTheme()

const typeSchool = async () => {
  try {
    const response = await axios.get(environment.API_URL + "auth/get-school-type-data", {
      params: {
        year: selectedYear.value || (selectedYear.value = new Date().getFullYear()),
        token:token,
      },
    });

     publicSchool.value = response.data.public;
     privateSchool.value = response.data.private;
       initializeData()
  } catch (error) {
    console.error('Error fetching data:', error);
  }
};
const data = ref()

async function initializeData() {
 data.value = {
  labels: [    
      "January",
      "February",
      "March",
      "April",
      "May",
      "June",
      "July",
      "August",
      "September",
      "October",
      "November",
      "December",],
  datasets: [
    {
      fill: false,
      tension: 0.5,
      pointRadius: 1,
      label: 'Public School',
      pointHoverRadius: 5,
      pointStyle: 'circle',
      borderColor: props.colors.primary,
      backgroundColor: props.colors.primary,
      pointHoverBorderWidth: 5,
      pointHoverBorderColor: props.colors.white,
      pointBorderColor: 'transparent',
      pointHoverBackgroundColor: props.colors.primary,
      data: [publicSchool.value.January, publicSchool.value.February, publicSchool.value.March, publicSchool.value.April, publicSchool.value.May, publicSchool.value.June, publicSchool.value.July, publicSchool.value.August, publicSchool.value.September, publicSchool.value.October, publicSchool.value.November, publicSchool.value.December],
    },
    {
      fill: false,
      tension: 0.5,
      label: 'Private Scool',
      pointRadius: 1,
      pointHoverRadius: 5,
      pointStyle: 'circle',
      borderColor: props.colors.warningShade,
      backgroundColor: props.colors.warningShade,
      pointHoverBorderWidth: 5,
      pointHoverBorderColor: props.colors.white,
      pointBorderColor: 'transparent',
      pointHoverBackgroundColor: props.colors.warningShade,
      data: [privateSchool.value.January, privateSchool.value.February, privateSchool.value.March, privateSchool.value.April, privateSchool.value.May, privateSchool.value.June, privateSchool.value.July, privateSchool.value.August, privateSchool.value.September, privateSchool.value.October, privateSchool.value.November, privateSchool.value.December],
    },
  
  ],
}
}
const years = ref<number[]>([]);
const selectedYear = ref<number | null>(null);

const populateYears = () => {
  const currentYear = new Date().getFullYear();
  for (let year = currentYear; year >= currentYear - 10; year--) {
    years.value.push(year);
  }
};
onMounted(async() => {
   await typeSchool();
  populateYears();
});

const chartConfig = computed(() => getLineChartConfig(vuetifyTheme.current.value))
</script>

<template>
     <VCol   class="text-left  mr-6">
  <v-select
    v-model="selectedYear"
    :items="years"
    label="Select Year"
    @update:model-value="typeSchool"
    solo
    class="ml-auto"
    style="max-width: 250px;"
    chips
  ></v-select>
  </VCol>
  <LineChart
    :chart-options="chartConfig"
    :height="400"
    :chart-data="data"
  />
</template>
