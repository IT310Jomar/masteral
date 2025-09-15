<script setup lang="ts">
import { environment } from "@/environments/environment";
import axios from "axios";
import { onMounted, ref } from "vue";
import VuePdfApp from "vue3-pdf-app";
import "vue3-pdf-app/dist/icons/main.css";
import { useRoute } from "vue-router";
import logo from "@images/DITADS Logo_PNG.png";
const token = JSON.parse(localStorage.getItem("accessToken") || "{}");
const route = useRoute();
const pdfUrl = ref();

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
      download: false,
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

const getRequestList = () => {
  axios
    .get(
      environment.API_URL + "auth/admin/get-uploaded-journal/" + route.params.id,{
        params: {
          token: token
        }
      }
    )
    .then((response) => {
      if (response.data.success) {
        RequestData.value = response.data.requestData[0];
        pdfUrl.value =
          "/storage/journals/" + response.data.requestData[0].uploaded_file;
      }
    });
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
    <VCard title="Pdf Viewer">
      <v-btn
        link
        to="/admin/request/uploaded_journal"
        class="d-flex float-right mr-5"
        style="margin-top: -5%"
        ><VIcon size="large">tabler:arrow-back-up</VIcon></v-btn
      >
      <VDivider class="mt-4" />
      <v-card-text>
        <VRow>
          <VCol cols="8">
            <VCard v-if="RequestData">
              <VRow v-if="RequestData.request_status == 'Rejected'">
                <VCol class="text-center">
                  <VAlert
                    color="warning"
                    variant="tonal"
                  >
                    <VAlertTitle class="mb-1">
                      Reasons:
                    </VAlertTitle>

                    <span>{{ RequestData.notes }}</span>
                  </VAlert>
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
            <VCard title="Client">
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
                <VCol v-if="RequestData">
                  <span><VIcon> material-symbols:alternate-email-rounded</VIcon> {{ RequestData.client.users.email }}</span><br/><br/>
                  <span><VIcon>material-symbols:smartphone-sharp</VIcon> {{ RequestData.client?.contact }}</span>
                </VCol>
              </VCardText>
            </VCard>
            <VCard class="mt-3" title="Assigned Editor" v-if="RequestData.request_status == 'Approved'">
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
            </VCard>
          </VCol>
        </VRow>
      </v-card-text>
    </VCard>
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
</style>
