<script setup lang="ts">
import { emailValidator, requiredValidator } from "@validators";
import { VForm } from "vuetify/components";
import { environment } from "@/environments/environment";
import axios from "axios";

const emit = defineEmits(["close", "handledata"]);
const props = defineProps(['row']);
const refsubmit = ref<VForm>();
const token = JSON.parse(localStorage.getItem("accessToken") || "{}");

const or_number = ref();
const ref_num = ref();
const date_of_payment = ref();
const paymentMode = ref();
const payment_status = ref();
const proof_of_payment = ref();
const amount =ref()
const servicesName = ref();
const account_sender = ref();
const QrCODE = ref();
const reasons = ref();
const modePayment = ref();
const paymentData = ref();
const paymentModeID = ref();
const qrcode_image = ref();
const getModePayment = () => {
  axios
    .get(environment.API_URL + "client/client/get-mode-of-payment", {
      params: {
        token:token
      }
    })
    .then((response) => {
      if (response.data.success && response.data.mode) {
          const data = response.data.mode.map((method: { name: any; id: any; }) => ({
            item: method.name,
            value: method.id,
          }));

          modePayment.value = data;

          // Assuming you want to log the selected ID when it changes
          watchEffect(() => {
            if (paymentModeID.value !== null) {
              const selectedPaymentId = paymentModeID.value; // Access the 'value' property for the ID
              // console.log('Selected Service ID:', selectedServiceId);

              getSinglePayment(selectedPaymentId);
            }
          });
        }
    });
};

const getSinglePayment = (id: any) => {
  axios.get(environment.API_URL + "client/client/get-mode-of-payment/" + id, {
      params: {
        token:token
      }
    })
    .then((response) => {
      if (response.data.success) {
        paymentData.value = response.data.mode[0]

        // Check if 'qr_code_image' property is available before accessing it
        if (paymentData.value && 'qr_code_image' in paymentData.value) {
          qrcode_image.value = '/storage/qr_code_image/' + paymentData.value.qr_code_image;
        }
      }
    });
};

const close = () => {
  emit("close");
};

const dialogPreview = ref(false);

const toggleDialogPayment = () => {
  dialogPreview.value = true;
};


onMounted(()=> {
  if(props.row != null) {
    payment_status.value = props.row?.payment_status
    reasons.value = props.row?.notes
    amount.value = props.row?.amount;
    or_number.value = props.row?.or_number;
    ref_num.value = props.row?.reference_number;
    date_of_payment.value = props.row?.date_of_payment;
    paymentMode.value = props.row?.modeofpayment.name;
    paymentModeID.value = props.row?.modeofpayment.id;
    proof_of_payment.value = props.row?.proof_of_payment;
    account_sender.value = props.row?.account;
    servicesName.value = props.row?.services.name;
    QrCODE.value = '/storage/proofofpayment_image/' + props.row?.proof_of_payment;
    getModePayment();
  }
})

</script>

<template>
      <section>
    <DialogCloseBtn @click="close" />
    <v-row>
      <v-col>
        <VCard
          max-height="580px"
          class="d-flex flex-column flex-grow-1 overflow-auto"
          title="View Payment"
        >
          <VDivider/>
          <VCardText>
            <div v-if="payment_status == 'Rejected'">
              <VRow style="margin-top: -8%">
                <VCol class="text-center">
                  <VAlert
                    color="warning"
                    variant="tonal"
                  >
                    <VAlertTitle class="mb-1">
                      Please try again!
                    </VAlertTitle>

                    <span>{{ reasons }}</span>
                  </VAlert>
                </VCol>
              </VRow>
            </div>
            <VRow>
              <VCol class="mb-2">
                <VTextField
                  v-model="servicesName"
                  label="Services"
                  readonly
                >
                </VTextField>
              </VCol>
              <VCol class="mb-2">
                <VTextField
                  v-model="amount"
                  label="Amount"
                  readonly
                ></VTextField>
              </VCol>
            </VRow>
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
              <VCol  hidden v-if="paymentModeID == 1">
                <VTextField
                  v-model="or_number"
                  :rules="[requiredValidator]"
                  label="O.R Number"
                  readonly
                ></VTextField>
              </VCol>
              <VCol v-if="paymentModeID == 2">
                <VTextField
                  v-model="ref_num"
                  :rules="[requiredValidator]"
                  label="Referece Number"
                  readonly
                ></VTextField>
              </VCol>
              <VCol v-if="paymentModeID > 2 && paymentData">
                <VTextField
                  v-if="paymentData.account"
                  v-model="paymentData.account"
                  :rules="[requiredValidator]"
                  readonly
                  label="Account Number"
                  class="mb-2"
                ></VTextField>
                <VTextField 
                  v-if="paymentData.account"
                  v-model="account_sender"
                  :rules="[requiredValidator]"
                  type="number"
                  label="Sender Account Number"
                  readonly
                ></VTextField>
                <h5 v-else class="text-center">No Account Number</h5>
              </VCol>
            </VRow>
            <VRow v-if="paymentModeID == 2 && paymentData">
              <VCol>
                <VTextField
                  v-model="account_sender"
                  :rules="[requiredValidator]"
                  type="number"
                  label="Sender Account Number"
                  readonly
                ></VTextField>
              </VCol>
              <VCol>
                <VTextField
                  v-model="paymentData.account"
                  :rules="[requiredValidator]"
                  readonly
                  label="Receiver Account Number"
                ></VTextField>
              </VCol>
            </VRow>
            <VRow>
              <VCol  hidden class="mb-2">
                <VTextField
                  v-model="date_of_payment"
                  type="date"
                  label="Date of Payment"
                  readonly
                ></VTextField>
              </VCol>
            </VRow>
            <VRow>
              <VCol  hidden>
                <VLabel class="text-blue">Proof of Payment</VLabel>
              </VCol>
            </VRow>
            <VRow class="text-center">
              <VCol>
                <VCol v-if="proof_of_payment">
                  <VBtn color="red" variant="tonal" prepend-icon="material-symbols:photo-camera-back-outline-sharp" size="small" @click="toggleDialogPayment"> Click ME!</VBtn>
                </VCol>
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
