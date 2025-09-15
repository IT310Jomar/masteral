<script setup lang="ts">
import { emailValidator, requiredValidator } from "@validators";
import { VForm } from "vuetify/components";
import loadingScreen from "@/pages/loading/Validationloading.vue";
import { environment } from "@/environments/environment";
import axios from "axios";

const emit = defineEmits(["close", "handledata"]);
const refsubmit = ref<VForm>();
const loading = ref(false);
const isSnackbarSuccess = ref(false);
const isSnackbarErorr = ref(false);
const isPasswordVisible = ref(false);

const firstname = ref();
const lastname = ref();
const middlename = ref();
const contact_number = ref('09');
const email = ref();
const password = ref();

const loaderTrue = () => {
  loading.value = true;
};

const loaderFalse = () => {
  loading.value = false;
};
const noLetterEOrHyphenValidator = (value: string) => {
  const pattern = /^[0-9]*$/; // Only allow digits
  return pattern.test(value) || 'Only numbers are allowed.';
};

const post_editor = () => {
  axios.post(environment.API_URL + 'auth/admin/post-editor', {
    email: email.value,
    // password: password.value,
    firstname: firstname.value,
    lastname: lastname.value,
    middlename: middlename.value,
    contact_number: contact_number.value
  }).then((response) => {
    if (response.data.success) {
      emit('close');
      emit("handledata", response.data);
    }
  }).catch((error) => {
    if (error.response) {
      // The request was made, but the server responded with an error response.
      // You can access the error response data here.
      const errorMessage = error.response.data.message;
      alert(errorMessage);
    } else {
      // Something else went wrong while making the request.
      console.error("An error occurred:", error.message);
    }
  });
}

//on submit function for validation
const onSubmit = () => {
  loaderTrue();
  refsubmit.value?.validate().then(({ valid: isValid }) => {
    if (isValid) {
      post_editor();
      isSnackbarSuccess.value = true
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

</script>

<template>
    <section>
    <DialogCloseBtn @click="close" />
    <v-row>
      <v-col>
        <VCard
          height="540px"
          class="d-flex flex-column flex-grow-1 overflow-auto"
          title="Add Editor"
        >
          <VDivider class="mt-4" />
          <VCardText>
            <VForm ref="refsubmit" @submit.prevent="onSubmit">
              <v-row>

                <v-card-text>
                    <VRow>
                      <VCol class="mb-2">
                        <v-text-field
                          :rules="[requiredValidator]"
                          v-model="firstname"
                          label="First Name"
                        />
                      </VCol>
                    </VRow>
                    <VRow>
                      <VCol class="mb-2">
                        <v-text-field
                          v-model="middlename"
                          label="Middle Name"
                        />
                      </VCol>
                    </VRow>
                    <VRow>
                      <VCol class="mb-2">
                        <v-text-field
                          :rules="[requiredValidator]"
                          v-model="lastname"
                          label="Last Name"
                        />
                      </VCol>
                    </VRow>
                    <VRow>
                      <VCol sm="3.5" md="3.5" class="mb-2">
                        <v-text-field
                          :rules="[requiredValidator, noLetterEOrHyphenValidator, () => (contact_number.length < 11 && 'Invalid Contact Number.')]"
                          v-model="contact_number"
                          label="Contact Number"
                        />
                      </VCol>
                    </VRow>
                    <VRow>
                      <!-- email -->
                      <VCol cols="12">
                        <VTextField
                          v-model="email"
                          :rules="[requiredValidator, emailValidator]"
                          label="Email"
                          type="email"
                        />
                      </VCol>

                      <!-- password -->
                      <!-- <VCol cols="12">
                        <VTextField
                          v-model="password"
                          :rules="[requiredValidator,
                          () => (password.length < 8 && 'Password must be of at least 8 alphanumeric character')]"
                          label="Password"
                          :type="isPasswordVisible ? 'text' : 'password'"
                          :append-inner-icon="
                            isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'
                          "
                          @click:append-inner="isPasswordVisible = !isPasswordVisible"
                        />
                      </VCol> -->
                    </VRow>
                </v-card-text>
              </v-row>
              <VDivider />
              <VCol class="mt-5" style="text-align: right">
                <VBtn type="submit" size="large"> Submit</VBtn>
              </VCol>
            </VForm>
          </VCardText>
        </VCard>
      </v-col>
    </v-row>
    <!-- SnackBar -->
    <VSnackbar v-model="isSnackbarSuccess" :timeout="4000" color="success">
      Successfully Added
    </VSnackbar>
    <VSnackbar v-model="isSnackbarErorr" :timeout="4000" color="error">
      Erorr! Please Check Your Field
    </VSnackbar>
    <VDialog v-model="loading" max-width="300">
      <loadingScreen />
    </VDialog>
  </section>
</template>
