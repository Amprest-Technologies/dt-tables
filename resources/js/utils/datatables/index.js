import jszip from 'jszip';
import DataTable from 'datatables.net-bs5';
import 'datatables.net-buttons-bs5';
import 'datatables.net-buttons/js/buttons.colVis.mjs';
import 'datatables.net-buttons/js/buttons.html5.mjs';

import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';
import 'datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css';

//  Set up buttons for DataTable
DataTable.Buttons.jszip(jszip);

//  Ensure that the DataTable is available globally
window.DataTable = DataTable;

//  Importing the config file to ensure it is executed
import './config';