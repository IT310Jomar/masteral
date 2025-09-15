<script setup lang="ts">
import { environment } from "@/environments/environment";
import axios from "axios";
import { onMounted, ref } from "vue";
import { VForm } from "vuetify/components";
import loadingScreen from "@/pages/loading/Validationloading.vue";
import { emailValidator, requiredValidator } from "@validators";
import logo from "@images/DITADS Logo_PNG.png";

const client_id = JSON.parse(localStorage.getItem("clientData") || "{}");
const refsubmit = ref<VForm>();

const emit = defineEmits(["close", "handledata"]);

const cancel = () => {
  emit("close");
};

const services_name = ref();
const price = ref();

const fileInputImage = ref<HTMLInputElement | null>(null);
const imageUrlPayment = ref<string | null>(null);

const dialogPreview = ref(false);

const toggleDialogPayment = () => {
  dialogPreview.value = true;
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

const loading = ref(false);
const isSnackbarSuccess = ref(false);
const isSnackbarErorr = ref(false);

const loaderTrue = () => {
  loading.value = true;
};

const loaderFalse = () => {
  loading.value = false;
};

const post_payment = () => {
  const formData = new FormData();
  formData.append("services_name", services_name.value);
  formData.append("price", price.value);
  // Check if the file input has files and if the first file is selected
  // if (fileInputImage.value?.files && fileInputImage.value.files.length > 0) {
  //   const file = fileInputImage.value.files[0];
  //   formData.append("qr_code", file);
  // }

  axios.post(environment.API_URL + "auth/admin/post-services-data", formData)
    .then((response) => {
      if (response.data.success) {
        emit("handledata", response.data);
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
    } else {
      isSnackbarErorr.value = true;
      loaderFalse();
    }
  });
};

const initializeData = () => {
  // getModePayment();
};

onMounted(() => {
  // initializeData();
});

const minimumValueValidator = (value: number) =>
  value >= 0 || 'Minimum value is 200';
  const integerValidator = (value: string) =>
  /^[0-9]+$/.test(value) || 'Please enter an integer value.Example 200';

const fileSizeValidator = () => {
  if (fileInputImage.value) {
    const maxSizeKB = 5000; // Maximum file size in kilobytes (2 MB)
    const maxSizeBytes = maxSizeKB * 1024; // Convert kilobytes to bytes

    if (fileInputImage.value.files && fileInputImage.value.files[0].size > maxSizeBytes) {
      return `File size must be less than ${maxSizeKB} KB`;
    }
  }

  return true;
};
</script>

<template>
  <section>
    <VCard title="Add Service" height="320px" class="d-flex flex-column flex-grow-1 overflow-auto">
      <VDivider />
      <VForm ref="refsubmit" @submit.prevent="onSubmit">
        <VCardText >
          <VRow>
            <VCol class="mb-2">
              <VTextField
                v-model="services_name"
                :rules="[requiredValidator]"
                label="Name"
              ></VTextField>
            </VCol>
          </VRow>
          <VRow>
            <VCol class="mb-2">
              <VTextField
                v-model="price"
                :rules="[requiredValidator,minimumValueValidator]"
                label="Price"
                type="number"
              ></VTextField>
            </VCol>
          </VRow>
          <!-- <VRow style="padding-left: 22%">
            <VCol>
              <div
                v-if="imageUrlPayment"
                class="circle-container"
                style="text-align: center"
              >
                <VImg
                  :src="imageUrlPayment"
                  alt="Uploaded Image"
                  class="circle-image"
                  style="display: inline-block; max-width: 100%"
                  @click="toggleDialogPayment"
                ></VImg>
              </div>
              <div
                v-else
                class="circle-container"
                style="text-align: center"
              >
                <VImg
                  :src="logo"
                  alt="Uploaded Image"
                  class="circle-image"
                  style="display: inline-block; max-width: 100%"
                  @click="toggleDialogPayment"
                ></VImg>
              </div>
            </VCol>
          </VRow> -->
        </VCardText>
        <VDivider />
        <VCardText>
          <VRow>
            <VCol class="text-right">
              <VBtn class="mr-1" type="submit" prepend-icon="tabler-plus">Save</VBtn>
              <VBtn color="error" @click="cancel">Cancel</VBtn>
            </VCol>
          </VRow>
        </VCardText>
      </VForm>
    </VCard>

    <!-- SnackBar -->
    <VSnackbar v-model="isSnackbarSuccess" :timeout="4000" color="success">
      Success! Services Added!
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
          <v-img :src="imageUrlPayment" alt="Zoomed Image"></v-img>
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
