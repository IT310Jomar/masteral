<script setup lang="ts"></script>

<template>
  <section
    fluid
    class="d-flex align-center justify-center mb-3 float-center content"
  >
    <VCard flat :max-width="500" class="mt-12 mt-sm-0 pa-4">
      <VCardText>
        <h5 class="text-h5 font-weight-semibold mb-1">Welcome! 👋🏻</h5>
        <p class="mb-0">
          Please sign-in to your account and start the adventure
        </p>
      </VCardText>
      <VCardText>
        <VCol cols="12" class="text-center">
          <RouterLink class="text-primary ms-2" :to="{ name: 'Login' }">
            <VBtn> Admin Login </VBtn>
          </RouterLink>
        </VCol>

        <VRow cols="12" class="text-center">
          <VCol>
            <RouterLink class="text-primary ms-2" :to="{ name: 'Client_Login' }">
              <VBtn> Client Login </VBtn>
            </RouterLink>
          </VCol>

          <VCol>
            <RouterLink class="text-primary ms-2" :to="{ name: 'Editor_Login' }">
              <VBtn> Editor Login </VBtn>
            </RouterLink>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
  </section>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth.scss";

.content {
  position: relative;
  background-image: url("assets/img/logo.png");
  background-position: center;
  background-size: cover;
  block-size: 100vh; /* Set the height to cover the full viewport height */
  inline-size: 100%;
}
</style>
