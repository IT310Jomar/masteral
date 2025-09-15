<script lang="ts" setup>
import logo from "@images/DITADS Logo_PNG.png";

const props = defineProps<Props>();
interface Props {
  Data: any;
}

</script>
<template>
  <VCard>

    <VCardTitle class="text-center"
      ><strong> DIT.ADS - Editor List Report </strong>
    </VCardTitle>

 
    <VCardText>
       
      <VRow>
        <VCol>
         
            <VTable ref="tableRef">
        <VCol hidden class="mb-5">
          <VImg :src="logo" alt="Dit.Ads Logo" class="logo" />
        </VCol>
        <thead>
          <tr>
            <th>No.</th>
            <th class="text-center">Image</th>
            <th>Full Name</th>
            <th>Contact</th>
            <th>Account Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, index) in props.Data" :key="row.id">
            <td>{{ index + 1 }}</td>
            <td>
              <div class="profile-card text-center mb-2">
                <div class="profile-card-photo">
                  <VAvatar
                    class="cursor-pointer"
                    color="primary"
                    variant="tonal"
                    style="
                      width: 50px;
                      height: 50px;
                      margin-top: 5%;
                      margin-bottom: 2%;
                    "
                  >
                    <VImg
                      v-if="row.image"
                      :src="
                        '/storage/profile_image/' + row.image
                      "
                      alt="Uploaded Image"
                      style="display: inline-block; max-width: 100%"
                    ></VImg>
                    <VImg
                      v-else
                      :src="logo"
                      alt="Uploaded Image"
                      class="circle-image"
                      style="display: inline-block; max-width: 100%"
                    ></VImg>
                  </VAvatar>
                </div>
              </div>
            </td>
            <td>{{ row.firstname }} {{ row.middlename }} {{ row.lastname }}</td>
            <td>{{ row.contact }}</td>
            <td>
              <VChip variant="outlined" color="primary" v-if="row.status === 'Active' && row.status !=null">{{
                row.status
              }}</VChip>
              <VChip variant="outlined" color="error" v-if="row.status === 'Disabled' && row.status !=null">{{ row.status }}</VChip>
            </td>

          
          </tr>
        </tbody>
        <!-- 👉 table footer  -->
        <tfoot v-if="props.Data?.length == 0">
          <tr>
            <td colspan="8">
              <div style="text-align: center">
                <VProgressLinear color="primary" indeterminate reverse />
              </div>
            </td>
          </tr>
        </tfoot>
      </VTable> 
        </VCol>
      </VRow>
    </VCardText>
  </VCard>
</template>

<style lang="scss">
.dialog-bottom-transition-enter-active,
.dialog-bottom-transition-leave-active {
  transition: transform 0.2s ease-in-out;
}

</style>
