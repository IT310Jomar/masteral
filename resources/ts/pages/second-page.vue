<script setup lang="ts">
import axios from "axios";
import { ref, onMounted } from "vue";
import ViewDialog from "@/pages/dialog.vue";
import Previewimage from "@/pages/preview-image.vue"

const fileInput = ref<HTMLInputElement | null>(null);
const imageUrl = ref<string | null>(null);
const dialog = ref(false);
const dialogpreview = ref(false)
const images = ref();
const getimages = ref();
const ImageID = ref();

const currentImage = ref(null)

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

const uploadImage = () => {
  if (!fileInput.value?.files || fileInput.value.files.length === 0) {
    return;
  }

  const file = fileInput.value.files[0];
  const formData = new FormData();
  formData.append("image", file);

  axios.post("/api/auth/upload", formData).then((response) => {
    console.log(response.data.message);
  });
};

const getImage = () => {
  axios.get("/api/auth/getImage").then((response) => {
    const data = response.data.image; // Make sure to access the correct property from the response
    images.value = "/storage/images/" + data[0].image;
  });
};

const image = () => {
  axios.get("/api/auth/getImage").then((response) => {
    const data = response.data.image; // Make sure to access the correct property from the response
    getimages.value = data;
    console.log(getimages.value, "hello image");
  });
};

const viewImage = (data: any) => {
  dialog.value = true;
  ImageID.value = data;
};

const viewPreview = (data: any) => {
      currentImage.value = data;
      dialogpreview.value = true;
};

onMounted(() => {
  getImage();
  image();
});
</script>

<template>
  <div>
    <v-card title="Create Awesome 🙌">
      <v-card-text>PROFILE</v-card-text>
      <v-card-text>
        <div class="image-upload">
          <v-file-input
            ref="fileInput"
            @change="handleFileChange"
            chips
            label="Select File"
            accept=".jpg,.jpeg,.png,.doc,.docx,.pdf"
          />
          <small class="text-muted ml-10"
            >(5MB maximum file size, allowed file types: jpg, png, doc,
            pdf)</small
          >
          <div v-if="images" class="circle-container">
            <VImg :src="images" alt="Uploaded Image" class="circle-image" />
          </div>
          <v-btn @click.prevent="uploadImage">SAVE</v-btn>
        </div>
      </v-card-text>

      <VCardText>
        <div v-for="image in getimages" :key="image.id">
          <VTable>
            <thead>
              <tr>
                <th>Image</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>
                  <div class="circle-container">
                    <img
                      :src="'/storage/images/' + image.image"
                      alt="Image"
                      class="circle-image"
                      @click="viewPreview(image)"
                    />
                  </div>
                </td>
                <td><VBtn @click="viewImage(image)">View</VBtn></td>
              </tr>
            </tbody>
          </VTable>
        </div>
      </VCardText>
    </v-card>

    <v-dialog v-model="dialogpreview" max-width="600px">
      <Previewimage @closeDialog="dialogpreview = false" :image="currentImage" />
      <v-btn text @click="dialogpreview = false">Close</v-btn>
    </v-dialog>

    <VDialog v-model="dialog" max-width="600" persistent>
      <ViewDialog @closeDialog="dialog = false" :image="ImageID" />
    </VDialog>
  </div>
</template>

<style scoped>
.image-upload {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.circle-container {
  width: 250px;
  height: 250px;
  border-radius: 50%;
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
  border-radius: 50%;
}

.circle-image {
  cursor: pointer;
}

.modal-image {
  max-width: 100%;
  max-height: 80vh;
}
</style>
