<script setup lang="ts">
import { environment } from "@/environments/environment";
import axios from "axios";
import { onMounted, ref } from "vue";
import VuePdfApp from "vue3-pdf-app";
import "vue3-pdf-app/dist/icons/main.css";
import { useRoute } from "vue-router";
import logo from "@images/DITADS Logo_PNG.png";
import clickhere from "@images/click here.png";
import approvedAssigned from "@/pages/editor/assigned-view/validation_dialog/validationApproved.vue";
import rejectedAssigned from "@/pages/editor/assigned-view/validation_dialog/reasonReject.vue";
import pages401 from '@images/pages/401_error.png'
import miscMaskDark from '@images/pages/misc-mask-dark.png'
import miscMaskLight from '@images/pages/misc-mask-light.png'
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'

const authThemeMask = useGenerateImageVariant(miscMaskLight, miscMaskDark)
const route = useRoute();
const pdfUrl = ref();
const editor_id = JSON.parse(localStorage.getItem("editorData") || "{}");

const config = {
  sidebar: {
    viewThumbnail: true,
    viewOutline: false,
    viewAttachments: false,
  },
  secondaryToolbar: {
    secondaryPresentationMode: true,
    secondaryOpenFile: true,
    secondaryPrint: true,
    secondaryDownload: true,
    secondaryViewBookmark: true,
    firstPage: true,
    lastPage: true,
    pageRotateCw: true,
    pageRotateCcw: true,
    cursorSelectTool: true,
    cursorHandTool: true,
    scrollVertical: true,
    scrollHorizontal: true,
    scrollWrapped: true,
    spreadNone: true,
    spreadOdd: true,
    spreadEven: true,
    documentProperties: true,
  },
  toolbar: {
    toolbarViewerLeft: {
      findbar: true,
      previous: true,
      next: true,
      pageNumber: true,
    },
    toolbarViewerRight: {
      presentationMode: true,
      openFile: false,
      print: false,
      download: true,
      viewBookmark: false,
    },
    toolbarViewerMiddle: {
      zoomOut: false,
      zoomIn: false,
      scaleSelectContainer: true,
    },
  },
  errorWrapper: false,
};

const RequestData = ref();

const Approvedvalidation = ref(false);
const Rejectedvalidation = ref(false);
const requestID = ref();

const openValidation = () => {
  Approvedvalidation.value = true
  requestID.value = route.params.id;
}

const openReject = () => {
  Rejectedvalidation.value = true
  requestID.value = route.params.id;
}

const originalname = ref()
const isLoading = ref(true);

const token = JSON.parse(localStorage.getItem("accessToken") || "{}");
const getRequestList = () => {
  isLoading.value = true;
  axios
    .get(
      environment.API_URL + "auth/admin/get-uploaded-journal/" + route.params.id,{
        params:{
          token:token
        }
      }
    )
    .then((response) => {
      if (response.data.success) {
        RequestData.value = response.data.requestData[0];
        if(RequestData.value && 'uploaded_file' in RequestData.value){
          pdfUrl.value = "/storage/journals/" + response.data.requestData[0].uploaded_file;
          originalname.value = response.data.requestData[0].uploaded_file;
        }
      }
    }).finally(() => {
      isLoading.value = false;
      })
      .catch((error) => {
        console.log('error');
      });
};

const loadings = ref<boolean[]>([])

const load = (i: number) => {
  loadings.value[i] = true
  setTimeout(() => {
    loadings.value[i] = false
  }, 3000)
}

const downloadPDF = async () => {
  try {
    const response = await axios.get(pdfUrl.value, {
      responseType: 'blob', // Set the response type to blob
    });
    load(1);
    // Create a download link
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', originalname.value); // You can set the desired filename here
    document.body.appendChild(link);
    link.click();

    // Clean up
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Error downloading PDF:', error);
  }
};

const initializeData = () => {
  getRequestList();
};

onMounted(() => {
  initializeData();
});
</script>

<template>
  <section>
    <VCard title="Upload Information">
      <v-btn
        link
        to="/editor/approved_documents"
        class="d-flex float-right mr-5"
        style="margin-top: -5%"
        ><VIcon size="large">tabler:arrow-back-up</VIcon></v-btn
      >
      <VDivider class="mt-2" />

      <VCardText v-if="isLoading">
        <!-- Loading state -->
        <div class="loading-container">
          <div class="loading-spinner"></div>
          <p>Please Wait...</p>
        </div>
      </VCardText>

      <v-card-text v-else-if="RequestData && RequestData.editor_status === 'Pending' && RequestData.editor_id === editor_id[0].id">
        <VRow>
          <VCol cols="8">
            <VCard>
              <!-- <div class="text-right">
                <div class="text-right mr-10">
                <h4 class="mt-2">Download</h4>
              </div>
                <VImg :src="clickhere" style="
                  display: inline-block;
                  max-width: 100%;
                  width: 8%;
                  height: 8%;
                  margin-right: 8%;
                  margin-bottom: -5%;
                  object-fit: cover;" />
              </div> -->
              <VRow class="text-center mt-2">
                <VCol xs="12" md="12">
                  <VBtn
                    :loading="loadings[1]"
                    :disabled="loadings[1]"
                    color="success" variant="flat"
                    @click="downloadPDF"
                  >
                  <VIcon start size="16" icon="tabler-download" />
                    Download
                  </VBtn>
                </VCol>
              </VRow>
              <div style="height: 80vh">
                <vue-pdf-app
                  style="margin: 20px; padding: 10px"
                  :pdf="pdfUrl"
                  :config="config"
                ></vue-pdf-app>
              </div>
            </VCard>
          </VCol>
          <VCol cols="4" v-if="RequestData">
            <VForm ref="refsubmit">
              <VCard>
                <VToolbar>
                  <VSpacer/>
                  <VRow>
                    <VCol xs="12" md="12">
                      <VBtn class="ml-2" color="success" variant="flat" @click="openValidation">
                        <VIcon start size="16" icon="solar:like-broken" />
                          Approved
                      </VBtn>
                    </VCol>
                  </VRow>
                  <VRow>
                    <VCol xs="12" md="12">
                      <VBtn color="error" variant="outlined" @click="openReject">
                        <VIcon start size="16" icon="solar:dislike-outline" />
                          Rejected
                      </VBtn>
                    </VCol>
                  </VRow>
                  <VSpacer/>
                </VToolbar>
              </VCard>
            </VForm>
            <VCard title="Client" class="mt-5">
              <VDivider />
              <VCardText>
                <VRow>
                  <VCol>
                    <VAvatar color="primary" variant="tonal">
                      <VImg v-if="RequestData.client.image" :src="'/storage/profile_image/' + RequestData.client.image" />
                      <VImg v-else :src="logo"/>
                    </VAvatar>
                    <span class="ml-3" style="font-size: medium;">{{ RequestData.client.firstname }} {{ RequestData.client?.middlename }} {{ RequestData.client.lastname }}</span>
                  </VCol>
                </VRow>
                <VDivider class="mb-5 mt-5"/>
                <VRow>
                  <VCol>
                    <VAvatar v-if="RequestData.client.status == 'Disabled'" color="error" variant="tonal">
                      <VIcon>material-symbols:person-cancel-outline-rounded</VIcon>
                    </VAvatar>
                    <VAvatar v-else color="primary" variant="tonal">
                      <VIcon>material-symbols:person-check-outline</VIcon>
                    </VAvatar>
                  </VCol>
                  <VCol v-if="RequestData">
                    <VListItemTitle style="margin-left: -75%; margin-top: 5%">
                      {{ RequestData.client.status }}
                    </VListItemTitle>
                  </VCol>
                </VRow>
                <VDivider class="mb-5 mt-5"/>
                <VRow>
                  <VCol>
                    <h5>Contact Info</h5>
                  </VCol>
                </VRow>
                <VCol v-if="RequestData != null">
                  <span><VIcon> material-symbols:alternate-email-rounded</VIcon> {{ RequestData.client.users.email }}</span><br/><br/>
                  <span><VIcon>material-symbols:smartphone-sharp</VIcon> {{ RequestData.client?.contact }}</span>
                </VCol>
              </VCardText>
            </VCard>
            <!-- <VCard class="mt-3" title="Assigned Editor" v-if="RequestData.request_status == 'Approved'">
              <VDivider />
              <VCardText>
                <VRow>
                  <VCol>
                    <VAvatar color="primary" variant="tonal">
                      <VImg v-if="RequestData.editor.image" :src="'/storage/profile_image/' + RequestData.editor.image" />
                      <VImg v-else :src="logo"/>
                    </VAvatar>
                    <span class="ml-3" style="font-size: medium;">{{ RequestData.editor.firstname }} {{ RequestData.editor?.middlename }} {{ RequestData.editor.lastname }}</span>
                  </VCol>
                </VRow>
              </VCardText>
            </VCard> -->
          </VCol>
        </VRow>
      </v-card-text>

      <VCardText v-else>
        <div class="misc-wrapper">
          <ErrorHeader
            error-title="You are not authorized! 🔐"
            error-description="You do not have permission to view this page using the credentials that you have provided while login.
      Please contact your site administrator."
          />
          <div class="text-center" style="margin-bottom: -2%;">
          <VBtn
            to="/editor/approved_documents"
            class="mb-12"
          >
            Back
          </VBtn>
        </div>
          <!-- 👉 Image -->
          <div class="misc-avatar w-100 text-center">
            <VImg
              :src="pages401"
              alt="Coming Soon"
              :max-width="590"
              class="mx-auto"
            />
          </div>

          <VImg
            :src="authThemeMask"
            class="misc-footer-img d-none d-md-block"
          />
        </div>
      </VCardText>
    </VCard>

    <VDialog v-model="Approvedvalidation" scrollable max-width="540">
      <approvedAssigned
        :row="requestID"
        @close="Approvedvalidation = false"
        @handleData="initializeData"
      />
    </VDialog>

    <VDialog v-model="Rejectedvalidation" scrollable max-width="540">
      <rejectedAssigned
        :row="requestID"
        @close="Rejectedvalidation = false"
        @handleData="initializeData"
      />
    </VDialog>

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
  max-width: 230px; /* Limit width for responsiveness */
  height: 330px; /* Automatically adjust height */
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
}

.form-container {
  max-height: 50vh;
  overflow-y: auto;
  padding: 16px;
}
.vue-pdf-app .open-file-button {
  display: none;
}
.loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 20px;
  }

  .loading-spinner {
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin-bottom: 10px;
    border-top: 4px solid #3498db;
    border-left: 4px solid #3498db;
    border-bottom: 4px solid transparent;
  }

  @keyframes spin {
    0% {
      transform: rotate(0deg);
    }

    100% {
      transform: rotate(360deg);
    }
  }
</style>
