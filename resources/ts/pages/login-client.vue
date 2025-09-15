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

const router = useRouter();
const ability = useAppAbility();
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
const disabled_dialog = ref(false);
const email = ref();
const password = ref();
const rememberMe = ref(false);
const validated = ref(false);
const loading = ref(false);
const refVForm = ref<VForm>();

watch(loading, (value) => {
  if (!value) return;

  setTimeout(() => {
    loading.value = false;
  }, 3000);
});

const loader = () => {
  loading.value = true;
};

//function for logging in
const login = () => {
  axios
    .post(environment.API_URL + "auth/login", {
      email: email.value,
      password: password.value,
    })
    .then((response) => {
      if(response.data.success){
        localStorage.setItem("userAbilities",JSON.stringify(response.data.user.ability));
        localStorage.setItem("userData", JSON.stringify(response.data.user));
        localStorage.setItem("accessToken",JSON.stringify(response.data.access_token));
        localStorage.setItem("userRole",JSON.stringify(response.data.user.role));
        localStorage.setItem("clientData",JSON.stringify(response.data.user.clientData));
        localStorage.setItem("editorData",JSON.stringify(response.data.user.editorData));

        ability.update(response.data.user.ability);
        // ability.update(response.data.level_id)
        console.log(response.data.user.role);

        router.push({ name: "index" });
      } else {
        disabled_dialog.value = true;
      }
    })
    .catch((error) => {
      if (error.response) {
        // The request was made and the server responded with a status code
        // that falls out of the range of 2xx
        const responseData = error.response.data;
        if (responseData.error === 'User not found') {
          // Handle "Invalid Credentials" error
          validated.value = true;
          console.log('Invalid Credentials error:', responseData.error);
        } else if (responseData.error === 'Account disabled') {
          disabled_dialog.value = true;
          // Handle "Account disabled" error
          // console.log('Account disabled error:', responseData.error);
        } else {
          // Handle other errors
          validated.value = true;
          console.error('Error:', responseData.error);
        }
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
</script>

<template>
  <section
    fluid
    class="d-flex align-center justify-center mb-3 float-center content"
  >
    <VCard flat :max-width="500" class="mt-12 mt-sm-0 pa-4">
      <VCardText>
        <!-- <VNodeRenderer :nodes="themeConfig.app.logo" class="mb-6" /> -->

        <h5 class="text-h5 font-weight-semibold mb-1">Welcome! Client👋🏻</h5>
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
                :rules="[requiredValidator]"
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
                  :to="{ name: 'forgot-password' }"
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
            <!-- create account -->
            <VCol cols="12" class="text-center">
              <span>Register</span>
                <RouterLink
                  class="text-primary ms-2"
                  :to="{ name: 'register' }"
                >
                  Create an account
                </RouterLink>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </section>
  <VSnackbar v-model="validated" color="error">
    Invalid Credentials!!
  </VSnackbar>
  <VDialog v-model="loading" max-width="300">
    <loadingScreen />
  </VDialog>

  <v-dialog v-model="disabled_dialog" max-width="500" persistent>
    <!-- <DialogCloseBtn @click="dialog = false" /> -->
    <VCard height="288">
      <VCol>
        <VCardText>
          <div style="text-align: center;">
            <VIcon color="error" size="80" icon="tabler-info-octagon"/>
            <h1>Account is disabled. Cannot log in.</h1>
          </div>
        </VCardText>
        <VCardText>
          <div class="float-right">
            <!-- <VBtn  @click="dialog = false" color="primary">Yes!</VBtn>&nbsp; -->
            <VBtn @click="disabled_dialog = false" variant="tonal" color="error">Ok!</VBtn>
          </div>
        </VCardText>
      </VCol>
    </VCard>
  </v-dialog>

</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth.scss";

.content {
  position: relative;
  background-image: url("assets/img/logo.png");
  background-position: center;
  background-size: cover;
  block-size: 100vh; /* Set the height to cover the full viewport height */
  inline-size: 100%;
}
</style>

<route lang="yaml">
meta:
  layout: blank
  action: read
  subject: Auth
  redirectIfLoggedIn: true
</route>
