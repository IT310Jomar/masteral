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
      ><strong> DIT.ADS - Sales List Report </strong>
    </VCardTitle>

    <VCardText>
      <VRow>
        <VCol>
          <VTable ref="tableRef">
            <thead>
              <tr>
                <th class="text-center">No.</th>
                <th class="text-center">Client Fullname</th>
                <th class="text-center">Mode of Payment</th>
                <!-- <th class="text-center">Proof of Payment</th> -->
                <th class="text-center">Reference / O.R Number</th>
                <th class="text-center">Amount</th>
                <th class="text-center">Date of Payment</th>
                <th class="text-center">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, index) in props.Data" :key="row.id">
                <td class="text-center">{{ index + 1 }}</td>
                <td class="text-center">
                  {{ row.payment.client.firstname }} {{ row.payment.client.middlename }}
                  {{ row.payment.client.lastname }}
                </td>
                <td class="text-center">
                  <VChip
                    color="red"
                    variant="tonal"
                    v-if="row.payment.modeofpayment.name == 'Cash'"
                    >{{ row.payment.modeofpayment.name }}</VChip
                  >
                  <VChip
                    color="blue"
                    variant="tonal"
                    v-if="row.payment.modeofpayment.name == 'GCash'"
                    >{{ row.payment.modeofpayment.name }}</VChip
                  >
                </td>
                <!-- <td class="text-center">
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
                          v-if="row.proof_of_payment"
                          :src="
                            '/storage/proofofpayment_image/' +
                            row.proof_of_payment
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
                </td> -->
                <td class="text-center" v-if="row.payment.modeofpayment.name == 'Cash'">
                  {{ row.payment.or_number }}
                </td>
                <td
                  class="text-center"
                  v-if="row.payment.modeofpayment.name == 'GCash'"
                >
                  {{ row.payment.reference_number }}
                </td>
                <td class="text-center text-success">
                  <VChip
                    color="primary"
                    variant="outlined" label>{{ row.payment.amount }}</VChip>
                </td>
                <td class="text-center">
                  {{ moment(row.payment.date_of_payment).format("MMMM DD, YYYY") }}
                </td>
                <td class="text-center">
                  <VChip
                    color="success"
                    variant="outlined"
                    v-if="row.payment.payment_status == 'Approved'"
                    >{{ row.payment.payment_status }}</VChip
                  >
                  <VChip
                    color="warning"
                    variant="outlined"
                    v-if="row.payment.payment_status == 'Pending'"
                    >{{ row.payment.payment_status }}</VChip
                  >
                  <VChip
                    color="error"
                    variant="outlined"
                    v-if="row.payment.payment_status == 'Rejected'"
                    >{{ row.payment.payment_status }}</VChip
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
