<script lang="ts" setup>
import avatar1 from "@images/avatars/avatar-1.png";
import axios from "axios";
import { ref } from 'vue'
import { environment } from "@/environments/environment";
import loadingScreen from "@/pages/loading/Validationloading.vue";

const token = JSON.parse(localStorage.getItem("accessToken") || "{}");
const accountData = {
  avatarImg: avatar1,
  firstName: 'john',
  lastName: 'Doe',
  email: 'johnDoe@example.com',
  org: 'Pixinvent',
  phone: '+1 (917) 543-9876',
  address: '123 Main St, New York, NY 10001',
  state: 'New York',
  zip: '10001',
  country: 'USA',
  language: 'English',
  timezone: '(GMT-11:00) International Date Line West',
  currency: 'USD',
}

const dialogValidation = ref(false);

const toggleDialogSuccess = () => {
  dialogValidation.value = true;
};

const refInputEl = ref<HTMLElement>()
const isUnderMaintenance = ref(false);

const isConfirmDialogOpen = ref(false)
const accountDataLocal = ref(structuredClone(accountData))
const isAccountDeactivated = ref(false)

const validateAccountDeactivation = [(v: string) => !!v || 'Please confirm maintenance mode']

const resetForm = () => {
  accountDataLocal.value = structuredClone(accountData)
}

// changeAvatar function
const changeAvatar = (file: Event) => {
  const fileReader = new FileReader()
  const { files } = file.target as HTMLInputElement

  if (files && files.length) {
    fileReader.readAsDataURL(files[0])
    fileReader.onload = () => {
      if (typeof fileReader.result === 'string')
        accountDataLocal.value.avatarImg = fileReader.result
    }
  }
}

// reset avatar image
const resetAvatar = () => {
  accountDataLocal.value.avatarImg = accountData.avatarImg
}

const timezones = [
  '(GMT-11:00) International Date Line West',
  '(GMT-11:00) Midway Island',
  '(GMT-10:00) Hawaii',
  '(GMT-09:00) Alaska',
  '(GMT-08:00) Pacific Time (US & Canada)',
  '(GMT-08:00) Tijuana',
  '(GMT-07:00) Arizona',
  '(GMT-07:00) Chihuahua',
  '(GMT-07:00) La Paz',
  '(GMT-07:00) Mazatlan',
  '(GMT-07:00) Mountain Time (US & Canada)',
  '(GMT-06:00) Central America',
  '(GMT-06:00) Central Time (US & Canada)',
  '(GMT-06:00) Guadalajara',
  '(GMT-06:00) Mexico City',
  '(GMT-06:00) Monterrey',
  '(GMT-06:00) Saskatchewan',
  '(GMT-05:00) Bogota',
  '(GMT-05:00) Eastern Time (US & Canada)',
  '(GMT-05:00) Indiana (East)',
  '(GMT-05:00) Lima',
  '(GMT-05:00) Quito',
  '(GMT-04:00) Atlantic Time (Canada)',
  '(GMT-04:00) Caracas',
  '(GMT-04:00) La Paz',
  '(GMT-04:00) Santiago',
  '(GMT-03:30) Newfoundland',
  '(GMT-03:00) Brasilia',
  '(GMT-03:00) Buenos Aires',
  '(GMT-03:00) Georgetown',
  '(GMT-03:00) Greenland',
  '(GMT-02:00) Mid-Atlantic',
  '(GMT-01:00) Azores',
  '(GMT-01:00) Cape Verde Is.',
  '(GMT+00:00) Casablanca',
  '(GMT+00:00) Dublin',
  '(GMT+00:00) Edinburgh',
  '(GMT+00:00) Lisbon',
  '(GMT+00:00) London',
]

const currencies = [
  'USD',
  'EUR',
  'GBP',
  'AUD',
  'BRL',
  'CAD',
  'CNY',
  'CZK',
  'DKK',
  'HKD',
  'HUF',
  'INR',
]

const loading = ref(false);
const isSnackbarSuccess = ref(false);
const isSnackbarErorr = ref(false);

const loaderTrue = () => {
  loading.value = true;
};

const loaderFalse = () => {
  loading.value = false;
};

const underMaintenance = () => {
  loaderTrue();
  axios.post(environment.API_URL + 'auth/admin/toggle-maintenance', {
    isUnderMaintenance: isUnderMaintenance.value,
  }).then((response) =>{
    if (!response.data.success){
      loaderFalse();
      getMaintenance();
      isUnderMaintenance.value = response.data.isUnderMaintenance;
      // console.log(isUnderMaintenance.value);
      if (isUnderMaintenance.value === false) {
        localStorage.removeItem("isUnderMaintenance");
      } else {
        localStorage.setItem("isUnderMaintenance", JSON.stringify(isUnderMaintenance.value));
      }
    }
  })
}

const maintenanceData = ref();

const getMaintenance = () => {
  axios.get(environment.API_URL + 'auth/admin/check-maintenance')
  .then((response) =>{
    if(response.data.success) {
      maintenanceData.value = response.data.maintenance[0].isUnderMaintenance
      // console.log(maintenanceData.value)
      // localStorage.setItem("isUnderMaintenance", JSON.stringify(maintenanceData.value));
      if(maintenanceData.value === 0) {
        isUnderMaintenance.value = false;
        isAccountDeactivated.value = false;
      } else {
        isUnderMaintenance.value = true;
        isAccountDeactivated.value = true;
      }
    }
  })
}

const capitalizedLabel = (label: boolean) => {
  const convertLabelText = label.toString()

  return convertLabelText.charAt(0).toUpperCase() + convertLabelText.slice(1)
}

onMounted(() => {
  getMaintenance();
})

</script>

<template>
  <VRow>
    <VCol cols="12">
      <!-- 👉 Delete Account -->
      <VCard title="Maintenance Mode">
        <VCardText>
          <!-- 👉 Checkbox and Button  -->
          <VAlert
            color="warning"
            variant="tonal"
            class="mb-4"
          >
          <VAlertTitle class="mb-1">
            Are you sure you want to enable maintenance mode?
          </VAlertTitle>
          <p class="mb-0">
            Once maintenance mode is activated, users will not be able to log in. Please be certain.
          </p>
          </VAlert>
          <div>
            <VCheckbox
              v-model="isAccountDeactivated"
              :rules="validateAccountDeactivation"
              label="I confirm to enable maintenance mode"
            />
          </div>

          <!-- <VBtn
            :disabled="!isAccountDeactivated"
            color="error"
            class="mt-3"
            @click="toggleDialogSuccess"
          >
            Maintenance Mode
          </VBtn> -->

          <VSwitch
            :disabled="!isAccountDeactivated"
            v-model="isUnderMaintenance"
            :label="capitalizedLabel(isUnderMaintenance)"
            @change="underMaintenance"
          />
        </VCardText>
      </VCard>
    </VCol>
  </VRow>

  <!-- Confirm Dialog -->
  <!-- <ConfirmDialog
    v-model:isDialogVisible="isConfirmDialogOpen"
    confirmation-msg="Are you sure you want to deactivate your account?"
    @confirmed="toggleDialogSuccess"
  /> -->

  <v-dialog v-model="dialogValidation" max-width="540">
    <!-- <DialogCloseBtn @click="dialogValidation = false" /> -->
    <VCard height="330" flat>
      <VCardText>
        <VRow>
          <VCol class="text-center mt-10">
            <div style="text-align: center;">
              <VIcon color="warning" size="110" icon="bi:exclamation-circle"/><br/><br/>
                <h6 class="justify-center text-lg font-weight-large">Are you sure you want to enable maintenance mode?</h6>
            </div>
            <div class="text-center mt-5">
              <VBtn type="submit" @click.prevent="underMaintenance" color="primary">Confirm</VBtn>&nbsp;
              <VBtn @click="dialogValidation = false" color="error" variant="tonal">Cancel</VBtn>
            </div>
          </VCol>
        </VRow>
      </VCardText>
   </VCard>
  </v-dialog>
  
    <!-- SnackBar -->
    <VSnackbar v-model="isSnackbarSuccess" :timeout="4000" color="success">
      Success! Active!
    </VSnackbar>
    <VSnackbar v-model="isSnackbarErorr" :timeout="4000" color="error">
      Erorr! Please Check Your Field!
    </VSnackbar>
    <VDialog v-model="loading" max-width="300">
      <loadingScreen />
    </VDialog>
</template>
