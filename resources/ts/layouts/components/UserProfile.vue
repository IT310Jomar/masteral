<script setup lang="ts">
import avatar1 from "@images/avatars/avatar-1.png";
import axios from "axios";
import { onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import { initialAbility } from "@/plugins/casl/ability";
import { useAppAbility } from "@/plugins/casl/useAppAbility";
import { environment } from "@/environments/environment";
import changepass from "@/layouts/components/change_password.vue"

const ability = useAppAbility();
const token = JSON.parse(localStorage.getItem("accessToken") || "null");
const client_id = JSON.parse(localStorage.getItem("clientData") || "null");
const editor_id = JSON.parse(localStorage.getItem("editorData") || "null");
const userID = JSON.parse(localStorage.getItem('userData') || 'null');

const router = useRouter();
const userData = ref();

const godmode = ref();
const adminData = ref();
const editor =ref();
const client = ref();

const userpass =ref();

const changepassword =ref(false);

const open_changepass =()=> {
  changepassword.value=true
  userpass.value = userID
}

const dialogValidation = ref(false);

const OpenValidation = () => {
  dialogValidation.value = true;
};


//logout function using remove item in localstorage
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

//function for getting user profile
const loginUser = () => {
  axios
    .get(environment.API_URL + "auth/user-profile", {
      params: {
        token: token,
      },
    })
    .then((response) => {
      if (response.data.success) {
          userData.value = response.data.dataUser;
          // console.log(userData.value);

          if (userData) {
            const clientData = userData.value.clientData;
            const editorData = userData.value.editorData;

            // Ensure clientData and editorData are available and not empty
            if (clientData && clientData.length > 0) {
              client.value = clientData[0];
            }

            if (editorData && editorData.length > 0) {
              editor.value = editorData[0];
            }

            // Assuming you want to assign the entire dataUser object to admin.value
            adminData.value = userData.value;
            godmode.value = userData.value;

            // console.log(godmode.value);
          }
      }
    })
    .catch((error) => {
      console.log('error');
      OpenValidation();
      setTimeout(() => {
        location.reload();
        logout();
      }, 5000);
    });
};

onMounted(() => {
  loginUser();
});
</script>

<template>
  <VBadge
    dot
    location="bottom right"
    offset-x="3"
    offset-y="3"
    bordered
    color="success"
  >
    <VAvatar class="cursor-pointer ml-2" color="primary" variant="tonal" >
      <VAvatar v-if="godmode !== null || adminData !== null || client !== null || editor !== null">
        <VImg :src="avatar1" v-if="godmode && godmode.role === 'God Mode' || adminData && adminData.role === 'Admin'"/>
        <VImg :src="'/storage/profile_image/' + client.image" v-else-if="client && adminData.role === 'Client'"/>
        <VImg :src="'/storage/profile_image/' + editor.image" v-else-if="editor && adminData.role === 'Editor'"/>
      </VAvatar>


      <!-- SECTION Menu -->
      <VMenu activator="parent" width="270" location="bottom end" offset="14px">
        <VList>
          <!-- 👉 User Avatar & Name -->
          <VListItem>
            <template #prepend>
              <VListItemAction start>
                <VBadge
                  dot
                  location="bottom right"
                  offset-x="3"
                  offset-y="3"
                  color="success"
                >
                  <VAvatar color="primary" variant="tonal" v-if="godmode != null || adminData != null || client != null || editor != null">
                    <VImg :src="avatar1" v-if="adminData.role === 'Admin' || godmode.role === 'God Mode'"/>
                    <VImg :src="'/storage/profile_image/' + client.image" v-if="client != null"/>
                    <VImg :src="'/storage/profile_image/' + editor.image" v-if="editor != null"/>
                  </VAvatar>
                </VBadge>
              </VListItemAction>
            </template>

            <VCol v-if="godmode != null || adminData != null || client != null || editor != null">
              <VListItemTitle style="margin-left: -8%; font-size: 15px" class="font-weight-semibold" v-if="client != null">
                {{ client.firstname }} {{ client.lastname }}
              </VListItemTitle>
              <VListItemTitle style="margin-left: -8%; font-size: 15px" class="font-weight-semibold" v-if="editor != null">
                {{ editor.firstname }} {{ editor.lastname }}
              </VListItemTitle>
              <VListItemTitle style="margin-left: -8%; font-size: 15px" class="font-weight-semibold" v-if="godmode.role === 'God Mode'">
                {{ godmode.name }}
              </VListItemTitle>
              <VListItemTitle style="margin-left: -8%; font-size: 15px" class="font-weight-semibold" v-if="adminData.role === 'Admin'">
                {{ adminData.name }}
              </VListItemTitle>
              <span>
                <VChip style="margin-left: -8%"
                  color="success"
                  v-if="adminData.role === 'God Mode'"
                  ><b>God Mode</b></VChip
                >
                <VChip style="margin-left: -8%"
                  color="success"
                  v-if="adminData.role === 'Admin'"
                  ><b>Admin</b></VChip
                >
                <VChip style="margin-left: -8%"
                  color="success"
                  v-if="adminData.role === 'Editor'"
                  ><b>Editor</b></VChip
                >
                <VChip style="margin-left: -8%"
                  color="success"
                  v-if="adminData.role === 'Client'"
                  ><b>Client</b></VChip
                >
              </span>
            </VCol>
          </VListItem>

          <VDivider class="my-2" />

          <!-- 👉 Profile -->
          <div v-if="godmode != null || adminData != null || client != null || editor != null">
            <VListItem link v-if="adminData.role === 'Client'"
            :to="'/client/profile/ViewProfile/' + client_id[0].user_id">
              <template #prepend>
                <VIcon class="me-2" icon="tabler-user" size="22" />
              </template>

              <VListItemTitle>Profile</VListItemTitle>
            </VListItem>

            <VListItem link v-if="adminData.role === 'Editor'"
            :to="'/editor/profile/ViewProfile/' + editor_id[0].user_id">
              <template #prepend>
                <VIcon class="me-2" icon="tabler-user" size="22" />
              </template>

              <VListItemTitle>Profile</VListItemTitle>
            </VListItem>

            <VListItem link v-if="adminData.role === 'Admin' || adminData.role === 'God Mode'"
            :to="'/admin/profile/ViewProfile/' + userID.id">
              <template #prepend>
                <VIcon class="me-2" icon="tabler-user" size="22" />
              </template>

              <VListItemTitle>Profile</VListItemTitle>
            </VListItem>

          <!-- 👉 Settings -->
          <VListItem :to="{ name: 'admin-settings-tab', params: { tab: 'maintenance' } }" v-if="adminData.role === 'Admin' || adminData.role === 'God Mode'">
            <template #prepend>
              <VIcon
                class="me-2"
                icon="tabler-settings"
                size="22"
              />
            </template>

            <VListItemTitle>Settings</VListItemTitle>
          </VListItem>
        </div>

          <VListItem link v-if="godmode != null || adminData != null || client != null || editor != null">
            <template #prepend>
              <VIcon class="me-2" icon="material-symbols:lock-person-outline" size="22" />
            </template>
            <VListItemTitle @click.prevent="open_changepass">Change Password</VListItemTitle>
          </VListItem>

          <!-- 👉 Settings -->
          <!-- <VListItem link>
            <template #prepend>
              <VIcon class="me-2" icon="tabler-settings" size="22" />
            </template>

            <VListItemTitle>Settings</VListItemTitle>
          </VListItem> -->

          <!-- 👉 Pricing -->
          <!-- <VListItem link>
            <template #prepend>
              <VIcon class="me-2" icon="tabler-currency-dollar" size="22" />
            </template>

            <VListItemTitle>Pricing</VListItemTitle>
          </VListItem> -->

          <!-- 👉 FAQ -->
          <!-- <VListItem link>
            <template #prepend>
              <VIcon class="me-2" icon="tabler-help" size="22" />
            </template>

            <VListItemTitle>FAQ</VListItemTitle>
          </VListItem> -->

          <!-- Divider -->
          <VDivider class="my-2" v-if="godmode != null || adminData != null || client != null || editor != null"/>

          <!-- 👉 Logout -->
          <VListItem to="/login">
            <template #prepend>
              <VIcon class="me-2" icon="tabler-logout" size="22" />
            </template>

            <VListItemTitle @click.prevent="logout">Logout</VListItemTitle>
          </VListItem>
        </VList>
      </VMenu>
      <!-- !SECTION -->
    </VAvatar>
  </VBadge>

  <VDialog v-model="changepassword" scrollable max-width="500">
    <changepass
      :userid="userpass"
      @close="changepassword = false"
    />
  </VDialog>
  
  <v-dialog v-model="dialogValidation" max-width="540" persistent>
      <!-- <DialogCloseBtn @click="dialogValidation = false" /> -->
      <VCard height="330" flat>
        <VCardText>
          <VRow>
            <VCol class="text-center mt-10">
              <div style="text-align: center;">
                <VIcon color="warning" size="110" icon="bi:exclamation-circle"/><br/><br/>
                <h6 class="text-lg font-weight-large" style="color: #f57c00;">Token Expired</h6>
                <p style="color: #757575;">Please wait to continue.</p>
              </div>
              <!-- <div class="text-center mt-5">
                <VBtn type="submit" @click.prevent="logout" color="primary">Logout</VBtn>
              </div> -->
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
  </v-dialog>
</template>
