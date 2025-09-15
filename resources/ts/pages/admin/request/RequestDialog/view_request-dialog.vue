<script setup lang="ts">
import { emailValidator, requiredValidator } from "@validators";
import { VForm } from "vuetify/components";
import { environment } from "@/environments/environment";
import axios from "axios";
import "vue3-pdf-app/dist/icons/main.css";
import { useRoute } from "vue-router";

const emit = defineEmits(["close", "handledata"]);
const props = defineProps(['row']);
const refsubmit = ref<VForm>();
const reasons = ref();
const pdfUrl = ref();

const close = () => {
  emit("close");
};

const dialogPreview = ref(false);

const toggleDialogPayment = () => {
  dialogPreview.value = true;
};


onMounted(()=> {
  if(props.row != null) {
    pdfUrl.value = props.row?.uploaded_file
    reasons.value = props.row?.notes
  }
})

</script>

<template>
      <section>
    <DialogCloseBtn @click="close" />
    <v-row>
      <v-col>
        <VCard
          height="520px"
          class="d-flex flex-column flex-grow-1 overflow-auto"
          title="View Payment"
        >
          <VDivider/>
          <VCardText>
            <VRow>
              <VCol class="mb-2">
                <VTextField
                  v-model="paymentMode"
                  label="Mode Of Payment"
                  readonly
                >
                </VTextField>
              </VCol>
            </VRow>
          <VRow>
            <VCol v-if="paymentMode == 'Cash'">
              <VTextField
                v-model="or_number"
                label="O.R Number"
                readonly
              ></VTextField>
            </VCol>
            <VCol v-if="paymentMode == 'GCash'">
              <VTextField
                v-model="ref_num"
                label="Referece Number"
                readonly
              ></VTextField>
            </VCol>
          </VRow>
          <VRow>
            <VCol class="mb-2">
              <VTextField
                v-model="date_of_payment"
                type="date"
                label="Date of Payment"
                readonly
              ></VTextField>
            </VCol>
          </VRow>
          <div v-if="payment_status == 'Rejected'">
            <VRow>
              <VCol>
                <VLabel class="text-red">Rejected Reason</VLabel>
              </VCol>
            </VRow>
            <VRow>
              <VCol>
                <VTextarea label="Reasons" v-model="reasons" readonly></VTextarea>
              </VCol>
            </VRow>
          </div>
          <VRow>
            <VCol>
              <VLabel class="text-red">Proof of Payment</VLabel>
            </VCol>
          </VRow>
          <VRow style="padding-left: 22%">
            <VCol>
              <div
                v-if="proof_of_payment"
                class="circle-container"
                style="text-align: center"
              >
                <VImg
                  :src="'/storage/proofofpayment_image/' + proof_of_payment"
                  alt="Uploaded Image"
                  class="circle-image"
                  style="display: inline-block; max-width: 100%"
                  @click="toggleDialogPayment"
                ></VImg>
              </div>
            </VCol>
          </VRow>
        </VCardText>
        <VDivider />
          <VCardText>
            <VCol style="text-align: right">
              <VBtn @click="close" color="error"> Close</VBtn>
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
