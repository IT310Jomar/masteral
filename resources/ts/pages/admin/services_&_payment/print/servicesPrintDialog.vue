<script lang="ts" setup>
import nodata from "@images/no-data-found.png";

const props = defineProps<Props>();
interface Props {
  Data: any;
}

</script>
<template>
  <VCard>

    <VCardTitle class="text-center"
      ><strong> DIT.ADS - Services List Report </strong>
    </VCardTitle>
    
    <VCardText>
       
      <VRow>
        <VCol>
         
          <VTable ref="tableRef">   
            <thead>
              <tr>
                <th class="text-center">No.</th>
                <th class="text-center">Name</th>
                <th class="text-center">Price</th>
                <th class="text-center">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, index) in props.Data" :key="row.id">
                <td>{{ index + 1 }}</td>
                <td>
                  {{ row.name }}
                </td>
                <td>
                  {{ row.price }}
                </td>
                <td>
                  <VChip
                    v-if="
                      row.status?.name != null &&
                      row.status?.name == 'Available'
                    "
                    color="success"
                    variant="outlined"
                    >{{ row.status?.name }}</VChip
                  >
                  <VChip
                    v-if="
                      row.status?.name != null &&
                      row.status?.name == 'Not Available'
                    "
                    color="warning"
                    variant="outlined"
                    >{{ row.status?.name }}</VChip
                  >
                </td>
              </tr>
            </tbody>
            <!-- 👉 table footer  -->
            <tfoot v-if="props.Data?.length == 0">
          <tr>
            <td colspan="8">
              <div style="text-align: center" class="mb-5 mt-5">
                <VImg
                  :src="nodata"
                  style="
                    display: inline-block;
                    max-width: 100%;
                    width: 35%;
                    height: 55%;
                    object-fit: cover;
                  "
                />
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
