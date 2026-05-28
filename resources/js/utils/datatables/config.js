import { Eta } from "eta"
import { ulid } from 'ulid'

/* -----------------------------------------------------------------
 * Create a function to clone the header so as to allow filtering
 * ------------------------------------------------------------------
 */
const eta = new Eta({
    cache: true,
    views: '',
    autoEscape: true,
    useWith: false,
    async: false,
});

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
            return meta.row + (type === 'display' ? meta.settings._iDisplayStart : 0) + 1;
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
            //  Define the actions array
            let actions = [];

            //  Loop through the actions in the row
            for (const action of Object.values(row.actions || {})) {
                //  Build the action container
                let htmlString = `<div ${action.containerAttributes || ''}>`;

                //  Get the button if it exists
                let button = action.button;

                //  Get the template if it exists
                let template = action.template;

                //  Append the buttons to the html string if it exists
                if (![undefined, null].includes(button)) {
                    htmlString += `<a ${button.attributes}">${button.label}</a>`;
                }

                //  If the template is not defined, skip it
                if (![undefined, null].includes(template)) {
                    //  Get the raw html
                    let rawHtml = template.html || '';

                    //  Generate the html for the template
                    htmlString += (template.rendered || false) ? rawHtml : eta.renderString(rawHtml, {
                        id: ulid().toLowerCase(),
                        row: row,
                        params: template.parameters || {}
                    });
                }

                //  Add the html string to the actions array
                actions.push(htmlString += '</div>');
            }

            //  Return the html
            return `<div class="action-container">${actions.join('')}</div>`;
        }
    };
};

/* -----------------------------------------------------------------
 * Create a function to add a select filter to the column
 * ------------------------------------------------------------------
 */
let createSelectFilter = function (cell, column, className) {
    // Create select element and add event listener
    let select = document.createElement('select');

    //  Add a class to the select element
    select.className = className;

    //  Add a data-dt-column attribute to the select element
    select.setAttribute('dtt-search-filter', column.index());

    //  Add a default option
    select.add(new Option('Show All', ''));

    //  Add an event listener to the select element
    select.addEventListener('change', function () {
        column.search(this.value, { exact: true }).draw();
    });

    //  Add list of options
    column.data()
        .unique()
        .map((item, index) => item?.value ?? item)
        .unique()
        .sort()
        .each(value => select.add(new Option(value, value)));

    //  Append to the filter row cell
    cell.appendChild(select);
};

/* -----------------------------------------------------------------
 * Create a function to add an input filter to the column
 * ------------------------------------------------------------------
 */
let createInputFilter = function (cell, column, className) {
    //  Create an input element
    let input = document.createElement('input');

    //  Add a class to the input element
    input.className = className;

    //  Add a data-dt-column attribute to the input element
    input.setAttribute('dtt-search-filter', column.index());

    //  Add a placeholder to the input element
    input.placeholder = 'Search...';

    //  Add an event listener to the input element
    input.addEventListener('keyup', function () {
        column.search(this.value).draw();
    });

    //  Append to the filter row cell
    cell.appendChild(input);
};
/* -----------------------------------------------------------------
 * Create a function to hide the loader and show the datatable
 * ------------------------------------------------------------------
 */
window.hideLoader = function() {
    //  Hide the loader
    document.querySelector('.dt-tables-loader').style.display = 'none';

    //  Show the datatable container
    document.querySelector('.dt-tables-container').style.display = 'block';
};

/* -----------------------------------------------------------------
 * Create a function to add the buttons to the datatable
 * ------------------------------------------------------------------
 */
window.buttons = function(buttons, theme, title) {
    //  Return an empty buttons array if the config is empty
    if (buttons.length === 0) {
        return [];
    }

    //  Define the configurations
    let options = [
        {
            extend: 'copy',
            name: 'copy',
            text: 'Copy',
            title: title,
            footer: false,
            className: theme.buttons,
            exportOptions: {
                orthogonal: 'sort',
            },
        },
        {
            extend: 'excel',
            name: 'excel',
            text: 'Excel',
            filename: title,
            title: title,
            footer: false,
            className: theme.buttons,
            exportOptions: {
                orthogonal: 'sort',
                columns: ':visible:not(th.exclude-from-export)',
            }
        },
        {
            extend: 'colvis',
            name: 'colvis',
            text: 'Hide Columns',
            className: theme.buttons,
            postfixButtons: ['colvisRestore'],
        }
    ];

    //  Return the buttons
    return options.filter(option => buttons.includes(option.extend));
};

/* -----------------------------------------------------------------
 * Build the columns for the datatable
 * ------------------------------------------------------------------
 */
window.columns = function (tableID, config) {
    return Array.from(document.querySelectorAll(`#${tableID} thead tr:first-of-type th`)).map(function (th) {
        //  Get the data title of the column.
        let dtTitle = th.getAttribute('dtt-title');

        //  Get the title of the column
        let title = dtTitle
            ? dtTitle.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
            : th.innerHTML;

        //  Define a column
        let column;

        //  Handle the case where the column is a row index
        if (th.hasAttribute('dtt-row-index')) {
            column = rowIndex(title);

        //  Handle the case where the column is an action button
        } else if (th.hasAttribute('dtt-actions')) {
            column = actionButtons(title);

        //  Handle the default case
        } else {
            //  Get the name of the column
            let name = title ? title.replace(/\s+/g, '_').toLowerCase() : null;

            //  Get the first config object that matches the name
            let configObj = config.find(obj => obj.key === name);

            //  Define the column configuration
            column = {
                name: name.replace(/[ ]/g, '_'),
                data: name,
                render: function (item, type, row, meta) {
                    //  Get the value
                    let value = item?.value ?? item?.display ?? item;

                    //  Handle displaying the value based on the type
                    if (type === 'display' && item?.display) {
                        return eta.renderString(item?.display, {
                            id: ulid().toLowerCase(),
                            row: row,
                        });
                    }

                    //  Return the required value
                    return value;
                },
                createdCell: function (td, data, rowData, row, col) {
                    //  Get the cell data
                    let cellData = rowData[name];

                    //  Check if the cell data is an object and add the classes if they exist
                    if (typeof cellData === 'object' && cellData?.classes) {
                        //  Remove empty strings from the classes array
                        const validClasses = cellData.classes.split(/\s+/).filter(c => c?.trim());

                        //  Add the classes to the cell
                        td.classList.add(...validClasses);
                    }
                }
            };
        }
        
        //  dtt-hidden: keep the column in the dataset but hide it from the UI
        if (th.hasAttribute('dtt-hidden')) {
            column.visible = false;
        }

        //  Return the column configuration
        return column;
    });
};

/* -----------------------------------------------------------------
 * Set up the styling for the datatable
 * ------------------------------------------------------------------
 */
window.setupStyling = function (theme) {
    //  Remove the classes from the buttons
    document.querySelectorAll('.dt-button').forEach(el => el.classList.remove('dt-button'));

    //  Remove dt-search and dt-input classes from the search input
    document.querySelectorAll('.dt-search').forEach(el => {
        el.classList.remove('dt-search');
        el.classList.add('dt-tables-search');
    });

    //  Add the classes from the datatable search input
    document.querySelectorAll('.dt-input').forEach(el => {
        el.classList.remove('dt-input')
        el.classList.add(...theme.input.split(/\s+/).filter(c => c?.trim()));
    });
};

/* -----------------------------------------------------------------
 * Set up the filters for the datatable
 * ------------------------------------------------------------------
 */
window.setupFilters = function (api, config, theme) {
    //  Loop through the columns and add a filter to each column
    if (config.filter(column => column.search_type !== 'none').length > 0) {
        //  Clone the header after DataTables has initialised so the filter row
        //  is not captured in export snapshots (DataTables reads headers at init time)
        let tableId = api.table().node().id;

        //  Clone the header to create a new row for filters and append it to the thead
        cloneHeader(tableId);

        //  Query the live DOM for the original header row cells to determine column positions
        let originalCells = Array.from(document.querySelectorAll(`#${tableId} thead tr:first-child th`));

        //  Query the live DOM for the filter row cells
        let filterCells = Array.from(document.querySelectorAll(`#${tableId} thead tr.selection-row th`));

        //  Loop through the columns in the datatable
        api.columns().every(function () {
            //  Get the column
            let column = this;

            //  Find the DOM position of this column's header in the original row
            let cellIndex = originalCells.indexOf(column.header(0));

            //  If the column header is not found, skip to the next iteration
            if (cellIndex === -1) { return; }

            //  Get the corresponding filter cell
            let cell = filterCells[cellIndex];

            //  If the cell is not found, skip to the next iteration
            if (!cell) { return; }

            //  Clear the cell
            cell.innerHTML = '';

            //  Get the name of the column
            let name = column.name();

            //  Get the first config object that matches the name
            let configObj = config.find(obj => obj.key === name);

            //  Get the search type
            let searchType = configObj ? configObj.search_type : null;

            //  Check if the column is in the select items
            switch (searchType) {
                case 'select':
                    createSelectFilter(cell, column, theme.select);
                    break;
                case 'input':
                    createInputFilter(cell, column, theme.input);
                    break;
            }
        });
    }
};

/* -----------------------------------------------------------------
 * Set up the search params for the datatable
 * ------------------------------------------------------------------
 */
window.setupSearchParams = function (api) {
    //  Get the table id
    let tableId = api.table().node().id;

    //  Get the columns in the table
    let columns = api.settings()[0].aoColumns;

    //  Loop through the search params
    new URLSearchParams(window.location.search).forEach((value, key) => {
        //  Get the column index
        let index = columns.findIndex(col => col.name === key.replace(/-/g, '_'));

        console.log({ key, value, index });

        //  Get the column
        let column = api.column(index);

        //  Get the element
        let element = document.querySelector(`#${tableId} [dtt-search-filter="${index}"]`);

        //  If the element is found, set the value
        if (element) {
            //  Set the value of the element
            element.value = value;

            //  Dispatch the event
            column.search(value).draw();
        }
    });
};