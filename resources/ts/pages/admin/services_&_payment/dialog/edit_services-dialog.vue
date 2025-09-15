<script setup lang="ts">
import { environment } from "@/environments/environment";
import loadingScreen from "@/pages/loading/Validationloading.vue";
import { requiredValidator } from "@validators";
import axios from "axios";
import { onMounted, ref } from "vue";
import { VForm } from "vuetify/components";

const emit = defineEmits(["close", "handledata"]);
const props = defineProps(["row"]);
const refsubmit = ref<VForm>();
const loading = ref(false);
const isSnackbarSuccess = ref(false);
const isSnackbarErorr = ref(false);

const services_name = ref();
const price = ref();
const status = ref();

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

const loaderTrue = () => {
  loading.value = true;
};

const loaderFalse = () => {
  loading.value = false;
};

const initializeData = () => {

};

const edit_payment = () => {
  const formData = new FormData();
  formData.append("services_name", services_name.value);
  formData.append("price", price.value);
  // Check if the file input has files and if the first file is selected
  // if (fileInputImage.value?.files && fileInputImage.value.files.length > 0) {
  //   const file = fileInputImage.value.files[0];
  //   formData.append("qr_code", file);
  // }
  
  axios.post(environment.API_URL + `auth/admin/edit-services-data/${props.row.id}`, formData, {
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
    services_name.value = props.row?.name;
    price.value = props.row?.price;
  }
  initializeData();
});
const minimumValueValidator = (value: number) =>
  value >= 200 || 'Minimum value is 200';
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
    <DialogCloseBtn @click="close" />
    <v-row>
      <v-col>
        <VCard
          height="320px"
          class="d-flex flex-column flex-grow-1 overflow-auto"
          title="Edit Service"
        >
          <VDivider />
            <VForm ref="refsubmit" @submit.prevent="onSubmit">
              <VCardText>
                <VRow>
                  <VCol class="mb-2">
                    <VTextField
                      v-model="services_name"
                      type="text"
                      :rules="[requiredValidator]"
                      label="Name"
                    ></VTextField>
                  </VCol>
                </VRow>
                <VRow>
                  <VCol class="mb-2">
                    <VTextField
                      v-model="price"
                      type="number"
                      :rules="[requiredValidator, minimumValueValidator]"
                      label="Price"
                    ></VTextField>
                  </VCol>
                </VRow>
                <!-- <VRow style="padding-left: 15%">
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
                        :src="'/storage/qr_code_image/' + qr_code"
                        alt="Uploaded Image"
                        class="circle-image"
                        style="display: inline-block; max-width: 100%"
                      ></VImg>
                    </div>
                  </VCol>
                </VRow> -->
              </VCardText>
              <VCardText>
                <VCol style="text-align: right">
                  <VBtn type="submit" prepend-icon="tabler-edit">Update</VBtn>
                </VCol>
              </VCardText>
            </VForm>
        </VCard>
      </v-col>
    </v-row>
    <!-- SnackBar -->
    <VSnackbar v-model="isSnackbarSuccess" :timeout="4000" color="success">
      Success! Services Data Edited.
    </VSnackbar>
    <VSnackbar v-model="isSnackbarErorr" :timeout="4000" color="error">
      Erorr! Please Check Your Field.
    </VSnackbar>
    <VDialog v-model="loading" max-width="300">
      <loadingScreen />
    </VDialog>

    <v-dialog v-model="dialogPreview" max-width="500">
      <DialogCloseBtn @click="dialogPreview = false" />
      <VCard
        title="Preview"
        class="d-flex flex-column flex-grow-1 overflow-auto"
      >
        <VCardText v-if="imageUrlPayment">
          <v-img :src="imageUrlPayment" alt="Zoomed Image"></v-img>
        </VCardText>
        <VCardText>
          <VCol>
            <div class="float-right">
              <v-btn @click="dialogPreview = false" color="error">Close</v-btn>
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
