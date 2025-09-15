<script setup lang="ts">
import { ref } from 'vue';
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant';
import { themeConfig } from '@themeConfig';
import logos from '@images/DITADS Logo_PNG.png'
import axios from 'axios';
import { emailValidator, requiredValidator } from "@validators";
import { useRoute } from "vue-router";
import { environment } from "@/environments/environment";
import { useRouter } from "vue-router";
import loadingScreen from "../pages/loading/Validationloading.vue";

const router = useRouter();
const loading = ref(false);
const dialogValidation = ref(false)
const dialogSuccess = ref();
const email = ref();

const sendResetLink = () => {
  loading.value = true
  axios.post(
    environment.API_URL + "auth/forgotPassword",
    { email: email.value },
    {
      headers: {
        'Content-Type': 'application/json',
      },
    }
  )
  .then((response) => {
    loading.value = false
    dialogSuccess.value = true
    setTimeout(() => {
      router.push({ name: "login" });
    }, 5000);
  }).catch((error) => {
      dialogValidation.value = true;
      loading.value = false
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
        <!-- <VNodeRenderer
            :nodes="themeConfig.app.logo"
            class="mb-6"
          /> -->

        <div
          class="d-flex align-center justify-center"
          style="width: 150px; height: auto; margin-left: 28%"
        >
          <VImg :src="logos" />
        </div>

        <h5 class="text-h5 font-weight-semibold mb-1">Forgot Password? 🔒</h5>
        <p class="mb-0">
          Enter your email and we'll send you instructions to reset your
          password
        </p>
      </VCardText>

      <VCardText>
        <VForm @submit.prevent="sendResetLink">
          <VRow>
            <!-- email -->
            <VCol cols="12">
              <VTextField v-model="email" label="Email" type="email" :rules="[emailValidator]"/>
            </VCol>

            <!-- Reset link -->
            <VCol cols="12">
              <VBtn block type="submit"> Send Reset Link </VBtn>
            </VCol>

            <!-- back to login -->
            <VCol cols="12">
              <RouterLink
                class="d-flex align-center justify-center"
                :to="{ name: 'login' }"
              >
                <VIcon icon="tabler-chevron-left" class="flip-in-rtl" />
                <span>Back to login</span>
              </RouterLink>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>

    <VDialog v-model="loading" max-width="300">
      <loadingScreen />
    </VDialog>

    <!-- <VSnackbar
      v-model="isSnackbarVisible"
      vertical
      location="center"
      variant="flat"
      color="warning"
      size="large"
      transition="fade-transition"
    >
      Email not found. Please check your registration.
      <template #actions>
        <VBtn
          color="error"
          @click="isSnackbarVisible = false"
        >
          Ok
        </VBtn>
      </template>
    </VSnackbar> -->


    <v-dialog v-model="dialogValidation" max-width="520">
      <!-- <DialogCloseBtn @click="dialogValidation = false" /> -->
      <VCard height="330" flat>
        <VCardText>
          <VRow>
            <VCol class="text-center mt-10">
              <div style="text-align: center;">
                <VIcon color="red" size="120" icon="ph:x-circle-thin"/><br/><br/>
                <h6 class="text-lg font-weight-large">Email not found. Please check your registration.</h6>
              </div>

              <div class="text-center mt-5">
                <VBtn @click="dialogValidation = false" color="blue" variant="flat">Ok</VBtn>
              </div>
            </VCol>
          </VRow>
        </VCardText>
    </VCard>
    </v-dialog>

    <v-dialog v-model="dialogSuccess" max-width="540" persistent>
      <!-- <DialogCloseBtn @click="dialogValidation = false" /> -->
      <VCard height="345" flat>
        <VCardText>
          <VRow>
            <VCol class="text-center mt-10">
              <div style="text-align: center;">
                <VIcon color="success" size="120" icon="system-uicons:check-circle-outside"/><br/><br/>
                <h6 class="text-lg font-weight-large">
                  Password reset successful! Please check your email to proceed.
                </h6>
              </div>
              <div class="text-center mt-5">
                <VBtn @click="dialogSuccess = false" color="error" variant="flat">OK</VBtn>
              </div>
            </VCol>
          </VRow>
        </VCardText>
     </VCard>
  </v-dialog>
  </section>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth.scss";
</style>

<route lang="yaml">
meta:
  layout: blank
  action: read
  subject: Auth
  redirectIfLoggedIn: false
</route>
