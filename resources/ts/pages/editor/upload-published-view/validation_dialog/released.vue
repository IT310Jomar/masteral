<script setup lang="ts">
import { environment } from "@/environments/environment";
import axios from "axios";
import loadingScreen from "@/pages/loading/Validationloading.vue";
import { onMounted, ref } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();

const emit = defineEmits(['close','handleData','cancel']);
const props = defineProps(["row","editor","fileUpload"]);

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
  const formData = new FormData();

  // Check if the file input has files and if the first file is selected
  if (props.fileUpload?.files && props.fileUpload.files.length > 0) {
    const file = props.fileUpload.files[0];
    formData.append("filepdf", file);
  }

  axios
    .post(environment.API_URL + "editor/editor/post-edited-uploadpdf/" + props.row, formData)
    .then((response) => {
      if (response.data.success) {
        emit("handleData", response.data);
        approvecancel();
        emit('cancel')
        router.push("/editor/publish/upload_documents");
      }
    });
};

</script>

<template>
  <section>
    <VCard height="330" flat>
      <VCardText>
        <VRow>
          <VCol class="text-center mt-10">
            <div style="text-align: center;">
              <VIcon color="warning" size="110" icon="bi:exclamation-circle"/><br/><br/>
              <h6 class="text-lg font-weight-large">Are you sure you want to Published?</h6>
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
      Success! Uploaded File!
    </VSnackbar>
    <VSnackbar v-model="isSnackbarErorr" :timeout="4000" color="error">
      Erorr! Please Check Your Field!
    </VSnackbar>
    <VDialog v-model="loading" max-width="300">
      <loadingScreen />
    </VDialog>
  </section>
</template>
