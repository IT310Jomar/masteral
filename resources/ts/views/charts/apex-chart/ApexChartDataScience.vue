<script lang="ts" setup>
import VueApexCharts from 'vue3-apexcharts'
import { useTheme } from 'vuetify'
import { getColumnChartConfig } from '@core/libs/apex-chart/apexCharConfig'
import axios from "axios";
import { environment } from "@/environments/environment";

const vuetifyTheme = useTheme()

const chartConfig = computed(() => getColumnChartConfig(vuetifyTheme.current.value))
const token = JSON.parse(localStorage.getItem("accessToken") || "{}");

const series = ref([
  {
    name: 'Registered Clients',
    data: [],
  },
  {
    name: 'Published Journal',
    data: [],
  },
])
const years = ref<number[]>([]);
const selectedYear = ref<number | null>(null);

const populateYears = () => {
  const currentYear = new Date().getFullYear();
  for (let year = currentYear; year >= currentYear - 10; year--) {
    years.value.push(year);
  }
};

const data = ref();
const row = ref();
const statisticsData = () => {
  axios
    .get(environment.API_URL + "auth/get-statistics-data", {
      params: {
        year:
          selectedYear.value || (selectedYear.value = new Date().getFullYear()),
          token:token
      },
    })
    .then((response) => {
      data.value = response.data.data;
      row.value = response.data.row;
      updateSeries();
     
    });
};
const updateSeries = () => {
  const months = [
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
    "December",
  ];
  series.value[0].data = months.map((month) => data.value[month]);
  series.value[1].data = months.map((month) => row.value[month]);
};
onMounted(() => {
   statisticsData();
  populateYears();
});
</script>

<template>
   <VCol   class="text-left  mr-6">
  <v-select
    v-model="selectedYear"
    :items="years"
    label="Select Year"
    @update:model-value="statisticsData"
    solo
    class="ml-auto"
    style="max-width: 250px;"
  ></v-select>
</VCol>
  <VueApexCharts
    type="bar"
    height="400"
    :options="chartConfig"
    :series="series"
  />
</template>
