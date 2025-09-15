<script lang="ts" setup>
import { ref } from "vue";
import logos from "@images/DITADS Logo_PNG.png";
import axios from "axios";
import { emailValidator, requiredValidator } from "@validators";
import { useRoute } from "vue-router";
import { environment } from "@/environments/environment";
import { useRouter } from "vue-router";
import loadingScreen from "../pages/loading/Validationloading.vue";

const router = useRouter();
const loading = ref(false);
const password = ref();
const password_confirmation = ref();
const isPasswordVisible = ref(false);
const isPasswordVisible1 = ref(false);
const dialogValidation = ref(false);
const dialogVerify = ref(false);
const route = useRoute();

const verifyEmail = () => {
  const token = route.query.token as string;
  loading.value = true
  axios
    .post(environment.API_URL + "auth/verify-email/" + token, {
      token,
    })
    .then((response) => {
      loading.value = false
      dialogVerify.value = true
      // setTimeout(() => {
      //   router.push({ name: "login" });
      // }, 2000);
    }).catch((error) => {
      console.log('error')
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
    <VCardText class="text-center">
      <div
        class="d-flex align-center justify-center"
        style="width: 150px; height: auto; margin-left: 17%"
      >
        <!-- Your logo image -->
        <VImg :src="logos" />
      </div>

      <h5 class="text-h5 font-weight-semibold mb-1">Email Verification 🔒</h5>
    </VCardText>

    <VCardText>
      <!-- Email verification form -->
      <VForm @submit.prevent="verifyEmail">
        <VRow>
          <!-- Verify Email button -->
          <VCol cols="12">
            <VBtn block type="submit">Verify Email</VBtn>
          </VCol>
        </VRow>
      </VForm>
    </VCardText>
  </VCard>

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
                <h6 class="text-lg font-weight-large">Invalid or expired token.</h6>
              </div>

              <div class="text-center mt-5">
                <VBtn @click="dialogValidation = false" color="error" variant="flat">Ok</VBtn>
              </div>
            </VCol>
          </VRow>
        </VCardText>
     </VCard>
    </v-dialog>

    <v-dialog v-model="dialogVerify" max-width="540" persistent>
      <!-- <DialogCloseBtn @click="dialogValidation = false" /> -->
      <VCard height="335" flat>
        <VCardText>
          <VRow>
            <VCol class="text-center mt-10">
              <div style="text-align: center;">
                <VIcon color="success" size="120" icon="system-uicons:check-circle-outside"/><br/><br/>
                <h6 class="text-lg font-weight-large">
                  Your email has been successfully verified! Please Click OK.
                </h6>
              </div>

              <VCol cols="12" class="text-center text-base mt-2">
                <RouterLink class="text-primary ms-2" :to="{ name: 'Login' }">
                  OK
                </RouterLink>
              </VCol>
              <!-- <div class="text-center mt-3">
                <VBtn @click="dialogVerify = false" color="error" variant="flat">OK</VBtn>
              </div> -->
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
