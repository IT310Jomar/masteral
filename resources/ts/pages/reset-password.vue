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
const dialogValidation = ref(false)
const dialogSuccess = ref();
const route = useRoute();

const resetPassword = () => {
  const token = route.query.token as string;
  loading.value = true
  axios
    .post(environment.API_URL + "auth/reset/password", {
      token,
      password: password.value,
      password_confirmation: password_confirmation.value,
    })
    .then((response) => {
      loading.value = false
      dialogSuccess.value = true
      setTimeout(() => {
        router.push({ name: "login" });
      }, 2000);
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
      <VCardText>
        <div
          class="d-flex align-center justify-center"
          style="width: 150px; height: auto; margin-left: 28%"
        >
          <VImg :src="logos" />
        </div>

        <h5 class="text-h5 font-weight-semibold mb-1">Reset Password? 🔒</h5>
      </VCardText>

      <VCardText>
        <VForm @submit.prevent="resetPassword">
          <VRow>
            <!-- email -->
            <VCol cols="12">
              <VTextField
                v-model="password"
                placeholder="New Password"
                :rules="[requiredValidator,
                  () => (password.length < 8 && 'Password must be of at least 8 alphanumeric character')]"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="
                    isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'
                  "
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
              />
            </VCol>

            <!-- Reset link -->
            <VCol cols="12">
              <VTextField
                v-model="password_confirmation"
                placeholder="Confirm New Password"
                :rules="[requiredValidator,
                  () => (password_confirmation.length < 8 && 'Password must be of at least 8 alphanumeric character')]"
                  :type="isPasswordVisible1 ? 'text' : 'password'"
                  :append-inner-icon="
                    isPasswordVisible1 ? 'tabler-eye-off' : 'tabler-eye'
                  "
                  @click:append-inner="isPasswordVisible1 = !isPasswordVisible1"
              />
            </VCol>

            <!-- back to login -->
            <VCol cols="12">
              <VBtn block type="submit"> Reset Password </VBtn>
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

    <v-dialog v-model="dialogSuccess" max-width="540" persistent>
      <!-- <DialogCloseBtn @click="dialogValidation = false" /> -->
      <VCard height="345" flat>
        <VCardText>
          <VRow>
            <VCol class="text-center mt-10">
              <div style="text-align: center;">
                <VIcon color="success" size="120" icon="system-uicons:check-circle-outside"/><br/><br/>
                <h6 class="text-lg font-weight-large">
                  Password reset successful! Please proceed to log in.
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
