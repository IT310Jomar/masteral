<script setup lang="ts">
import { environment } from "@/environments/environment";
import axios from "axios";
import loadingScreen from "@/pages/loading/Validationloading.vue";

const emit = defineEmits(['close','closeApproved','handleData','tabs']);
const props = defineProps(["row"]);

const loading = ref(false);
const isSnackbarSuccess = ref(false);
const isSnackbarErorr = ref(false);

const loaderTrue = () => {
  loading.value = true;
};

const loaderFalse = () => {
  loading.value = false;
};

const approvecancel =() =>  {
  emit("close");
}

const post_approve = () => {
  loaderTrue();
  axios.put(environment.API_URL + 'auth/admin/post-approved-paid/' + props.row.id)
  .then((response) => {
    emit('handleData', response.data);
    emit('close');
    emit('closeApproved');
    loaderFalse();
    isSnackbarSuccess.value = true
    emit('tabs');
  }
  )
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
              <h6 class="text-lg font-weight-large">Are you sure you want to Approve?</h6>
            </div>

            <div class="text-center mt-5">
              <VBtn  @click="post_approve" color="primary">Yes!</VBtn>&nbsp;
              <VBtn @click="approvecancel" color="error" variant="tonal">Cancel</VBtn>
            </div>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
    
    <!-- SnackBar -->
    <VSnackbar v-model="isSnackbarSuccess" :timeout="4000" color="success">
      Success! Approved!
    </VSnackbar>
    <VSnackbar v-model="isSnackbarErorr" :timeout="4000" color="error">
      Erorr! Please Check Your Field!
    </VSnackbar>
    <VDialog v-model="loading" max-width="300">
      <loadingScreen />
    </VDialog>
  </section>
</template>
