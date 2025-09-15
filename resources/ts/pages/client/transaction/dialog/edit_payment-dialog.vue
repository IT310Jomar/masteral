<script setup lang="ts">
import { emailValidator, requiredValidator } from "@validators";
import { VForm } from "vuetify/components";
import loadingScreen from "@/pages/loading/Validationloading.vue";
import { environment } from "@/environments/environment";
import axios from "axios";

const emit = defineEmits(["close", "handledata"]);
const props = defineProps(["row"]);
const refsubmit = ref<VForm>();
const loading = ref(false);
const isSnackbarSuccess = ref(false);
const isSnackbarErorr = ref(false);
const token = JSON.parse(localStorage.getItem("accessToken") || "{}");

const or_number = ref();
const ref_num = ref();
const date_of_payment = ref();
const paymentMode = ref();
const proof_of_payment = ref();
const client_id = ref();
const amount = ref()

const fileInputImage = ref<HTMLInputElement | null>(null);
const imageUrlPayment = ref<string | null>(null);

const dialogPreview = ref(false);

const toggleDialogPayment = () => {
  dialogPreview.value = true;
};

const dialogCode = ref(false);

const toggleDialogQRCode = () => {
  dialogCode.value = true;
};

const handleFileChangeImage = () => {
  if (
    fileInputImage.value &&
    fileInputImage.value.files &&
    fileInputImage.value.files.length > 0
  ) {
    const file = fileInputImage.value.files[0];
    const reader = new FileReader();

    reader.onload = (e) => {
      if (e.target && typeof e.target.result === "string") {
        imageUrlPayment.value = e.target.result;
      }
    };

    reader.readAsDataURL(file);
  }
};

const loaderTrue = () => {
  loading.value = true;
};

const loaderFalse = () => {
  loading.value = false;
};

const modePayment = ref();
const paymentData = ref();
const qrcode_image = ref();
const servicesName = ref();
const account_sender = ref();
const QrCODE = ref();
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
            if (paymentMode.value !== null) {
              const selectedPaymentId = paymentMode.value; // Access the 'value' property for the ID
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

const modeServices = ref();
const serviceData = ref();
const getServices = () => {
  axios.get(environment.API_URL + "client/client/get-services", {
      params: {
        token:token
      }
    })
    .then((response) => {
      if (response.data.success && response.data.services) {
        const data = response.data.services.map((service: { name: any; id: any; }) => ({
          item: service.name,
          value: service.id,
        }));
        modeServices.value = data;

        // Assuming you want to log the selected ID when it changes
        watchEffect(() => {
          if (servicesName.value !== null) {
            const selectedServiceId = servicesName.value; // Access the 'value' property for the ID
            // console.log('Selected Service ID:', selectedServiceId);

            getSingleServices(selectedServiceId);
          }
        });
      }
    });
};

const getSingleServices = (id: any) => {
  axios.get(environment.API_URL + "client/client/get-services/" + id, {
      params: {
        token:token
      }
    })
    .then((response) => {
      if (response.data.success) {
        serviceData.value = response.data.services[0]
        // console.log(serviceData.value)
        // Check if 'price' property is available before accessing it
        if (serviceData.value && 'price' in serviceData.value) {
          amount.value = serviceData.value.price;
        }
      }
    });
};

const initializeData = () => {
  getModePayment();
  getServices();
};

const edit_payment = () => {
  const formData = new FormData();
  formData.append("client_id", client_id.value);
  formData.append("serviceName", servicesName.value);
  formData.append("mode_of_payment_id", paymentMode.value);
  formData.append("amount", amount.value);
  if(account_sender.value != null) {
    formData.append("account_sender", account_sender.value);
  }
  if(paymentMode.value === 1){
    formData.append("or_number", or_number.value);
    formData.append("account_sender", '');
  }
  if(paymentMode.value === 2){
    formData.append("reference_number", ref_num.value);
  }
  formData.append("date_of_payment", date_of_payment.value);

  // Check if the file input has files and if the first file is selected
  if (fileInputImage.value?.files && fileInputImage.value.files.length > 0) {
    const file = fileInputImage.value.files[0];
    formData.append("proofofpayment", file);
  }
  
  axios.post(environment.API_URL + `client/client/edit-payment/${props.row.id}`, formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    })
    .then((response) => {
      if (response.data.success) {
        emit("close");
        emit("handledata", response.data);
      }
    });
};

//on submit function for validation
const onSubmit = () => {
  loaderTrue();
  refsubmit.value?.validate().then(({ valid: isValid }) => {
    if (isValid) {
      edit_payment();
      isSnackbarSuccess.value = true;
      loaderFalse();
    } else {
      isSnackbarErorr.value = true;
      loaderFalse();
    }
  });
};

const close = () => {
  emit("close");
};

onMounted(() => {
  if (props.row != null) {
    client_id.value = props.row?.client_id;
    amount.value = props.row?.amount;
    or_number.value = props.row?.or_number;
    ref_num.value = props.row?.reference_number;
    date_of_payment.value = props.row?.date_of_payment;
    paymentMode.value = props.row?.modeofpayment.id;
    proof_of_payment.value = props.row?.proof_of_payment;
    account_sender.value = props.row?.account;
    servicesName.value = props.row?.services.id;
    QrCODE.value = '/storage/proofofpayment_image/' + props.row?.proof_of_payment;
  }
  initializeData();
});
const minimumValueValidator = (value: number) =>
  value >= 200 || 'Minimum value is 200';
  const integerValidator = (value: string) =>
  /^[0-9]+$/.test(value) || 'Please enter an integer value.Example 200';
</script>

<template>
  <section>
    <DialogCloseBtn @click="close" />
        <VCard
          max-height="580px"
          class="d-flex flex-column flex-grow-1 overflow-auto"
          title="Edit Payment"
        >
          <VDivider />
            <VForm ref="refsubmit" @submit.prevent="onSubmit">
              <VCardText>
                <VRow>
                  <VCol class="mb-2">
                    <VSelect
                      v-model="servicesName"
                      :rules="[requiredValidator]"
                      label="Services"
                      :items="modeServices"
                      item-title="item"
                      item-value="value"
                    >
                    </VSelect>
                  </VCol>
                  <VCol class="mb-2" v-if="amount">
                    <VTextField
                      v-model="amount"
                      readonly
                      label="Amount"
                    ></VTextField>
                  </VCol>
                </VRow>
                <VRow>
                  <VCol>
                    <VSelect
                      v-model="paymentMode"
                      :rules="[requiredValidator]"
                      label="Mode Of Payment"
                      :items="modePayment"
                      item-title="item"
                      item-value="value"
                    >
                    </VSelect>
                  </VCol>
                </VRow>
                <VRow>
                  <VCol v-if="paymentMode == 1">
                    <VTextField
                      v-model="or_number"
                      :rules="[requiredValidator]"
                      label="O.R Number"
                    ></VTextField>
                  </VCol>
                  <VCol v-if="paymentMode == 2">
                    <VTextField
                      v-model="ref_num"
                      :rules="[requiredValidator]"
                      label="Referece Number"
                    ></VTextField>
                  </VCol>
                  <VCol v-if="paymentMode > 2 && paymentData">
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
                    ></VTextField>
                    <h5 v-else class="text-center">No Account Number</h5>
                  </VCol>
                </VRow>
                <VRow v-if="paymentMode == 2 && paymentData">
                  <VCol>
                    <VTextField
                      v-model="account_sender"
                      :rules="[requiredValidator]"
                      type="number"
                      label="Sender Account Number"
                    ></VTextField>
                  </VCol>
                  <VCol>
                    <VTextField
                      v-model="paymentData.account"
                      readonly
                      label="Receiver Account Number"
                    ></VTextField>
                  </VCol>
                </VRow>
                <VRow>
                  <VCol class="mb-2">
                    <VTextField
                      v-model="date_of_payment"
                      type="date"
                      :rules="[requiredValidator]"
                      label="Date of Payment"
                    ></VTextField>
                  </VCol>
                </VRow>
                <VRow>
                  <VCol>
                    <h5 class="text-center text-blue">
                      Proof of Payment
                    </h5>
                  </VCol>
                </VRow>
                <VRow>
                  <VCol class="mb-2">
                    <v-file-input
                      accept=".jpg,.jpeg,.png"
                      ref="fileInputImage"
                      chips
                      label="Select File"
                      @change="handleFileChangeImage"
                      prepend-icon="tabler:photo-edit"
                    />
                  </VCol>
                </VRow>
                <VRow class="text-center">
                  <VCol v-if="imageUrlPayment">
                    <VBtn color="primary" variant="tonal" prepend-icon="material-symbols:photo-camera-back-outline-sharp" size="small" @click="toggleDialogPayment"> Click ME!</VBtn>
                  </VCol>
                  <VCol v-else>
                    <VBtn color="blue" variant="tonal"  size="small" @click="toggleDialogQRCode" prepend-icon="material-symbols:photo-camera-back-outline-sharp"> Click ME!</VBtn>
                  </VCol>
                </VRow>
              </VCardText>
              <VDivider />
              <VCol class="mt-5 mb-2" style="text-align: right">
                <VBtn type="submit" prepend-icon="tabler-edit" class="mr-2"> Update</VBtn>
                <VBtn color="error" @click="close" prepend-icon="material-symbols-light:tab-close-outline">Cancel</VBtn>
              </VCol>
            </VForm>
        </VCard>
    <!-- SnackBar -->
    <VSnackbar v-model="isSnackbarSuccess" :timeout="4000" color="success">
      Success! Payment Data Edited.
    </VSnackbar>
    <VSnackbar v-model="isSnackbarErorr" :timeout="4000" color="error">
      Erorr! Please Check Your Field.
    </VSnackbar>
    <VDialog v-model="loading" max-width="300">
      <loadingScreen />
    </VDialog>

    <v-dialog v-model="dialogPreview" max-width="500">
      <DialogCloseBtn @click="dialogPreview = false" />
      <VCard title="Preview" class="d-flex flex-column flex-grow-1 overflow-auto">
        <VCardText v-if="imageUrlPayment">
          <v-img class="circle-image" :src="imageUrlPayment" alt="Zoomed Image"></v-img>
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

    <v-dialog v-model="dialogCode" max-width="500">
      <DialogCloseBtn @click="dialogCode = false" />
      <VCard title="Preview Uploaded" class="d-flex flex-column flex-grow-1 overflow-auto">
        <VCardText v-if="QrCODE">
          <v-img class="circle-image" :src="QrCODE" alt="Zoomed Image"></v-img>
        </VCardText>
        <VCardText>
          <VCol>
            <div class="float-right">
              <v-btn @click="dialogCode = false" color="error" >Close</v-btn>
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
