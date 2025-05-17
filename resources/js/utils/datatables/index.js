import jszip from 'jszip';
import DataTable from 'datatables.net-dt';
import 'datatables.net-buttons-dt';
import 'datatables.net-buttons/js/buttons.colVis.mjs';
import 'datatables.net-buttons/js/buttons.html5.mjs';

import 'datatables.net-dt/css/dataTables.dataTables.min.css';
import 'datatables.net-buttons-dt/css/buttons.dataTables.min.css';

//  Set up buttons for DataTable
DataTable.Buttons.jszip(jszip);

//  Ensure that the DataTable is available globally
window.DataTable = DataTable;

//  Importing the config file to ensure it is executed
import './config';