<script lang="ts" setup>
import { environment } from "@/environments/environment";
import { exportToExcel } from "@/excellClient";
import addservices from "@/pages/admin/services_&_payment/dialog/addservices-dialog.vue";
import editservices from "@/pages/admin/services_&_payment/dialog/edit_services-dialog.vue";
import loadingScreen from "@/pages/loading/Validationloading.vue";
import nodata from "@images/no-data-found.png";
import logo from "@images/DITADS Logo_PNG.png";
import axios from "axios";
import jsPDF from "jspdf";
import "jspdf-autotable";
import { debounce } from "lodash";
import { computed, onMounted, ref, watch } from "vue";
import printServices from "./print/servicesPrintDialog.vue";

const userRole = JSON.parse(localStorage.getItem("userRole") || "{}");
const token = JSON.parse(localStorage.getItem("accessToken") || "{}");
const servicesData = ref([]);
const tableRef = ref();
const searchQuery = ref(null);
const currentPage = ref(1);
const perPages = [5, 20, 50, 100, 500];
const itemsPerPage = ref(perPages[0]);
const selectedPerPage = ref(itemsPerPage.value);
const paginationDataServicesPage = ref(currentPage);
const printTable = ref();
const selected = ref(itemsPerPage);

//Pending Pagination
const paginatedServicesData = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage.value;
  const endIndex = startIndex + itemsPerPage.value;
  return servicesData.value.slice(startIndex, endIndex);
});

let totalPages = computed(() => {
  if (servicesData.value.length > 0) {
    return Math.ceil(servicesData.value.length / itemsPerPage.value);
  } else {
    return 0;
  }
});

//handle the pagination in pending request that will emitted to the parent component
function updatePage() {
  paginationDataServicesPage.value = 1;
  // console.log(paginationDataOfficerPage.value);
}

//Search for Regulatory Officer
const searchServices = () => {
  axios
    .get(environment.API_URL + "auth/admin/search-services-data", {
      params: {
        query: searchQuery.value,
        token: token,
      },
    })
    .then((response) => {
      if (response.data.success) {
        servicesData.value = response.data.searchService;
      }
    })
    .catch((error) => {
      console.log("Error");
    });
};

// watch the data input search by the user for officer
const debouncedSearch = debounce(() => {
  if (searchQuery.value === "") {
    getAllServices();
  } else {
    searchServices();
  }
}, 300);
watch([searchQuery], debouncedSearch);

//get function for ghetting the data of clients
const getAllServices = () => {
  axios
    .get(environment.API_URL + "auth/admin/get-all-services-data", {
      params: {
        token: token
      }
    })
    .then((response) => {
      if (response.data.success) {
        servicesData.value = response.data.servicedata;
        // console.log(servicesData.value);
      }
    });
};
onMounted(() => {
  getAllServices();
});

const loading = ref(false);

const loaderTrue = () => {
  loading.value = true;
};

const loaderFalse = () => {
  loading.value = false;
};

const post_status = (row: any) => {
  loaderTrue();
  axios.put(environment.API_URL + "auth/admin/status-services-data/" + row.id, {
    status: row.status.toString(),
  })
  .then((response) => {
    if (response.data.success){
      loaderFalse();
    }
  })
}

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

    const title = "DIT.ADS- Services List Management Report";
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
const add_payment = ref(false);
const edit_payment = ref(false);

const addDialog = () => {
  add_payment.value = true;
};

const methods = ref()

const editform = (row: any) => {
  edit_payment.value = true;
  methods.value = row;
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
  getAllServices();
};

</script>

<template>
  <section>
    <VCard v-if="userRole == 'Admin' || userRole == 'God Mode'">
      <VToolbar>
        <VToolbarTitle class="text-h6">Services List</VToolbarTitle>
        <VSpacer />
        <VBtn
          color="primary"
          variant="flat"
          @click="addDialog"
          class="mr-2"
        >
          <VIcon start size="16" icon="tabler-plus" />Add Service</VBtn
        >
      </VToolbar>
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
            <th class="text-center">No.</th>
            <th class="text-center">Name</th>
            <th class="text-center">Price</th>
            <th class="text-center">Status</th>
            <th class="text-center">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, index) in paginatedServicesData" :key="row.id">
            <td class="text-center">{{ index + 1 }}</td>
            <td class="text-center">
              <VChip
                color="blue"
                variant="outlined"
                ><v-icon start icon="carbon:settings-services"></v-icon><b
                  >{{ row.name }}
                  </b
                ></VChip
              >
            </td>
            <td class="text-center">
              {{ row.price }}
            </td>
            <td class="text-center">
              <VCol class="d-flex align-center justify-center">
                <VSwitch
                  v-model="row.status"
                  :label="row.status.toString()"
                  true-value="Available"
                  false-value="Not Available"
                  @change="post_status(row)"
                />
              </VCol>
            </td>
            <td class="text-center">
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
                      @click="editform(row)"
                    >
                      <template #default>
                        <VIcon>tabler-edit</VIcon>
                        Edit
                      </template>
                    </VListItem>
                  </VList>
                </VMenu>
              </VBtn>
            </td>
          </tr>
        </tbody>
        <!-- 👉 table footer  -->
        <tfoot v-if="servicesData?.length == 0">
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

      <VPagination :length="totalPages" v-model="paginationDataServicesPage" />
    </VCard>
    <VDialog
      fullscreen
      v-model="printTable"
      transition="dialog-bottom-transition"
    >
      <printServices :Data="paginatedServicesData" />
    </VDialog>

    <VDialog v-model="loading" max-width="300">
      <loadingScreen />
    </VDialog>

    <VDialog v-model="add_payment" scrollable width="60vh">
      <addservices @close="add_payment = false" @handledata="handleData" />
    </VDialog>

    <VDialog v-model="edit_payment" scrollable width="60vh">
      <editservices @close="edit_payment = false" @handledata="handleData" :row="methods" />
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
