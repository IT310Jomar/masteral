<script setup lang="ts">
import { environment } from "@/environments/environment";
import axios from "axios";
import logo from "@images/DITADS Logo_PNG.png";
import nodata from "@images/no-data-found.png";
import { onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import moment from "moment";
import { debounce } from "lodash";

const editorData = ref();
const route = useRoute();
const searchQuery = ref(null);
const currentPage = ref(1);
const perPages = [5, 20, 50, 100, 500];
const itemsPerPage = ref(perPages[0]);
const paginationDataPage = ref(currentPage);
const selected = ref(itemsPerPage);

const PublishedData = ref([])

//Pagination
const paginatedPublishedData = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage.value;
  const endIndex = startIndex + itemsPerPage.value;
  return PublishedData.value.slice(startIndex, endIndex);
});

let totalPages = computed(() => {
  if (PublishedData.value.length > 0) {
    return Math.ceil(PublishedData.value.length / itemsPerPage.value);
  } else {
    return 0;
  }
}); //end

//handle the pagination in pending request that will emitted to the parent component
function updatePage() {
  paginationDataPage.value = 1;
}
const token = JSON.parse(localStorage.getItem("accessToken") || "{}");
//Search for Request Upload Data
const searchPublishedData = () => {
  axios
    .get(environment.API_URL + "auth/admin/search-published-editor-journal/" + route.params.id, {
      params: {
        query: searchQuery.value,
        token:token
      },
    })
    .then((response) => {
      if (response.data.success) {
        PublishedData.value = response.data.searchPublishedData;
      }
    })
    .catch((error) => {
      console.log("Error");
    });
};

// watch the data input search by the upload
const debouncedSearch = debounce(() => {
  if (searchQuery.value === "") {
    getSinglePublishedData();
  } else {
    searchPublishedData();
  }
});
watch([searchQuery], debouncedSearch);
//end


//get function for ghetting the data of clients
const getSingleEditor= () => {
  axios
    .get(environment.API_URL + "auth/admin/get-editor-data/" + route.params.id,{
      params: {
        token: token
      }
    })
    .then((response) => {
      if (response.data.success) {
        editorData.value = response.data.editor;
        // console.log(editorData.value);
      }
    });
};

const getSinglePublishedData = () => {
  axios.get(environment.API_URL + "auth/admin/get-published-editor-data/" + route.params.id,{
    params:{
      token:token
    }
  })
  .then((response) => {
    if (response.data.success) {
        PublishedData.value = response.data.PublishedData;
        // console.log(PublishedData.value);
      }
  })
}

onMounted(() => {
  getSingleEditor();
  getSinglePublishedData();
});
</script>

<template>
  <section>
    <VRow>
      <VCol>
        <VCard>
          <VRow>
            <VCol>
              <VCardTitle class="mt-4"
                >View Information
                <v-btn
                  link
                  to="/admin/editor/editorList"
                  class="d-flex float-right mr-5"
                >
                  <VIcon size="large">tabler:arrow-back-up</VIcon>
                </v-btn></VCardTitle
              >
            </VCol>
          </VRow>

          <VDivider class="mt-4" />

          <VCardText v-if="editorData">
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
                            <VImg v-if="editorData?.image" :src="'/storage/profile_image/' + editorData?.image" />
                            <VImg
                              v-else
                              :src="logo"
                            />
                          </VAvatar>
                        </div>
                        <div style="font-weight: bold"></div>
                        <div style="font-weight: bold"></div>
                        <div class="mb-4"></div>

                        <h3 class="text-center mt-2">{{ editorData.firstname }} {{ editorData?.middlename }} {{ editorData.lastname }}</h3>
                        <h1 class="text-center mt-1">
                          <VChip color="blue" variant="outlined" size="small">{{ editorData.users.roles.name }}</VChip>
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
                              v-if="editorData?.lastname != null && editorData?.firstname"
                              ><v-icon start icon="mdi-account-circle-outline"></v-icon><b
                                >{{ editorData.firstname }} {{ editorData.middlename }} {{ editorData.lastname }}</b
                              ></VChip
                            >
                          </td>
                        </tr>

                        <tr>
                          <td><strong>Email</strong></td>
                          <td>
                            <VChip
                              color="warning"
                              variant="outlined"
                              v-if="editorData.users?.email != null"
                              ><v-icon start icon="ic:twotone-mail-outline"></v-icon><b>{{ editorData.users?.email }}</b></VChip
                            >
                          </td>
                        </tr>
                        <tr>
                          <td><strong>Contact</strong></td>
                          <td>
                            <VChip
                              color="success"
                              variant="outlined"
                              v-if="editorData?.contact != null"
                              ><v-icon start icon="material-symbols:phone-android-outline"></v-icon><b>{{ editorData?.contact }}</b></VChip
                            >
                          </td>
                        </tr>
                        <tr>
                          <td><strong>Status</strong></td>
                          <td>
                            <VChip
                              color="success"
                              variant="outlined"
                              v-if="editorData?.status != null && editorData?.status=='Active'"
                              ><v-icon start icon="ph:activity-light"></v-icon><b>Active</b></VChip
                            > <VChip
                              color="error"
                              variant="outlined"
                              v-if="editorData?.status != null && editorData?.status=='Disabled'"
                              ><v-icon start icon="fe:disabled"></v-icon><b>Disabled</b></VChip
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
                  title="Published Journal List"
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
                        <th class="text-center">Client Fullname</th>
                        <th class="text-center">Published File</th>
                        <th class="text-center">Date of Published</th>
                        <th class="text-center">Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(row, index) in paginatedPublishedData" :key="row.id">
                        <td class="text-center">{{ index + 1 }}</td>
                        <td class="text-center">
                          {{ row.client.firstname }} {{ row.client.middlename }} {{ row.client.lastname }}
                        </td>
                        <td class="text-center">
                          {{ row.editor_uploaded_file }}
                        </td>
                        <td class="text-center">
                          {{ moment(row.date_of_published).format("MMMM DD, YYYY") }}
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
                    <tfoot v-if="PublishedData?.length == 0">
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
