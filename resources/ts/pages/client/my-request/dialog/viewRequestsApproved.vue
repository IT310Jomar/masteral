<script setup lang="ts">
import { environment } from "@/environments/environment";
import axios from "axios";
import moment from "moment";
import { onMounted, ref } from "vue";
import VuePdfApp from "vue3-pdf-app";
import "vue3-pdf-app/dist/icons/main.css";

const pdfUrl = ref();

const props = defineProps(['row']);
const emit = defineEmits(["close"]);

const token = JSON.parse(localStorage.getItem("accessToken") || "{}");
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
    .get(environment.API_URL + "auth/admin/get-uploaded-journal/" + props.row,{
      params: {
        token:token
      }
    })
    .then((response) => {
      if (response.data.success) {
        RequestData.value = response.data.requestData[0];
        pdfUrl.value = '/storage/journals/' + response.data.requestData[0].uploaded_file;
        console.log(RequestData.value)
      }
    });
};

const initializeData = () => {
  getRequestList();
};

onMounted(() => {
  initializeData();
});
const close = () => {
  emit("close");
};

</script>

<template>
  <section v-if="RequestData">
    <DialogCloseBtn @click="close" />
    <VCard title="Pdf Viewer"  class="d-flex flex-column flex-grow-1 overflow-auto" height="720">
      <VDivider />
      <v-card-text>
        <div style="height: 80vh;">
          <vue-pdf-app style=" padding: 10px;margin: 20px;" :pdf="pdfUrl" :config="config"></vue-pdf-app>
        </div>
        <VDivider />

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
  display: flex;
  overflow: hidden;
  align-items: center;
  justify-content: center;
  background-color: lightgray;
  block-size: 330px; /* Automatically adjust height */
  inline-size: 230px;
  margin-block-start: 10px;
  max-inline-size: 230px; /* Limit width for responsiveness */
}

.circle-image {
  block-size: 100%;
  inline-size: 100%;
  object-fit: cover;
}

.form-container {
  padding: 16px;
  max-block-size: 50vh;
  overflow-y: auto;
}
</style>
