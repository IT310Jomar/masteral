<script setup lang="ts">
import { environment } from "@/environments/environment";
import axios from "axios";
import loadingScreen from "@/pages/loading/Validationloading.vue";

const emit = defineEmits(['close','closeRejected','handleData','currentTab']);
const props = defineProps(["row","reasons"]);
const rejectedcancel =() =>  {
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

const post_rejected = () => {
  loaderTrue();
  axios.put(environment.API_URL + 'auth/admin/post-rejected-request/' + props.row.id, {
    reasons: props.reasons
  })
  .then((response) => {
    emit('handleData', response.data)
    emit('closeRejected')
    rejectedcancel();
    loaderFalse();
    isSnackbarSuccess.value = true
    emit('currentTab');
  })
}

</script>

<template>
  <section>
    <VCard height="330" flat>
      <VCardText>
        <VRow>
          <VCol class="text-center mt-10">
            <div style="text-align: center;">
              <VIcon color="warning" size="110" icon="bi:exclamation-circle"/><br/><br/>
              <h6 class="text-lg font-weight-large">Are you sure you want to Reject?</h6>
            </div>

            <div class="text-center mt-5">
              <VBtn  @click="post_rejected" color="primary">Yes!</VBtn>&nbsp;
              <VBtn @click="rejectedcancel" color="error" variant="tonal">Cancel</VBtn>
            </div>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <!-- SnackBar -->
    <VSnackbar v-model="isSnackbarSuccess" :timeout="4000" color="success">
      Success! Rejected!
    </VSnackbar>
    <VSnackbar v-model="isSnackbarErorr" :timeout="4000" color="error">
      Erorr! Please Check Your Field!
    </VSnackbar>
    <VDialog v-model="loading" max-width="300">
      <loadingScreen />
    </VDialog>
  </section>
</template>
