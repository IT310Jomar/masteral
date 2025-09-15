<script lang="ts" setup>
import moment from "moment";
import { environment } from "@/environments/environment";
import { exportToExcel } from "@/excelPaidList";
import logo from "@images/DITADS Logo_PNG.png";
import nodata from "@images/no-data-found.png";
import axios from "axios";
import jsPDF, { RGBAData } from "jspdf";
import "jspdf-autotable";
import { debounce } from "lodash";
import { ref } from "vue";
import printPaid from "./print/paidPrintDialog.vue";

const PaidData = ref([]);
const tableRef = ref();
const searchQuery = ref();
const currentPage = ref(1);
const perPages = [5, 20, 50, 100, 500];
const itemsPerPage = ref(perPages[0]);
const selectedPerPage = ref(itemsPerPage.value);
const paginationDataPaidPage = ref(currentPage);
const printTable = ref();
const selected = ref(itemsPerPage);
const dateRange = ref(null);
const startDate = ref();
const endDate = ref();

//Pending Pagination
const paginatedPaidData = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage.value;
  const endIndex = startIndex + itemsPerPage.value;
  return PaidData.value.slice(startIndex, endIndex);
});

let totalPages = computed(() => {
  if (PaidData.value.length > 0) {
    return Math.ceil(PaidData.value.length / itemsPerPage.value);
  } else {
    return 0;
  }
});

//handle the pagination in pending request that will emitted to the parent component
function updatePage() {
  paginationDataPaidPage.value = 1;
}

//Function for searching daterange
const searchRange = () => {

if (dateRange.value != null) {
  const d: string[] = []
  d.push(dateRange.value)
  const range = d[0].split(' to ')
  axios.get(environment.API_URL + 'auth/admin/paid-list/range', {
    params: {
      startDate: startDate.value = range[0],
      endDate: endDate.value = range[1],
      paymentmode: paymentMode.value,
      token:token
    }
  })
    .then((response) => {
      paymentMode.value = []
      if (response.data.success) {
        dateRange.value = null
        PaidData.value = response.data.paiddata;

      }
    })
 }
}

//Search for Paid Accounts Approved
const searchApproved = () => {
  axios
    .get(environment.API_URL + "auth/admin/search-paid-accounts", {
      params: {
        query: searchQuery.value,
        paymentmode: paymentMode.value,
        token: token
      },
    })
    .then((response) => {
      if (response.data.success) {
        PaidData.value = response.data.searchApproved;
      }
    })
    .catch((error) => {
      console.log("Error");
    });
};

// watch the data input search by the paid accounts
const debouncedSearchApproved = debounce(() => {
  if (searchQuery.value === "") {
    getPaidList();
  } else if (searchQuery.value) {
    paymentMode.value = []
    searchApproved();
  } else if (paymentMode.value !== "") {
    // Search based on paymentMode
    searchApproved();
  } else {
    getPaidList();
  }
});
watch([searchQuery], debouncedSearchApproved);
//end
const token = JSON.parse(localStorage.getItem("accessToken") || "{}");
//get function for ghetting the data of clients
const getPaidList = () => {
  axios
    .get(environment.API_URL + "auth/admin/get-paid-accounts-lists",{
      params: {
        token:token
      }
    })
    .then((response) => {
      if (response.data.success) {
        PaidData.value = response.data.approveddata;
      }
    });
};

const paymentMode = ref();
const modePayment = ref();
const getModePayment = () => {
  axios
    .get(environment.API_URL + "client/client/get-mode-of-payment",{
      params:{
        token: token
      }
    })
    .then((response) => {
      if (response.data.success) {
        if (response.data.mode) {
          const data = [];
          for (var x = 0; x < response.data.mode.length; x++) {
            const modes = {
              item: response.data.mode[x].name,
              value: response.data.mode[x].id,
            };
            data.push(modes);
          }
          modePayment.value = data;
        }
      }
    });
};

function updateMode() {
  if(searchQuery.value = "") {
    searchApproved();
  } else {
    searchApproved();
  }
  searchApproved();
}

const initializeData = () => {
  getPaidList();
  getModePayment();
};

onMounted(() => {
  initializeData();
});

//function for print
const print = () => {
  printTable.value = true;
  if (printTable.value == true) {
    setTimeout(() => {
      window.print();
    }, 1000); // Add a delay of 100 milliseconds
  }
  window.addEventListener("beforeprint", () => {
    // The print dialog is about to be displayed
  });
  window.addEventListener("afterprint", () => {
    // The print dialog has been closed
    printTable.value = false;
  });
};

async function generatePdf(tableRef: any, logoUrl: string | HTMLImageElement | HTMLCanvasElement | Uint8Array | RGBAData) {
  try {
    const doc = new jsPDF({
      orientation: "landscape",
      unit: "mm",
      format: "legal",
    });

    const data = [tableRef];
    const table = data[0].$el.querySelector("table");
    const thead = table.querySelector("thead");

    const marginLeft = 50;
    const marginRight = 10;
    const marginTop = 5;
    const marginBottom = 0;

    const imageColumnIndex = 3; // Adjust the image column index as needed
    const logoX = marginLeft;
    const logoY = marginTop;
    const logoWidth = 40;
    const logoHeight = 40;

    const title = "DIT.ADS- Paid List Management Report";
    const titleX = doc.internal.pageSize.getWidth() / 2;
    doc.setFont("Georgia", "bold");
    doc.text(title, titleX, 30, { align: "center" });
    doc.addImage(logoUrl, logoX, logoY, logoWidth, logoHeight);

    const contentWidth = doc.internal.pageSize.getWidth() - marginLeft - marginRight;
    const contentHeight = doc.internal.pageSize.getHeight() - marginTop - marginBottom;

    // Create an array to store image promises
    const imagePromises: any[] = [];

    // Create an array to store image sources
    const imageSources: any[] = [];

    // Find the "Proof of Payment" column and position the image there in each row
    const rows = table.querySelectorAll("tbody tr");
    const proofOfPaymentColumnIndex = 3; // Adjust the column index if needed
    let proofOfPaymentY = 0; // Define the initial Y position

    rows.forEach((row: { querySelectorAll: (arg0: string) => any[]; offsetTop: number; }) => {
      const proofOfPaymentCell = row.querySelectorAll("td")[proofOfPaymentColumnIndex];
      const proofOfPaymentImage = proofOfPaymentCell.querySelector("img");
      if (proofOfPaymentImage) {
        const proofOfPaymentImageUrl = proofOfPaymentImage.getAttribute("src");

        imagePromises.push(
          new Promise<void>((resolve) => {
            const img = new Image();
            img.onload = function () {
              const proofOfPaymentX = marginLeft; // Adjust the X position
              proofOfPaymentY = row.offsetTop + marginTop; // Update the Y position
              // Store the image source in the array
              imageSources.push(this.src);
              resolve();
            };
            img.src = proofOfPaymentImageUrl;
          })
        );
      }
    });

    // Wait for all image promises to resolve
    await Promise.all(imagePromises);

    // Extract table data and headers
    const headerCells = thead.querySelectorAll("th");
    const headers = Array.from(headerCells).map((cell) => cell.innerText.trim());

    // Filter out the Action column header
    const filteredHeaders = headers.filter((header) => header !== "Action");

    // Extract table data
    const datas: any[][] = [];

    rows.forEach((row: { querySelectorAll: (arg0: string) => any; }) => {
      const rowCells = row.querySelectorAll("td");
      const rowData = Array.from(rowCells).map((cell) => cell.innerText.trim());
      // Exclude the Action column data
      rowData.splice(imageColumnIndex, 0);
      datas.push(rowData);
    });

    // Increase the column count in the autoTable configuration to accommodate the image column
    const columnCount = filteredHeaders.length + 1;

    doc.autoTable({
      head: [filteredHeaders],
      body: datas, // Use modified data
      startY: logoHeight + 10,
      margin: { top: 5, right: 10, bottom: 5, left: 10 },
      styles: {
        cellPadding: 2,
        fontSize: 12,
      },
      headStyles: {
        fillColor: "#00A36C",
        textColor: "#ffffff", // Changed text color to white
      },
      bodyStyles: {
        rowHeight: 55, // Adjust the table data row height (in units like mm)
      },
      columnStyles: {
        [columnCount - 1]: {
          align: "center", // Center align the content in the image column
        },
      },
      didDrawCell: function (data: { column: { index: number; }; row: { index: string | number; }; cell: { x: number; y: number; width: number; height: number; }; }) {
        // Check if we are in the image column
        if (data.column.index === imageColumnIndex) {
          const imageSrc = imageSources[data.row.index];
          // const imageSrc = img.src; // Use the correct image for each row
          doc.addImage(imageSrc, data.cell.x, data.cell.y, data.cell.width, data.cell.height);
        }
      },
    });

    doc.save("Dit.Ads.pdf");
  } catch (error) {
    console.error("Error generating PDF:", error);
  }
}

const isHovered = ref(null);

const showTooltip = (id: any) => {
  isHovered.value = id;
};

const hideTooltip = (id: any) => {
  if (isHovered.value === id) {
    isHovered.value = null;
  }
};
</script>

<template>
  <section>
    <VCard title="Paid List">
      <!-- <VDivider/> -->
      <!-- <VCardText>
        <VRow>
          <VCol cols="6" offset="2" style="display:flex; align-items:center; margin-left: 25%;">
            <AppDateTimePicker label="Date Range" v-model="dateRange" :config="{ mode: 'range' }" />
            <VBtn color="blue" @click.prevent="searchRange" class="ml-2">Search</VBtn>
          </VCol>
        </VRow>
        <hr class="mt-5" />
      </VCardText> -->
      <VCardText class="d-flex flex-wrap py-4 px-4 gap-2">
        <div
          class="me-3"
          style="display: flex; width: 10.5rem; align-items: center"
        >
          <v-label style="font-size: small" class="mr-1"
            ><strong>Show</strong></v-label
          >
          <VSelect
            v-model="selected"
            :items="perPages"
            @update:modelValue="updatePage"
          />
          <v-label style="font-size: small" class="ml-1"
            ><strong>entries</strong></v-label
          >
        </div>
        <v-spacer />

        <!-- 👉 PDF -->
        <!-- <VBtn color="error" @click.prevent="generatePdf(tableRef, logo)"
          ><VIcon>tabler:file-type-pdf</VIcon></VBtn
        > -->

        <!-- 👉 Excel -->
        <!-- <VBtn @click.prevent="exportToExcel(tableRef)"
          ><VIcon>tabler:file-spreadsheet</VIcon></VBtn
        > -->

        <!-- 👉 Print -->
        <!-- <VBtn @click.prevent="print" color="warning"
          ><VIcon>tabler:printer</VIcon></VBtn
        > -->
        <v-spacer />
        <div
          class="app-user-search-filter d-flex align-center flex-wrap gap-4 justify-end"
        >
          <!-- 👉 Search  -->
          <div style="display: flex; width: 17rem; align-items: center">
            <v-text-field
              v-model="searchQuery"
              label="Search"
              class="search-field"
              solo
              append-inner-icon="mdi-magnify"
              density="compact"
            >
            </v-text-field>
          </div>
        </div>

        <!-- <div class="app-user-search-filter d-flex align-center flex-wrap gap-4 justify-end">
          <div style="width: 17rem; display:flex; align-items:center;">
            <VSelect
                v-model="paymentMode"
                label="Mode Of Payment"
                :items="modePayment"
                item-title="item"
                item-value="value"
                @update:modelValue="updateMode"
              >
              </VSelect>
          </div>
        </div> -->
      </VCardText>

      <VTable ref="tableRef">
        <VCol hidden class="mb-5">
          <VImg :src="logo" alt="Dit.Ads Logo" class="logo" />
        </VCol>
        <thead>
          <tr>
            <th class="text-center">No.</th>
            <th class="text-center">Client Fullname</th>
            <th class="text-center">Mode of Payment</th>
            <!-- <th class="text-center">Proof of Payment</th> -->
            <th class="text-center">Amount</th>
            <th class="text-center">Sender Account Number</th>
            <th class="text-center">Reference/O.R Number</th>
            <th class="text-center">Date of Payment</th>
            <th class="text-center">Status</th>
            <!-- <th class="text-center">Action</th> -->
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, index) in paginatedPaidData" :key="row.id">
            <td class="text-center">{{ index + 1 }}</td>
            <td class="text-center">
              {{ row.client.firstname }} {{ row.client.middlename }}
              {{ row.client.lastname }}
            </td>
            <td class="text-center">
              <VChip
                color="red"
                variant="tonal"
                v-if="row.modeofpayment.name == 'Cash'"
                >{{ row.modeofpayment.name }}</VChip
              >
              <VChip
                color="blue"
                variant="tonal"
                v-else-if="row.modeofpayment.name == 'GCash'"
                >{{ row.modeofpayment.name }}</VChip
              >
              <VChip v-else
                color="success"
                variant="tonal"
                >{{ row.modeofpayment.name }}</VChip
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
                        '/storage/proofofpayment_image/' + row.proof_of_payment
                      "
                      alt="Uploaded Image"
                      style="display: inline-block; max-width: 100%"
                      id="proofImage"
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
            <td class="text-center text-success">
              <VChip
                color="primary"
                variant="outlined" label>₱{{ row.amount }}.00</VChip>
            </td>
            <td class="text-center" v-if="row.account">
              {{ row.account }}
            </td>
            <td class="text-center" v-else>
              <h5>No Sender Account Number</h5>
            </td>
            <td class="text-center" v-if="row.modeofpayment.name == 'Cash'">
              {{ row.or_number }}
            </td>
            <td class="text-center" v-else-if="row.modeofpayment.name == 'GCash'">
              {{ row.reference_number }}
            </td>
            <td class="text-center" v-else>
              <h5>No Reference/O.R Number</h5>
            </td>
            <td class="text-center">{{ moment(row.date_of_payment).format('MMMM DD, YYYY') }}</td>
            <td class="text-center">
              <VChip
                color="success"
                variant="outlined"
                v-if="row.payment_status == 'Approved'"
                >{{ row.payment_status }}</VChip
              >
              <VChip
                color="warning"
                variant="outlined"
                v-if="row.payment_status == 'Pending'"
                >{{ row.payment_status }}</VChip
              >
              <VChip
                color="error"
                variant="outlined"
                v-if="row.payment_status == 'Rejected'"
                >{{ row.payment_status }}</VChip
              >
            </td>
            <!-- <td class="text-center">
              <VBtn icon size="x-small" variant="text" class="icon-container">
                <VIcon
                  icon="tabler-table-options"
                  size="22"
                  @mouseenter="showTooltip(index)"
                  @mouseleave="hideTooltip(index)"
                />
                <span v-if="isHovered === index" class="tooltip">Click Me</span>
                <VMenu activator="parent">
                  <VList>
                    <VListItem @click="viewform(row)">
                      <template #default>
                        <VIcon>material-symbols:visibility-outline-rounded</VIcon>
                        View
                      </template>
                    </VListItem>
                  </VList>
                </VMenu>
              </VBtn>
            </td> -->
          </tr>
        </tbody>
        <!-- 👉 table footer  -->
        <tfoot v-if="PaidData?.length == 0">
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

      <VPagination :length="totalPages" v-model="paginationDataPaidPage" />
    </VCard>
    <VDialog
      fullscreen
      v-model="printTable"
      transition="dialog-bottom-transition"
    >
      <printPaid :Data="paginatedPaidData" />
    </VDialog>
  </section>
</template>
<style scoped>
.logo {
  max-inline-size: 100px;
}

.logo-content {
  color: #fff; /* Adjust text color based on your logo */
  padding-inline-start: 20px; /* Adjust as needed */
}

.logo-title {
  font-size: 18px;
  font-weight: bold;
  margin-block-end: 5px;
}

.logo-body {
  margin: 0;
  font-size: 14px;
}

.icon-container {
  position: relative;
}

.tooltip {
  position: absolute;
  padding: 5px;
  border-radius: 5px;
  background: rgba(0, 0, 0, 80%);
  color: white;
  inset-block-start: -10px; /* Adjust this value to position the tooltip as desired */
  inset-inline-start: 0;
}
</style>
