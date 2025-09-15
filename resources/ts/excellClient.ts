import ExcelJS from 'exceljs';

export function exportToExcel(tableRef: { $el: { querySelector: (arg0: string) => any; }; }) {
  const table = tableRef.$el.querySelector('table');

  if (!table) {
    console.error('Table not found');
    return;
  }

  const workbook = new ExcelJS.Workbook();
  const worksheet = workbook.addWorksheet('Sheet1');

  // Define column widths (you can adjust these as needed)
  worksheet.columns = [
    { width: 10 },
    { width: 30 },
    { width: 20 },
    { width: 20 },
    { width: 20 },
    { width: 20 },
    { width: 20 },
  ];

  // Extract table data and headers
  const headerCells = table.querySelector('thead').querySelectorAll('th');
  const headers: any[] = [];

  // Filter out the Action column from headers
  headerCells.forEach((cell: { innerText: string; }) => {
    if (cell.innerText.trim() !== 'Action') {
      headers.push(cell.innerText.trim());
    }
  });

  worksheet.addRow(headers);

  const rows = table.querySelectorAll('tbody tr');
  rows.forEach((row: { querySelectorAll: (arg0: string) => any[]; }) => {
    const rowData: { type: string; link: any; }[] = [];
    const dataCells = row.querySelectorAll('td');

    // Filter out the Action column from row data
    dataCells.forEach((cell, index) => {
      if (headerCells[index].innerText.trim() !== 'Action') {
        if (cell.querySelector('img')) {
          const imgSrc = cell.querySelector('img').getAttribute('src');
          // Add a hyperlink to the image
          rowData.push({
            type: 'link',
            link: imgSrc,
          });
        } else {
          rowData.push(cell.innerText.trim());
        }
      }
    });

    worksheet.addRow(rowData);
  });

  // Create a buffer with the Excel file data
  workbook.xlsx.writeBuffer().then((buffer) => {
    const blob = new Blob([buffer], {
      type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'Dit.Ads.xlsx';
    a.click();
    window.URL.revokeObjectURL(url);
  });
}
