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

/* -----------------------------------------------------------------
 * Create a function to add the buttons to the datatable
 * ------------------------------------------------------------------
 */
let buttons = function(config) {
    //  Return an empty buttons array if the config is empty
    if (config.length === 0) {
        return [];
    }

    //  Define the configurations
    let buttonsConfigurations = [
        {
            extend: 'copy',
            text: 'Copy to Clipboard',
            footer: false
        },
        {
            extend: 'csv',
            text: 'Export as CSV',
            footer: false
        },
        {
            extend: 'excel',
            text: 'Export as Excel',
            footer: false
        },
        {
            extend: 'colvis',
            text: 'Column Visibility',
            postfixButtons: ['colvisRestore'],
        }
    ];

    //  Return the buttons
    return [
        {
            extend: 'collection',
            text: 'Table Actions',
            className: 'btn btn-sm btn-primary',
            buttons: Object.values(config).map((item) => {
                return buttonsConfigurations.find(button => button.extend === item);
            })
        }
    ];
};

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
 * Create a function to initialize the datatable
 * ------------------------------------------------------------------
 */
window.layout = function(config) {
    return {
        top: {
            className: 'top-row d-flex justify-content-between align-items-center w-full px-3 border-bottom pb-0',
            features: [ { buttons: buttons(config) }, 'pageLength' ]
        },
        
        bottom: {
            className: 'bottom-row d-flex justify-content-between align-items-center w-full px-3',
            features: ['info', 'paging']
        },
        
        topStart: null,
        topEnd: null,
        bottomStart: null,
        bottomEnd: null,
    };
};

/* -----------------------------------------------------------------
 * Build the columns for the datatable
 * ------------------------------------------------------------------
 */
window.columns = function (tableID, config) {
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

        //  Get the name of the column
        let name = innerHTML ? innerHTML.replace(/\s+/g, '_').toLowerCase() : null;

        //  Get the first config object that matches the name
        let configObj = config.find(obj => obj.key === name);

        //  Handle the rest of the columns
        return {
            data: name,
            name: name,
            title: innerHTML,
            type: configObj ? configObj.data_type : 'string',
        };
    });
};

/* -----------------------------------------------------------------
 * Set up the styling for the datatable
 * ------------------------------------------------------------------
 */
window.setupStyling = function () {
    //  Handle buttons
    document.querySelectorAll('.dt-button').forEach(el => el.classList.remove('dt-button'));
};

/* -----------------------------------------------------------------
 * Set up the filters for the datatable
 * ------------------------------------------------------------------
 */
window.setupFilters = function (api, config) {
    //  Loop through the columns and add a filter to each column
    api.columns().every(function () {
        //  Get the column
        let column = this;

        //  Get the header element
        column.header(1).innerHTML = '';

        //  Get the name of the column
        let name = column.name();

        //  Get the first config object that matches the name
        let configObj = config.find(obj => obj.key === name);

        //  Get the search type
        let searchType = configObj ? configObj.search_type : null;

        //  Check if the column is in the select items
        switch (searchType) {
            case 'select':
                createSelectFilter(column);
                break;
            case 'input':
                createInputFilter(column);
                break;
        }
    });
};