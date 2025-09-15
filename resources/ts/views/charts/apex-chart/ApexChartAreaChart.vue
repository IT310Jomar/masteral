<script lang="ts" setup>
import VueApexCharts from "vue3-apexcharts";
import { useTheme } from "vuetify";
import { getAreaChartSplineConfig } from "@core/libs/apex-chart/apexCharConfig";
import axios from "axios";
import { environment } from "@/environments/environment";

const token = JSON.parse(localStorage.getItem("accessToken") || "{}");
const vuetifyTheme = useTheme();

const chartConfig = computed(() =>
  getAreaChartSplineConfig(vuetifyTheme.current.value)
);

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
const paidData = () => {
  axios
    .get(environment.API_URL + "auth/get-payments-data", {
      params: {
        year: selectedYear.value || (selectedYear.value = new Date().getFullYear()),
        token:token,
      },
    })
    .then((response) => {
      data.value = response.data.data;
      row.value = response.data.row;
      updateSeries();
      // console.log(row.value);
    });
};

const series = ref([
  {
    name: "Paid Accounts",
    data: [], // Initialize with an empty array
  },
  {
    name: "Journal Request",
    data: [], // Initialize with an empty array
  },
]);
// const updateSeries = () => {
//   // series.value[0].data = [january.value, february.value, march.value, april.value, may.value, june.value, july.value, august.value, september.value, october.value, november.value, december.value];
//   series.value[0].data = [data.value.January,data.value.February,data.value.March,data.value.April,data.value.May,data.value.June,data.value.July,data.value.August,data.value.September,data.value.October,data.value.November,data.value.December]
// };
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
  paidData();
  populateYears();
});
</script>

<template>
 <VCol   class="text-left  mr-6">
  <v-select
    v-model="selectedYear"
    :items="years"
    label="Select Year"
    @update:model-value="paidData"
    solo
    class="ml-auto"
    style="max-width: 250px;"
    chips
   
  

  ></v-select>
</VCol>
  <VueApexCharts
    type="area"
    height="400"
    :options="chartConfig"
    :series="series"
  />
</template>
