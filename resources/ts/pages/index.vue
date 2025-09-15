<script setup lang="ts">
import ApexChartExpenseRatio from "@/views/charts/apex-chart/ApexChartExpenseRatio.vue";
import ApexChartStatistics from "@/views/charts/apex-chart/ApexChartStatistics.vue";
import ChartJsPolarAreaChart from "@/views/charts/chartjs/ChartJsPolarAreaChart.vue";
import ApexChartAreaChart from "@/views/charts/apex-chart/ApexChartAreaChart.vue";
import ApexChartDataScience from "@/views/charts/apex-chart/ApexChartDataScience.vue";
import ChartJsBarChart from "@/views/charts/chartjs/ChartJsBarChart.vue"
import axios from "axios";
import { environment } from "@/environments/environment";
import type { ChartJsCustomColors } from "@/views/charts/chartjs/types";
import ChangePassword from "@/pages/editor/setup-password_Dialog/reset-password.vue";
import nodata from "@images/pages/auth-v2-verify-email-illustration-dark.png";
import nodata1 from "@images/pages/misc-under-maintenance.png";
import ditads from "@images/DITADS Logo_PNG.png";
import { useRouter } from "vue-router";
import { initialAbility } from "@/plugins/casl/ability";
import { useAppAbility } from "@/plugins/casl/useAppAbility";
const userRole = JSON.parse(localStorage.getItem("userRole") || "{}");
const editor_id = JSON.parse(localStorage.getItem("editorData") || "{}");
const token = JSON.parse(localStorage.getItem("accessToken") || "{}");
const router = useRouter();
const ability = useAppAbility();

const dialogValidation = ref(false);

const OpenValidation = () => {
  dialogValidation.value = true;
};

//logout function using remove item in localstorage
const logout = () => {
  localStorage.removeItem("userData");
  localStorage.removeItem("accessToken");
  localStorage.removeItem("userAbilities");
  localStorage.removeItem("userRole");
  localStorage.removeItem("clientData");
  localStorage.removeItem("editorData");

  ability.update(initialAbility);

  router.push('/login'); // Redirect the user to the login page
};


const chartJsCustomColors: ChartJsCustomColors = {
  white: "#fff",
  yellow: "#ffe802",
  primary: "#836af9",
  areaChartBlue: "#2c9aff",
  barChartYellow: "#ffcf5c",
  polarChartGrey: "#4f5d70",
  polarChartInfo: "#ffb6c1",
  lineChartYellow: "#d4e157",
  polarChartGreen: "#28dac6",
  lineChartPrimary: "#9e69fd", 
  lineChartWarning: "#ff9800",
  horizontalBarInfo: "#26c6da",
  polarChartWarning: "#ff8131",
  scatterChartGreen: "#28c76f",
  warningShade: "#ffbd1f",
  areaChartBlueLight: "#84d0ff",
  areaChartGreyLight: "#edf1f4",
  scatterChartWarning: "#ff9f43",
};

//bfp dashboard
const paymentRequestData = async () => {
  try {
    const response = await axios.get(
      environment.API_URL + "auth/get-payment-request-data", {
      params: {
        token:token
      }
    });

    if (response.data.success) {
      const total =
        response.data.pending + response.data.approved + response.data.rejected;
      const percentPending = ((response.data.pending / total) * 100).toFixed(2);
      const percentApproved = ((response.data.approved / total) * 100).toFixed(
        2
      );
      const percentRejected = ((response.data.rejected / total) * 100).toFixed(
        2
      );

      // all request that are pending
      Admin_Meta.value[0].stats = response.data.pending.toString();
      Admin_Meta.value[0].color = "warning";
      Admin_Meta.value[0].title = "Pending - Payment Request";
      Admin_Meta.value[0].icon = "tabler:loader-3";
      Admin_Meta.value[0].percentage = percentPending + "%";

      // all request that are approved
      Admin_Meta.value[1].stats = response.data.approved.toString();
      Admin_Meta.value[1].color = "success";
      Admin_Meta.value[1].title = "Approved - Payment Request";
      Admin_Meta.value[1].icon = "tabler:thumb-up-filled";
      Admin_Meta.value[1].percentage = percentApproved + "%";

      Admin_Meta.value[2].stats = response.data.rejected.toString();
      Admin_Meta.value[2].color = "error";
      Admin_Meta.value[2].title = "Rejected - Payment Request";
      Admin_Meta.value[2].icon = "tabler:thumb-down-filled";
      Admin_Meta.value[2].percentage = percentRejected + "%";
    } else {
    }
  } catch (error) {
    console.log('error');
  }
};

const client_id = JSON.parse(localStorage.getItem("clientData") || "{}");
const paymentRequestDataClient = async () => {
  try {
    const response = await axios.get(
      environment.API_URL + "client/get-payment-request-data/" +  client_id[0].id, {
      params: {
        token:token
      }
    });

    if (response.data.success) {
      const total =
      response.data.pending + response.data.approved + response.data.rejected;

      const percentPending = ((response.data.pending / total) * 100).toFixed(2);
      const percentApproved = ((response.data.approved / total) * 100).toFixed(
        2
      );
      const percentRejected = ((response.data.rejected / total) * 100).toFixed(
        2
      );

      // all request that are pending
     Client_Meta.value[0].stats = response.data.pending.toString();
     Client_Meta.value[0].color = "warning";
     Client_Meta.value[0].title = "Pending - Payment Request";
     Client_Meta.value[0].icon = "tabler:loader-3";
     Client_Meta.value[0].percentage = percentPending + "%";

      // all request that are approved
     Client_Meta.value[1].stats = response.data.approved.toString();
     Client_Meta.value[1].color = "success";
     Client_Meta.value[1].title = "Approved - Payment Request";
     Client_Meta.value[1].icon = "tabler:thumb-up-filled";
     Client_Meta.value[1].percentage = percentApproved + "%";

     Client_Meta.value[2].stats = response.data.rejected.toString();
     Client_Meta.value[2].color = "error";
     Client_Meta.value[2].title = "Rejected - Payment Request";
     Client_Meta.value[2].icon = "tabler:thumb-down-filled";
     Client_Meta.value[2].percentage = percentRejected + "%";
    } else {
    }
  } catch (error) {}
};

const paymentRequestDataEditor = async () => {
  try {
    const response = await axios.get(
      environment.API_URL + "editor/get-request-data-editor/" + editor_id[0]?.id, {
      params: {
        token:token
      }
    });

    if (response.data.success) {
      const total =
      response.data.pending + response.data.approved ;

      const percentPending = ((response.data.pending / total) * 100).toFixed(2);
      const percentApproved = ((response.data.approved / total) * 100).toFixed(
        2
      );
      const percentRejected = ((response.data.total/response.data.total) * 100).toFixed(
        
      );

      // all request that are pending
     Editor_Meta.value[0].stats = response.data.pending.toString();
     Editor_Meta.value[0].color = "warning";
     Editor_Meta.value[0].title = "Pending - Journal Request";
     Editor_Meta.value[0].icon = "tabler:loader-3";
     Editor_Meta.value[0].percentage = percentPending + "%";

      // all request that are approved
     Editor_Meta.value[1].stats = response.data.approved.toString();
     Editor_Meta.value[1].color = "success";
     Editor_Meta.value[1].title = "Approved - Journal Request";
     Editor_Meta.value[1].icon = "tabler:thumb-up-filled";
     Editor_Meta.value[1].percentage = percentApproved + "%";

     Editor_Meta.value[2].stats = response.data.total.toString();
     Editor_Meta.value[2].color = "info";
     Editor_Meta.value[2].title = "Total - Journal Request";
     Editor_Meta.value[2].icon = "tabler:check";
     Editor_Meta.value[2].percentage = percentRejected + "%";
    } else {
    }
  } catch (error) {}
};

const Admin_Meta = ref([{}, {}, {}]);
const Client_Meta = ref([{}, {}, {}]);
const Editor_Meta = ref([{}, {}, {}]);

const clientsData = ref([]);
const getAllClients = () => {
  axios
    .get(environment.API_URL + "auth/admin/get-all-clients", {
      params: {
        token: token
      }
    })
    .then((response) => {
      if (response.data.success) {
        clientsData.value = response.data.client;
        // console.log(clientsData.value);
      }
    }).catch((error) => {
      console.log('error');
      OpenValidation();
      setTimeout(() => {
        location.reload();
        logout();
      }, 5000);
    });
};

const changepasswordDialog = ref(false);

const resetPassword = () => {
  // Add logic to reset the password
  // For example, display a dialog for password reset
  changepasswordDialog.value = true;
};

const editorData = ref();
const getEditor = async () => {
  await axios
    .get(
      environment.API_URL +
        "editor/editor/get-reset-pass-editor/" +
        editor_id[0]?.id
    )
    .then((response) => {
      if (response.data.success) {
        editorData.value = response.data.editor[0]?.reset_password_status;

        // Compare the received hashed password with the hashed input password
        const isMatch = editorData.value === 1;
        // console.log('Passwords Match:', isMatch);

        if (isMatch) {
          resetPassword();
        }
      }
    })
};

onMounted(async () => {
  await getEditor();
  paymentRequestData();
  paymentRequestDataClient();
  paymentRequestDataEditor();
  getAllClients();
});
const items = ref([
  { imageSrc: ditads },
  { imageSrc: nodata1 },
  { imageSrc: nodata },
]);
</script>

<template>
  <section>
    <VCard class="mb-3">
      <VRow>
        <VCol>
          <v-carousel
            style="outline: none"
            hide-delimiter-background
            height="300"
            cycle
          >
            <!-- Carousel items -->
            <v-carousel-item v-for="(item, index) in items" :key="index">
              <v-img :src="item.imageSrc" aspect-ratio="16/9"></v-img>
            </v-carousel-item>
          </v-carousel>
        </VCol>
      </VRow>
    </VCard>

    <VRow v-if="userRole == 'Admin' || userRole == 'God Mode'" class="mb-6">
      <VCol
        v-for="meta in Admin_Meta"
        :key="meta.title"
        cols="12"
        sm="4"
        md="4"
        lg="4"
      >
        <VCard>
          <VCardText class="d-flex justify-space-between">
            <div>
              <h6 class="text-h6 mt-3">{{ meta.title }}</h6>
              <div class="d-flex align-center gap-2 my-1">
                <h3 class="text-h6">
                  {{ meta.stats }}
                </h3>
                <div class="d-flex align-center gap-2 my-1">
                  <h6>
                    <VChip
                      v-if="meta.color == 'warning' && meta.stats != 0"
                      color="warning"
                      variant="text"
                    >
                      <span
                        ><strong>( {{ meta.percentage }}) </strong></span
                      ></VChip
                    >

                    <VChip
                      v-if="meta.color == 'success' && meta.stats != 0"
                      color="success"
                      variant="text"
                    >
                      <span
                        ><strong>( {{ meta.percentage }}) </strong></span
                      ></VChip
                    >
                    <VChip
                      v-if="meta.color == 'error' && meta.stats != 0"
                      color="error"
                      variant="text"
                    >
                      <span v-if="meta.stats != 0"
                        ><strong>( {{ meta.percentage }}) </strong></span
                      >
                    </VChip>
                  </h6>
                </div>
              </div>
              <p class="text-sm text-disabled mt-1 mb-0">{{ meta.subtitle }}</p>
            </div>

            <VAvatar
              rounded
              variant="tonal"
              :color="meta.color"
              :icon="meta.icon"
            />
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
    <VRow v-if="userRole == 'Admin' || userRole == 'God Mode'" class="mb-6">
      <VCol>
        <VCard title="Registered Clients and Published Journal Statistics">
          <VCardText>
            <ApexChartDataScience />
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
    <VRow v-if="userRole == 'Admin' || userRole == 'God Mode'" class="mb-6">
      <VCol cols="12" md="6">
        <VCard title="Journal Requests Status Statistics" height="470">
          <VCardText>
            <ApexChartStatistics />
          </VCardText>
        </VCard>
      </VCol>
      <!-- <VCol class="text-center">
        <VCard style="padding: 200px" height="470">
          <VRow>
            <VCol class="text-center"
              ><VProgressCircular
                :size="80"
                width="3"
                color="primary"
                indeterminate
              />
            </VCol>
          </VRow>
        </VCard>
      </VCol> -->

      <VCol cols="12" md="6">
        <VCard title="Operational Ratio" height="470">
          <VCardText>
            <ApexChartExpenseRatio />
          </VCardText>
        </VCard>
      </VCol>
      <!-- <VCol class="text-center">
        <VCard style="padding: 200px" height="470">
          <VRow>
            <VCol class="text-center"
              ><VProgressCircular
                :size="80"
                width="3"
                color="primary"
                indeterminate
              />
            </VCol>
          </VRow>
        </VCard>
      </VCol> -->
    </VRow>
    <VRow v-if="userRole == 'Admin' || userRole == 'God Mode'" class="mb-6">
      <VCol>
        <VCard title="Paid Accounts and Request Statistics">
          <ApexChartAreaChart />
        </VCard>
      </VCol>
    </VRow>

    <VRow v-if="userRole == 'Client'">
      <VCol
        v-for="meta in Client_Meta"
        :key="meta.title"
        cols="12"
        sm="4"
        md="4"
        lg="4"
      >
        <VCard>
          <VCardText class="d-flex justify-space-between">
            <div>
              <h6 class="text-h6 mt-3">{{ meta.title }}</h6>
              <div class="d-flex align-center gap-2 my-1">
                <h6 class="text-h6">
                  {{ meta.stats }}
                </h6>
                <div class="d-flex align-center gap-2 my-1">
                  <h6 class="text-h6">
                    <VChip
                      v-if="meta.color == 'warning' && meta.stats != 0"
                      color="warning"
                      variant="text"
                    >
                      <span
                        ><strong>( {{ meta.percentage }}) </strong></span
                      ></VChip
                    >
                    <VChip
                      v-if="meta.color == 'success' && meta.stats != 0"
                      color="success"
                      variant="text"
                    >
                      <span
                        ><strong>( {{ meta.percentage }}) </strong></span
                      ></VChip
                    >
                    <VChip
                      v-if="meta.color == 'error' && meta.stats != 0"
                      color="error"
                      variant="text"
                    >
                      <span
                        ><strong>( {{ meta.percentage }}) </strong></span
                      ></VChip
                    >
                  </h6>
                </div>
              </div>
              <p class="text-sm text-disabled mt-1 mb-0">{{ meta.subtitle }}</p>
            </div>

            <VAvatar
              rounded
              variant="tonal"
              :color="meta.color"
              :icon="meta.icon"
            />
          </VCardText>
        </VCard>
      </VCol>
      <VCol>
        <VCard title="Client Request Ratio">
          <VCardText>
            <ChartJsPolarAreaChart :colors="chartJsCustomColors" />
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <VRow v-if="userRole == 'Editor'">
      <VCol
        v-for="meta in Editor_Meta"
        :key="meta.title"
        cols="12"
        sm="4"
        md="4"
        lg="4"
      >
        <VCard>
          <VCardText class="d-flex justify-space-between">
            <div>
              <h6 class="text-h6 mt-3">{{ meta.title }}</h6>
              <div class="d-flex align-center gap-2 my-1">
                <h6 class="text-h6">
                  {{ meta.stats }}
                </h6>
                <div class="d-flex align-center gap-2 my-1">
                  <h6 class="text-h6">
                    <VChip
                      v-if="meta.color == 'warning' && meta.stats != 0"
                      color="warning"
                      variant="text"
                    >
                      <span
                        ><strong>( {{ meta.percentage }}) </strong></span
                      ></VChip
                    >
                    <VChip
                      v-if="meta.color == 'success' && meta.stats != 0"
                      color="success"
                      variant="text"
                    >
                      <span
                        ><strong>( {{ meta.percentage }}) </strong></span
                      ></VChip
                    >
                    <VChip
                      v-if="meta.color == 'info' && meta.stats != 0"
                      color="info"
                      variant="text"
                    >
                      <span
                        ><strong>( {{ meta.percentage }}) </strong></span
                      ></VChip
                    >
                  </h6>
                </div>
              </div>
              <p class="text-sm text-disabled mt-1 mb-0">{{ meta.subtitle }}</p>
            </div>

            <VAvatar
              rounded
              variant="tonal"
              :color="meta.color"
              :icon="meta.icon"
            />
          </VCardText>
        </VCard>
      </VCol>
      <VCol>
        <VCard>
          <VCardText>
            <ChartJsBarChart/>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <VDialog
      v-model="changepasswordDialog"
      scrollable
      max-width="400"
      persistent
    >
      <ChangePassword @close="changepasswordDialog = false" />
    </VDialog>

    <v-dialog v-model="dialogValidation" max-width="540" persistent>
      <!-- <DialogCloseBtn @click="dialogValidation = false" /> -->
      <VCard height="330" flat>
        <VCardText>
          <VRow>
            <VCol class="text-center mt-10">
              <div style="text-align: center;">
                <VIcon color="warning" size="110" icon="bi:exclamation-circle"/><br/><br/>
                <h6 class="text-lg font-weight-large" style="color: #f57c00;">Token Expired</h6>
                <p style="color: #757575;">Please wait to continue.</p>
              </div>
              <!-- <div class="text-center mt-5">
                <VBtn type="submit" @click.prevent="logout" color="primary">Logout</VBtn>
              </div> -->
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
  </v-dialog>
  </section>
</template>
