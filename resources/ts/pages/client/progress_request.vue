<script setup lang="ts">
import { environment } from '@/environments/environment';
import viewApproved from "@/pages/client/my-request/dialog/viewRequestsApproved.vue";
import { library } from '@fortawesome/fontawesome-svg-core';
import { fas } from '@fortawesome/free-solid-svg-icons';
import axios from 'axios';
import { debounce } from "lodash";
import StepProgress from 'vue-step-progress';
import 'vue-step-progress/dist/main.css';
import nodata from "@images/no-data-found.png";
const token = JSON.parse(localStorage.getItem("accessToken") || "{}");

library.add(fas);
const approved = ref([]);
const tableRef = ref();
const searchQuery = ref(null);
const currentPage = ref(1);
const perPages = [5, 20, 50, 100, 500];
const itemsPerPage = ref(perPages[0]);
const selectedPerPage = ref(itemsPerPage.value);
const paginationDataApprovedPage = ref(currentPage);
const selected = ref(itemsPerPage);


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
  axios.get(environment.API_URL + 'client/client/get-journal-requested-list/'+client_id[0].id,{
        params: {
          token: token
        }
      }).then(response => {
    if(response.data.success){
    approved.value = response.data.approved
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

const getSteps = (progressStatus: any) => {
  const steps = ['Approved', 'Editing', 'Publishing'];

  // Change the label at index 0 to 'tabler-check' if progress_status is 1
  if (progressStatus === '1') {
    steps[0] = '✔️';
  } else if (progressStatus === '2') {
    steps[0] = '✔️';
    steps[1] = '✔️';
  } else if (progressStatus === '3') {
    steps[0] = '✔️';
    steps[1] = '✔️';
    steps[2] = '✔️';
  }
  return steps;
  
};

</script>


<template>
  <section>
    <VCard title="My Progress Status">
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
            <!-- <th class="text-center">Title</th> -->
            <th class="text-center">Progress Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, index) in paginatedApprovedRequests " :key="row.id">
            <td class="text-center">{{ index + 1 }}</td>
            <!-- <td class="text-center">
             {{ row.uploaded_file }}
            </td> -->
            <td class="text-center" ><br/>
              <p><VChip color="info" variant="text" >{{ row.uploaded_file }}</VChip></p><br/>
              <step-progress :steps="getSteps(row.progress_status)" :current-step="Number(row.progress_status)" active-color="green"  :line-thickness="5" :active-thickness="5" :passive-thickness="5"   style="width: 600px; "/>
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

