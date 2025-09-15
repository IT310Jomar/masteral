<script lang="ts" setup>
import { environment } from "@/environments/environment";
import axios from "axios";
import logo from "@images/DITADS Logo_PNG.png";
import nodata from "@images/no-data-found.png";
import { ref } from "vue";
import moment from "moment";
import { debounce } from "lodash";
import editEditorVue from "@/pages/admin/request/editDialog/editEditorDialog.vue"
// import approveRequest from "@/pages/admin/request/RequestDialog/approve_request-dialog.vue";
import rejectRequest from "@/pages/admin/request/RequestDialog/reject_request-dialog.vue";
// import viewRequest from "@/pages/admin/request/RequestDialog/view_request-dialog.vue";

const token = JSON.parse(localStorage.getItem("accessToken") || "{}");
const currentTab = ref();
const tableRef = ref();
const searchQuery = ref(null);
const searchQueryApproved = ref(null);
const searchQueryRejected = ref(null);
const currentPage = ref(1);
const perPages = [5, 20, 50, 100, 500];
const itemsPerPage = ref(perPages[0]);
const itemsPerPageApproved = ref(perPages[0]);
const itemsPerPageRejected = ref(perPages[0]);
const selectedPerPage = ref(itemsPerPage.value);
const paginationDataPendingPage = ref(currentPage);
const paginationDataApprovedPage = ref(currentPage);
const paginationDataRejectedPage = ref(currentPage);
const printTable = ref();
const selected = ref(itemsPerPage);
const selectedApproved = ref(itemsPerPageApproved);
const selectedRejected = ref(itemsPerPageRejected);
const profile_image = ref();

//Pending Pagination
const paginatedPendingData = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage.value;
  const endIndex = startIndex + itemsPerPage.value;
  return PendingData.value.slice(startIndex, endIndex);
});

let totalPages = computed(() => {
  if (PendingData.value.length > 0) {
    return Math.ceil(PendingData.value.length / itemsPerPage.value);
  } else {
    return 0;
  }
}); //end

//Approved Pagination
const paginatedApprovedData = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage.value;
  const endIndex = startIndex + itemsPerPage.value;
  return ApprovedData.value.slice(startIndex, endIndex);
});

let totalPagesApproved = computed(() => {
  if (ApprovedData.value.length > 0) {
    return Math.ceil(ApprovedData.value.length / itemsPerPage.value);
  } else {
    return 0;
  }
}); //end

//Rejected Pagination
const paginatedRejectedData = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage.value;
  const endIndex = startIndex + itemsPerPage.value;
  return RejectedData.value.slice(startIndex, endIndex);
});

let totalPagesRejected = computed(() => {
  if (RejectedData.value.length > 0) {
    return Math.ceil(RejectedData.value.length / itemsPerPage.value);
  } else {
    return 0;
  }
}); //end

//handle the pagination in pending request that will emitted to the parent component
function updatePage() {
  paginationDataPendingPage.value = 1;
}
function updatePageApproved() {
  paginationDataApprovedPage.value = 1;
}
function updatePageRejected() {
  paginationDataRejectedPage.value = 1;
}

//Search for Paid Accounts Pending
const searchPending = () => {
  axios
    .get(environment.API_URL + "auth/admin/search-uploaded-journal", {
      params: {
        query: searchQuery.value,
        token: token
      },
    })
    .then((response) => {
      if (response.data.success) {
        PendingData.value = response.data.searchPending;
      }
    })
    .catch((error) => {
      console.log("Error");
    });
};

// watch the data input search by the paid accounts
const debouncedSearch = debounce(() => {
  if (searchQuery.value === "") {
    getRequestList();
  } else {
    searchPending();
  }
});
watch([searchQuery], debouncedSearch);
//end

//Search for Paid Accounts Approved
const searchApproved = () => {
  axios
    .get(environment.API_URL + "auth/admin/search-uploaded-journal", {
      params: {
        query: searchQueryApproved.value,
        token:token
      },
    })
    .then((response) => {
      if (response.data.success) {
        ApprovedData.value = response.data.searchApproved;
      }
    })
    .catch((error) => {
      console.log("Error");
    });
};

// watch the data input search by the paid accounts
const debouncedSearchApproved = debounce(() => {
  if (searchQueryApproved.value === "") {
    getRequestList();
  } else {
    searchApproved();
  }
});
watch([searchQueryApproved], debouncedSearchApproved);
//end

//Search for Paid Accounts Rejected
const searchRejected = () => {
  axios
    .get(environment.API_URL + "auth/admin/search-uploaded-journal", {
      params: {
        query: searchQueryRejected.value,
        token:token
      },
    })
    .then((response) => {
      if (response.data.success) {
        RejectedData.value = response.data.searchRejected;
      }
    })
    .catch((error) => {
      console.log("Error");
    });
};

// watch the data input search by the paid accounts
const debouncedSearchRejected = debounce(() => {
  if (searchQueryRejected.value === "") {
    getRequestList();
  } else {
    searchRejected();
  }
});
watch([searchQueryRejected], debouncedSearchRejected);
//end

const isHovered = ref(null);

const showTooltip = (id: any) => {
  isHovered.value = id;
};

const hideTooltip = (id: any) => {
  if (isHovered.value === id) {
    isHovered.value = null;
  }
};

const approveDialog = ref(false);
const requestRow = ref();
const approveform = (row: any) => {
  requestRow.value = row;
  approveDialog.value = true;
};

const rejectDialog = ref(false);
const rejectform = (row: any) => {
  requestRow.value = row;
  rejectDialog.value = true;
};
const viewDialog = ref(false);
const viewform = (row: any) => {
  requestRow.value = row;
  viewDialog.value = true;
};


const PendingData = ref([]);
const ApprovedData = ref([]);
const RejectedData = ref([]);

const getRequestList = () => {
  axios
    .get(environment.API_URL + "auth/admin/get-uploaded-journal-lists",{
      params: {
        token:token
      }
    })
    .then((response) => {
      if (response.data.success) {
        PendingData.value = response.data.pendingdata;
        ApprovedData.value = response.data.approveddata;
        RejectedData.value = response.data.rejecteddata;
      }
    });
};

const initializeData = () => {
  getRequestList();
};
const handleFresh = () => {
  getRequestList();
}
onMounted(() => {
  initializeData();
});
const editEditor = ref(false)
const data = ref()
const editor_id = ref()
const updateEDitor = (id: any,editorId: any) => {
 editEditor.value = true
 data.value = id
 editor_id.value = editorId
}
</script>

<template>
  <section>
    <v-card-title>Uploaded Journal List</v-card-title>
    <v-tabs v-model="currentTab" class="mt-5 mb-5">
      <VTab value="pending"> Pending</VTab>
      <VTab value="approved"> Approved</VTab>
      <VTab value="rejected">Rejected</VTab>
    </v-tabs>

    <v-window v-model="currentTab">
      <v-window-item value="pending">
        <VCard title="Pending List">
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
                <th class="text-center">Client Fullname</th>
                <th class="text-center">Date of Request</th>
                <th class="text-center">Uploaded File</th>
                <th class="text-center">Status</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, index) in paginatedPendingData" :key="row.id">
                <td class="text-center">{{ index + 1 }}</td>
                <td class="text-center">
                  {{ row.client.firstname }} {{ row.client.middlename }}
                  {{ row.client.lastname }}
                </td>
                <td class="text-center">
                  {{ moment(row.date_time_requests).format("MMMM DD, YYYY") }}
                </td>
                <td class="text-center">
                  {{ row.uploaded_file }}
                </td>
                <td class="text-center">
                  <VChip
                    color="success"
                    variant="outlined"
                    v-if="row.request_status == 'Approved'"
                    >{{ row.request_status }}</VChip
                  >
                  <VChip
                    color="warning"
                    variant="outlined"
                    v-if="row.request_status == 'Pending'"
                    >{{ row.request_status }}</VChip
                  >
                  <VChip
                    color="error"
                    variant="outlined"
                    v-if="row.request_status == 'Rejected'"
                    >{{ row.request_status }}</VChip
                  >
                </td>
                <td class="text-center">
                  <VBtn
                    icon
                    size="x-small"
                    variant="text"
                    class="icon-container"
                  >
                    <VIcon
                      icon="tabler-table-options"
                      size="22"
                      @mouseenter="showTooltip(index)"
                      @mouseleave="hideTooltip(index)"
                    />
                    <span v-if="isHovered === index" class="tooltip"
                      >Click Me</span
                    >
                    <VMenu activator="parent">
                      <VList>
                        <VListItem :to="'/admin/request/RequestDialog/approved-request/' + row.id">
                          <template #default>
                            <VIcon>solar:like-broken</VIcon>
                            Approve
                          </template>
                        </VListItem>
                        <VListItem @click="rejectform(row)" v-if="row.editor_status == 'Pending'">
                          <template #default>
                            <VIcon>solar:dislike-outline</VIcon>
                            Reject
                          </template>
                        </VListItem>
                      </VList>
                    </VMenu>
                  </VBtn>
                </td>
              </tr>
            </tbody>
            <!-- 👉 table footer  -->
            <tfoot v-if="PendingData?.length == 0">
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

          <VPagination :length="totalPages" v-model="paginationDataPendingPage" />
        </VCard>
      </v-window-item>

      <v-window-item value="approved">
        <VCard title="Approved List">
          <VCardText class="d-flex flex-wrap py-4 px-4 gap-2">
            <div
              class="me-3"
              style="width: 10.5rem; display: flex; align-items: center"
            >
              <v-label style="font-size: small" class="mr-1"
                ><strong>Show</strong></v-label
              >
              <VSelect
                v-model="selectedApproved"
                :items="perPages"
                @update:modelValue="updatePageApproved"
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
                  v-model="searchQueryApproved"
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
                <th class="text-center">Client Fullname</th>
                <th class="text-center">Editor Fullname</th>
                <th class="text-center">Date of Request</th>
                <th class="text-center">Uploaded File</th>
                <th class="text-center">Status</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, index) in paginatedApprovedData" :key="row.id">
                <td class="text-center">{{ index + 1 }}</td>
                <td class="text-center">
                  {{ row.client.firstname }} {{ row.client.middlename }}
                  {{ row.client.lastname }}
                </td>
                <td class="text-center">
                  {{ row.editor.firstname }} {{ row.editor.middlename }}
                  {{ row.editor.lastname }}<sup v-if="row.release_status == 1" ><span class="ml-1"><v-icon @click.prevent="updateEDitor(row.id,row.editor_id)" color="warning">tabler:edit</v-icon></span></sup><sup v-else><span><VIcon color="success">tabler:check</VIcon></span></sup>
                </td>
                <td class="text-center">
                  {{ moment(row.date_time_requests).format("MMMM DD, YYYY") }}
                </td>
                <td class="text-center">
                  {{ row.uploaded_file }}
                </td>
                <td class="text-center">
                  <VChip
                    color="success"
                    variant="outlined"
                    v-if="row.request_status == 'Approved'"
                    >{{ row.request_status }}</VChip
                  >
                  <VChip
                    color="warning"
                    variant="outlined"
                    v-if="row.request_status == 'Pending'"
                    >{{ row.request_status }}</VChip
                  >
                  <VChip
                    color="error"
                    variant="outlined"
                    v-if="row.request_status == 'Rejected'"
                    >{{ row.request_status }}</VChip
                  >
                </td>
                <td class="text-center">
                  <VBtn
                    icon
                    size="x-small"
                    variant="text"
                    class="icon-container"
                  >
                    <VIcon
                      icon="tabler-table-options"
                      size="22"
                      @mouseenter="showTooltip(index)"
                      @mouseleave="hideTooltip(index)"
                    />
                    <span v-if="isHovered === index" class="tooltip"
                      >Click Me</span
                    >
                    <VMenu activator="parent">
                      <VList>
                        <VListItem :to="'/admin/request/RequestDialog/view-request/' + row.id">
                          <template #default>
                            <VIcon>material-symbols:visibility-outline-rounded</VIcon>
                            View
                          </template>
                        </VListItem>
                      </VList>
                    </VMenu>
                  </VBtn>
                </td>
              </tr>
            </tbody>
            <!-- 👉 table footer  -->
            <tfoot v-if="ApprovedData?.length == 0">
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

          <VPagination
            :length="totalPagesApproved"
            v-model="paginationDataApprovedPage"
          />
        </VCard>
      </v-window-item>

      <v-window-item value="rejected">
        <VCard title="Rejected List">
          <VCardText class="d-flex flex-wrap py-4 px-4 gap-2">
            <div
              class="me-3"
              style="width: 10.5rem; display: flex; align-items: center"
            >
              <v-label style="font-size: small" class="mr-1"
                ><strong>Show</strong></v-label
              >
              <VSelect
                v-model="selectedRejected"
                :items="perPages"
                @update:modelValue="updatePageRejected"
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
                  v-model="searchQueryRejected"
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
                <th class="text-center">Client Fullname</th>
                <th class="text-center">Date of Request</th>
                <th class="text-center">Uploaded File</th>
                <th class="text-center">Status</th>
                <th class="text-center">Reasons</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, index) in paginatedRejectedData" :key="row.id">
                <td class="text-center">{{ index + 1 }}</td>
                <td class="text-center">
                  {{ row.client.firstname }} {{ row.client.middlename }}
                  {{ row.client.lastname }}
                </td>
                <td class="text-center">
                  {{ moment(row.date_time_requests).format("MMMM DD, YYYY") }}
                </td>
                <td class="text-center">
                  {{ row.uploaded_file }}
                </td>
                <td class="text-center">
                  <VChip
                    color="success"
                    variant="outlined"
                    v-if="row.request_status == 'Approved'"
                    >{{ row.request_status }}</VChip
                  >
                  <VChip
                    color="warning"
                    variant="outlined"
                    v-if="row.request_status == 'Pending'"
                    >{{ row.request_status }}</VChip
                  >
                  <VChip
                    color="error"
                    variant="outlined"
                    v-if="row.request_status == 'Rejected'"
                    >{{ row.request_status }}</VChip
                  >
                </td>
                <td class="text-center">
                  {{ row.notes }}
                </td>
                <td class="text-center">
                  <VBtn
                    icon
                    size="x-small"
                    variant="text"
                    class="icon-container"
                  >
                    <VIcon
                      icon="tabler-table-options"
                      size="22"
                      @mouseenter="showTooltip(index)"
                      @mouseleave="hideTooltip(index)"
                    />
                    <span v-if="isHovered === index" class="tooltip"
                      >Click Me</span
                    >
                    <VMenu activator="parent">
                      <VList>
                        <VListItem :to="'/admin/request/RequestDialog/view-request/' + row.id">
                          <template #default>
                            <VIcon>material-symbols:visibility-outline-rounded</VIcon>
                            View
                          </template>
                        </VListItem>
                      </VList>
                    </VMenu>
                  </VBtn>
                </td>
              </tr>
            </tbody>
            <!-- 👉 table footer  -->
            <tfoot v-if="RejectedData?.length == 0">
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

          <VPagination
            :length="totalPagesRejected"
            v-model="paginationDataRejectedPage"
          />
        </VCard>
      </v-window-item>
    </v-window>

    <!-- <VDialog v-model="approveDialog" max-width="850">
      <approveRequest
        :row="requestRow"
        @close="approveDialog = false"
        @handledata="initializeData"
      />
    </VDialog> -->

    <VDialog v-model="rejectDialog" max-width="450">
      <rejectRequest
        :row="requestRow"
        @close="rejectDialog = false"
        @handledata="initializeData"
        @currentTab="currentTab = 'rejected'"
      />
    </VDialog>

    <!-- <VDialog v-model="viewDialog" max-width="450">
      <viewRequest
        :row="requestRow"
        @close="viewDialog = false"
        @handledata="initializeData"
      />
    </VDialog> -->
      <VDialog v-model="editEditor" max-width="450">
        <editEditorVue @close="editEditor = false" :row="data" @edited-data="handleFresh" :dataId="editor_id"/>
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
