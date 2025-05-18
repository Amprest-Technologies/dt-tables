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

    //  Add a class to the cloned <tr>
    tr.classList.add('selection-row');

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
        className: 'row-index',
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
        className: 'exclude-from-export',
        render: (data, type, row, meta) => {
            //  Check if the row has actions
            const html = Object.values(row.actions || {})
                .map(button => `<a ${button.attributes}">${button.label}</a>`)
                .join('');

            //  Return the html
            return `<div class="button-container">${html}</div>`;
        }
    };
};

/* -----------------------------------------------------------------
 * Create a function to add a select filter to the column
 * ------------------------------------------------------------------
 */
let createSelectFilter = function (column, className) {
    // Create select element and add event listener
    let select = document.createElement('select');

    //  Add a class to the select element
    select.className = className;

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
let createInputFilter = function (column, className) {
    //  Create an input element
    let input = document.createElement('input');

    //  Add a class to the input element
    input.className = className;

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
window.setupDataTable = function (tableId, columns) {
    if (columns.filter(column => column.search_type !== 'none').length > 0) {
        cloneHeader(tableId);
    }
};

/* -----------------------------------------------------------------
 * Create a function to add the buttons to the datatable
 * ------------------------------------------------------------------
 */
window.buttons = function(api, config, theme, title) {
    //  Return an empty buttons array if the config is empty
    if (config.length === 0) {
        return [];
    }

    //  Define the configurations
    let buttonsConfigurations = [
        {
            extend: 'copy',
            text: 'Copy to Clipboard',
            title: title,
            footer: false
        },
        {
            extend: 'csv',
            text: 'Export as CSV',
            filename: title,
            footer: false,
            exportOptions: {
                columns: ':visible:not(th.exclude-from-export)'
            }
        },
        {
            extend: 'excel',
            text: 'Export as Excel',
            filename: title,
            title: title,
            footer: false,
            exportOptions: {
                columns: ':visible:not(th.exclude-from-export)'
            }
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
            className: theme.buttons,
            buttons: Object.values(config).map((item) => {
                return buttonsConfigurations.find(button => button.extend === item);
            })
        }
    ];
};

/* -----------------------------------------------------------------
 * Build the columns for the datatable
 * ------------------------------------------------------------------
 */
window.columns = function (tableID, config) {
    return Array.from(document.querySelectorAll(`#${tableID} thead tr:first-of-type th`)).map(function (th) {
        //  Get the data title of the column.
        let dtTitle = th.getAttribute('dtt-data-title');
        
        //  Get the title of the column
        let title = dtTitle
            ? dtTitle.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
            : th.innerHTML;

        //  Handle the case where the column is a row index
        if (th.hasAttribute('dtt-row-index')) {
            return rowIndex(title);
        }

        //  Handle the case where the column is an action button
        if (th.hasAttribute('dtt-actions')) {
            return actionButtons(title);
        }

        //  Get the name of the column
        let name = title ? title.replace(/\s+/g, '_').toLowerCase() : null;

        //  Get the first config object that matches the name
        let configObj = config.find(obj => obj.key === name);

        //  Handle the rest of the columns
        return {
            data: name,
            name: name,
            className: configObj ? configObj.classes : '',
            render: function(data, type, row, meta) {
                //  Deal with objects
                if (typeof data === 'object') {
                    //  Check the object has a className property
                    if (data?.class && type === 'display') {
                        return `<span class="${data.class}">${data.value}</span>`;
                    }

                    //  Else return the value as is
                    return data?.value ?? '';
                }

                //  Else return the data as is
                return data;
            }
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
window.setupFilters = function (api, config, theme) {
    //  Loop through the columns and add a filter to each column
    if (config.filter(column => column.search_type !== 'none').length > 0) {
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
                    createSelectFilter(column, theme.select);
                    break;
                case 'input':
                    createInputFilter(column, theme.input);
                    break;
            }
        });
    }
};