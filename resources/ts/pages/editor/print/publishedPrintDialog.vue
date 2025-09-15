<script lang="ts" setup>
import moment from "moment";

const props = defineProps<Props>();
interface Props {
  Data: any;
}
</script>
<template>
  <VCard>
    <VCardTitle class="text-center"
      ><strong> DIT.ADS - Published Journal List Report </strong>
    </VCardTitle>

    <VCardText>
      <VRow>
        <VCol>
          <VTable ref="tableRef">
            <thead>
              <tr>
                <th class="text-center">No.</th>
                <th class="text-center">Client Fullname</th>
                <th class="text-center">Published File</th>
                <th class="text-center">Date of Published</th>
                <th class="text-center">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, index) in props.Data" :key="row.id">
                <td class="text-center">{{ index + 1 }}</td>
            <td class="text-center">
              {{ row.client.firstname }} {{ row.client.middlename }}
              {{ row.client.lastname }}
            </td>
            <td class="text-center">
              {{ row.editor_uploaded_file }}
            </td>
            <td class="text-center">
              {{ moment(row.date_time_requests).format("MMMM DD, YYYY") }} <br/>
            </td>
            <td class="text-center">
              <VChip
                color="success"
                variant="outlined"
                v-if="row.request_status == 'Approved'"
                >{{ row.request_status }}</VChip
              >
              <VChip
                color="warning"
                variant="outlined"
                v-if="row.request_status == 'Pending'"
                >{{ row.request_status }}</VChip
              >
              <VChip
                color="error"
                variant="outlined"
                v-if="row.request_status == 'Rejected'"
                >{{ row.request_status }}</VChip
              >
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
