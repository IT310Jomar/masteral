<script setup lang="ts">
import { environment } from "@/environments/environment";
import axios from "axios";
import { ref } from "vue";
import loadingScreen from "@/pages/loading/Validationloading.vue";


const emit = defineEmits(['close','handledata'])
const props = defineProps(["disabledID"]);
const cancel =() =>  {
  emit("close");
}

const loading = ref(false);
const isSnackbarSuccess = ref(false);
const isSnackbarErorr = ref(false);

const loaderTrue = () => {
  loading.value = true;
};

const loaderFalse = () => {
  loading.value = false;
};

const disabled =() =>  {
  loaderTrue();
  axios.put(environment.API_URL + `auth/admin/disable-editor/`+ props.disabledID)
  .then((response) => {
    emit('handledata', response.data)
    emit('close')
    isSnackbarSuccess.value = true
    loaderFalse();
  }
  )
}

</script>

<template>
  <section>
    <VCard height="200">
      <VCol>
        <VCardText>
          <div style="text-align: center;">
            <VIcon color="error" size="50" icon="tabler-info-octagon"/>
            <h1>Are you sure?</h1>
          </div>
        </VCardText>
        <div class="float-right">
            <VBtn  @click="disabled" color="primary">Yes!</VBtn>&nbsp;
            <VBtn @click="cancel" variant="tonal" color="error">Cancel</VBtn>
          </div>
      </VCol>
    </VCard>

    <!-- SnackBar -->
    <VSnackbar v-model="isSnackbarSuccess" :timeout="4000" color="success">
      Success! Disabled!
    </VSnackbar>
    <VSnackbar v-model="isSnackbarErorr" :timeout="4000" color="error">
      Erorr! Please Check Your Field!
    </VSnackbar>
    <VDialog v-model="loading" max-width="300">
      <loadingScreen />
    </VDialog>
  </section>
</template>
