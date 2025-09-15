<script setup lang="ts">
import avatar1 from "@images/avatars/avatar-1.png";
import { environment } from "@/environments/environment";
import axios from "axios";
import { onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import editprofile from "@/pages/ViewProfile/dialog/editDialog.vue";

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

const openFileDialog = () => {
  fileInput.value.click();
};

const editDialog = () => {
  clientData.value = clientprofile.value;
  editorData.value = editorprofile.value;
  edit_editor.value = true;
};

const getprofile =() => {
  axios.get(environment.API_URL + 'auth/admin/get-profile/' + route.params.id,{
    params: {
      token:token
    }
  })
  .then ((response)=> {
    clientprofile.value = response.data.clientprofile[0]
    editorprofile.value = response.data.editorprofile[0]
    // console.log(clientprofile.value)
    // console.log(editorprofile.value)
  })
}

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
    })
    .catch((error) => {
      console.log(error);
    });
};

onMounted(() => {
  getprofile();
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
      <VCardText v-if="clientprofile">
        <v-row>
          <v-col cols="4" md="4" sm="12">
            <VCard class="d-flex flex-column mb-4 mt-3">
              <div>
                <div>
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
                              margin-top: 10%;
                              margin-bottom: 3%;
                            "
                          >
                            <VImg
                              :src="'/storage/profile_image/' + clientprofile.image"
                              alt="Uploaded Image"
                              class="circle-image"></VImg>
                        </VAvatar>
                      </VCol>

                      <VRow>
                        <VCol style="text-align: center" class="mt-5">
                          <VListItem link>
                            <v-btn
                              @click="editDialog()"
                              rounded="pill"
                              size="small"
                              class="btn btn-rounded btn-sm"
                            >
                              Edit Profile
                            </v-btn>
                          </VListItem>
                        </VCol>
                      </VRow>
                    </div>
                    <div style="font-weight: bold"></div>
                    <div style="font-weight: bold"></div>
                    <div class="mb-4"></div>
                    <h3 class="text-center mt-2">{{ clientprofile.firstname }} {{ clientprofile?.middlename }} {{ clientprofile.lastname }}</h3>
                      <h1 class="text-center mt-1">
                        <VChip color="blue" size="small" variant="outlined">{{
                          clientprofile.users.roles.name
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
              style="margin-left: 1%;">
              <VDivider />
              <v-card-text>
                <v-table>
                  <tbody>
                    <tr>
                      <td width="300px"><strong>Full Name</strong></td>
                      <td>
                        <VChip color="blue" label
                          ><v-icon start icon="mdi-account-circle-outline"></v-icon><b
                            >{{ clientprofile.firstname }}
                            {{ clientprofile?.middlename }}
                            {{ clientprofile.lastname }}</b
                          ></VChip
                        >
                      </td>
                    </tr>
                    <tr>
                      <td><strong>Contact</strong></td>
                      <td>
                        <VChip color="info" label v-if="clientprofile?.contact != null"
                          ><v-icon start icon="material-symbols:phone-android-outline"></v-icon><b>{{ clientprofile.contact }}</b></VChip
                        >
                      </td>
                    </tr>
                    <tr>
                      <td><strong>Email</strong></td>
                      <td>
                        <VChip color="info" label
                          ><v-icon start icon="ic:twotone-mail-outline"></v-icon><b>{{ clientprofile.users.email }}</b></VChip
                        >
                      </td>
                    </tr>
                    <tr>
                      <td><strong>Status</strong></td>
                      <td>
                        <VChip color="success" 
                          ><v-icon start icon="ph:activity-light"></v-icon><b>{{ clientprofile.status }}</b></VChip
                        >
                      </td>
                    </tr>
                  </tbody>
                </v-table>
                <VDivider />
              </v-card-text>
            </v-card>

            <v-card
              class="headline"
              title="Education Attainment"
              tabindex="-1"
              style="margin-left: 1%;">
              <VDivider />
              <v-card-text>
                <v-table>
                  <tbody>
                    <tr>
                      <td width="300px"><strong>School</strong></td>
                      <td>
                        <VChip color="yellow" label v-if="clientprofile?.school != null"
                          ><v-icon start icon="teenyicons:school-outline"></v-icon><b
                            >{{ clientprofile.school }}</b
                          ></VChip
                        >
                      </td>
                    </tr>
                   
                    <tr>
                      <td><strong>School Type</strong></td>
                      <td>
                        <VChip color="error" label v-if="clientprofile.school_type?.name != null"
                          ><v-icon start icon="iconoir:learning"></v-icon><b>{{ clientprofile.school_type.name }}</b></VChip
                        >
                      </td>
                    </tr>
                    <tr>
                      <td><strong>Course</strong></td>
                      <td>
                        <VChip color="success" label v-if="clientprofile?.course != null"
                          ><v-icon start icon="guidance:golf-course"></v-icon><b>{{ clientprofile.course }}</b></VChip
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

      <VCardText v-if="editorprofile">
        <v-row>
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
                            :src="'/storage/profile_image/' + editorprofile.image"
                            alt="Uploaded Image"
                            class="circle-image"
                          ></VImg>
                        </VAvatar>
                      </VCol>

                      <VRow>
                        <VCol style="text-align: center" class="mt-5">
                          <VListItem link>
                            <input ref="fileInput" type="file" hidden />
                            <v-btn
                              @click="editDialog()"
                              rounded="pill"
                              size="small"
                              class="btn btn-rounded btn-sm"
                            >
                              Edit Profile
                            </v-btn>
                          </VListItem>
                        </VCol>
                      </VRow>
                    </div>
                    <div style="font-weight: bold"></div>
                    <div style="font-weight: bold"></div>
                    <div class="mb-4"></div>
                    
                      <h3 class="text-center mt-2">{{ editorprofile.firstname }} {{ editorprofile?.middlename }} {{ editorprofile.lastname }}</h3>
                      <h1 class="text-center mt-1">
                        <VChip color="blue" variant="outlined" size="small">{{
                          editorprofile.users.roles.name
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
                            >{{ editorprofile.firstname }}
                            {{ editorprofile?.middlename }}
                            {{ editorprofile.lastname }}</b
                          ></VChip
                        >
                      </td>
                    </tr>
                    <tr>
                      <td><strong>Contact</strong></td>
                      <td>
                        <VChip color="info" label
                          ><v-icon start icon="material-symbols:phone-android-outline"></v-icon><b>{{ editorprofile.contact }}</b></VChip
                        >
                      </td>
                    </tr>
                    <tr>
                      <td><strong>Email</strong></td>
                      <td>
                        <VChip color="info" label
                          ><v-icon start icon="ic:twotone-mail-outline"></v-icon><b>{{ editorprofile.users.email }}</b></VChip
                        >
                      </td>
                    </tr>
                    <tr>
                      <td><strong>Status</strong></td>
                      <td>
                        <VChip color="success" 
                          ><v-icon start icon="ph:activity-light"></v-icon><b>{{ editorprofile.status }}</b></VChip
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

      <VCardText v-if="admin && admin != null">
        <v-row v-if="admin.role === 'Admin' || admin.role === 'God Mode'">
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
    </VCard>

    <VDialog v-model="edit_editor" scrollable width="60vh">
      <editprofile @close="edit_editor = false" @handledata="getprofile" :clientData="clientData" :editorData="editorData"/>
    </VDialog>
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
</style>
