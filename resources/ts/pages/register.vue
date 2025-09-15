<script setup lang="ts">
import { environment } from "@/environments/environment";
import { initialAbility } from "@/plugins/casl/ability";
import { useAppAbility } from "@/plugins/casl/useAppAbility";
import axios from "@axios";
import { useGenerateImageVariant } from "@core/composable/useGenerateImageVariant";
import logo from "@images/logos.png";
import authV2MaskDark from "@images/pages/misc-mask-dark.png";
import authV2MaskLight from "@images/pages/misc-mask-light.png";
import {
  emailValidator,
  requiredValidator
} from "@validators";
import { VForm } from "vuetify/components";
import loadingScreen from "../pages/loading/Validationloading.vue";

const refVForm = ref<VForm>();
const firstname = ref();
const middlename = ref();
const lastname = ref();
const course = ref();
const contact = ref();
const school = ref();
const school_type_id = ref();
const schoolType = [
  { title: "Public", value: "Public" },
  { title: "Private", value: "Private" },
];
const email = ref();
const password = ref();
const privacyPolicies = ref(true);

// Router
const route = useRoute();
const router = useRouter();

const loading = ref(false);
const validated = ref(false);
const successfully = ref(false);
const dialogNeedVerify = ref(false);

watch(loading, (value) => {
  if (!value) return;

  setTimeout(() => {
    loading.value = false;
  }, 1000);
});

const loader = () => {
  loading.value = true;
};

// Ability
const ability = useAppAbility();

// Form Errors
const errors = ref<Record<string, string | undefined>>({
  email: undefined,
  password: undefined,
});

const logout = () => {
  localStorage.removeItem("userData");
  localStorage.removeItem("accessToken");
  localStorage.removeItem("userAbilities");
  localStorage.removeItem("userRole");
  localStorage.removeItem("clientData");
  localStorage.removeItem("editorData");

  ability.update(initialAbility);

  router.push('/login'); // Redirect the user to the login page
};

const register = () => {
  axios
    .post(environment.API_URL + "client/client-register", {
      firstname: firstname.value,
      middlename: middlename.value,
      lastname: lastname.value,
      // contact: contact.value,
      // course: course.value,
      // school: school.value,
      // school_type_id: school_type_id.value,
      email: email.value,
      password: password.value,
    })
    .then((r) => {
      localStorage.setItem("userAbilities",JSON.stringify(r.data.user.ability));
      localStorage.setItem("userData", JSON.stringify(r.data.user.user));
      localStorage.setItem("accessToken", JSON.stringify(r.data.access_token));
      localStorage.setItem("userRole",JSON.stringify(r.data.user.role));
      localStorage.setItem("clientData",JSON.stringify(r.data.user.clientData));
      localStorage.setItem("editorData",JSON.stringify(r.data.user.editorData));

      ability.update(r.data.user.ability);

      // Redirect to `to` query if exist or redirect to index route
      // router.replace(route.query.to ? String(route.query.to) : '/')

      logout();
    })
    .catch((error) => {
      console.log('error')
      alert("Email is already in use. Please choose a different email.");
    });
};

const authThemeMask = useGenerateImageVariant(authV2MaskLight, authV2MaskDark);

const isPasswordVisible = ref(false);

const onSubmit = () => {
  refVForm.value?.validate().then(({ valid: isValid }) => {
    loader();
    if (isValid) {
      setTimeout(() => {
        register();
      }, 2000);
      dialogNeedVerify.value = true
      successfully.value = true;
    } else {
      validated.value = true;
    }
  });
};
</script>

<template>
  <VRow no-gutters class="auth-wrapper">

    <VCol lg="6" class="d-none d-lg-flex">
      <VImg
        
        cover
        class="d-flex align-center justify-center content"
      >
      <div class="position-relative auth-bg rounded-lg w-100 ma-8 me-0">
        <div
          class="d-flex align-center justify-center w-100 h-100"
          style="margin-top: -9%"
        >
        <VImg
        :src="logo"
        style="width: 250px;height:250px"
        class="d-flex align-center justify-center content loaders"
      />
        </div>

        
      </div>
    </VImg>

    </VCol>

    <VCol cols="14" lg="6" class="d-flex align-center justify-center">
      <VImg
    
        cover
        class="d-flex align-center justify-center content"
      >
      <div class="d-flex align-center justify-center content">
        <VCard flat :max-width="700" class="mt-12 mt-sm-0 pa-4">
          <VCardText>
           
            <h5 class="text-h5 font-weight-semibold mb-1">
              Adventure starts here 🚀
            </h5>
            <p class="mb-0">Make your app management easy and fun!</p>
          </VCardText>

          <VCardText>
            <VForm ref="refVForm" @submit.prevent="onSubmit">
              <VRow>
                <!-- Username -->
                <VCol cols="12" sm="4" md="4">
                  <VTextField
                    v-model="firstname"
                    :rules="[requiredValidator]"
                    label="First Name"
                  />
                </VCol>

                <VCol cols="12" sm="4" md="4">
                  <VTextField v-model="middlename" label="Middle Name" />
                </VCol>

                <VCol cols="12" sm="4" md="4">
                  <VTextField
                    v-model="lastname"
                    :rules="[requiredValidator]"
                    label="Last Name"
                  />
                </VCol>
              </VRow>

              <!-- <VRow>
                <VCol cols="6">
                  <v-text-field
                    :rules="[requiredValidator]"
                    v-model="contact"
                    label="Contact Number"
                    type="number"
                  />
                </VCol>
                <VCol cols="6">
                  <VSelect
                    :rules="[requiredValidator]"
                    v-model="school_type_id"
                    label="School Type"
                    :items="schoolType"
                    item-title="title"
                    item-value="value"
                  />
                </VCol>
              </VRow> -->

              <VRow>
                <!-- <VCol cols="6">
                  <v-text-field
                    :rules="[requiredValidator]"
                    v-model="course"
                    label="Course Name"
                  />
                </VCol>
                <VCol cols="6">
                  <v-text-field
                    :rules="[requiredValidator]"
                    v-model="school"
                    label="School Name"
                  />
                </VCol> -->

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
                <VCol cols="12">
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

                  <div class="d-flex align-center mt-2 mb-4">
                    <VCheckbox
                      id="privacy-policy"
                      v-model="privacyPolicies"
                      inline
                    />
                    <VLabel for="privacy-policy" class="pb-1" style="opacity: 1">
                      <span class="me-1">I agree to</span>
                      <a href="javascript:void(0)" class="text-primary"
                        >privacy policy & terms</a
                      >
                    </VLabel>
                  </div>

                  <VBtn block type="submit"> Sign up </VBtn>
                </VCol>

                <!-- create account -->
                <VCol cols="12" class="text-center text-base">
                  <span>Already have an account?</span>
                  <RouterLink class="text-primary ms-2" :to="{ name: 'Login' }">
                    Sign in instead
                  </RouterLink>
                </VCol>
              </VRow>
            </VForm>
          </VCardText>
        </VCard>
      </div>
    </VImg>
    </VCol>
    <VSnackbar v-model="validated" :timeout="4000"
      variant="flat"
      color="error">
      <VIcon>mdi-alert-circle-outline</VIcon>&nbsp; Erorr! Please Check Your Field.
    </VSnackbar>
    <VSnackbar v-model="successfully" :timeout="6000"
      variant="flat" color="primary">
      <VIcon>tabler:discount-check</VIcon>&nbsp; Success! Register Please check your email for verification before logging in.
    </VSnackbar>
    <VDialog v-model="loading" max-width="300">
      <loadingScreen />
    </VDialog>

    <v-dialog v-model="dialogNeedVerify" max-width="540" persistent>
      <!-- <DialogCloseBtn @click="dialogValidation = false" /> -->
      <VCard height="345" flat>
        <VCardText>
          <VRow>
            <VCol class="text-center mt-10">
              <div style="text-align: center;">
                <VIcon color="success" size="120" icon="system-uicons:check-circle-outside"/><br/><br/>
                <h6 class="text-lg font-weight-large">
                  Registration Successful! Please check your email for verification before logging in.
                </h6>
              </div>

              <div class="text-center mt-5">
                <VBtn @click="dialogNeedVerify = false" color="error" variant="flat">OK</VBtn>
              </div>
            </VCol>
          </VRow>
        </VCardText>
     </VCard>
  </v-dialog>
  </VRow>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth.scss";

.content {
  position: relative;
  /* background-image: url("assets/img/logo.png"); */
  background-position: center;
  background-size: cover;
  block-size: 100vh; /* Set the height to cover the full viewport height */
  inline-size: 100%;
}


.loaders {
    position: relative;
    width: 164px;
    height: 164px;
  }
  .loaders::before , .loaders::after {
    content: '';
    position: absolute;
    width: 40px;
    height: 40px;
    background-color: #27ae60;;
    left: 50%;
    top: 50%;
    animation: rotate 1s ease-in infinite;
}
.loaders::after {
  width: 20px;
  height: 20px;
  background-color: #000;
  animation: rotate 1s ease-in infinite, moveY 1s ease-in infinite ;
}

@keyframes moveY {
  0% , 100% {top: 10%}
  45% , 55% {top: 59%}
  60% {top: 40%}
}
@keyframes rotate {
  0% { transform: translate(-50%, -100%) rotate(0deg) scale(1 , 1)}
  25%{ transform: translate(-50%, 0%) rotate(180deg) scale(1 , 1)}
  45% , 55%{ transform: translate(-50%, 100%) rotate(180deg) scale(3 , 0.5)}
  60%{ transform: translate(-50%, 100%) rotate(180deg) scale(1, 1)}
  75%{ transform: translate(-50%, 0%) rotate(270deg) scale(1 , 1)}
  100%{ transform: translate(-50%, -100%) rotate(360deg) scale(1 , 1)}
}
    

</style>

<route lang="yaml">
meta:
  layout: blank
  action: read
  subject: Auth
  redirectIfLoggedIn: true
</route>
