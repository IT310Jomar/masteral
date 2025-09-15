<script setup lang="ts">
import { emailValidator, requiredValidator } from "@validators";
import { VForm } from "vuetify/components";
import loadingScreen from "@/pages/loading/Validationloading.vue";
import { environment } from "@/environments/environment";
import axios from "axios";

const emit = defineEmits(["close", "handledata"]);
const props = defineProps(['row']);
const refsubmit = ref<VForm>();
const loading = ref(false);
const isSnackbarSuccess = ref(false);
const isSnackbarErorr = ref(false);
const isPasswordVisible = ref(false);

const firstname = ref();
const lastname = ref();
const middlename = ref();
const contact_number = ref();
const email = ref();

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

const edit_editor = () => {
  axios.put(environment.API_URL + 'auth/admin/edit-editor/' + props.row.id, {
    email: email.value,
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
      edit_editor();
      isSnackbarSuccess.value = true
      loaderFalse()
    } else {
      isSnackbarErorr.value = true;
      loaderFalse();
    }
  });
};

const close = () => {
  emit("close");
};

onMounted(()=> {
  if(props.row != null) {
    firstname.value = props.row?.firstname
    lastname.value = props.row?.lastname
    middlename.value = props.row?.middlename
    contact_number.value = props.row?.contact
    email.value = props.row?.users.email
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
          title="Edit Infomation Editor"
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
                          readonly
                        />
                      </VCol>
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
      Success! Editor Information Edited.
    </VSnackbar>
    <VSnackbar v-model="isSnackbarErorr" :timeout="4000" color="error">
      Erorr! Please Check Your Field.
    </VSnackbar>
    <VDialog v-model="loading" max-width="300">
      <loadingScreen />
    </VDialog>
  </section>
</template>
