<script setup lang="ts">
import { emailValidator, requiredValidator } from "@validators";
import { VForm } from "vuetify/components";
import { environment } from "@/environments/environment";
import axios from "axios";
import { onMounted, ref } from "vue";
import rejectedRequest from "@/pages/admin/request/RequestDialog/validationRejected.vue";


const emit = defineEmits(["close", "handledata",'currentTab']);
const props = defineProps(['row']);
const refsubmit = ref<VForm>();
const isSnackbarErorr = ref(false);
const reasons = ref();


const close = () => {
  emit("close");
};
const rejectedValid_dialog = ref(false);

const paidID = ref();

const openValidation = () => {
  refsubmit.value?.validate().then(({ valid: isValid }) => {
    if (isValid) {
      rejectedValid_dialog.value = true;
      paidID.value = props.row;
    } else {
      isSnackbarErorr.value = true;
    }
  });
}

</script>

<template>
      <section>
    <DialogCloseBtn @click="close" />
    <v-row>
      <v-col>
        <VCard
          height="420px"
          class="d-flex flex-column flex-grow-1 overflow-auto"
          title="Reject Payment"
        >
          <VDivider/>
        <VCardText>
          <VForm ref="refsubmit">
            <VRow>
              <VCol>
                <VLabel class="text-red">*Reasons:</VLabel>
              </VCol>
            </VRow>
            <VRow>
              <VCol>
                <VTextarea label="Reasons" v-model="reasons" :rules="[requiredValidator]"></VTextarea>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
        <VDivider />
          <VCardText>
            <VCol style="text-align: right">
              <VBtn class="mr-3" @click="openValidation" type="submit">Rejected</VBtn>
              <VBtn @click="close" color="error"> Close</VBtn>
            </VCol>
          </VCardText>
        </VCard>
      </v-col>
    </v-row>

    <VDialog v-model="rejectedValid_dialog" scrollable max-width="540">
      <rejectedRequest
        :row="paidID"
        @close="rejectedValid_dialog = false"
        @closeRejected="close()"
        @handleData="emit('handledata')"
        @currentTab="emit('currentTab')"
        :reasons="reasons"
      />
    </VDialog>
    <VSnackbar v-model="isSnackbarErorr" :timeout="4000" color="error">
      Erorr! Please Check Your Field!
    </VSnackbar>
    
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
  max-width: 230px; /* Limit width for responsiveness */
  height: 330px; /* Automatically adjust height */
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
}

.form-container {
  max-height: 50vh;
  overflow-y: auto;
  padding: 16px;
}
</style>
