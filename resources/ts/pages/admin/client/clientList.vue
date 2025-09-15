<script lang="ts" setup>
import { environment } from "@/environments/environment";
import { exportToExcel } from "@/excellClient";
import active from "@/pages/admin/client/dialog/active-dialog.vue";
import disabled from "@/pages/admin/client/dialog/disabled-dialog.vue";
import nodata from "@images/no-data-found.png";
import logo from "@images/DITADS Logo_PNG.png";
import axios from "axios";
import jsPDF from "jspdf";
import "jspdf-autotable";
import { debounce } from "lodash";
import { ref } from "vue";
import printClient from "./print/clientPrintDialog.vue";

const userRole = JSON.parse(localStorage.getItem("userRole") || "{}");
const token = JSON.parse(localStorage.getItem("accessToken") || "{}");
const clientsData = ref([]);
const tableRef = ref();
const searchQuery = ref(null);
const currentPage = ref(1);
const perPages = [5, 20, 50, 100, 500];
const itemsPerPage = ref(perPages[0]);
const selectedPerPage = ref(itemsPerPage.value);
const paginationDataClientPage = ref(currentPage);
const printTable = ref();
const selected = ref(itemsPerPage);

//Pending Pagination
const paginatedClientData = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage.value;
  const endIndex = startIndex + itemsPerPage.value;
  return clientsData.value.slice(startIndex, endIndex);
});

let totalPages = computed(() => {
  if (clientsData.value.length > 0) {
    return Math.ceil(clientsData.value.length / itemsPerPage.value);
  } else {
    return 0;
  }
});

//handle the pagination in pending request that will emitted to the parent component
function updatePage() {
  paginationDataClientPage.value = 1;
  // console.log(paginationDataOfficerPage.value);
}

//Search for Regulatory Officer
const searchclient = () => {
  axios
    .get(environment.API_URL + "auth/admin/search-clients", {
      params: {
        query: searchQuery.value,
        token: token,
      },
    })
    .then((response) => {
      if (response.data.success) {
        clientsData.value = response.data.searchClient;
      }
    })
    .catch((error) => {
      console.log("Error");
    });
};

// watch the data input search by the user for officer
const debouncedSearch = debounce(() => {
  if (searchQuery.value === "") {
    getAllClients();
  } else {
    searchclient();
  }
}, 300);
watch([searchQuery], debouncedSearch);

//get function for ghetting the data of clients
const getAllClients = () => {
  axios
    .get(environment.API_URL + "auth/admin/get-all-clients", {
      params: {
        token: token
      }
    })
    .then((response) => {
      if (response.data.success) {
        clientsData.value = response.data.client;
        // console.log(clientsData.value);
      }
    });
};
onMounted(() => {
  getAllClients();
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

async function generatePdf(
  tableRef: any,
  logoUrl: string | HTMLImageElement | HTMLCanvasElement | Uint8Array | RGBAData
) {
  try {
    const doc = new jsPDF({
      orientation: "landscape", // Set orientation to landscape
      unit: "mm", // Set unit to millimeters
      format: "legal", // Set the page format to A4 (or use another format as needed)
    });
    const data = [tableRef];
    const table = data[0].$el.querySelector("table");
    const thead = table.querySelector("thead");

    // Define margins
    const marginLeft = 50;
    const marginRight = 10;
    const marginTop = 5;
    const marginBottom = 0;

    const logoX = marginLeft;
    const logoY = marginTop;
    const logoWidth = 40;
    const logoHeight = 40;

    const title = "DIT.ADS- Client List Management Report";
    const titleX = doc.internal.pageSize.getWidth() / 2;
    doc.setFont("Georgia", "bold");
    doc.text(title, titleX, 30, { align: "center" });
    doc.addImage(logoUrl, "JPEG", logoX, logoY, logoWidth, logoHeight);

    const contentWidth =
      doc.internal.pageSize.getWidth() - marginLeft - marginRight;
    const contentHeight =
      doc.internal.pageSize.getHeight() - marginTop - marginBottom;

    // Extract table data and headers
    const headerCells = thead.querySelectorAll("th");
    const headers = Array.from(headerCells).map((cell) => cell.innerText.trim());

    // Filter out the Action column header
    const filteredHeaders = headers.filter((header) => header !== "Action");

    const rows = table.querySelectorAll("tbody tr");
    const datas = Array.from(rows).map((row) => {
      // Extract row data and exclude the Action column data
      const rowCells = row.querySelectorAll("td");
      return Array.from(rowCells).map((cell) => {
        if (headerCells[cell.cellIndex].innerText.trim() !== "Action") {
          return cell.innerText.trim();
        }
        return "";
      });
    });

    doc.autoTable({
      head: [filteredHeaders], // Use filtered headers
      body: datas, // Use filtered row data
      startY: logoY + logoHeight + 10,
      margin: { top: 5, right: 10, bottom: 5, left: 10 },
      styles: {
        cellPadding: 2,
        fontSize: 12,
      },
      headStyles: {
        fillColor: "#00A36C",
        textColor: "#000000",
      },
    });

    // Save the PDF with a specific name
    doc.setFontSize(12);
    doc.save("Dit.Ads.pdf");
  } catch (error) {
    console.error("Error generating PDF:", error);
  }
}

const isHovered = ref(null);
const activeDialog = ref(false);
const disabledDialog = ref(false);
const activeID = ref();
const disabledID = ref();

const activeform = (id: any) => {
  activeDialog.value = true;
  activeID.value = id;
};

const disabledform = (id: any) => {
  disabledDialog.value = true;
  disabledID.value = id;
};

const showTooltip = (id: any) => {
  isHovered.value = id;
};

const hideTooltip = (id: any) => {
  if (isHovered.value === id) {
    isHovered.value = null;
  }
};

const handleData = () => {
  getAllClients();
};

</script>

<template>
  <section>
    <VCard title="Client List" v-if="userRole == 'Admin' || userRole == 'God Mode'">
      <VCardText class="d-flex flex-wrap py-4 px-4 gap-2">
        <div
          class="me-3"
          style=" display: flex;width: 10.5rem; align-items: center;"
        >
          <v-label style="font-size: small;" class="mr-1"
            ><strong>Show</strong></v-label
          >
          <VSelect
            v-model="selected"
            :items="perPages"
            @update:modelValue="updatePage"
          />
          <v-label style="font-size: small;" class="ml-1"
            ><strong>entries</strong></v-label
          >
        </div>
        <v-spacer />

        <!-- 👉 PDF -->
        <VBtn color="error" @click.prevent="generatePdf(tableRef, logo)"
          ><VIcon>tabler:file-type-pdf</VIcon></VBtn
        >

        <!-- 👉 Excel -->
        <VBtn @click.prevent="exportToExcel(tableRef)"
          ><VIcon>tabler:file-spreadsheet</VIcon></VBtn
        >

        <!-- 👉 Print -->
        <VBtn @click.prevent="print" color="warning"
          ><VIcon>tabler:printer</VIcon></VBtn
        >
        <v-spacer />
        <div
          class="app-user-search-filter d-flex align-center flex-wrap gap-4 justify-end"
        >
          <!-- 👉 Search  -->
          <div style=" display: flex;width: 17rem; align-items: center;">
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
      </VCardText>

      <VTable ref="tableRef">
        <VCol hidden class="mb-5">
          <VImg :src="logo" alt="Dit.Ads Logo" class="logo" />
        </VCol>
        <thead>
          <tr>
            <th>No.</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Contact Number</th>
            <th>Status</th>
            <!-- <th>School</th>
            <th>Course</th>
            <th>School Type</th> -->
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, index) in paginatedClientData" :key="row.id">
            <td>{{ index + 1 }}</td>
            <td>{{ row.firstname }} {{ row.middlename }} {{ row.lastname }}</td>
            <td>
              {{ row.users?.email }}
            </td>
            <td>
              {{ row.contact }}
            </td>
            <td>
              <VChip
                color="success"
                variant="outlined"
                class="font-weight-medium"
                size="small"
                v-if="
                  row.status != null &&
                  row.status == 'Active'
                "
                >{{ row.status }}</VChip
              >
              <VChip color="error"
                variant="outlined"
                class="font-weight-medium"
                size="small"
                v-if="
                  row.status != null &&
                  row.status == 'Disabled'
                "
                >{{ row.status }}</VChip>
            </td>
            <!-- <td>
              {{ row.school }}
            </td>
            <td>{{ row.course }}</td>
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
            </td> -->
            <td>
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
                    <VListItem
                      :to="'/admin/client/view/' + row.id"
                    >
                    <template #default>
                      <VIcon>material-symbols:visibility-outline-rounded</VIcon>
                      View
                    </template>
                  </VListItem>
                    <VListItem
                      v-if="row.status == 'Disabled'"
                      @click="activeform(row.id)"
                    >
                    <template #default>
                      <VIcon>material-symbols:interactive-space-outline</VIcon>
                      Active
                    </template>
                    </VListItem>
                    <VListItem
                      v-if="row.status == 'Active'"
                      @click="disabledform(row.id)"
                    >
                    <template #default>
                      <VIcon>ic:outline-person-add-disabled</VIcon>
                      Disabled
                    </template>
                    </VListItem>
                  </VList>
                </VMenu>
              </VBtn>
            </td>
          </tr>
        </tbody>
        <!-- 👉 table footer  -->
        <tfoot v-if="clientsData?.length == 0">
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

      <VPagination :length="totalPages" v-model="paginationDataClientPage" />
    </VCard>
    <VDialog
      fullscreen
      v-model="printTable"
      transition="dialog-bottom-transition"
    >
      <printClient :Data="paginatedClientData" />
    </VDialog>

    <VDialog v-model="activeDialog" scrollable max-width="400">
      <active
        @close="activeDialog = false"
        @handledata="handleData"
        :activeID="activeID"
      />
    </VDialog>

    <VDialog v-model="disabledDialog" scrollable max-width="400">
      <disabled
        @close="disabledDialog = false"
        @handledata="handleData"
        :disabledID="disabledID"
      />
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
