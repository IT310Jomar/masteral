<script setup lang="ts">
import type { ChartJsCustomColors } from "@/pages/chart/chartjs/types";
import { useTheme } from "vuetify";
import { getPolarChartConfig } from "@core/libs/chartjs/chartjsConfig";
import PolarAreaChart from "@core/libs/chartjs/components/PolarAreaChart";
import axios from "axios";
import { environment } from "@/environments/environment";


interface Props {
  colors: ChartJsCustomColors;
}

const props = defineProps<Props>();
const vuetifyTheme = useTheme();
const chartConfig = computed(() => getPolarChartConfig(vuetifyTheme.current.value));
const regularOrProb = ref();
const contigents = ref();
const onLeave = ref()



const getHeadCountsRegOrProb = () => {
  return axios
    .get(environment.API_URL + "user/dashboard/headcounts/employees/regular")
    .then((response) => {
      if (response.data.success) {
        regularOrProb.value = response.data.counts;
      }
    });
};

const getHeadCountsContingets = () => {
  return axios
    .get(environment.API_URL + "user/dashboard/headcounts/employees/contigents")
    .then((response) => {
      if (response.data.success) {
        contigents.value = response.data.conti;
        onLeave.value = response.data.onLeave
      }
    });
};


const d = ref();
let con = ref()
let reg = ref()
let leave = ref()
onMounted(async () => {
  await Promise.all([getHeadCountsContingets(), getHeadCountsRegOrProb()]);
  const total = contigents.value + regularOrProb.value + onLeave.value;
  const contigentsPercentage = ((contigents.value / total) * 100).toFixed() + "%";
  const regularOrProbPercentage = ((regularOrProb.value / total) * 100).toFixed() + "%";
  const onLeavePercentage = ((onLeave.value / total) * 100).toFixed() + "%";
  //  console.log(total)
  const data = {
    labels: ["Contingent" + ' ' + contigentsPercentage, "Active (Regular)" + ' ' + regularOrProbPercentage, 'On Leave' + ' ' + onLeavePercentage],
    datasets: [
      {
        borderWidth: 0,
        label: [
          `Contingent`,
          `Active (Regular)`,
          `On Leave`,
        ],
        data: [contigents.value, regularOrProb.value, onLeave.value],
        // data: [0, 0],
        backgroundColor: [props.colors.polarChartWarning, props.colors.polarChartInfo, props.colors.polarChartGreen],
        hoverBackgroundColor: [
          props.colors.polarChartWarning,
          props.colors.polarChartInfo,
          props.colors.polarChartGreen,
        ],
        hoverBorderColor: "transparent",
        hoverBorderWidth: 0,
      },

    ],
  };
  d.value = data;
  con = d.value.datasets[0].data[0]
  reg = d.value.datasets[0].data[1]
  leave = d.value.datasets[0].data[2]

});

</script>
<template>
  <section>
    <table class="borderless-table">
      <tr v-if="con !== 0 || reg !== 0 || leave !== 0">
        <td>
          <PolarAreaChart :height="360"  :chart-data="d" :chart-options="chartConfig" />
        </td>
      </tr>
      <tr v-else>
        <td style="text-align: center; border: none;">
          No Data Found!
        </td>
      </tr>
    </table>
  </section>
</template> 
<style scoped>
.borderless-table {
  border-collapse: collapse;
  border: none;
}
.borderless-table td {
  border: none;
}
</style>

