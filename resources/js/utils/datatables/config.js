import { Eta } from "eta"
import { ulid } from 'ulid'

/* -----------------------------------------------------------------
 * Create a function to clone the header so as to allow filtering
 * ------------------------------------------------------------------
 */
const eta = new Eta({
    cache: true,
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
            //  Define the actions array
            let buttons = [];

            //  Define the template array
            let templates = [];

            //  Loop through the actions in the row
            for (const button of Object.values(row.actions || {})) {
                //  Handle action buttons
                buttons.push(`<a ${button.attributes}">${button.label}</a>`);

                //  Get the template if it exists
                let template = button.template;

                //  If the template is not defined, skip it
                if (![undefined, null].includes(template)) {
                    //  Get the raw html
                    let rawHtml = template.html || '';

                    //  Generate the html for the template
                    let html = (template.rendered || false) ? rawHtml : eta.renderString(rawHtml, {
                        id: ulid().toLowerCase(),
                        row: row,
                        params: button.template.parameters || {}
                    });

                    //  Push the template html to the templates array
                    templates.push(html);
                }
            }

            //  Return the html
            return `
                <div class="button-container">${buttons.join('')}</div>
                <div class="template-container">${templates.join('')}</div>
            `;
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
 * Create a function to hide the loader and show the datatable
 * ------------------------------------------------------------------
 */
window.hideLoader = function() {
    document.querySelector('.dt-tables-loader').style.display = 'none';
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
            text: 'Copy',
            title: title,
            footer: false,
            className: theme.buttons,
        },
        {
            extend: 'excel',
            text: 'Excel',
            filename: title,
            title: title,
            footer: false,
            className: theme.buttons,
            exportOptions: {
                columns: ':visible:not(th.exclude-from-export)'
            }
        },
        {
            extend: 'colvis',
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
            data: function (row, type, val, meta) {
                //  Get the value of the column
                let data = row[name];

                //  Return the required value
                return data?.value ?? data;
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
        el.style.display = 'flex';
    });

    //  Add the classes from the datatable search input
    document.querySelectorAll('.dt-input').forEach(el => {
        el.classList.remove('dt-input')
        el.classList.add(...theme.input.split(' '));
    });
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