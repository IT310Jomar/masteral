<script lang="ts" setup>
import VueApexCharts from 'vue3-apexcharts'
import { useTheme } from 'vuetify'
import { getLineChartSimpleConfig } from '@core/libs/apex-chart/apexCharConfig'
import axios from "axios";
import { environment } from "@/environments/environment";
const token = JSON.parse(localStorage.getItem("accessToken") || "{}");
const vuetifyTheme = useTheme()

const balanceChartConfig = computed(() => getLineChartSimpleConfig(vuetifyTheme.current.value))

const years = ref<number[]>([]);
const selectedYear = ref<number | null>(null);
const totalSales = ref()
const populateYears = () => {
  const currentYear = new Date().getFullYear();
  for (let year = currentYear; year >= currentYear - 10; year--) {
    years.value.push(year);
  }
};
const sales = ref()
const salesStats = () => {
  axios
    .get(environment.API_URL + "auth/get-sales-request-data", {
      params: {
        year:
          selectedYear.value || (selectedYear.value = new Date().getFullYear()),
          token: token
      },
    })
    .then((response) => {
      sales.value = response.data.sales;
      totalSales.value = sales.value.Total
      // console.log(sales.value.Total)
      updateSeries();
     
    });
};


const series = ref([
  {
    data: [],
  },
])
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
  series.value[0].data = months.map((month) => sales.value[month]);
  // series.value[1].data = months.map((month) => row.value[month]);
};

onMounted(() => {
  salesStats();
  populateYears();
});
const formattedTotalSales = computed(() => {
  // Format the totalSales value as "Php 4,000.00"
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
  }).format(totalSales.value);
});
</script>

<template>
  <section>
<VRow class="mt-4">
 <VCol class="text-right ml-4">
    <VTextField
    v-model="formattedTotalSales"
    readonly
    label="Total Sales"
    style="max-width: 250px;">
    </VTextField>
  </VCol>
    <VCol   class="text-left mr-6">
  <v-select
    v-model="selectedYear"
    :items="years"
    label="Select Year"
    @update:model-value="salesStats"
    solo
    class="ml-auto"
    style="max-width: 250px;"
    chips
  ></v-select>
</VCol>
</VRow>
   
  <VueApexCharts
    type="line"
    height="400"
    :options="balanceChartConfig"
    :series="series"
  />
</section>
</template>
