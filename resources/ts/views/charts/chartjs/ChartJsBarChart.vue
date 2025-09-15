<script setup lang="ts">
import { useTheme } from "vuetify";
import BarChart from "@/@core/libs/chartjs/components/BarChart";
import { getLatestBarChartConfig } from "@core/libs/chartjs/chartjsConfig";
import axios from "axios";
import { environment } from "@/environments/environment";

const editor_id = JSON.parse(localStorage.getItem("editorData") || "{}");
const vuetifyTheme = useTheme();
const published = ref()
const chartOptions = computed(() =>
  getLatestBarChartConfig(vuetifyTheme.current.value)
);

const publishedData = async () => {
  try {
    const response = await axios.get(environment.API_URL + "editor/get-request-published-editor/" + editor_id[0]?.id, {
      params: {
        year: selectedYear.value || (selectedYear.value = new Date().getFullYear()),
      },
    });

      published.value = response.data.published;
      initializeData()
  } catch (error) {
    console.error('Error fetching data:', error);
  }
};
const data = ref()
async function initializeData() {

  

   // Ensure that january.value is set before using it
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
      "December",
    ],
    datasets: [
      {
        maxBarThickness: 15,
        backgroundColor: "#ffcf5c",
        borderColor: "transparent",
        borderRadius: { topRight: 15, topLeft: 15 },
        data: [published.value.January,published.value.February,published.value.March,published.value.April,published.value.May,published.value.June,published.value.July,published.value.August,published.value.September,published.value.October,published.value.November,published.value.December],
      },
    ],
  };
};

const years = ref<number[]>([]);
const selectedYear = ref<number | null>(null);

const populateYears = () => {
  const currentYear = new Date().getFullYear();
  for (let year = currentYear; year >= currentYear - 10; year--) {
    years.value.push(year);
  }
};
onMounted(async() => {
  await publishedData();
  populateYears();
});
</script>

<template>
   <VCol   class="text-left  mr-6">
  <v-select
    v-model="selectedYear"
    :items="years"
    label="Select Year"
    @update:model-value="publishedData"
    solo
    class="ml-auto"
    style="max-width: 250px;"
    chips
  ></v-select>
  </VCol>
  <BarChart :height="400" :chart-data="data" :chart-options="chartOptions" />
</template>
