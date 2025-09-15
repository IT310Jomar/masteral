<script setup lang="ts">
import { environment } from '@/environments/environment';
import viewApproved from "@/pages/client/my-request/dialog/viewRequestsApproved.vue";
import axios from 'axios';
import { debounce } from "lodash";
import moment from "moment";
import nodata from "@images/no-data-found.png";

const approved = ref([]);
const tableRef = ref();
const searchQuery = ref(null);
const currentPage = ref(1);
const perPages = [5, 20, 50, 100, 500];
const itemsPerPage = ref(perPages[0]);
const selectedPerPage = ref(itemsPerPage.value);
const paginationDataApprovedPage = ref(currentPage);
const selected = ref(itemsPerPage);
const token = JSON.parse(localStorage.getItem("accessToken") || "{}");

//Pending Pagination
const paginatedApprovedRequests = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage.value;
  const endIndex = startIndex + itemsPerPage.value;
  return approved.value.slice(startIndex, endIndex);
});

let totalPages = computed(() => {
  if (approved.value.length > 0) {
    return Math.ceil(approved.value.length / itemsPerPage.value);
  } else {
    return 0;
  }
});

//handle the pagination in pending request that will emitted to the parent component
function updatePage() {
  paginationDataApprovedPage.value = 1;
  // console.log(paginationDataOfficerPage.value);
}
const client_id = JSON.parse(localStorage.getItem("clientData") || "{}");
const getPendingRequest = () => {
  axios.get(environment.API_URL + 'client/client/get-journal-requested-list/' +client_id[0].id, {
      params: {
        token: token
      }
    }).then(response => {
    if(response.data.success){
    approved.value = response.data.approved
    // console.log(approved.value)
    }
  })
}
const isHovered = ref(null);
const showTooltip = (id: any) => {
  isHovered.value = id;
};

const hideTooltip = (id: any) => {
  if (isHovered.value === id) {
    isHovered.value = null;
  }
};

onMounted(()=>{
  getPendingRequest()
})
//Search for Regulatory Officer
const searchPayment = () => {
  axios
    .get(environment.API_URL + "client/client/get-search-journal-requested-list/"+client_id[0].id, {
      params: {
        query: searchQuery.value,
        token: token,
      },
    })
    .then((response) => {
      if (response.data.success) {
        approved.value = response.data.approved;
      }
    })
    .catch((error) => {
      console.log("Error");
    });
};
// watch the data input search by the user for officer
const debouncedSearch = debounce(() => {
  if (searchQuery.value === "") {
    getPendingRequest();
  } else {
    searchPayment();
  }
});
watch([searchQuery], debouncedSearch);

const openViewDialog = ref(false)
const clientID = ref()
const viewDialog = (id: any)=>{
  openViewDialog.value = true
  clientID.value = id

}

</script>


<template>
  <section>
    <VCard title="My Approved Requests">
      <VCardText class="d-flex flex-wrap py-4 px-4 gap-2">
        <div
          class="me-3"
          style=" display: flex;width: 10.5rem; align-items: center;"
        >
          <v-label style="font-size: small;" class="mr-1"
            ><strong>Show</strong></v-label
          >
          <VSelect
            v-model="selected"
            :items="perPages"
            @update:modelValue="updatePage"
          />
          <v-label style="font-size: small;" class="ml-1"
            ><strong>entries</strong></v-label
          >
        </div>

        <v-spacer />
        <div
          class="app-user-search-filter d-flex align-center flex-wrap gap-4 justify-end"
        >
          <!-- 👉 Search  -->
          <div style=" display: flex;width: 17rem; align-items: center;">
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
        <thead>
          <tr>
            <th class="text-center">No.</th>
            <th class="text-center">Editor</th>
            <th class="text-center">Date and Time of Requests</th>
            <th class="text-center">Uploaded Journal</th>
            <th class="text-center">Request Status</th>
            <th >Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, index) in paginatedApprovedRequests " :key="row.id">
            <td class="text-center">{{ index + 1 }}</td>
            <td class="text-center">
             {{ row.editor?.lastname }}   {{ row.editor?.firstname }}   {{ row.editor?.middlename }}
            </td>
            <td class="text-center">{{ moment(row.date_time_requests).format('MMMM DD, YYYY h:mm A') }}</td>
            <td class="text-center">
             {{ row.uploaded_file }}
            </td>
            <td class="text-center">
           <VChip color="success" variant="outlined">{{ row?.request_status }}</VChip>
            </td>
            <td >
              <VBtn icon size="x-small" variant="text" class="icon-container">
                <VIcon
                  icon="tabler-table-options"
                  size="22"
                  @mouseenter="showTooltip(index)"
                  @mouseleave="hideTooltip(index)"
                />
                <span v-if="isHovered === index" class="tooltip">Click Me</span>
                <VMenu activator="parent">
                  <VList>
                    <VListItem @click.prevent="viewDialog(row.id)">
                      <VIcon>tabler:eye</VIcon>
                      View Journal
                    </VListItem>
                  </VList>
                </VMenu>
              </VBtn>
            </td>
          </tr>
        </tbody>
        <!-- 👉 table footer  -->
        <tfoot v-if="approved?.length == 0">
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
      <VPagination :length="totalPages" v-model="paginationDataApprovedPage" />
    </VCard>
    <VDialog scrollable v-model="openViewDialog" max-width="1000">
    <viewApproved :row="clientID" @close="openViewDialog = false"/>
    </VDialog>
  </section>
</template>
<style scoped>
.tooltip {
  position: absolute;
  padding: 5px;
  border-radius: 5px;
  background: rgba(0, 0, 0, 80%);
  color: white;
  inset-block-start: -10px; /* Adjust this value to position the tooltip as desired */
  inset-inline-start: 0;
}
</style>
