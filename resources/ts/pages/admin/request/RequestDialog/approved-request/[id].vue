<script setup lang="ts">
import { environment } from "@/environments/environment";
import axios from "axios";
import { onMounted, ref } from "vue";
import approvedrequest from "@/pages/admin/request/RequestDialog/validationApproved.vue";
import reuploaded from "@/pages/admin/request/RequestDialog/reupload.vue";
import VuePdfApp from "vue3-pdf-app";
import "vue3-pdf-app/dist/icons/main.css";
import { useRoute } from "vue-router";
import { emailValidator, requiredValidator } from "@validators";
import { VForm } from "vuetify/components";

const token = JSON.parse(localStorage.getItem("accessToken") || "{}");
const refsubmit = ref<VForm>();
const route = useRoute();
const pdfUrl = ref();
const approvedValid_dialog = ref(false);
const isSnackbarErorr = ref(false);
const requestID = ref();
const reasons = ref()

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

const openValidation = () => {
  refsubmit.value?.validate().then(({ valid: isValid }) => {
    if (isValid) {
      approvedValid_dialog.value = true;
      requestID.value = route.params.id;
    } else {
      isSnackbarErorr.value = true;
    }
  });
};

const reupload = ref(false);

const openEdit = () => {
  reupload.value = true
  requestID.value = route.params.id;
}

const RequestData = ref();

const getRequestList = () => {
  axios
    .get(
      environment.API_URL + "auth/admin/get-uploaded-journal/" + route.params.id,{
        params: {
          token:token
        }
      }
    )
    .then((response) => {
      if (response.data.success) {
        RequestData.value = response.data.requestData[0];
        // console.log(RequestData.value)
        pdfUrl.value =
          "/storage/journals/" + response.data.requestData[0].uploaded_file;
      }
    });
};

const pdfError = ref<string | null>(null);

const loadPDF = async () => {
  try {
    await VuePdfApp.loadPdf(pdfUrl);
    // Successfully loaded PDF
  } catch (error) {
    pdfError.value = `Error loading PDF: ${error}`;
  }
};
const editor = ref();
const editorData = ref();
const getEditor = () => {
  axios
    .get(environment.API_URL + "auth/admin/get-assigned-editor",{
      params: {
        token:token
      }
    })
    .then((response) => {
      if (response.data.success) {
        if (response.data.editor) {
          const data = [];
          for (var x = 0; x < response.data.editor.length; x++) {
            const editor = response.data.editor[x];
            let fullName = editor.firstname;
            if (editor.middlename) {
              fullName += ` ${editor.middlename}`;
            }
            if (editor.lastname) {
              fullName += ` ${editor.lastname}`;
            }
            data.push({
              item: fullName,
              value: editor.id,
            });
          }
          editorData.value = data;
        }
      }
    });
};

const initializeData = () => {
  getRequestList();
  loadPDF();
  getEditor();
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
        <VForm ref="refsubmit">
          <VToolbar>
            <VCol cols="4">
              <VSelect
                v-model="editor"
                :rules="[requiredValidator]"
                label="Please Select Editor"
                :items="editorData"
                item-title="item"
                item-value="value"
              >
              </VSelect>
            </VCol>
            <VSpacer />
            <VBtn
              color="primary"
              variant="flat"
              @click="openValidation"
              class="mr-2"
            >
              <VIcon start size="16" icon="solar:like-broken" /> Approved</VBtn
            >
            <VBtn color="blue" variant="outlined" @click="openEdit">
              <VIcon start size="16" icon="tabler:pencil-down" />
              Re-Upload
            </VBtn>
          </VToolbar>
        </VForm>
        <VRow class="mt-2">
          <VCol>
            <VCard>
              <div v-if="RequestData">
                <VRow class="py-5 px-5" v-if="RequestData.editor_status == 'Rejected'" style="margin-bottom: -3%">
                  <VCol class="text-center">
                    <VAlert
                      color="warning"
                      variant="tonal"
                    >
                      <VAlertTitle class="mb-1">
                        Editor Notes:
                      </VAlertTitle>

                      <span>{{ RequestData.reasons }}</span>
                    </VAlert>
                  </VCol>
                </VRow>
              </div>
              <div style="height: 100vh" class="mb-5">
                <vue-pdf-app
                  style="margin: 20px; padding: 10px"
                  :pdf="pdfUrl"
                  :config="config"
                ></vue-pdf-app>
              </div>
            </VCard>
          </VCol>
        </VRow>
      </v-card-text>
    </VCard>

    <VDialog v-model="approvedValid_dialog" scrollable max-width="540">
      <approvedrequest
        :row="requestID"
        :editor="editor"
        @close="approvedValid_dialog = false"
        @handleData="initializeData"
      />
    </VDialog>

    <VDialog v-model="reupload" scrollable max-width="400">
      <reuploaded
        :row="requestID"
        @close="reupload = false"
        @handleData="initializeData"
      />
    </VDialog>

    <VSnackbar v-model="isSnackbarErorr" :timeout="4000" color="error">
      Erorr! Please Select Editor!
    </VSnackbar>
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
