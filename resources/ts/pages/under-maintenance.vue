<script setup lang="ts">
import miscMaskDark from '@images/pages/misc-mask-dark.png'
import miscMaskLight from '@images/pages/misc-mask-light.png'
import miscUnderMaintenance from '@images/pages/misc-under-maintenance.png'
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import { initialAbility } from "@/plugins/casl/ability";
import { useAppAbility } from "@/plugins/casl/useAppAbility";
import { useRouter } from "vue-router";
const authThemeMask = useGenerateImageVariant(miscMaskLight, miscMaskDark)
const ability = useAppAbility();
const router = useRouter();
const logout = () => {
  localStorage.removeItem("userData");
  localStorage.removeItem("accessToken");
  localStorage.removeItem("userAbilities");
  localStorage.removeItem("userRole");
  localStorage.removeItem("clientData");
  localStorage.removeItem("editorData");

  ability.update(initialAbility);

  router.push('/login'); // Redirect the user to the login page
};

</script>

<template>
  <div class="misc-wrapper">
    <div class="text-center mb-12">
      <!-- 👉 Title and subtitle -->
      <h4 class="text-h4 font-weight-medium mb-3">
        Under Maintenance! 🚧
      </h4>
      <p>Sorry for the inconvenience but we're performing some maintenance at the moment</p>

      <VBtn @click.prevent="logout">
        Logout
      </VBtn>
    </div>

    <!-- 👉 Image -->
    <div class="misc-avatar w-100 text-center">
      <VImg
        :src="miscUnderMaintenance"
        alt="Coming Soon"
        :max-width="550"
        class="mx-auto"
      />
    </div>

    <VImg
      :src="authThemeMask"
      class="misc-footer-img d-none d-md-block"
    />
  </div>
</template>

<style lang="scss">
@use "@core-scss/template/pages/misc.scss";
</style>

<route lang="yaml">
meta:
  layout: blank
</route>
