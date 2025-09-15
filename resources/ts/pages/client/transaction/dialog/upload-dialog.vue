<script setup lang="ts">
import { environment } from "@/environments/environment";
import loadingScreen from "@/pages/loading/Validationloading.vue";
import { requiredValidator } from "@validators";
import axios from "axios";
import { ref } from "vue";
import { VForm } from "vuetify/components";
import { useRouter } from "vue-router";

const router = useRouter();

const client_id = JSON.parse(localStorage.getItem("clientData") || "{}");
const token = JSON.parse(localStorage.getItem("accessToken") || "{}");
const refsubmit = ref<VForm>();

const emit = defineEmits(["close", "paymentData"]);
const props = defineProps(['row']);

const cancel = () => {
  emit("close");
};

const dialog = ref(false);
const currentItemIndex = ref(0);

const steps = [
  { title: "Step 1: File Naming", example: "Smith_Jones_2023_Research_Paper.pdf", 
    sub1: "Author(s) last name(s)", sub2: "Publication year", sub3: "Title of the paper (shortened if necessary)",
    description: "Choose a descriptive and standardized file name that includes essential information. A common format is to use the following elements separated by underscores:" 
  },

  { title: "Step 2: PDF Formatting", sub1: "Use a standard paper size, typically A4 (8.5 x 11) or letter (8.5 x 11).", sub2: "Use a readable font, such as Times New Roman, Arial, or similar, with a size of 12-point.", sub3: "Maintain consistent margins (1-inch or 2.54 cm) on all sides.", 
    sub4: "Include page numbers at the bottom or top of the page.", 
    description: "Ensure that your PDF file adheres to these formatting standards:" },

  { title: "Step 3: Abstract", description: "Include a concise abstract at the beginning of the paper. The abstract should summarize the research, methods, key findings, and conclusions in a clear and concise manner (usually 150-250 words)." },
 
  { title: "Step 4: Upload Your File", sub1: "That's it! Your PDF file is ready for upload. If you have any questions or encounter issues during the process, feel free to reach out for assistance. Happy uploading!",
    description: "When you reach this step, use the file input field to select your PDF file. Ensure the file is in PDF format for compatibility. Once selected, proceed to the next step." }
];

// const currentStep = toRef(steps[currentItemIndex.value] || {});

const nextStep = () => {
  if (currentItemIndex.value < steps.length - 1) {
    currentItemIndex.value++;
  }
};

const previousStep = () => {
  if (currentItemIndex.value > 0) {
    currentItemIndex.value--;
  }
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

//on submit function for validation
const onSubmit = () => {
  loaderTrue();
  refsubmit.value?.validate().then(({ valid: isValid }) => {
    if (isValid) {
      post_uploadPdf();
      isSnackbarSuccess.value = true;
      loaderFalse();
    } else {
      isSnackbarErorr.value = true;
      loaderFalse();
    }
  });
};

const post_uploadPdf = () => {
  const formData = new FormData();
  formData.append("client_id", client_id[0].id);
  formData.append("payment_id", props.row.id);

  // Check if the file input has files and if the first file is selected
  if (fileInputFile.value?.files && fileInputFile.value.files.length > 0) {
    const file = fileInputFile.value.files[0];
    formData.append("filepdf", file);
  }

  axios
    .post(environment.API_URL + "client/client/post-uploadpdf", formData)
    .then((response) => {
      if (response.data.success) {
        router.push("/client/my-request/pending");
        emit("paymentData", response.data);
        cancel();
      }
    });
};

const modePayment = ref();
const getModePayment = () => {
  axios
    .get(environment.API_URL + "client/client/get-mode-of-payment", {
      params: {
        token:token
      }
    })
    .then((response) => {
      if (response.data.success) {
        modePayment.value = response.data.mode;
      }
    });
};

const initializeData = () => {
  getModePayment();
};

onMounted(() => {
  initializeData();
});
</script>

<template>
  <section>
    <!-- <VCard class="d-flex flex-column flex-grow-1 overflow-auto">
      <VRow>
        <VCol class="mb-4">
          <v-card-title>Upload your PDF file</v-card-title>
        </VCol>
      </VRow>

      <VDivider />
      <VForm ref="refsubmit" @submit.prevent="onSubmit">
        <VCardText>
          <VRow>
            <VCol class="mb-2">
              <v-file-input
                accept=".pdf"
                ref="fileInputFile"
                chips
                :rules="[requiredValidator]"
                label="Select File"
                @change="handleFileChangeFile"
              />
            </VCol>
          </VRow>
        </VCardText>
        <VDivider />
        <VCardText>
          <VRow>
            <VCol class="text-right">
              <v-card-actions>
                <v-btn color="error" v-if="currentItemIndex === 0" @click="cancel" prepend-icon="tabler:arrow-back">Back</v-btn>
                <v-btn @click="previousItem" v-if="currentItemIndex > 0" prepend-icon="tabler:arrow-back">Previous</v-btn>
                <v-btn @click="nextItem" v-if="currentItemIndex < 6" append-icon="tabler:arrow-forward">Next</v-btn>
                <v-btn class="mr-1" type="submit" v-if="currentItemIndex === 6">Upload</v-btn>
              </v-card-actions>
            </VCol>
          </VRow>
        </VCardText>
      </VForm>
    </VCard> -->

    <VCard class="step-card d-flex flex-column flex-grow-1 overflow-auto">

    <!-- CARD OF STEPS -->
    <VCardText>
      <h2 class="mb-5">{{ steps[currentItemIndex].title }}</h2>
      <p style="text-align: justify;">{{ steps[currentItemIndex].description }}</p>


      <ul>
          <li v-if="currentItemIndex === 0">
            {{ steps[currentItemIndex].sub1 }}
          </li>
          <li v-if="currentItemIndex === 0">
            {{ steps[currentItemIndex].sub2 }}
          </li>
          <li v-if="currentItemIndex === 0">
            {{ steps[currentItemIndex].sub3 }}
          </li>
          <li v-if="currentItemIndex === 0">
            {{ steps[currentItemIndex].sub4 }}
          </li>
        </ul>

        <ul v-if="currentItemIndex === 1">
          <li>
            {{ steps[currentItemIndex].sub1 }}
          </li>
          <li>
            {{ steps[currentItemIndex].sub2 }}
          </li>
          <li>
            {{ steps[currentItemIndex].sub3 }}
          </li>
          <li>
            {{ steps[currentItemIndex].sub4 }}
          </li>
        </ul>

        <ul v-if="currentItemIndex === 2">
          <li>
            {{ steps[currentItemIndex].sub1 }}
          </li>
          <li>
            {{ steps[currentItemIndex].sub2 }}
          </li>
          <li>
            {{ steps[currentItemIndex].sub3 }}
          </li>
        </ul>

      <h5 class="mt-3 text-center" v-if="currentItemIndex === 3">{{ steps[currentItemIndex].sub1 }}</h5>
        
      <h5 class="mt-3 text-center">{{ steps[currentItemIndex].example }}</h5>

      <!-- UPLOAD FILE -->
        <VForm ref="refsubmit" class="mb-5">
          <VRow v-if="currentItemIndex === steps.length - 1">
            <VDivider class="mt-2" />
              <VCardText>
                <VRow>
                  <VCol class="mb-2" cols="12">
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
              </VCardText>
              <!-- <VRow>
                <VCol class="text-right">
                  <v-card-actions>
                    <v-btn color="error" @click="cancel" prepend-icon="tabler:folder-cancel">Cancel</v-btn>
                    <v-btn class="mr-1" type="submit" prepend-icon="tabler:file-upload">Upload</v-btn>
                  </v-card-actions>
                </VCol>
              </VRow> -->
          </VRow>
        </VForm>
      <VRow>
        <VCol class="text-right" style="margin-bottom: -5%;">
      <VDivider class="mt-2 mb-3" />
          <v-card-actions>
            <v-btn color="error" v-if="currentItemIndex === 0" @click="cancel" prepend-icon="tabler:arrow-back">Back</v-btn>
            <!-- <v-btn color="error" @click="cancel" prepend-icon="tabler:folder-cancel" v-if="currentItemIndex === steps.length - 1">Cancel</v-btn> -->
            <v-btn color="error" @click="previousStep" v-if="currentItemIndex > 0" prepend-icon="tabler:arrow-back">Previous</v-btn>
            <v-btn @click.prevent="onSubmit" class="mr-1" type="submit" prepend-icon="tabler:file-upload" v-if="currentItemIndex === steps.length - 1">Upload</v-btn>
            <v-btn color="blue" @click="nextStep" :hidden="currentItemIndex === steps.length - 1" append-icon="tabler:arrow-forward">Next</v-btn>
          </v-card-actions>
        </VCol>
      </VRow>
    </VCardText>
  </VCard>

    <!-- SnackBar -->
    <VSnackbar v-model="isSnackbarSuccess" :timeout="4000" color="success">
      Success! Uploaded!
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

.step-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    background-color: #fff;
    transition: transform 0.2s ease-in-out;
  }

  .step-card:hover {
    transform: scale(1.02);
  }
</style>
