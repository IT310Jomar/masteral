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
      ><strong> DIT.ADS - Client List Report </strong>
    </VCardTitle>

 
    <VCardText>
       
      <VRow>
        <VCol>
         
          <VTable ref="tableRef">   
            <thead>
              <tr>
                <th>No.</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Contact</th>
                <th>School</th>
                <th>School Type</th>
                <th>Course</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, index) in props.Data" :key="row.id">
                <td>{{ index + 1 }}</td>
                <td>
                  {{ row.firstname }} {{ row.middlename }} {{ row.lastname }}
                </td>
                <td>
                  {{ row.users?.email }}
                </td>
                <td>{{ row.contact }}</td>
                <td>
                  {{ row.school }}
                </td>
                <td>
                  <VChip
                    v-if="
                      row.school_type?.name != null &&
                      row.school_type?.name == 'Public'
                    "
                    color="success"
                    variant="outlined"
                    >{{ row.school_type?.name }}</VChip
                  >
                  <VChip
                    v-if="
                      row.school_type?.name != null &&
                      row.school_type?.name == 'Private'
                    "
                    color="warning"
                    variant="outlined"
                    >{{ row.school_type?.name }}</VChip
                  >
                </td>
                <td>{{ row.course }}</td>
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
