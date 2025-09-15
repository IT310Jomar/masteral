<script setup lang="ts">
import { environment } from "@/environments/environment";
import loadingScreen from "@/pages/loading/Validationloading.vue";
import { requiredValidator } from "@validators";
import axios from "axios";
import { ref } from "vue";
import { VForm } from "vuetify/components";

const client_id = JSON.parse(localStorage.getItem("clientData") || "{}");
const refsubmit = ref<VForm>();
const refsubmit2 = ref<VForm>();
const token = JSON.parse(localStorage.getItem("accessToken") || "{}");

const emit = defineEmits(["close", "paymentData"]);

const cancel = () => {
  emit("close");
};

const or_number = ref();
const ref_num = ref();
const date_of_payment = ref();
const paymentMode = ref();
const servicesName = ref();
const amount = ref();
const account_sender = ref();
const qrcode_image = ref();

const fileInputImage = ref<HTMLInputElement | null>(null);
const imageUrlPayment = ref<string | null>(null);

const dialogPreview = ref(false);

const toggleDialogPayment = () => {
  dialogPreview.value = true;
};

const dialogCode = ref(false);

const toggleQRcode = () => {
  dialogCode.value = true;
};

const currentTab = ref();

const showTabs = ref(false);

function next() {
  refsubmit2.value?.validate().then(({ valid: isValid }) => {
    if (isValid) {
      currentTab.value = "Step 2";
    } else {
      isSnackbarErorr.value = true;
    }
  });

}
function back() {
  currentTab.value = "Step 1";
}

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

const loading = ref(false);
const isSnackbarSuccess = ref(false);
const isSnackbarErorr = ref(false);

const loaderTrue = () => {
  loading.value = true;
};

const loaderFalse = () => {
  loading.value = false;
};

const dialogValidation = ref(false);

const OpenValidation = () => {
  refsubmit.value?.validate().then(({ valid: isValid }) => {
    if (isValid) {
      dialogValidation.value = true;
    } else {
      isSnackbarErorr.value = true;
    }
  });
};

const post_payment = () => {
  const formData = new FormData();
  formData.append("client_id", client_id[0].id);
  formData.append("serviceName", servicesName.value);
  formData.append("mode_of_payment_id", paymentMode.value);
  formData.append("amount", amount.value);
  if(account_sender.value != null) {
    formData.append("account_sender", account_sender.value);
  }
  if(paymentMode.value === 1) {
    formData.append("or_number", or_number.value);
  }
  if(paymentMode.value === 2) {
    formData.append("reference_number", ref_num.value);
  }
  // formData.append("date_of_payment", '');
  formData.append("date_of_payment", date_of_payment.value);
  // Check if the file input has files and if the first file is selected
  if (fileInputImage.value?.files && fileInputImage.value.files.length > 0) {
    const file = fileInputImage.value.files[0];
    formData.append("proofofpayment", file);
  }

  axios
    .post(environment.API_URL + "client/client/post-payment", formData)
    .then((response: any) => {
      if (response.data.success) {
        emit("paymentData", response.data);
        cancel();
      }
    });
};

//on submit function for validation
const onSubmit = () => {
  loaderTrue();
  refsubmit.value?.validate().then(({ valid: isValid }) => {
    if (isValid) {
      post_payment();
      isSnackbarSuccess.value = true;
      loaderFalse();
      dialogValidation.value = false;
    } else {
      isSnackbarErorr.value = true;
      loaderFalse();
    }
  });
};

const modePayment = ref();
const paymentData = ref();
const getModePayment = () => {
  axios
    .get(environment.API_URL + "client/client/get-mode-of-payment", {
      params: {
        token:token
      }
    })
    .then((response: any) => {
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

// const getSinglePayment = (id: any) => {
//   console.log(id)
//   axios.get(environment.API_URL + "client/client/get-mode-of-payment/" + id, {
//       params: {
//         token:token
//       }
//     })
//     .then((response: any) => {
//       if (response.data.success) {
//         paymentData.value = response.data.mode[0]
     
//         // Check if 'qr_code_image' property is available before accessing it
//         if (paymentData.value && 'qr_code_image' in paymentData.value) {
//           qrcode_image.value = '/storage/qr_code_image/' + paymentData.value.qr_code_image;
//         }
//       }
//     });
// };

const getSinglePayment = (id: any) => {
  axios.get(environment.API_URL + "client/client/get-mode-of-payment/" + id, {
      params: {
        token: token
      }
    })
    .then((response: any) => {
      if (response.data.success) {
        paymentData.value = response.data.mode[0];

        // Ensure qrcode_image is available only for the relevant payment modes
        if (paymentData.value && 'qr_code_image' in paymentData.value && paymentMode.value === 2) {
          qrcode_image.value = '/storage/qr_code_image/' + paymentData.value.qr_code_image;
        } else {
          qrcode_image.value = '/storage/qr_code_image/null';
        }
      }
    });
};


const fileSizeValidator = () => {
  if (fileInputImage.value) {
    const maxSizeKB = 2048; // Maximum file size in kilobytes (2 MB)
    const maxSizeBytes = maxSizeKB * 4024; // Convert kilobytes to bytes

    if (fileInputImage.value.files && fileInputImage.value.files[0].size > maxSizeBytes) {
      return `File size must be less than ${maxSizeKB} KB`;
    }
  }

  return true;
};

const modeServices = ref();
const serviceData = ref();
const getServices = () => {
  axios.get(environment.API_URL + "client/client/get-services", {
      params: {
        token:token
      }
    })
    .then((response: any) => {
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
    .then((response: any) => {
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

onMounted(() => {
  initializeData();
});

</script>
<template>
  <section>
      <VTabs v-model="currentTab" class="mt-5 mb-5" v-show="showTabs">
        <VTab value="Step 1">Step 1</VTab>
        <VTab value="Step 2"> Step 2</VTab>
      </VTabs>
      <VWindow v-model="currentTab">
        <VWindowItem value="Step 1">
          <VCard title="Step 1" max-height="520px" class="d-flex flex-column flex-grow-1 overflow-auto">
            <VDivider/>
            <VForm ref="refsubmit2">
              <VCardText >
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
                  <VCol class="mb-2">
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
                  <VCol  v-if="paymentMode == 1">
                    <VTextField 
                      v-model="or_number"
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
                      :rules="[requiredValidator]"
                      label="Receiver Account Number"
                    ></VTextField>
                  </VCol>
                </VRow>
                <!-- <VRow v-if="paymentMode">
                  <VCol v-if="qrcode_image === '/storage/qr_code_image/null'"></VCol>
                  <VCol class="mb-2 text-center" v-else>
                    <h5>OR</h5>
                    <VBtn color="blue" variant="tonal" prepend-icon="tabler:qrcode" size="small" @click="toggleQRcode"> QR Code</VBtn>
                  </VCol>
                </VRow> -->
                <VRow v-if="paymentMode === 2 && qrcode_image !== '/storage/qr_code_image/null'">
                <VCol class="mb-2 text-center">
                <h5>OR</h5>
                <VBtn color="blue" variant="tonal" prepend-icon="tabler:qrcode" size="small" @click="toggleQRcode"> QR Code</VBtn>
                </VCol>
                </VRow>
                <VRow style="padding-left: 22%" v-if="paymentMode">
                  <VCol>
                    <div
                      v-if="qrcode_image === '/storage/qr_code_image/null'"
                      style="text-align: center"
                    >
                    </div>
                    <div
                      v-else
                      class="circle-container"
                      style="text-align: center"
                    >
                      <VImg
                        :src="qrcode_image"
                        alt="Uploaded Image"
                        class="circle-image"
                        style="display: inline-block; max-width: 100%"
                      ></VImg>
                    </div>
                  </VCol>
                </VRow>
              </VCardText>
              <VDivider />
              <VCardText>
                <VRow>
                  <VCol class="text-right">
                    <VBtn class="mr-1" @click.prevent="next" append-icon="tabler:arrow-forward">Next</VBtn>
                    <!-- <VBtn class="mr-1" type="submit">Pay</VBtn> -->
                    <VBtn color="error" @click="cancel" prepend-icon="material-symbols-light:tab-close-outline">Cancel</VBtn>
                  </VCol>
                </VRow>
              </VCardText>
            </VForm>
          </VCard>
        </VWindowItem>

        <VWindowItem value="Step 2">
          <VCard title="Step 2" max-height="520px" class="d-flex flex-column flex-grow-1 overflow-auto">
            <VDivider/>
            <VForm ref="refsubmit">
              <VCardText >
                <VRow>
                  <VCol  class="mb-2">
                    <VTextField
                      v-model="date_of_payment"
                      type="date"
                      label="Date of Payment"
                      :rules="[requiredValidator]"
                    ></VTextField>
                  </VCol>
                </VRow>
                <VRow>
                  <VCol >
                    <h5 class="text-center text-red">
                      Proof of Payment*
                    </h5>
                  </VCol>
                </VRow>
                <VRow>
                  <VCol  class="mb-2">
                    <v-file-input
                      accept=".jpg,.jpeg,.png"
                      ref="fileInputImage"
                      chips
                      :rules="[requiredValidator]"
                      label="Select File"
                      @change="handleFileChangeImage"
                      prepend-icon="tabler:photo-plus"
                    />
                  </VCol>
                </VRow>
                <VRow class="text-center">
                  <VCol v-if="imageUrlPayment">
                    <VBtn color="blue" variant="tonal" prepend-icon="tabler:eye" size="small" @click="toggleDialogPayment"> Click ME!</VBtn>
                  </VCol>
                </VRow>
              </VCardText>
              <VDivider />
              <VCardText>
                <VRow>
                  <VCol class="text-right">
                    <VBtn color="blue" class="mr-1" @click.prevent="back" prepend-icon="tabler:arrow-back">Back</VBtn>
                    <VBtn class="mr-1" prepend-icon="material-symbols-light:payments-outline" @click.prevent="OpenValidation">Submit</VBtn>
                    <VBtn color="error" @click="cancel" prepend-icon="material-symbols-light:tab-close-outline">Cancel</VBtn>
                  </VCol>
                </VRow>
              </VCardText>
            </VForm>
          </VCard>
        </VWindowItem>
      </VWindow>

    <!-- SnackBar -->
    <VSnackbar v-model="isSnackbarSuccess" :timeout="4000" color="success">
      Success! Payment Added!
    </VSnackbar>
    <VSnackbar v-model="isSnackbarErorr" :timeout="4000" color="error">
      Erorr! Please Check Your Field!
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

    <v-dialog v-model="dialogValidation" max-width="540">
      <!-- <DialogCloseBtn @click="dialogValidation = false" /> -->
      <VCard height="330" flat>
        <VCardText>
          <VRow>
            <VCol class="text-center mt-10">
              <div style="text-align: center;">
                <VIcon color="warning" size="110" icon="bi:exclamation-circle"/><br/><br/>
                <h6 class="text-lg font-weight-large">Are you sure you want to Pay?</h6>
              </div>

              <div class="text-center mt-5">
                <VBtn type="submit" @click.prevent="onSubmit" color="primary">Yes! Proceed</VBtn>&nbsp;
                <VBtn @click="dialogValidation = false" color="error" variant="tonal">Cancel</VBtn>
              </div>
            </VCol>
          </VRow>
        </VCardText>
    </VCard>
    </v-dialog>

    <v-dialog v-model="dialogCode" max-width="500">
      <DialogCloseBtn @click="dialogCode = false" />
      <VCard title="QR Code" class="d-flex flex-column flex-grow-1 overflow-auto">
        <VCardText v-if="qrcode_image">
          <v-img class="circle-image" :src="qrcode_image" alt="Zoomed Image"></v-img>
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
