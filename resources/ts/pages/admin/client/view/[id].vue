<script setup lang="ts">
import { environment } from "@/environments/environment";
import logo from "@images/DITADS Logo_PNG.png";
import nodata from "@images/no-data-found.png";
import axios from "axios";
import { ref } from "vue";
import { useRoute } from "vue-router";
import moment from "moment";
import { debounce } from "lodash";

const userRole = JSON.parse(localStorage.getItem("userRole") || "{}");
const token = JSON.parse(localStorage.getItem("accessToken") || "{}");
const clientsData = ref();
const route = useRoute();
const searchQuery = ref(null);
const currentPage = ref(1);
const perPages = [5, 20, 50, 100, 500];
const itemsPerPage = ref(perPages[0]);
const paginationDataPage = ref(currentPage);
const selected = ref(itemsPerPage);

const uploadData = ref([])

//Pagination
const paginateduploadData = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage.value;
  const endIndex = startIndex + itemsPerPage.value;
  return uploadData.value.slice(startIndex, endIndex);
});

let totalPages = computed(() => {
  if (uploadData.value.length > 0) {
    return Math.ceil(uploadData.value.length / itemsPerPage.value);
  } else {
    return 0;
  }
}); //end

//handle the pagination in pending request that will emitted to the parent component
function updatePage() {
  paginationDataPage.value = 1;
}

//Search for Request Upload Data
const searchUploadData = () => {
  axios
    .get(environment.API_URL + "auth/admin/search-client-upload-journal/" + route.params.id, {
      params: {
        query: searchQuery.value,
        token: token,
      },
    })
    .then((response) => {
      if (response.data.success) {
        uploadData.value = response.data.searchUploadData;
      }
    })
    .catch((error) => {
      console.log("Error");
    });
};

// watch the data input search by the upload
const debouncedSearch = debounce(() => {
  if (searchQuery.value === "") {
    getSingleUploadData();
  } else {
    searchUploadData();
  }
});
watch([searchQuery], debouncedSearch);
//end

//get function for ghetting the data of clients
const getSingleClients = async () => {
  await axios
    .get(environment.API_URL + "auth/admin/get-client-data/" + route.params.id, {
      params: {
        token: token
      }
    })
    .then((response) => {
      if (response.data.success) {
        clientsData.value = response.data.client[0];
        // console.log(clientsData.value);
      }
    });
};

const getSingleUploadData = async () => {
  await axios.get(environment.API_URL + "auth/admin/get-upload-data/" + route.params.id, {
      params: {
        token: token
      }
    })
  .then((response) => {
    if (response.data.success) {
        uploadData.value = response.data.uploaddata;
        // console.log(uploadData.value);
      }
  })
}

onMounted(async () => {
  await getSingleClients();
  await getSingleUploadData();
});
</script>

<template>
  <section v-if="userRole == 'Admin' || userRole == 'God Mode'">
    <VRow>
      <VCol>
        <VCard>
          <VRow>
            <VCol>
              <VCardTitle class="mt-4"
                >View Information
                <v-btn
                  link
                  :to="'/admin/client/clientlist'"
                  class="d-flex float-right mr-5"
                >
                  <VIcon size="large">tabler:arrow-back-up</VIcon>
                </v-btn></VCardTitle
              >
            </VCol>
          </VRow>

          <VDivider class="mt-2" />

          <VCardText v-if="clientsData">
            <v-row>
              <v-col>
                <VCard class="d-flex flex-column mb-4 mt-3">
                  <div>
                    <div>
                      <div class="profile-card text-center mb-2">
                        <div class="profile-card-photo">
                          <VAvatar
                            class="cursor-pointer"
                            color="secondary"
                            variant="tonal"
                            style="
                              width: 200px;
                              height: 200px;
                              margin-top: 10%;
                              margin-bottom: 3%;
                            "
                          >
                            <VImg v-if="clientsData?.image" :src="'/storage/profile_image/' + clientsData?.image"/>
                            <VImg
                              v-else
                              :src="logo"
                            />
                          </VAvatar>
                        </div>
                        <div style="font-weight: bold"></div>
                        <div style="font-weight: bold"></div>
                        <div class="mb-4"></div>

                      <h3 class="text-center mt-2">{{ clientsData.firstname }} {{ clientsData?.middlename }} {{ clientsData.lastname }}</h3>
                      <h1 class="text-center mt-1">
                        <VChip color="blue" variant="outlined" size="small">{{ clientsData.users.roles.name }}</VChip>
                      </h1>
                      </div>
                    </div>
                  </div>
                </VCard>

                <VRow>
                  <VCol style="text-align: center" class="mt-5"></VCol>
                </VRow>
              </v-col>

              <v-col cols="12" md="8" lg="8">
                <v-card
                  class="headline mb-8"
                  title="Personal Information"
                  tabindex="-1"
                >
                  <VDivider />
                  <v-card-text>
                    <v-table tabindex="-1">
                      <tbody>
                        <tr>
                          <td width="300px"><strong>Full Name</strong></td>
                          <td>
                            <VChip
                              color="blue"
                              variant="outlined"
                              v-if="clientsData?.lastname != null &&  clientsData?.firstname != null"
                              ><v-icon start icon="mdi-account-circle-outline"></v-icon><b
                                >{{ clientsData.firstname }} {{ clientsData.lastname }} {{ clientsData.middlename }}
                                </b
                              ></VChip
                            >
                          </td>
                        </tr>

                        <tr>
                          <td><strong>Email</strong></td>
                          <td>
                            <VChip
                              color="success"
                              variant="outlined"
                              v-if="clientsData.users?.email != null"
                              ><v-icon start icon="ic:twotone-mail-outline"></v-icon><b>{{ clientsData.users?.email }}</b></VChip
                            >
                          </td>
                        </tr>
                        <tr>
                          <td><strong>Contact</strong></td>
                          <td>
                            <VChip
                              color="warning"
                              variant="outlined"
                              v-if="clientsData?.contact != null"
                              ><v-icon start icon="material-symbols:phone-android-outline"></v-icon><b>{{ clientsData?.contact }}</b></VChip
                            >
                          </td>
                        </tr>
                        <tr>
                          <td><strong>School</strong></td>
                          <td>
                            <VChip
                              color="success"
                              variant="outlined"
                              v-if="clientsData?.school != null"
                              ><v-icon start icon="teenyicons:school-outline"></v-icon><b>{{ clientsData?.school }}</b></VChip
                            >
                          </td>
                        </tr>
                        <tr>
                          <td><strong>School Type</strong></td>
                          <td>
                            <VChip
                              color="blue"
                              variant="outlined"
                              v-if="clientsData.school_type?.name != null && clientsData.school_type?.name === 'Public'"
                              ><v-icon start icon="iconoir:learning"></v-icon><b>{{ clientsData.school_type?.name }}</b></VChip
                            >
                            <VChip
                              color="warning"
                              variant="outlined"
                              v-if="clientsData.school_type?.name != null && clientsData.school_type?.name === 'Private'"
                              ><v-icon start icon="iconoir:learning"></v-icon><b>{{ clientsData.school_type?.name }}</b></VChip
                            >
                          </td>
                        </tr>
                        <tr>
                          <td><strong>Course</strong></td>
                          <td>
                            <VChip color="error" variant="outlined" v-if="clientsData?.course != null"
                              ><v-icon start icon="guidance:golf-course"></v-icon><b>{{ clientsData.course }}</b></VChip
                            >
                          </td>
                        </tr>
                      </tbody>
                    </v-table>
                    <VDivider />
                  </v-card-text>
                </v-card>
              </v-col>
            </v-row>

            <VRow>
              <VCol>
                <v-card
                  class="headline mb-8"
                  title="Upload Journal List"
                >
                  <VDivider />
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
                  <VTable>
                    <thead>
                      <tr>
                        <th class="text-center">No.</th>
                        <th class="text-center">Uploaded File</th>
                        <th class="text-center">Amount</th>
                        <th class="text-center">Date of Request</th>
                        <th class="text-center">Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(row, index) in paginateduploadData" :key="row.id">
                        <td class="text-center">{{ index + 1 }}</td>
                        <td class="text-center">
                          {{ row.uploaded_file }}
                        </td>
                        <td class="text-center">
                          {{ row.payment.amount }}
                        </td>
                        <td class="text-center">
                          {{ moment(row.date_time_requests).format("MMMM DD, YYYY") }}
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
                      </tr>
                    </tbody>
                    <!-- 👉 table footer  -->
                    <tfoot v-if="uploadData?.length == 0">
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
                  <VPagination :length="totalPages" v-model="paginationDataPage" />
                </v-card>
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </section>
</template>

<style scoped>
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
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: lightgray;
}

.circle-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}
.float-right {
  float: none;
  margin-right: 0;
  text-align: center; /* Center the button */
}
</style>
