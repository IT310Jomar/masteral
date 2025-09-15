<script setup lang="ts">
import { environment } from "@/environments/environment";
import ApexChartDataScience from "@/views/charts/apex-chart/ApexChartDataScience.vue";
import logo from "@images/logos.png";
import nodata from "@images/no-data-found.png";
import axios from "axios";
import { debounce } from "lodash";
import { computed, onMounted, ref, watch } from "vue";

const drawer = ref(false);
const currentPage = ref(1);
const perPages = [5, 20, 50, 100, 500];
const itemsPerPage = ref(perPages[0]);
const selectedPerPage = ref(itemsPerPage.value);
const paginationDataPaidPage = ref(currentPage);
const searchQuery = ref();
const RequestData = ref();
const isLoading = ref(true);

const Approvedvalidation = ref(false);
const Rejectedvalidation = ref(false);
const requestID = ref();
const originalname = ref();
const pdfUrl = ref();
//Pagination
const paginatedPublishedJournal = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage.value;
  const endIndex = startIndex + itemsPerPage.value;
  return PublishedJournal.value.slice(startIndex, endIndex);
});

let totalPages = computed(() => {
  if (PublishedJournal.value.length > 0) {
    return Math.ceil(PublishedJournal.value.length / itemsPerPage.value);
  } else {
    return 0;
  }
});

const items = ref([{ imageSrc: logo }]);
const PublishedJournal = ref([]);

//Search for Assigned Data
const searchPublished = () => {
  axios
    .get(environment.API_URL + "auth/admin/search-all-published-journal", {
      params: {
        query: searchQuery.value,
        // paymentmode: paymentMode.value,
      },
    })
    .then((response) => {
      if (response.data.success) {
        PublishedJournal.value = response.data.searchAllPublished;
      }
    })
    .catch((error) => {
      console.log("Error");
    });
};

// watch the data input search by the paid accounts
const debouncedSearchPublished = debounce(() => {
  if (searchQuery.value === "") {
    getPublishedList();
  } else {
    searchPublished();
  }
});
watch([searchQuery], debouncedSearchPublished);

//get function for ghetting the data of clients
const getPublishedList = () => {
  axios
    .get(environment.API_URL + "auth/admin/get-all-published-journal")
    .then((response) => {
      if (response.data.success) {
        PublishedJournal.value = response.data.Allpublished;
        console.log(PublishedJournal.value);
      }
    });
};

const load = (i: number) => {
  setTimeout(() => {}, 3000);
};

// const toDownload = (id: any) => {
//   axios.get(environment.API_URL + "auth/admin/get-uploaded-journal/" + id
//   )
//     .then(async (response) => {
//       if (response.data.success) {

//         RequestData.value = response.data.requestData[0];
//         // console.log(RequestData.value)
//         if (RequestData.value && 'editor_uploaded_file' in RequestData.value) {
//           console.log("test")
//           pdfUrl.value = "/storage/published_journals/" + response.data.requestData[0].editor_uploaded_file;
//           originalname.value = response.data.requestData[0].editor_uploaded_file;

//           try {
//             const response = await axios.get(pdfUrl.value, {
//               responseType: 'blob', // Set the response type to blob
//             });

//             // Create a download link
//             const url = window.URL.createObjectURL(new Blob([response.data]));
//             const link = document.createElement('a');
//             link.href = url;
//             link.setAttribute('download', originalname.value); // You can set the desired filename here
//             document.body.appendChild(link);
//             link.click();

//             // Clean up
//             document.body.removeChild(link);
//             window.URL.revokeObjectURL(url);
//           } catch (error) {
//             console.error('Error downloading PDF:', error);
//           }
//         }
//       }
//     }).finally(() => {
//     })
//     .catch((error) => {
//       console.log('error');
//     });
// };

const toDownload = async (id: any) => {
  try {
    const response = await axios.get(
      environment.API_URL + "auth/admin/get-uploaded-journal/" + id
    );

    if (response.data.success) {
      const requestData = response.data.requestData[0];

      if (requestData && requestData.editor_uploaded_file) {
        const pdfUrl = `/storage/published_journals/${requestData.editor_uploaded_file}`;
        const originalName = requestData.editor_uploaded_file;

        try {
          const pdfResponse = await axios.get(pdfUrl, {
            responseType: "blob",
            headers: {},
            withCredentials: true,
          });

          // Create a download link
          const url = window.URL.createObjectURL(new Blob([pdfResponse.data]));
          const link = document.createElement("a");
          link.href = url;
          link.setAttribute("download", originalName);
          document.body.appendChild(link);
          link.click();

          // Clean up
          document.body.removeChild(link);
          window.URL.revokeObjectURL(url);
        } catch (pdfError) {
          console.error("Error downloading PDF:", pdfError);
        }
      }
    }
  } catch (error) {
    console.error("Error fetching journal data:", error);
  }
};

const initializeData = () => {
  getPublishedList();
};

onMounted(() => {
  initializeData();
});
</script>

<template>
  <v-app>
    <v-app-bar app dark style="margin: 0; padding: 0">
      <!-- Logo (Visible in All Views) -->
      <v-app-bar-title class="title">
        <div class="logo-title">
          <v-img
            :src="logo"
            alt="IJIRITEBZDITADS Logo"
            max-height="50"
            contain
            class="logo"
          ></v-img>
          <span class="company d-none d-md-inline">IJIRITEBZDITADS</span>
        </div>
      </v-app-bar-title>

      <v-spacer></v-spacer>

      <!-- Desktop Navigation -->
      <div class="nav-links d-none d-md-flex">
        <VBtn href="/login"  class="nav-item">Login</VBtn>
        <VBtn href="/register"  class="nav-item">Register</VBtn>
        <v-text-field
          dense
          variant="outlined"
          hide-details
          placeholder="Search"
          class="searchField"
          v-model="searchQuery"
          label="Search"
          style="margin-right: 20px"
        ></v-text-field>
      </div>

      <!-- Mobile Menu Button -->
      <v-app-bar-nav-icon
        class="d-md-none"
        @click="drawer = !drawer"
      ></v-app-bar-nav-icon>
    </v-app-bar>

    <!-- Mobile Drawer (No Logo or Company Name) -->
    <v-navigation-drawer v-model="drawer" temporary right>
      <v-list>
        <v-list-item>
          <VBtn href="/login" class="nav-item">Login</VBtn>
        </v-list-item>
        <v-list-item>
          <VBtn href="/register" class="nav-item">Register</VBtn>
        </v-list-item>
        <v-list-item>
          <v-text-field
            dense
            variant="outlined"
            hide-details
            color="success"
            placeholder="Search"
            v-model="searchQuery"
            label="Search"
            class="searchField"
            style="margin-right: 20px"
          ></v-text-field>
        </v-list-item>
      </v-list>
    </v-navigation-drawer>

    <VRow>
      <VCol style="margin-top: 60px">
        <VCol>
          <VCard class="mt-1">
            <VRow>
              <VCol>
                <v-carousel
                  style="outline: none"
                  hide-delimiter-background
                  hide-delimiters
                  height="20vh"
                  show-arrows="false"
                >
                  <v-carousel-item v-for="(item, index) in items" :key="index">
                    <v-img :src="item.imageSrc" aspect-ratio="16/9"></v-img>
                  </v-carousel-item>
                </v-carousel>
              </VCol>
            </VRow>
          </VCard>
        </VCol>
        <VCol cols="12" style="width: 100%">
          <VCard
            height="fit-content"
            class="pa-4 d-flex flex-column align-center full-width-card"
          >
            <VRow justify="center" class="mb-0">
              <VCol cols="12" md="8" class="text-center" width="fit-content">
                <VCardTitle class="title-text text-wrap"
                  ><strong>
                    The International Journal of Interdisciplinary Research in
                    Information Technology, Education, and Business
                    through</strong
                  >
                  <span style="color: green">
                    <strong>
                      Zas Digital Institute Training and Development
                      Services</strong
                    >
                  </span>
                </VCardTitle>
              </VCol>
            </VRow>
          </VCard>
        </VCol>
        <VCol>
          <VCard
            class="headline full-width-card"
            title="Published Journals"
            width="100%"
          >
            <VDivider />
            <v-card-text>
              <v-table class="mb-8">
                <tbody>
                  <tr
                    v-for="(row, index) in paginatedPublishedJournal"
                    :key="row.id"
                  >
                    <td style="border: none">
                      <VRow>
                        <VCol>
                          <h3>
                            <VIcon>tabler:report-search</VIcon
                            >{{ row.editor_uploaded_file.replace(".pdf", "") }}
                          </h3>
                        </VCol>
                      </VRow>
                    </td>
                    <td style="border: none">
                      <VRow>
                        <VCol class="text-right">
                          <VBtn color="secondary" variant="tonal">
                            <VIcon>tabler:file-type-pdf</VIcon>Preview
                          </VBtn>
                          <VBtn
                            variant="tonal"
                            class="ml-2 mr-2"
                            @click.prevent="toDownload(row.id)"
                          >
                            <VIcon>tabler:download</VIcon>Download
                          </VBtn>
                        </VCol>
                      </VRow>
                    </td>
                  </tr>
                </tbody>
                <!-- 👉 table footer  -->
                <tfoot v-if="PublishedJournal?.length == 0">
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
                        <VProgressLinear
                          color="primary"
                          indeterminate
                          reverse
                        />
                      </div>
                    </td>
                  </tr>
                </tfoot>
              </v-table>
              <VDivider />
              <VPagination
                :length="totalPages"
                v-model="paginationDataPaidPage"
              />
            </v-card-text>
          </VCard>
        </VCol>
        <VCol>
          <VCard title="Registered Clients and Published Journal Statistics">
            <VCardText>
              <ApexChartDataScience />
            </VCardText>

            <VCardText class="text-center">
              <div class="h-100 d-flex align-center justify-space-between;">
                <!-- 👉 Footer: left content -->
                <span class="d-flex align-center">
                  &copy;
                  {{ new Date().getFullYear() }}
                  Made With
                  <VIcon
                    icon="tabler-heart"
                    color="error"
                    size="1.25rem"
                    class="mx-1"
                  />
                  By
                  <a
                    href="https://web.facebook.com/DIT.ADS.net"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-primary ms-1"
                    >Dit.ads</a
                  >
                </span>
                <!-- 👉 Footer: right content -->
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VCol>
    </VRow>
  </v-app>
</template>

<style scoped>
v-table tr {
  border-bottom: none !important;
}

.logo-title {
  display: flex;
  align-items: center;
  gap: 10px;
}

.logo {
  max-width: 50px;
}

.company {
  font-size: 1.2rem;
  font-weight: bold;
  font-family: "Poppins" !important;
}

.nav-links {
  display: flex;
  align-items: center;
  gap: 15px;
}

.nav-item {
  text-decoration: none;
  color: white;
  font-weight: bold;
}

.searchField {
  width: 300px;
  max-width: 300px;
}

@media (max-width: 960px) {
  .searchField {
    max-width: 100%;
  }
}

.full-width-card {
  width: 100vw;
  max-width: 100%;
}

.full-width-row {
  width: 100%;
  padding: 0;
}

.title-text {
  font-size: 1.5rem;
  text-align: center;
  margin-bottom: 10px;
}

.mt-2 {
  margin-top: 5px !important;
}
.v-carousel__controls {
  display: none !important;
}
</style>
