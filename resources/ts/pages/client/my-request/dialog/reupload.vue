<script setup lang="ts">
import { environment } from "@/environments/environment";
import axios from "axios";
import { ref } from "vue";
import { VForm } from "vuetify/components";
import loadingScreen from "@/pages/loading/Validationloading.vue";
import { emailValidator, requiredValidator } from "@validators";

const refsubmit = ref<VForm>();

const emit = defineEmits(["close", "handleData"]);
const props = defineProps(['row']);

const cancel = () => {
  emit("close");
};

const fileInputFile = ref<HTMLInputElement | null>(null);
const imageUrlupload = ref<string | null>(null);

const handleFileChangeFile = () => {
  if (
    fileInputFile.value &&
    fileInputFile.value.files &&
    fileInputFile.value.files.length > 0
  ) {
    const file = fileInputFile.value.files[0];
    const reader = new FileReader();

    reader.onload = (e) => {
      if (e.target && typeof e.target.result === "string") {
        imageUrlupload.value = e.target.result;
      }
    };

    reader.readAsDataURL(file);
  }
};

const fileSizeValidator = () => {
  if (fileInputFile.value) {
    const maxSizeKB = 2048; // Maximum file size in kilobytes (2 MB)
    const maxSizeBytes = maxSizeKB * 1024; // Convert kilobytes to bytes

    if (fileInputFile.value.files && fileInputFile.value.files[0].size > maxSizeBytes) {
      return `File size must be less than ${maxSizeKB} KB`;
    }
  }

  return true;
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

const post_reuploadPdf = () => {
  const formData = new FormData();

  // Check if the file input has files and if the first file is selected
  if (fileInputFile.value?.files && fileInputFile.value.files.length > 0) {
    const file = fileInputFile.value.files[0];
    formData.append("filepdf", file);
  }

  axios
    .post(environment.API_URL + "client/client/post-re-uploadpdf/" + props.row, formData)
    .then((response) => {
      if (response.data.success) {
        emit("handleData", response.data);
        cancel();
      }
    });
};

//on submit function for validation
const onSubmit = () => {
  loaderTrue();
  refsubmit.value?.validate().then(({ valid: isValid }) => {
    if (isValid) {
      post_reuploadPdf();
      isSnackbarSuccess.value = true;
      loaderFalse();
    } else {
      isSnackbarErorr.value = true;
      loaderFalse();
    }
  });
};


// onMounted(() => {

// });
</script>

<template>
  <section>
    <VCard title="Re-Upload PDF File" class="d-flex flex-column flex-grow-1 overflow-auto">
      <VDivider />
      <VForm ref="refsubmit" @submit.prevent="onSubmit">
        <VCardText>
          <VRow>
            <VCol class="mb-2">
              <v-file-input
                accept=".pdf"
                ref="fileInputFile"
                chips
                :rules="[requiredValidator, fileSizeValidator]"
                label="Select File"
                @change="handleFileChangeFile"
                prepend-icon="tabler:file-type-pdf"
              />
            </VCol>
          </VRow>
          <!-- <VRow style="padding-left: 22%">
            <VCol>

            </VCol>
          </VRow> -->
        </VCardText>
        <VDivider />
        <VCardText>
          <VRow>
            <VCol class="text-right">
              <VBtn class="mr-1" type="submit">Re-Upload</VBtn>
              <VBtn color="error" @click="cancel">Cancel</VBtn>
            </VCol>
          </VRow>
        </VCardText>
      </VForm>
    </VCard>

    <!-- SnackBar -->
    <VSnackbar v-model="isSnackbarSuccess" :timeout="4000" color="success">
      Success! Re-Uploaded!
    </VSnackbar>
    <VSnackbar v-model="isSnackbarErorr" :timeout="4000" color="error">
      Erorr! Please Check Your Field!
    </VSnackbar>
    <VDialog v-model="loading" max-width="300">
      <loadingScreen />
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
