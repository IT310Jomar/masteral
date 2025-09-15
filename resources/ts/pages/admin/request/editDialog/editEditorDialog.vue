<script setup lang="ts">
import { environment } from "@/environments/environment";
import axios from "axios";
import { ref } from "vue";
import { VForm } from "vuetify/components";
import loadingScreen from "@/pages/loading/Validationloading.vue";
import { emailValidator, requiredValidator } from "@validators";

const token = JSON.parse(localStorage.getItem("accessToken") || "{}");
const emit = defineEmits(["close", "editedData"]);
const refVForm = ref<VForm>();
const validated = ref(false);
const successfully = ref(false);
const editors = ref();
const loading = ref(false);
function close() {
  emit("close");
}
const props = defineProps(["row", "dataId"]);
const editor = ref();

const getEditors = () => {
  axios.get(environment.API_URL + "auth/admin/editors",{
    params: {
      token:token
    }
  }).then((response) => {
    if (response.data.success) {
      const newEditors = response.data.editor.map((editorItem: { firstname: any; middlename: any; lastname: any; id: any; }) => ({
        name: `${editorItem.firstname} ${editorItem.middlename ?? ""} ${editorItem.lastname}`,
        id: editorItem.id,
      }));

      if (props.dataId != null) {
        editors.value = props.dataId;
      }
      editor.value = newEditors;
    }
  });
};


const onSubmit = () => {
  refVForm.value?.validate().then(({ valid: isValid }) => {
    if (isValid) {
      updateEditor();
    } else {
      validated.value = true;
    }
  });
};
const updateEditor = () => {
  loading.value = true;
  axios
    .put(environment.API_URL + "auth/admin/edit-assigned-editor/" + props.row, {
      editor: editors.value,
    })
    .then((response) => {
      if (response.data.success) {
        emit("editedData", response.data);
        successfully.value = true;
        loading.value = false;
        close();
      }
    });
};
onMounted(() => {
  getEditors();
});
</script>
<template>
  <section>
    <DialogCloseBtn @click="close" />
    <VCard>
      <VCardTitle>Edit Editor</VCardTitle>
      <VDivider />
      <VForm ref="refVForm" @submit.prevent="onSubmit">
        <VCardText>
          <VRow>
            <VCol>
              <VSelect
                :items="editor"
                item-title="name"
                item-value="id"
                label="Select Editor"
                v-model="editors"
                :rules="[requiredValidator]"
              ></VSelect>
            </VCol>
          </VRow>
        </VCardText>
        <VDivider />
        <VCardText>
          <VRow>
            <VCol class="text-right">
              <VBtn class="mr-1" color="warning" type="submit">Update</VBtn>
              <VBtn variant="tonal" @click.prevent="close" color="error"
                >Cancel</VBtn
              >
            </VCol>
          </VRow>
        </VCardText>
      </VForm>
    </VCard>
    <VSnackbar v-model="validated" color="error" timeout="4000" variant="flat">
      Error!!
    </VSnackbar>
    <VSnackbar
      v-model="successfully"
      :timeout="4000"
      variant="flat"
      color="primary"
    >
      <VIcon>tabler:discount-check</VIcon>&nbsp; Success!!
    </VSnackbar>
    <VDialog v-model="loading" max-width="300">
      <loadingScreen />
    </VDialog>
  </section>
</template>
