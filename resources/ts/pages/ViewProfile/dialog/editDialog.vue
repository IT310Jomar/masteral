<script setup lang="ts">
import { emailValidator, requiredValidator } from "@validators";
import { VForm } from "vuetify/components";
import loadingScreen from "@/pages/loading/Validationloading.vue";
import { environment } from "@/environments/environment";
import axios from "axios";
import { onMounted, ref } from "vue";

const emit = defineEmits(["close", "handledata"]);
const props = defineProps(['clientData','editorData']);
const refsubmit = ref<VForm>();
const loading = ref(false);
const isSnackbarSuccess = ref(false);
const isSnackbarErorr = ref(false);
const isPasswordVisible = ref(false);

const firstname = ref();
const lastname = ref();
const clientmiddlename = ref();
const middlename = ref();
const contact_number = ref();
const email = ref();
const school = ref();
const school_type = ref();
const course = ref();

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

const previosimage = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

const handleFileChange = () => {
  if (
    fileInput.value &&
    fileInput.value.files &&
    fileInput.value.files.length > 0
  ) {
    const file = fileInput.value.files[0];
    const reader = new FileReader();

    reader.onload = (e) => {
      if (e.target && typeof e.target.result === "string") {
        previosimage.value = e.target.result;
      }
    };

    reader.readAsDataURL(file);
  }
};

const clientupload = () =>{
  const formData = new FormData();

  formData.append("client_id", props.clientData.user_id);
  formData.append("email", email.value);
  formData.append("firstname", firstname.value,);
  formData.append("lastname", lastname.value);
  if (clientmiddlename.value !== null) {
    formData.append("middlename", clientmiddlename.value);
  }
  if (contact_number.value !== null) {
    formData.append("contact_number", contact_number.value);
  }
  if (school.value !== null) {
    formData.append("schoolname", school.value);
  }
  if (school_type.value !== null) {
    formData.append("school_type", school_type.value);
  }
  if (course.value !== null) {
    formData.append("course", course.value);
  }

  if (fileInput.value?.files && fileInput.value.files.length > 0) {
    const file = fileInput.value.files[0];
    formData.append("image", file);
  }

  axios.post(environment.API_URL + `auth/admin/client-upload-profile/${props.clientData.id}`, formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    })
    .then((response) => {
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

const editorupload = () =>{
  const formData = new FormData();

  formData.append("editor_id", props.editorData.user_id);
  formData.append("email", email.value);
  formData.append("firstname", firstname.value,);
  formData.append("lastname", lastname.value);
  if (middlename.value !== null) {
    formData.append("middlename", middlename.value);
  }
  formData.append("contact_number", contact_number.value);

  if (fileInput.value?.files && fileInput.value.files.length > 0) {
    const file = fileInput.value.files[0];
    formData.append("image", file);
  }

  axios.post(environment.API_URL + `auth/admin/editor-upload-profile/${props.editorData.id}`, formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    })
    .then((response) => {
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
      clientupload();
      isSnackbarSuccess.value = true
      loaderFalse()
    } else {
      isSnackbarErorr.value = true;
      loaderFalse();
    }
  });
};

//on submit function for validation
const onSubmitEditor = () => {
  loaderTrue();
  refsubmit.value?.validate().then(({ valid: isValid }) => {
    if (isValid) {
      editorupload();
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

const token = JSON.parse(localStorage.getItem("accessToken") || "{}");
const schoolType = ref();
const getSchoolType = () => {
  axios
    .get(environment.API_URL + "auth/admin/get-school-type",{
      params: {
        token:token
      }
    })
    .then((response) => {
      if (response.data.success) {
        if (response.data.types) {
          const data = [];
          for (var x = 0; x < response.data.types.length; x++) {
            const types = {
              item: response.data.types[x].name,
              value: response.data.types[x].id,
            };
            data.push(types);
          }
          schoolType.value = data;
        }
      }
    });
};

onMounted(()=> {
  if(props.clientData != null) {
    firstname.value = props.clientData?.firstname
    lastname.value = props.clientData?.lastname
    clientmiddlename.value = props.clientData.middlename
    contact_number.value = props.clientData?.contact
    email.value = props.clientData?.users.email
    school.value = props.clientData?.school
    school_type.value = props.clientData.school_type_id
    course.value = props.clientData?.course
  }

  if(props.editorData != null) {
    firstname.value = props.editorData?.firstname
    lastname.value = props.editorData?.lastname
    middlename.value = props.editorData.middlename
    contact_number.value = props.editorData?.contact
    email.value = props.editorData?.users.email
  }

  getSchoolType();
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
          title="Edit Your Profile"
        >
          <VDivider class="mt-4" />
          <VCardText v-if="props.clientData != null">
            <VForm ref="refsubmit" @submit.prevent="onSubmit">
              <v-row>
                <v-card-text>
                    <div class="profile-card text-center mb-2">
                    <div class="profile-card-photo"
                   >
                    <VCol>
                        <VAvatar
                            class="cursor-pointer"
                            color="primary"
                            variant="tonal"
                            style="
                              width: 200px;
                              height: 200px;
                              margin-top: -5%;
                              margin-bottom: 3%;
                            "
                          >
                            <VImg
                            :src="previosimage"
                            alt="Uploaded Image"
                            class="circle-image"></VImg >
                        </VAvatar>
                      </VCol>
                      </div>
                      </div>
                    <VRow>
                      <VCol>
                        <v-file-input
                            ref="fileInput"
                            @change="handleFileChange"
                            label="Select File"
                            accept=".jpg,.jpeg,.png"
                          />
                      </VCol>
                    </VRow>
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
                          v-model="clientmiddlename"
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
                    <VRow>
                      <VCol class="mb-2">
                        <v-text-field
                          :rules="[requiredValidator]"
                          v-model="school"
                          label="School"
                        />
                      </VCol>
                    </VRow>
                    <VRow>
                      <VCol class="mb-2">
                        <VSelect
                          v-model="school_type"
                          :rules="[requiredValidator]"
                          label="School Type"
                          :items="schoolType"
                          item-title="item"
                          item-value="value"
                        >
                        </VSelect>
                      </VCol>
                    </VRow>
                    <VRow>
                      <VCol class="mb-2">
                        <v-text-field
                          :rules="[requiredValidator]"
                          v-model="course"
                          label="Course"
                        />
                      </VCol>
                    </VRow>
                </v-card-text>
              </v-row>
              <VDivider />
              <VCol class="mt-5" style="text-align: right">
                <VBtn color="error" variant="tonal" class="mr-2" @click="close">Cancel</VBtn>
                <VBtn type="submit" color="primary" ><v-icon start icon="ph:note-pencil-fill"></v-icon>Update</VBtn>
              </VCol>
            </VForm>
          </VCardText>

          <VCardText v-if="props.editorData != null">
            <VForm ref="refsubmit" @submit.prevent="onSubmitEditor">
              <v-row>
                <v-card-text>
                    <div class="profile-card text-center mb-2">
                    <div class="profile-card-photo"
                   >
                    <VCol>
                        <VAvatar
                            class="cursor-pointer"
                            color="primary"
                            variant="tonal"
                            style="
                              width: 200px;
                              height: 200px;
                              margin-top: -5%;
                              margin-bottom: 3%;
                            "
                          >
                            <VImg
                            :src="previosimage"
                            alt="Uploaded Image"
                            class="circle-image"></VImg >
                        </VAvatar>
                      </VCol>
                      </div>
                      </div>
                    <VRow>
                      <VCol>
                        <v-file-input
                            ref="fileInput"
                            @change="handleFileChange"
                            label="Select File"
                            accept=".jpg,.jpeg,.png"
                          />
                      </VCol>
                    </VRow>
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
                    <VRow>
                      <VCol sm="3.5" md="3.5" class="mb-2">
                        <v-text-field
                          :rules="[requiredValidator, noLetterEOrHyphenValidator, () => (contact_number.length < 11 && 'Invalid Contact Number.')]"
                          v-model="contact_number"
                          label="Contact Number"
                        />
                      </VCol>
                    </VRow>
                </v-card-text>
              </v-row>
              <VDivider />
              <VCol class="mt-5" style="text-align: right">
                <VBtn color="error" variant="tonal" class="mr-2" @click="close">Cancel</VBtn>
                <VBtn type="submit" color="primary" ><v-icon start icon="ph:note-pencil-fill"></v-icon>Update</VBtn>
              </VCol>
            </VForm>
          </VCardText>
        </VCard>
      </v-col>
    </v-row>
    <!-- SnackBar -->
    <VSnackbar v-model="isSnackbarSuccess" :timeout="4000" color="success">
      Success! Updated your Information.
    </VSnackbar>
    <VSnackbar v-model="isSnackbarErorr" :timeout="4000" color="error">
      Erorr! Please Check Your Field.
    </VSnackbar>
    <VDialog v-model="loading" max-width="300">
      <loadingScreen />
    </VDialog>
  </section>
</template>
