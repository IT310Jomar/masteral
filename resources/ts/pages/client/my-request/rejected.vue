<script setup lang="ts">
import { environment } from '@/environments/environment';
import viewRejected from "@/pages/client/my-request/dialog/viewRequestsRejected.vue";
import reuploadVue from "@/pages/client/my-request/dialog/reupload.vue";
import axios from 'axios';
import { debounce } from "lodash";
import moment from "moment";
import nodata from "@images/no-data-found.png";

const rejected = ref([]);
const tableRef = ref();
const searchQuery = ref(null);
const currentPage = ref(1);
const perPages = [5, 20, 50, 100, 500];
const itemsPerPage = ref(perPages[0]);
const selectedPerPage = ref(itemsPerPage.value);
const paginationDataRejectedPage = ref(currentPage);
const selected = ref(itemsPerPage);
const token = JSON.parse(localStorage.getItem("accessToken") || "{}");

//Pending Pagination
const paginatedRejectedRequests = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage.value;
  const endIndex = startIndex + itemsPerPage.value;
  return rejected.value.slice(startIndex, endIndex);
});

let totalPages = computed(() => {
  if (rejected.value.length > 0) {
    return Math.ceil(rejected.value.length / itemsPerPage.value);
  } else {
    return 0;
  }
});

//handle the pagination in pending request that will emitted to the parent component
function updatePage() {
  paginationDataRejectedPage.value = 1;
  // console.log(paginationDataOfficerPage.value);
}
const client_id = JSON.parse(localStorage.getItem("clientData") || "{}");
const getPendingRequest = () => {
  axios.get(environment.API_URL + 'client/client/get-journal-requested-list/'+client_id[0].id, {
      params: {
        token: token
      }
    }).then(response => {
    if(response.data.success){
    rejected.value = response.data.rejected
    // console.log(rejected.value)
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

const isHover = ref(null);
const showTooltipUpload = (id: any) => {
  isHover.value = id;
};

const hideTooltipUpload = (id: any) => {
  if (isHover.value === id) {
    isHover.value = null;
  }
};

const handleData = () =>{
  getPendingRequest()
}

onMounted(()=>{
  getPendingRequest();
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
        rejected.value = response.data.rejected;
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

const openReUploadDialog = ref(false)
const openReupload = (row: any)=>{
  openReUploadDialog.value = true
  clientID.value = row
}

const loadings = ref<boolean[]>([])

const load = (i: number) => {
  loadings.value[i] = true
  setTimeout(() => {
    loadings.value[i] = false
  }, 2000)
}


</script>


<template>
  <section>
    <VCard title="My Rejected Requests">
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
            <th class="text-center">Date and Time of Requests</th>
            <th class="text-center">Uploaded Journal</th>
            <th class="text-center">Request Status</th>
            <th class="text-center">Notes</th>
            <th class="text-center">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, index) in paginatedRejectedRequests " :key="row.id">
            <td class="text-center">{{ index + 1 }}</td>
            <td class="text-center">{{ moment(row.date_time_requests).format('MMMM DD, YYYY h:mm A') }}</td>
            <td class="text-center">
             {{ row.uploaded_file }}
             <VBtn
              :loading="loadings[4]"
              :disabled="loadings[4]"
              color="warning"
              @click="load(4)"
              rounded="pill"
              size="small"
              @click.prevent="openReupload(row.id)"
            >
            <VIcon
              icon="tabler-cloud-upload"
              size="22"
              @mouseenter="showTooltipUpload(index)"
              @mouseleave="hideTooltipUpload(index)"
            />
            <span v-if="isHover === index" class="tooltip">Please Re-Upload</span>
          </VBtn>
            </td>
            <td class="text-center">
           <VChip color="error" variant="outlined">{{ row?.request_status }}</VChip>
            </td>
            <td class="text-center">{{ row.notes }}</td>
            <td class="text-center">
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
                      View
                    </VListItem>
                  </VList>
                </VMenu>
              </VBtn>
            </td>
          </tr>
        </tbody>
        <!-- 👉 table footer  -->
        <tfoot v-if="rejected?.length == 0">
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
      <VPagination :length="totalPages" v-model="paginationDataRejectedPage" />
    </VCard>
    <VDialog scrollable v-model="openViewDialog" max-width="1000">
      <viewRejected :row="clientID" @close="openViewDialog = false"/>
    </VDialog>
    <VDialog v-model="openReUploadDialog" max-width="450">
        <reuploadVue @close="openReUploadDialog = false" :row="clientID" @handleData="handleData"/>
    </VDialog> 
  </section>
</template>
<style lang="scss" scoped>
.tooltip {
  position: absolute;
  padding: 5px;
  border-radius: 5px;
  background: rgba(0, 0, 0, 80%);
  color: white;
  inset-block-start: -10px; /* Adjust this value to position the tooltip as desired */
  inset-inline-start: 0;
}
.custom-loader {
    display: flex;
    animation: loader 1s infinite;
  }

  @keyframes loader {
    from {
      transform: rotate(0);
    }

    to {
      transform: rotate(360deg);
    }
  }
</style>
