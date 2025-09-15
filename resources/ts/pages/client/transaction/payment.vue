<script lang="ts" setup>
import { environment } from "@/environments/environment";
import editpayment from "@/pages/client/transaction/dialog/edit_payment-dialog.vue";
import transaction from "@/pages/client/transaction/dialog/paymentDialog.vue";
import uploadPdf from "@/pages/client/transaction/dialog/upload-dialog.vue";
import viewpayment from "@/pages/client/transaction/dialog/view_payment-dialog.vue";
import logo from "@images/DITADS Logo_PNG.png";
import nodata from "@images/no-data-found.png";
import axios from "axios";
import { debounce } from "lodash";
import { ref } from "vue";
import moment from 'moment';


const token = JSON.parse(localStorage.getItem("accessToken") || "{}");
const tableRef = ref();
const searchQuery = ref(null);
const currentPage = ref(1);
const perPages = [5, 20, 50, 100, 500];
const itemsPerPage = ref(perPages[0]);
const selectedPerPage = ref(itemsPerPage.value);
const paginationDataEditorPage = ref(currentPage);
const printTable = ref();
const selected = ref(itemsPerPage);
const profile_image = ref();

//Pending Pagination
const paginatedPaymentData = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage.value;
  const endIndex = startIndex + itemsPerPage.value;
  return PaymentData.value.slice(startIndex, endIndex);
});

let totalPages = computed(() => {
  if (PaymentData.value.length > 0) {
    return Math.ceil(PaymentData.value.length / itemsPerPage.value);
  } else {
    return 0;
  }
});

//handle the pagination in pending request that will emitted to the parent component
function updatePage() {
  paginationDataEditorPage.value = 1;
  // console.log(paginationDataOfficerPage.value);
}

//Search for Regulatory Officer
const searchPayment = () => {
  axios
    .get(environment.API_URL + "client/client/search-payments/"  + client_id[0].id , {
      params: {
        query: searchQuery.value,
      },
    })
    .then((response: any) => {
      if (response.data.success) {
        PaymentData.value = response.data.searchPayment;
      }
    })
    .catch((error: any) => {
      console.log("Error");
    });
};

// watch the data input search by the user for officer
const debouncedSearch = debounce(() => {
  if (searchQuery.value === "") {
    getPaymentList();
  } else {
    searchPayment();
  }
});
watch([searchQuery], debouncedSearch);

const isHovered = ref(null);

const showTooltip = (id: any) => {
  isHovered.value = id;
};

const hideTooltip = (id: any) => {
  if (isHovered.value === id) {
    isHovered.value = null;
  }
};
const paymentDialog = ref(false);
const openDialog = () => {
  paymentDialog.value = true;
};

const uploadDialog = ref(false);
const openDialog_upload = (row: any) => {
  paymentRow.value = row;
  uploadDialog.value = true;
};

const paymentDialogView = ref(false);
const paymentRow = ref();
const viewform = (row: any) => {
  paymentRow.value = row;
  paymentDialogView.value = true;
};

const paymentDialogEdit = ref(false);
const editform = (row: any) => {
  paymentRow.value = row;
  paymentDialogEdit.value = true;
};

const PaymentData = ref([]);
const client_id = JSON.parse(localStorage.getItem("clientData") || "{}");
const getPaymentList = () => {
  axios
    .get(environment.API_URL + "client/client/get-payment-list/" + client_id[0].id, {
      params: {
        token:token
      }
    })
    .then((response: any) => {
      if (response.data.success) {
        PaymentData.value = response.data.paymentdata;
      }
    });
};

const initializeData = () => {
   getPaymentList();
};

const reloadPayment = () => {
  getPaymentList();
}

onMounted(() => {
  initializeData();
});
</script>

<template>
  <section>
    <VCard title="My Transaction List">
      <VCardText>
        <VRow>
          <VCol style="text-align: right">
            <VBtn
              @click.prevent="openDialog"
              prepend-icon="tabler-plus"
              color="primary"
              >Payment</VBtn
            >
          </VCol>
        </VRow>
      </VCardText>
      <VCardText class="d-flex flex-wrap py-4 px-4 gap-2">
        <div
          class="me-3"
          style="width: 10.5rem; display: flex; align-items: center"
        >
          <v-label style="font-size: small" class="mr-1"
            ><strong>Show</strong></v-label
          >
          <VSelect
            v-model="selected"
            :items="perPages"
            @update:modelValue="updatePage"
          />
          <v-label style="font-size: small" class="ml-1"
            ><strong>entries</strong></v-label
          >
        </div>

        <v-spacer />
        <div
          class="app-user-search-filter d-flex align-center flex-wrap gap-4 justify-end"
        >
          <!-- 👉 Search  -->
          <div style="width: 17rem; display: flex; align-items: center">
            <v-text-field
              v-model="searchQuery"
              label="Search"
              class="search-field"
              solo
              append-inner-icon="mdi-magnify"
              density="compact"
            >
            </v-text-field>
          </div>
        </div>
      </VCardText>

      <VTable ref="tableRef">
        <VCol hidden class="mb-5">
          <VImg :src="logo" alt="Dit.Ads Logo" class="logo" />
        </VCol>
        <thead>
          <tr>
            <th class="text-center">No.</th>
            <th class="text-center">Mode of Payment</th>
            <th class="text-center">Amount</th>
            <th class="text-center">Proof of Payment</th>
            <th class="text-center">Sender Account Number</th>
            <th class="text-center">Reference/O.R Number</th>
            <th class="text-center">Date of Payment</th>
            <th class="text-center">Status</th>
            <th class="text-center">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, index) in paginatedPaymentData" :key="row.id">
            <td class="text-center">{{ index + 1 }}</td>
            <td class="text-center">
              <VChip
                color="red"
                variant="tonal"
                v-if="row.modeofpayment.name == 'Cash'"
                >{{ row.modeofpayment.name }}</VChip
              >
              <VChip
                color="blue"
                variant="tonal"
                v-else-if="row.modeofpayment.name == 'GCash'"
                >{{ row.modeofpayment.name }}</VChip
              >
              <VChip v-else
                color="success"
                variant="tonal"
                >{{ row.modeofpayment.name }}</VChip
              >
            </td>
            <td class="text-center">₱{{ row.amount }}.00</td>
            <td class="text-center">
              <div class="profile-card text-center mb-2">
                <div class="profile-card-photo">
                  <VAvatar
                    class="cursor-pointer"
                    color="primary"
                    variant="tonal"
                    style="
                      width: 50px;
                      height: 50px;
                      margin-top: 5%;
                      margin-bottom: 2%;
                    "
                  >
                    <VImg
                      v-if="row.proof_of_payment"
                      :src="
                        '/storage/proofofpayment_image/' + row.proof_of_payment
                      "
                      alt="Uploaded Image"
                      style="display: inline-block; max-width: 100%"
                    ></VImg>
                    <VImg
                      v-else
                      :src="logo"
                      alt="Uploaded Image"
                      class="circle-image"
                      style="display: inline-block; max-width: 100%"
                    ></VImg>
                  </VAvatar>
                </div>
              </div>
            </td>
            <td class="text-center" v-if="row.account">
              {{ row.account }}
            </td>
            <td class="text-center" v-else>
              <h5>No Sender Account Number</h5>
            </td>
            <td class="text-center" v-if="row.modeofpayment.name == 'Cash'">
              {{ row.or_number }}
            </td>
            <td class="text-center" v-else-if="row.modeofpayment.name == 'GCash'">
              {{ row.reference_number }}
            </td>
            <td class="text-center" v-else>
              <h5>No Reference/O.R Number</h5>
            </td>
            <!-- <td class="text-center"></td> -->
            <td class="text-center">{{ moment(row.date_of_payment).format('MMMM DD, YYYY') }}</td>
            <td class="text-center">
              <VChip
                color="success"
                variant="outlined"
                v-if="row.payment_status == 'Approved'"
                >{{ row.payment_status }}</VChip
              >
              <VChip
                color="warning"
                variant="outlined"
                v-if="row.payment_status == 'Pending'"
                >{{ row.payment_status }}</VChip
              >
              <VChip
                color="error"
                variant="outlined"
                v-if="row.payment_status == 'Rejected'"
                >{{ row.payment_status }}</VChip
              >
            </td>
            <td class="text-center" >
              <VBtn v-if="row.payment_status == 'Approved' && row.upload_status == 'No'" @click="openDialog_upload(row)" class="mr-3" size="x-small" prepend-icon="tabler-plus">Upload</VBtn
              >
              <VBtn icon size="x-small" variant="text" class="icon-container" v-if="row.payment_status == 'Approved' || row.payment_status == 'Pending' || row.payment_status == 'Rejected'">
                <VIcon
                  icon="tabler-table-options"
                  size="22"
                  @mouseenter="showTooltip(index)"
                  @mouseleave="hideTooltip(index)"
                />
                <span v-if="isHovered === index" class="tooltip">Click Me</span>
                <VMenu activator="parent">
                  <VList>
                    <VListItem @click="viewform(row)">
                      <VIcon>tabler:eye</VIcon>
                      View
                    </VListItem>
                    <VListItem v-if="row.payment_status=='Approved'" @click="editform(row)">
                      <VIcon>tabler-edit</VIcon>
                      Edit
                    </VListItem>
                  </VList>
                </VMenu>
              </VBtn>
            </td>
          </tr>
        </tbody>
        <!-- 👉 table footer  -->
        <tfoot v-if="PaymentData?.length == 0">
          <tr>
            <td colspan="8">
              <div style="text-align: center" class="mb-5 mt-5">
                <VImg
                  :src="nodata"
                  style="
                    display: inline-block;
                    max-width: 100%;
                    width: 35%;
                    height: 55%;
                    object-fit: cover;
                  "
                />
                <VProgressLinear color="primary" indeterminate reverse />
              </div>
            </td>

          </tr>
        </tfoot>
      </VTable>

      <VPagination :length="totalPages" v-model="paginationDataEditorPage" />
    </VCard>
    <VDialog v-model="paymentDialog" max-width="580">
      <transaction
        @close="paymentDialog = false"
        @paymentData="initializeData"
      />
    </VDialog>

    <VDialog v-model="paymentDialogView" max-width="580">
      <viewpayment
        :row="paymentRow"
        @close="paymentDialogView = false"
        @handledata="initializeData"
      />
    </VDialog>

    <VDialog v-model="paymentDialogEdit" max-width="580">
      <editpayment
        :row="paymentRow"
        @close="paymentDialogEdit = false"
        @handledata="initializeData"
      />
    </VDialog>

    <VDialog v-model="uploadDialog" max-width="650">
      <uploadPdf
        :row="paymentRow"
        @close="uploadDialog = false"
        @handledata="reloadPayment"
      />
    </VDialog>
  </section>
</template>
<style scoped>
.logo {
  max-width: 100px;
}

.logo-content {
  padding-left: 20px; /* Adjust as needed */
  color: #fff; /* Adjust text color based on your logo */
}

.logo-title {
  font-size: 18px;
  font-weight: bold;
  margin-bottom: 5px;
}

.logo-body {
  font-size: 14px;
  margin: 0;
}

.image-upload {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.circle-container {
  width: 230px;
  height: 230px;
  border-radius: 50%;
  overflow: hidden;
  margin-top: 10px;
  background-color: lightgray;
  display: flex;
  justify-content: center;
  align-items: center;
}

.circle-image {
  width: 50%;
  height: 50%;
  object-fit: cover;
}

.icon-container {
  position: relative;
}

.tooltip {
  position: absolute;
  background: rgba(0, 0, 0, 0.8);
  color: white;
  padding: 5px;
  border-radius: 5px;
  top: -10px; /* Adjust this value to position the tooltip as desired */
  left: 0;
}
</style>
