/* stylelint-disable function-url-quotes */
<script setup lang="ts">
import { useGenerateImageVariant } from "@core/composable/useGenerateImageVariant";
import authV2LoginIllustrationBorderedDark from "@images/pages/auth-v2-login-illustration-bordered-dark.png";
import authV2LoginIllustrationBorderedLight from "@images/pages/auth-v2-login-illustration-bordered-light.png";
import authV2LoginIllustrationDark from "@images/pages/auth-v2-login-illustration-dark.png";
import authV2LoginIllustrationLight from "@images/pages/auth-v2-login-illustration-light.png";
import authV2MaskDark from "@images/pages/misc-mask-dark.png";
import authV2MaskLight from "@images/pages/misc-mask-light.png";
import { emailValidator, requiredValidator } from "@validators";
import axios from "axios";
import { ref } from "vue";
import { useRouter } from "vue-router";
import { VForm } from "vuetify/components";
import loadingScreen from "../pages/loading/loading.vue";
import { useAppAbility } from "@/plugins/casl/useAppAbility";
import { environment } from "@/environments/environment";
import background from "@images/background.png"

const router = useRouter();
const ability = useAppAbility();
const token = JSON.parse(localStorage.getItem("accessToken") || "{}");
// console.log('ability', ability)

const authThemeImg = useGenerateImageVariant(
  authV2LoginIllustrationLight,
  authV2LoginIllustrationDark,
  authV2LoginIllustrationBorderedLight,
  authV2LoginIllustrationBorderedDark,
  true
);
const authThemeMask = useGenerateImageVariant(authV2MaskLight, authV2MaskDark);
const isPasswordVisible = ref(false);
const email = ref();
const password = ref();
const rememberMe = ref(false);
const validated = ref(false);
const successfully = ref(false);
const loading = ref(false);
const refVForm = ref<VForm>();
const dialogValidation = ref(false)

watch(loading, (value) => {
  if (!value) return;

  setTimeout(() => {
    loading.value = false;
  }, 2000);
});

const loader = () => {
  loading.value = true;
};
const maintenanceData = ref();
const isUnderMaintenance = ref(false)
const getMaintenance = () => {
  axios.get(environment.API_URL + 'auth/admin/check-maintenance')
  .then((response) =>{
    if(response.data.success) {
      maintenanceData.value = response.data.maintenance[0].isUnderMaintenance
      // console.log(maintenanceData.value)
      if(maintenanceData.value === 0) {
        isUnderMaintenance.value = false;
        localStorage.removeItem("isUnderMaintenance");
      } else {
        isUnderMaintenance.value = true;
        localStorage.setItem("isUnderMaintenance", JSON.stringify(isUnderMaintenance.value));
      }
    }
  })
}

//function for logging in
const login = () => {
  axios
    .post(environment.API_URL + "auth/login", {
      email: email.value,
      password: password.value,
    })
    .then((response) => {
      if(response.data.success) {
        localStorage.setItem("userAbilities",JSON.stringify(response.data.user.ability));
        localStorage.setItem("userData", JSON.stringify(response.data.user));
        localStorage.setItem("accessToken",JSON.stringify(response.data.access_token));
        localStorage.setItem("userRole",JSON.stringify(response.data.user.role));
        localStorage.setItem("clientData",JSON.stringify(response.data.user.clientData));
        localStorage.setItem("editorData",JSON.stringify(response.data.user.editorData));

        ability.update(response.data.user.ability);
        // ability.update(response.data.level_id)
        // console.log(response.data.user);
        // getMaintenance();
        router.push({ name: "index" });
      } else {
        validated.value = true;
      }
    })
    .catch((error) => {
      console.log('error');

      // Check if the error is a JSON response
      if (error.response && error.response.data && error.response.data.error) {
        const errorMessage = error.response.data.error;

        if (errorMessage === 'Email not verified. Please check your email for verification.') {
          dialogValidation.value = true;
        } else {
          validated.value = true;
        }
      } else {
        // Handle other types of errors or log them
        console.log('');
      }
    });
};

//on submit function for validation
const onSubmit = () => {
  refVForm.value?.validate().then(({ valid: isValid }) => {
    loader();
    if (isValid) {
      login();
    } else {
      validated.value = true;
    }
  });
};

onMounted(() => {
  getMaintenance();
})

</script>

<template>
  <section
    fluid
  >
  <VImg
     
      cover
      class="d-flex align-center justify-center content"
    >
    <VCol class="d-flex align-center justify-center float-center">
      <VCard flat :max-width="500" class="mt-12 mt-sm-0 pa-4" dark>
      <VCardText>
        <!-- <VNodeRenderer :nodes="themeConfig.app.logo" class="mb-6" /> -->

        <h5 class="text-h5 font-weight-semibold mb-1">Welcome! Back👋🏻</h5>
        <p class="mb-0">
          Please sign-in to your account and start the adventure
        </p>
      </VCardText>
      <VCardText>
        <!-- <VForm @submit.prevent="() => {}"> -->
        <VForm ref="refVForm" @submit.prevent="onSubmit">
          <VRow>
            <!-- email -->
            <VCol cols="12">
              <VTextField
                v-model="email"
                label="Email"
                type="email"
                :rules="[requiredValidator, emailValidator]"
              />
            </VCol>

            <!-- password -->
            <VCol cols="12">
              <VTextField
                  v-model="password"
                  label="Password"
                  :rules="[requiredValidator,
                  () => (password.length < 8 && 'Password must be of at least 8 alphanumeric character')]"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="
                    isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'
                  "
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />

              <div
                class="d-flex align-center flex-wrap justify-space-between mt-2 mb-4"
              >
                <VCheckbox v-model="rememberMe" label="Remember me" />
                <RouterLink
                  class="text-primary ms-2 mb-1"
                  :to="{ name: 'ForgotPassword' }"
                >
                  Forgot Password?
                </RouterLink>
              </div>
              <VCol cols="12" class="d-flex align-center">
                <VDivider />

                <VDivider />
              </VCol>

              <VBtn block type="submit"> Login </VBtn>
            </VCol>

            <!-- auth providers
              <VCol cols="12" class="text-center">
                <AuthProvider />
              </VCol> -->
            <!-- create account -->
            <VCol cols="12" class="text-center">
              <span>Not a member?</span>
                <RouterLink
                  class="text-primary ms-2"
                  :to="{ name: 'Register' }"
                >
                  Signup now
                </RouterLink>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
    </VCol>

  </VImg>
  </section>
  <VSnackbar v-model="validated" color="error" timeout="4000" variant="flat">
    Invalid Credentials!!
  </VSnackbar>
  <VSnackbar v-model="successfully" :timeout="4000"
      variant="flat" color="primary">
      <VIcon>tabler:discount-check</VIcon>&nbsp; Success! Login.
    </VSnackbar>
  <VDialog v-model="loading" max-width="300">
    <loadingScreen />
  </VDialog>

  <v-dialog v-model="dialogValidation" max-width="520">
      <!-- <DialogCloseBtn @click="dialogValidation = false" /> -->
      <VCard height="330" flat>
        <VCardText>
          <VRow>
            <VCol class="text-center mt-10">
              <div style="text-align: center;">
                <VIcon color="red" size="120" icon="ph:x-circle-thin"/><br/><br/>
                <h6 class="text-lg font-weight-large">Email not verified. Please check your email for verification.</h6>
              </div>

              <div class="text-center mt-5">
                <VBtn @click="dialogValidation = false" color="error" variant="flat">Ok</VBtn>
              </div>
            </VCol>
          </VRow>
        </VCardText>
     </VCard>
  </v-dialog>
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
</style>
<!-- layout: blank -->
<route lang="yaml">
meta:
  action: read
  subject: Auth
  redirectIfLoggedIn: true
</route>
