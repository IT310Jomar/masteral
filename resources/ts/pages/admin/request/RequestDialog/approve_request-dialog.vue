<script setup lang="ts">
import { emailValidator, requiredValidator } from "@validators";
import { VForm } from "vuetify/components";
import { environment } from "@/environments/environment";
import axios from "axios";
import { onMounted, ref } from "vue";
import approvedrequest from "@/pages/admin/request/RequestDialog/validationApproved.vue";
import VuePdfApp from "vue3-pdf-app";
import "vue3-pdf-app/dist/icons/main.css";

const emit = defineEmits(["close", "handledata"]);
const props = defineProps(['row']);
const refsubmit = ref<VForm>();
const pdfUrl = '/survey-questionnaire (1).pdf';

const close = () => {
  emit("close");
};

const dialogPreview = ref(false);
const approvedValid_dialog = ref(false);

const toggleDialogPayment = () => {
  dialogPreview.value = true;
};

const requestID = ref();

const openValidation = () => {
  approvedValid_dialog.value = true;
  requestID.value = props.row;
}

onMounted(()=> {
  if(props.row != null) {
    pdfUrl.value = '/storage/journals/' + props.row?.uploaded_file
  }
})

</script>

<template>
  <section>
    <DialogCloseBtn @click="close" />
    <v-row>
      <v-col>
        <VCard
          height="80vh"
          class="d-flex flex-column flex-grow-1 overflow-auto"
          title="Approve Request"
        >
          <VDivider/>
        <v-card-text>
          <vue-pdf-app :pdf="pdfUrl"></vue-pdf-app>
        </v-card-text>
        <VDivider />
          <VCardText>
            <VCol style="text-align: right">
              <VBtn class="mr-3" @click="openValidation">Approved</VBtn>
              <VBtn @click="close" color="error">Close</VBtn>
            </VCol>
          </VCardText>
        </VCard>
      </v-col>
    </v-row>

    <v-dialog v-model="dialogPreview" max-width="500">
      <DialogCloseBtn @click="dialogPreview = false" />
      <VCard title="Preview" class="d-flex flex-column flex-grow-1 overflow-auto">
        <VCardText v-if="proof_of_payment">
          <v-img :src="'/storage/proofofpayment_image/' + proof_of_payment" alt="Zoomed Image"></v-img>
        </VCardText>
        <VCardText>
          <VCol>
            <div class="float-right">
              <v-btn @click="dialogPreview = false" color="error" >Close</v-btn>
            </div>
          </VCol>
        </VCardText>
      </VCard>
    </v-dialog>

    <VDialog v-model="approvedValid_dialog" scrollable max-width="300">
      <approvedrequest
        :row="requestID"
        @close="approvedValid_dialog = false"
        @closeApproved="close()"
        @handleData="emit('handledata')"
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
</style>
