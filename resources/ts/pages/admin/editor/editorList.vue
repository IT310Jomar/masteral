<script lang="ts" setup>
import { environment } from "@/environments/environment";
import axios from "axios";
import logo from "@images/DITADS Logo_PNG.png";
import nodata from "@images/no-data-found.png";
import { ref } from "vue";
import { debounce } from "lodash";
import jsPDF, { RGBAData } from "jspdf";
import "jspdf-autotable";
import { exportToExcel } from "@/excellClient";
import addeditor from "@/pages/admin/editor/dialog/add_editor-dialog.vue";
import editeditor from "@/pages/admin/editor/dialog/edit_editor-dialog.vue";
import printEditor from "@/pages/admin/editor/print/editorPrintDialog.vue";
import Dialog from "@/pages/dialog.vue";
import active from "@/pages/admin/editor/dialog/active-dialog.vue";
import disabled from "@/pages/admin/editor/dialog/disabled-dialog.vue";


const editorsData = ref([]);
const tableRef = ref();
const searchQuery = ref(null);
const currentPage = ref(1);
const perPages = [5, 20, 50, 100, 500];
const itemsPerPage = ref(perPages[0]);
const selectedPerPage = ref(itemsPerPage.value);
const paginationDataEditorPage = ref(currentPage);
const printTable = ref();
const selected = ref(itemsPerPage);
const profile_image = ref()

//Pending Pagination
const paginatedEditorsData = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage.value;
  const endIndex = startIndex + itemsPerPage.value;
  return editorsData.value.slice(startIndex, endIndex);
});

let totalPages = computed(() => {
  if (editorsData.value.length > 0) {
    return Math.ceil(editorsData.value.length / itemsPerPage.value);
  } else {
    return 0;
  }
});

//handle the pagination in pending request that will emitted to the parent component
function updatePage() {
  paginationDataEditorPage.value = 1;
  // console.log(paginationDataOfficerPage.value);
}

//Search for Regulatory Officer
const searchEditor = () => {
  axios
    .get(environment.API_URL + "auth/admin/search-editors", {
      params: {
        query: searchQuery.value,
        token: token
      },
    })
    .then((response) => {
      if (response.data.success) {
        editorsData.value = response.data.searchEditor;
      }
    })
    .catch((error) => {
      console.log("Error");
    });
};

// watch the data input search by the user for officer
const debouncedSearch = debounce(() => {
  if (searchQuery.value === "") {
    getAllEditor();
  } else {
    searchEditor();
  }
});
watch([searchQuery], debouncedSearch);

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
      orientation: "landscape",
      unit: "mm",
      format: "legal",
    });
    const data = [tableRef];
    const table = data[0].$el.querySelector("table");
    const thead = table.querySelector("thead");

    // Define margins
    const marginLeft = 50;
    const marginRight = 10;
    const marginTop = 5;
    const marginBottom = 0;
    const imageColumnIndex = 1; // Adjust the index as needed
    const logoX = marginLeft;
    const logoY = marginTop;
    const logoWidth = 40;
    const logoHeight = 40;

    const title = "DIT.ADS- Editor List Management Report";
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

    // Modify the datas array to include an image column
    const datasWithImages = datas.map((rowData) => {

      rowData.splice(imageColumnIndex,0);

      return rowData;
    });

    
    // Increase the column count in the autoTable configuration to accommodate the image column
    const columnCount = filteredHeaders.length + 1;

    doc.autoTable({
      head: [filteredHeaders],
      body: datasWithImages, // Use original data
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
      columnStyles: {
        [columnCount - 1]: { // Apply special styles to the last column (image column)
          halign: "center", // Center align the content in the image column
        },
      },
      // Add a hook to customize cell rendering
      didDrawCell: function (data: { column: { index: string | number }; row: { index: string | number }; cell: { x: number; y: number; width: number; height: number } }) {
        // Check if we are in the image column
        if (data.column.index === imageColumnIndex) {
          const imageSrc = logo; // Use the same image for each row
          // const imageSrc = datasWithImages[data.row.index][imageColumnIndex];
          
          // You can add additional content or styling in the image column if needed
          // For example, center-align the image and specify its width and height
          doc.addImage(imageSrc, "JPEG", data.cell.x, data.cell.y, data.cell.width, data.cell.height);
        }
      },
    });

    doc.save("Dit.Ads.pdf");
  } catch (error) {
    console.error("Error generating PDF:", error);
  }
}

const add_editor = ref(false);
const edit_editor = ref(false);
const activeDialog = ref(false);
const disabledDialog = ref(false);
const rowData = ref();
const activeID = ref();
const disabledID = ref();

const addDialog = () => {
  add_editor.value = true;
};

const editDialog = (row: any) => {
  rowData.value = row;
  edit_editor.value = true;
};

const activeform = (id: any) => {
  activeDialog.value = true;
  activeID.value = id;
}

const disabledform = (id: any) => {
  disabledDialog.value = true;
  disabledID.value = id;
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

const handleData = () => {
  getAllEditor();
};
const token = JSON.parse(localStorage.getItem("accessToken") || "{}");
//get function for ghetting the data of clients
const getAllEditor = () => {
  axios
    .get(environment.API_URL + "auth/admin/get-all-editors",{
      params: {
        token: token
      }
    })
    .then((response) => {
      if (response.data.success) {
        editorsData.value = response.data.editor;
        // console.log(editorsData.value);
      }
    });
};
onMounted(() => {
  getAllEditor();
});
</script>

<template>
  <section>
    <VCard title="Editor List">
      <VCardText>
        <VRow>
          <VCol style="text-align: right">
            <VBtn
              @click.prevent="addDialog"
              prepend-icon="tabler-plus"
              color="primary"
              >Add Editor</VBtn
            >
          </VCol>
        </VRow>
      </VCardText>
      <VCardText class="d-flex flex-wrap py-4 px-4 gap-2">
        <div
          class="me-3"
          style="width: 10.5rem; display: flex; align-items: center"
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
          <div style="width: 17rem; display: flex; align-items: center">
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
            <th class="text-center">Image</th>
            <th>Full Name</th>
            <th>Contact</th>
            <th>Account Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, index) in paginatedEditorsData" :key="row.id">
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
                    :to="'/admin/editor/view/' + row.id"
                  >
                  <template #default>
                    <VIcon>material-symbols:visibility-outline-rounded</VIcon>
                    View
                  </template>
                  </VListItem>
                  <VListItem @click="editDialog(row)">
                    <template #default>
                      <VIcon>heroicons:pencil-square</VIcon>
                      Edit
                    </template>
                  </VListItem>
                  <VListItem v-if="row.status == 'Disabled'" @click="activeform(row.id)">
                    <template #default>
                      <VIcon>material-symbols:interactive-space-outline</VIcon>
                      Active
                    </template>
                  </VListItem>
                  <VListItem v-if="row.status == 'Active'" @click="disabledform(row.id)">
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
        <tfoot v-if="editorsData?.length == 0">
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
                    />Z
                <VProgressLinear color="primary" indeterminate reverse />
              </div>
            </td>
          </tr>
        </tfoot>
      </VTable>

      <VPagination :length="totalPages" v-model="paginationDataEditorPage" />
    </VCard>
    <VDialog
      fullscreen
      v-model="printTable"
      transition="dialog-bottom-transition"
    >
    <printEditor :Data="paginatedEditorsData" />
  </VDialog>

    <VDialog v-model="add_editor" scrollable width="60vh">
      <addeditor @close="add_editor = false" @handledata="handleData" />
    </VDialog>

    <VDialog v-model="edit_editor" scrollable width="60vh">
      <editeditor @close="edit_editor = false" @handledata="handleData" :row="rowData" />
    </VDialog>

    <VDialog v-model="activeDialog" scrollable max-width="400">
      <active @close="activeDialog = false" @handledata="handleData" :activeID="activeID" />
    </VDialog>

    <VDialog v-model="disabledDialog" scrollable max-width="400">
      <disabled @close="disabledDialog = false" @handledata="handleData" :disabledID="disabledID" />
    </VDialog>
  </section>
</template>
<style scoped>
.logo {
  max-width: 100px;
}

.logo-content {
  padding-left: 20px; /* Adjust as needed */
  color: #fff; /* Adjust text color based on your logo */
}

.logo-title {
  font-size: 18px;
  font-weight: bold;
  margin-bottom: 5px;
}

.logo-body {
  font-size: 14px;
  margin: 0;
}

.image-upload {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.circle-container {
  width: 230px;
  height: 230px;
  border-radius: 50%;
  overflow: hidden;
  margin-top: 10px;
  background-color: lightgray;
  display: flex;
  justify-content: center;
  align-items: center;
}

.circle-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}

.icon-container {
  position: relative;
}

.tooltip {
  position: absolute;
  background: rgba(0, 0, 0, 0.8);
  color: white;
  padding: 5px;
  border-radius: 5px;
  top: -10px; /* Adjust this value to position the tooltip as desired */
  left: 0;
}
</style>
