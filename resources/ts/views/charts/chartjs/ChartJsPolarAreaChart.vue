<script setup lang="ts">
import { useTheme } from 'vuetify'
import type { ChartJsCustomColors } from '@/views/demos/charts-and-maps/charts/chartjs/types'
import { getPolarChartConfig } from '@core/libs/chartjs/chartjsConfig'
import PolarAreaChart from '@core/libs/chartjs/components/PolarAreaChart'
import axios from "axios";
import { environment } from "@/environments/environment";

interface Props {
  colors: ChartJsCustomColors
}

const props = defineProps<Props>()

const vuetifyTheme = useTheme()

const chartConfig = computed(() => getPolarChartConfig(vuetifyTheme.current.value))

const pending = ref();
const approved = ref();
const rejected = ref();
const clientID = JSON.parse(localStorage.getItem("clientData") || "{}");

const getClientRequests = () => {
  return axios
    .get(environment.API_URL + "client/get-request-data/" + clientID[0].id)
    .then((response) => {
      if (response.data.success) {
        pending.value = response.data.pending;
        approved.value = response.data.approved;
        rejected.value = response.data.rejected;
      }
    });
};

const d = ref();
let con = ref();
let reg = ref();
let leave = ref();
onMounted(async () => {
  // console.log(clientID[0].id)
  await Promise.all([getClientRequests()]);
  
  const total = pending.value + approved.value + rejected.value;
  const pendingRate = (total !== 0 && !isNaN(pending.value) ? ((pending.value / total) * 100).toFixed(2) : 0) + "%";
  const approvedRate = (total !== 0 && !isNaN(approved.value) ? ((approved.value / total) * 100).toFixed(2) : 0) + "%";
 const rejectedRate = (total !== 0 && !isNaN(rejected.value) ? ((rejected.value / total) * 100).toFixed(2) : 0) + "%";



  const data = {
    labels: [
      "Pending" + " " + pendingRate ??  0,
      "Approved" + " " + approvedRate,
      "Rejected" + " " + rejectedRate,
    ],
    datasets: [
      {
        borderWidth: 0,
        label: [`Pending`, `Approved`, `Rejected`],
        data: [pending.value, approved.value, rejected.value],
        // data: [0, 0],
        backgroundColor: [
          props.colors.lineChartWarning,
          props.colors.polarChartInfo,
          props.colors.polarChartGreen,
        ],
        hoverBackgroundColor: [
          props.colors.lineChartWarning,
          props.colors.polarChartInfo,
          props.colors.polarChartGreen,
        ],
        hoverBorderColor: "transparent",
        hoverBorderWidth: 0,
      },
    ],
  };
  d.value = data;
  con = d.value.datasets[0].data[0];
  reg = d.value.datasets[0].data[1];
  leave = d.value.datasets[0].data[2];
});
</script>

<template>
  <PolarAreaChart
    :height="400"
    :chart-data="d"
    :chart-options="chartConfig"
  />
</template>
