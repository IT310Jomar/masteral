<script setup lang="ts">
import axios from "axios";

const fileInput = ref<HTMLInputElement | null>(null);
const imageUrl = ref<string | null>(null);
const image_id = ref();

const props = defineProps(["image"]);
const emit = defineEmits(["closeDialog"]);

const closeDialog = () => {
  emit("closeDialog");
};

const zoomImage = () => {
  
};

</script>

<template>
  <!-- Dialog close btn -->
  <DialogCloseBtn @click="closeDialog" />

  <!-- Dialog Content -->
  <VCard max-width="600px">
    <VCardText>
      <VCardTitle class="mb-5">PROFILE</VCardTitle>
      <VCardText>
        <VRow>
          <VCol cols="12">
            <v-img
              :src="'/storage/images/' + props.image.image"
              alt="Image"
              class="modal-image"
              :contain="true"
              @click="zoomImage"
            />
          </VCol>
        </VRow>
      </VCardText>
    </VCardText>

    <!-- <VCardText class="d-flex justify-end">
      <VBtn @click="closeDialog"> Close </VBtn>
    </VCardText> -->
  </VCard>
</template>

<style>

.modal-image {
  max-width: 100%;
  max-height: 80vh;
  cursor: zoom-in;
}

.v-dialog--active .modal-image {
  cursor: zoom-out;
}


</style>
