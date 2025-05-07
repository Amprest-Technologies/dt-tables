/* -----------------------------------------------------------------
 * Create a function to clone the header so as to allow filtering
 * ------------------------------------------------------------------
 */
let cloneHeader = function (tableId) {
    //  Get the table element
    let table = document.getElementById(tableId);

    //  Get the thead element
    let thead = table.querySelector('thead');

    //  Duplicate the tr element in the thead element
    let tr = thead.querySelector('tr').cloneNode(true);

    //  Add data-dt-order="disable" attribute to the cloned <tr>
    tr.setAttribute('data-dt-order', 'disable');

    // Append the cloned <tr> to the thead (or insert wherever needed)
    thead.appendChild(tr);
};

/* -----------------------------------------------------------------
 * Handle the row indexes for the datatable
 * ------------------------------------------------------------------
 */
let rowIndex = function (title) {
    return {
        data: null,
        name: 'row_index',
        title: title,
        className: 'text-center fw-bold',
        render: function (data, type, row, meta) {
            return meta.row + meta.settings._iDisplayStart + 1;
        }
    };
}

/*-----------------------------------------------------------------
 * Create add an action button to the last column
 * ------------------------------------------------------------------
 */
let actionButtons = function (title) {
    return {
        data: null,
        name: 'actions',
        title: title,
        orderable: false,
        searchable: false,
        render: (data, type, row, meta) => {
            //  Check if the row has actions
            const html = Object.values(row.actions || {}).map(button => {
                return `<a href="${button.url}" class="btn btn-sm btn-primary me-1" data-id="${row.id}">
                    ${button.label}
                </a>`;
            }).join('');

            //  Return the html
            return `<div class="w-100 d-flex justify-content-center">${html}</div>`;
        }
    };
};

/* -----------------------------------------------------------------
 * Create a function to add the buttons to the datatable
 * ------------------------------------------------------------------
 */
let buttons = function() {
    return [
        {
            extend: 'collection',
            text: 'Table Options',
            className: 'btn btn-sm btn-primary',
            buttons: [
                {
                    extend: 'copy',
                    text: 'Copy',
                    footer: false
                },
                {
                    extend: 'csv',
                    text: 'Export CSV',
                    footer: false
                },
                {
                    extend: 'excel',
                    text: 'Export Excel',
                    footer: false
                },
                {
                    extend: 'colvis',
                    text: 'Column Visibility',
                    postfixButtons: ['colvisRestore'],
                }
            ]
        }
    ];
};

/* -----------------------------------------------------------------
 * Create a function to add a select filter to the column
 * ------------------------------------------------------------------
 */
let createSelectFilter = function (column) {
    // Create select element and add event listener
    let select = document.createElement('select');

    //  Add a class to the select element
    select.className = 'form-control form-control-sm';

    //  Add a default option
    select.add(new Option('Show All', ''));
        
    //  Add an event listener to the select element
    select.addEventListener('change', function () {
        column.search(this.value, { exact: true }).draw();
    });

    // Add list of options
    column.data().unique().sort().each(function (item, index) {
        select.add(new Option(item, item));
    });

    //  Get the second tr element in the thead
    column.header(1).appendChild(select);
};

/* -----------------------------------------------------------------
 * Create a function to add an input filter to the column
 * ------------------------------------------------------------------
 */
let createInputFilter = function (column) {
    //  Create an input element
    let input = document.createElement('input');

    //  Add a class to the input element
    input.className = 'form-control form-control-sm';

    //  Add a placeholder to the input element
    input.placeholder = 'Search...';

    //  Add an event listener to the input element
    input.addEventListener('keyup', function () {
        column.search(this.value).draw();
    });

    //  Get the second tr element in the thead
    column.header(1).appendChild(input);
};

/* -----------------------------------------------------------------
 * Create a function to initialize the datatable
 * ------------------------------------------------------------------
 */
window.setupDataTable = function (tableId) {
    cloneHeader(tableId);
};

/* -----------------------------------------------------------------
 * Build the columns for the datatable
 * ------------------------------------------------------------------
 */
window.columns = function (tableID) {
    return Array.from(document.querySelectorAll(`#${tableID} thead tr:first-of-type th`)).map(function (th) {
        //  Get the inner html
        let innerHTML = th.innerHTML;

        //  Handle the case where the column is a row index
        if (th.hasAttribute('ldt-row-index')) {
            return rowIndex(innerHTML);
        }

        //  Handle the case where the column is an action button
        if (th.hasAttribute('ldt-actions')) {
            return actionButtons(innerHTML);
        }

        //  Handle the rest of the columns
        return {
            data: innerHTML ? innerHTML.replace(/\s+/g, '_').toLowerCase() : null,
            name: innerHTML ? innerHTML.replace(/\s+/g, '_').toLowerCase() : null,
            title: innerHTML,
        };
    });
};

/* -----------------------------------------------------------------
 * Create a function to set the layout of the datatable
 * ------------------------------------------------------------------
 */
window.layout = function (tableID) {
    return {
        topStart: { buttons: buttons() },
        topEnd: 'pageLength',
        bottomStart: 'info',
        bottomEnd: 'paging'
    }
};

/* -----------------------------------------------------------------
 * Set up the filters for the datatable
 * ------------------------------------------------------------------
 */
window.setupFilters = function (api) {
    //  Loop through the columns and add a filter to each column
    api.columns().every(function () {
        //  Get the column
        let column = this;

        //  Get the header element
        column.header(1).innerHTML = '';

        //  Check if the column is in the select items
        if(['payment_method', 'status', 'organization_name'].includes(column.name())) {
            createSelectFilter(column);
        }

        //  Add a search input to the column
        if(['tenant_name', 'reference_no'].includes(column.name())) {
            createInputFilter(column);
        }
    });
};