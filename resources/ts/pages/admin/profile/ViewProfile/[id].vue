<script setup lang="ts">
import avatar1 from "@images/avatars/avatar-1.png";
import { environment } from "@/environments/environment";
import axios from "axios";
import { onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import pages401 from '@images/pages/page_error_401.png'
import miscMaskDark from '@images/pages/misc-mask-dark.png'
import miscMaskLight from '@images/pages/misc-mask-light.png'
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'

const authThemeMask = useGenerateImageVariant(miscMaskLight, miscMaskDark)
const token = JSON.parse(localStorage.getItem("accessToken") || "null");

const vieweditor = ref();
const route = useRoute();
const image = ref();
const first_name = ref();
const last_name = ref();
const middle_name = ref();
const email = ref();
const contact = ref();
const address = ref();
const age = ref();
const gender = ref();

const clientprofile = ref();
const editorprofile =ref();
const edit_editor = ref(false);
const previosimage = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
const clientData = ref();
const editorData = ref();
const admin = ref();
const isLoading = ref(true);

// const getprofile =() => {
//   axios.get(environment.API_URL + 'auth/admin/get-profile/' + route.params.id,{
//     params: {
//       token:token
//     }
//   })
//   .then ((response)=> {
//     clientprofile.value = response.data.clientprofile[0]
//     editorprofile.value = response.data.editorprofile[0]
//     // console.log(clientprofile.value)
//     // console.log(editorprofile.value)
//   })
// }

const loginUser = () => {
  axios
    .get(environment.API_URL + "auth/user-profile", {
      params: {
        token: token,
      },
    })
    .then((response) => {
      if (response.data.success) {
          admin.value = response.data.dataUser;
      }
    }).finally(() => {
      isLoading.value = false;
    })
};

onMounted(() => {
  // getprofile();
  loginUser();
});
</script>

<template>
  <section>
    <!-- <DialogCloseBtn @click.prevent="closeDialog" /> -->
    <VCard class="mb-4">
      <VToolbar color="yellow">
        <VToolbarTitle class="text-h5" style="color: white;">Your Profile</VToolbarTitle>
        <VSpacer />
        <VBtn
          color="primary" variant="flat"
          to="/"
          >
          <VIcon
            start
            size="16"
            icon="material-symbols:arrow-back-rounded"
          />
          Back</VBtn
        >
      </VToolbar>
      <!-- <VDivider class="mt-4" /> -->

      <VCardText v-if="isLoading">
        <!-- Loading state -->
        <div class="loading-container">
          <div class="loading-spinner"></div>
          <p>Please Wait...</p>
        </div>
      </VCardText>

      <VCardText v-else-if="admin && admin != null">
        <v-row v-if="admin.role === 'Admin' || admin.role === 'God Mode' && admin.id">
          <v-col cols="4" md="4" sm="12">
            <VCard class="d-flex flex-column mb-4 mt-3">
              <div>
                <div>
                  <div class="profile-card text-center mb-2">
                    <div class="profile-card-photo">
                      <VCol>
                        <VAvatar
                          class="cursor-pointer"
                          color="primary"
                          variant="tonal"
                          style="
                            width: 200px;
                            height: 200px;
                            margin-top: 10%;
                            margin-bottom: 3%;
                          "
                        >
                          <VImg
                            :src="avatar1"
                            alt="Uploaded Image"
                            class="circle-image"
                          ></VImg>
                        </VAvatar>
                      </VCol>
                    </div>
                    <div style="font-weight: bold"></div>
                    <div style="font-weight: bold"></div>
                    <div class="mb-4"></div>
                    
                      <h3 class="text-center mt-2">{{ admin.name }}</h3>
                      <h1 class="text-center mt-1">
                        <VChip color="blue" variant="outlined" size="small">{{
                          admin.role
                        }}</VChip>
                      </h1>
                  </div>
                </div>
              </div>
            </VCard>
            <!-- <VDivider /> -->
            <VRow>
              <VCol style="text-align: center" class="mt-5"> </VCol>
            </VRow>
          </v-col>
          <VCol>
          <v-card-text>
            <v-card
              class="headline mb-8"
              title="Personal Information"
              tabindex="-1"
              style="
                margin-left: 1%;
              "
            >
              <VDivider />
              <v-card-text>
                <v-table>
                  <tbody>
                    <tr>
                      <td width="300px"><strong>Full Name</strong></td>
                      <td>
                        <VChip color="blue" label
                          ><v-icon start icon="mdi-account-circle-outline"></v-icon><b
                            >Administrator</b
                          ></VChip
                        >
                      </td>
                    </tr>
                    <tr>
                      <td><strong>Contact</strong></td>
                      <td>
                        <VChip color="info" label
                          ><v-icon start icon="material-symbols:phone-android-outline"></v-icon><b>+639098765432</b></VChip
                        >
                      </td>
                    </tr>
                    <tr>
                      <td><strong>Email</strong></td>
                      <td>
                        <VChip color="info" label
                          ><v-icon start icon="ic:twotone-mail-outline"></v-icon><b>{{ admin.email }}</b></VChip
                        >
                      </td>
                    </tr>
                    <tr>
                      <td><strong>Status</strong></td>
                      <td>
                        <VChip color="success" 
                          ><v-icon start icon="ph:activity-light"></v-icon><b>Active</b></VChip
                        >
                      </td>
                    </tr>
                  </tbody>
                </v-table>
                <VDivider />
              </v-card-text>
            </v-card>
          </v-card-text>
        </VCol>
        </v-row>
      </VCardText>
      <VCardText v-else>
        <div class="misc-wrapper">
          <ErrorHeader
            error-title="You are not authorized! 🔐"
            error-description="You do not have permission to view this page using the credentials that you have provided while login.
      Please contact your site administrator."
          />
          <div class="text-center" style="margin-bottom: -2%;">
          <VBtn
            to="/"
            class="mb-12"
          >
            Back to Home
          </VBtn>
        </div>
          <!-- 👉 Image -->
          <div class="misc-avatar w-100 text-center">
            <VImg
              :src="pages401"
              alt="Coming Soon"
              :max-width="590"
              class="mx-auto"
            />
          </div>

          <VImg
            :src="authThemeMask"
            class="misc-footer-img d-none d-md-block"
          />
        </div>
      </VCardText>
    </VCard>
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
  height: 230px;
  border-radius: 50%;
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
  border-radius: 50%;
}

.loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 20px;
  }

  .loading-spinner {
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin-bottom: 10px;
    border-top: 4px solid #3498db;
    border-left: 4px solid #3498db;
    border-bottom: 4px solid transparent;
  }

  @keyframes spin {
    0% {
      transform: rotate(0deg);
    }

    100% {
      transform: rotate(360deg);
    }
  }
</style>
