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

const handleFileChange = () => {
  if (fileInput.value?.files && fileInput.value.files.length > 0) {
    const file = fileInput.value.files[0];
    const reader = new FileReader();

    reader.onload = (e) => {
      if (e.target && typeof e.target.result === "string") {
        imageUrl.value = e.target.result;
      }
    };

    reader.readAsDataURL(file);
  }
};

const imageData = ref<String>();

const updateImage = () => {
  if (!fileInput.value?.files || fileInput.value.files.length === 0) {
    return;
  }

  const file = fileInput.value.files[0];
  const formData = new FormData();
  formData.append("image", file);

  axios
    .post(`/api/auth/image/${image_id.value}`, formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    })
    .then((response) => {
      // console.log('Image updated successfully:', response.data);
    })
    .catch((error) => {
      console.error("Error updating image:", error);
    });
};

onMounted(() => {
  image_id.value = props.image.id;

  console.log(image_id.value, "IDIDIDID");
});
</script>

<template>
  <!-- Dialog close btn -->
  <DialogCloseBtn @click="closeDialog" />

  <!-- Dialog Content -->
  <VCard>
    <VCardText>
      <VCardTitle class="mb-5">PROFILE</VCardTitle>
      <VCardText>
        <VRow>
          <VCol cols="12">
            <v-file-input
              ref="fileInput"
              @change="handleFileChange"
              chips
              label="Select File"
              accept=".jpg,.jpeg,.png,.doc,.docx,.pdf"
            />
            <img
              v-if="imageUrl"
              :src="imageUrl"
              alt="Image Preview"
              class="preview-image"
            />
            <img
              v-else
              :src="'/storage/images/' + props.image.image"
              alt="Image"
              class="circle-image"
            />
          </VCol>
        </VRow>

        <VBtn @click.prevent="updateImage"> Update </VBtn>
      </VCardText>
    </VCardText>

    <VCardText class="d-flex justify-end">
      <VBtn @click="closeDialog"> Close </VBtn>
    </VCardText>
  </VCard>
</template>
