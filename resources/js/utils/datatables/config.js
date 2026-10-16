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
 * Create a function to build the filter row, one cell per column
 * ------------------------------------------------------------------
 */
let buildFilterRow = function (tableId, api) {
    //  Get the table element
    let table = document.getElementById(tableId);

    //  Get the thead element
    let thead = table.querySelector('thead');

    //  Create the filter row
    let tr = document.createElement('tr');

    //  Add data-dt-order="disable" attribute to the filter row
    tr.setAttribute('data-dt-order', 'disable');

    //  Add a class to the filter row
    tr.classList.add('selection-row');

    //  Add one cell per column, cloned from that column's own header cell
    api.columns().every(function () {
        tr.appendChild(this.header(0).cloneNode(true));
    });

    //  Append the filter row to the thead
    thead.appendChild(tr);

    //  Return the filter row so its cells can be addressed by column index
    return tr;
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
            return meta.row + (type === 'display' ? new DataTable.Api(meta.settings).page.info().start : 0) + 1;
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
                    htmlString += `<a ${button.attributes}>${button.label}</a>`;
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
 * Get the kind of filter a column has: 'select', 'input' or null
 * ------------------------------------------------------------------
 */
let filterType = function (config, remote, name) {
    //  Inline tables use the columns configured in the table's json
    if (! remote) {
        return config.find(obj => obj.key === name)?.search_type ?? null;
    }

    //  Server-side tables ignore the json: nothing to search means no filter
    if (! remote.searchable?.includes(name)) {
        return null;
    }

    //  Keys with options get a select, the rest an input
    return remote.selects?.includes(name) ? 'select' : 'input';
};

/* -----------------------------------------------------------------
 * Create a function to add a select filter to the column
 * ------------------------------------------------------------------
 */
let createSelectFilter = function (cell, column, className, remote) {
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
        //  Get the table node
        let node = column.table().node();

        //  Keep track of the columns filtered by an exact value
        node.dttExact = node.dttExact || new Set();

        //  if the value is set, add the column index to the set
        this.value
            ? node.dttExact.add(column.index()) 
            : node.dttExact.delete(column.index());

        //  Filter the column
        column.search(this.value, { exact: true }).draw();
    });

    //  Server-side tables hold one page, so the options come from the server
    if (remote) {
        //  Get the options
        remote.options(column.name()).then(options => {
            //  Add the options
            options.forEach(option => select.add(new Option(option.label, option.value)));

            //  Restore a search that was applied before the options arrived (e.g. from the URL)
            select.value = column.search() || '';
        });

    //  Otherwise add the list of options from the loaded rows
    } else {
        column.data()
            .unique()
            .map((item, index) => item?.value ?? item)
            .unique()
            .sort()
            .each(value => select.add(new Option(value, value)));
    }

    //  Append to the filter row cell
    cell.appendChild(select);
};

/* -----------------------------------------------------------------
 * Create a function to add an input filter to the column
 * ------------------------------------------------------------------
 */
let createInputFilter = function (cell, column, className, remote) {
    //  Create an input element
    let input = document.createElement('input');

    //  Add a class to the input element
    input.className = className;

    //  Add a data-dt-column attribute to the input element
    input.setAttribute('dtt-search-filter', column.index());

    //  Add a placeholder to the input element
    input.placeholder = 'Search...';

    //  Server-side tables send a request per search, so wait for typing to pause
    let timer;

    //  Add an event listener to the input element
    input.addEventListener('keyup', function () {
        //  Local tables filter instantly
        if (! remote) {
            return column.search(this.value).draw();
        }

        //  Clear the timer
        clearTimeout(timer);

        //  Set the timer
        timer = setTimeout(() => column.search(this.value).draw(), 400);
    });

    //  Append to the filter row cell
    cell.appendChild(input);
};

/* -----------------------------------------------------------------
 * Create a function to show the message above a table whose data could not be loaded
 * ------------------------------------------------------------------
 */
window.showTableError = function (tableId, error) {
    //  Get the container of this table
    let container = document.querySelector(`.dt-tables-container[data-dt-table="${tableId}"]`);

    //  Nothing to show it in
    if (! container) { return; }

    //  Define the action that reloads the page, for problems a fresh page fixes
    let reload = { label: 'Reload page', run: () => window.location.reload() };

    //  Define the action that asks for the same page again, for problems that may pass
    let retry = { label: 'Try again', run: () => new DataTable.Api(`#${tableId}`).draw(false) };

    //  Explain the failure in terms the user can act on, and offer the step that can fix it
    let problem = {
        419: { title: 'Session expired', text: 'Your session has expired. Reload the page to continue.', action: reload },
        401: { title: 'Sign in required', text: 'You need to sign in to see this data.', action: reload },
        403: { title: 'Access denied', text: 'You are not allowed to see this data.', action: null },
    }[error?.status] ?? { title: 'Could not load data', text: 'Something went wrong while loading the table.', action: retry };

    //  Replace any earlier message
    clearTableError(tableId);

    //  Create the message element
    let element = document.createElement('div');

    //  Add the class that styles the message
    element.className = 'dt-tables-error';

    //  Let screen readers announce the message
    element.setAttribute('role', 'alert');

    //  Add the warning icon, which is fixed markup and holds no user data
    element.innerHTML = '<svg class="dt-tables-error-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';

    //  Create the container of the title and the explanation
    let body = document.createElement('div');

    //  Add the class that styles the container
    body.className = 'dt-tables-error-body';

    //  Create the title
    let title = document.createElement('strong');

    //  Set the title, as text so nothing is read as markup
    title.textContent = problem.title;

    //  Create the explanation
    let text = document.createElement('span');

    //  Set the explanation, as text so nothing is read as markup
    text.textContent = problem.text;

    //  Put the title and the explanation in the container
    body.append(title, text);

    //  Put the container in the message
    element.append(body);

    //  Add the button that can fix the problem
    if (problem.action) {
        //  Create the button
        let button = document.createElement('button');

        //  Keep the button from submitting any form around the table
        button.type = 'button';

        //  Add the class that styles the button
        button.className = 'dt-tables-error-action';

        //  Label the button
        button.textContent = problem.action.label;

        //  Run the action when the button is clicked
        button.addEventListener('click', problem.action.run);

        //  Put the button in the message
        element.append(button);
    }

    //  Show it above the table
    container.prepend(element);
};

/* -----------------------------------------------------------------
 * Create a function to clear the message once the table's data loads again
 * ------------------------------------------------------------------
 */
window.clearTableError = function (tableId) {
    document.querySelector(`.dt-tables-container[data-dt-table="${tableId}"] .dt-tables-error`)?.remove();
};

/* -----------------------------------------------------------------
 * Create the helper that talks to the server for a server-side table
 * ------------------------------------------------------------------
 */
window.remoteTable = function (settings, csrfToken) {
    //  Get the CSRF header, preferring the cookie because the session keeps it fresh while the page stays open
    let csrfHeader = function () {
        //  Find the cookie Laravel sets with each response
        let cookie = document.cookie.split('; ').find(item => item.startsWith('XSRF-TOKEN='));

        //  Fall back to the token the page was rendered with
        return cookie
            ? { 'X-XSRF-TOKEN': decodeURIComponent(cookie.substring('XSRF-TOKEN='.length)) }
            : { 'X-CSRF-TOKEN': csrfToken };
    };

    //  Post a JSON request to one of the server-side endpoints
    let post = function (url, body) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...csrfHeader(),
            },
            body: JSON.stringify({ token: settings.token, ...body }),
        }).then(response => {
            //  Fail on error responses, keeping the status so the user can be told why
            if (! response.ok) {
                //  Create the error
                let error = new Error(`Failed to load table data (${response.status})`);

                //  Keep the status on the error
                error.status = response.status;

                //  Throw the error
                throw error;
            }

            //  Return the decoded body
            return response.json();
        });
    };

    //  Hold the filters waiting for their options
    let queue = [];

    //  Return the helper
    return {
        //  get the searchable property
        searchable: settings.searchable ?? null,

        //  get the orderable property
        orderable: settings.orderable ?? null,

        //  Get the keys filtered with a select
        selects: settings.selects ?? [],

        //  Get the options of a select filter; filters asked for together share one request
        options: function (column) {
            return new Promise(resolve => {
                //  Queue the column
                queue.push({ column, resolve });

                //  The first column of a batch schedules the request for when the others have queued
                if (queue.length > 1) { return; }

                //  Wait for the current task to finish so every filter can queue
                Promise.resolve().then(() => {
                    //  Take the batch
                    let batch = queue;

                    //  Start a new batch
                    queue = [];

                    //  Ask for every column at once
                    post(settings.optionsUrl, { columns: [...new Set(batch.map(item => item.column))] })
                        .then(json => batch.forEach(item => item.resolve(json.options[item.column] ?? [])))
                        .catch(error => {
                            //  Log the failure and show the filters without options
                            console.error(error);

                            //  Resolve every filter with an empty list
                            batch.forEach(item => item.resolve([]));
                        });
                });
            });
        },

        //  Get a page of rows
        rows: function (data) {
            return post(settings.rowsUrl, data);
        },
    };
};

/* -----------------------------------------------------------------
 * Create the datatable options that make the server page, search and order
 * ------------------------------------------------------------------
 */
window.serverSideOptions = function (tableId, remote, config, columns, onParameters) {
    //  Get the search terms given in the URL
    let urlSearch = new URLSearchParams(window.location.search);

    //  Track the columns filtered by an exact value; select filters share this set
    let exact = new Set();

    //  Keep the set on the table node
    document.getElementById(tableId).dttExact = exact;

    //  Switch off the sorting and searching the server cannot do, so the browser does not offer them
    columns.forEach(function (column) {
        //  If the column is not orderable or searchable, switch it off
        if (remote.orderable && ! remote.orderable.includes(column.name)) {
            column.orderable = false;
        }

        //  If the column is not orderable or searchable, switch it off
        if (remote.searchable && ! remote.searchable.includes(column.name)) {
            column.searchable = false;
        }
    });

    //  Apply the URL searches to the very first request, so it is not followed by a second
    let searchCols = columns.map(function (column, index) {
        //  Find the term for this column
        let term = column.name 
            ? urlSearch.get(column.name) ?? urlSearch.get(column.name.replace(/_/g, '-')) 
            : null;

        //  Select filters match the whole value
        if (term !== null && filterType(config, remote, column.name) === 'select') {
            exact.add(index);
        }

        //  Return the initial search of the column
        return term === null ? null : { search: term };
    });

    //  Return the options
    return {
        serverSide: true,
        processing: true,
        searchCols: searchCols,
        ajax: function (data, callback) {
            //  Ask the server for the page, naming the exact-match columns
            remote.rows({ ...data, exact: [...document.getElementById(tableId).dttExact] })
                .then(json => {
                    //  Clear any earlier failure
                    clearTableError(tableId);

                    //  Keep the parameters used by the button events
                    onParameters(json.parameters || {});

                    //  Draw the page
                    callback(json);
                })
                .catch(error => {
                    //  Log the failure
                    console.error(error);

                    //  Tell the user, so an empty table is not mistaken for no data
                    showTableError(tableId, error);

                    //  Draw an empty table so the loader is dismissed
                    callback({ draw: data.draw, recordsTotal: 0, recordsFiltered: 0, data: [] });
                });
        },
    };
};

/* -----------------------------------------------------------------
 * Create a function to hide the loader and show the datatable
 * ------------------------------------------------------------------
 */
window.hideLoader = function(tableId) {
    //  Hide only this table's loader
    document.querySelector(`.dt-tables-loader[data-dt-table="${tableId}"]`).style.display = 'none';

    //  Show only this table's container
    document.querySelector(`.dt-tables-container[data-dt-table="${tableId}"]`).style.display = 'block';
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
            title: function () {
                //  Get the current time
                const now = new Date();

                //  Extract the date
                const date = now.toLocaleDateString('en-GB');

                //  Extract the time
                const time = now.toLocaleTimeString('en-GB');

                //  Return the string
                return `${title} - Generated at ${date} ${time}`;
            },
            footer: false,
            className: theme.buttons,
            exportOptions: {
                orthogonal: 'sort',
                columns: 'th:not(.exclude-from-export):visible',
            },
            customize: function (xlsx) {
                //  sheet1.xml is the grid of rows and cells written to the Excel file.
                const sheet = xlsx.xl.worksheets['sheet1.xml'];

                //  Set a counter to 0
                let rowNum = 0;

                //  Loop through the items and apply borders of different widths
                sheet.querySelectorAll('sheetData row').forEach(function (row) {
                    //  Loop through each cell
                    row.querySelectorAll('c').forEach(function (cell) {
                        cell.setAttribute('s', rowNum <= 1 ? '27' : '25');
                    });

                    //  Increment the counter
                    rowNum++;
                });
            },
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
                defaultContent: '',
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
        
        //  dtt-align: left, center or right aligns the column's header and cells
        if (th.hasAttribute('dtt-align')) {
            column.className = [
                column.className, 
                `dtt-align-${th.getAttribute('dtt-align')}`
            ].filter(Boolean).join(' ');
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
window.setupStyling = function (api, theme) {
    //  Scope all lookups to this table's own DataTables wrapper
    let container = api.table().container();

    //  Remove the classes from the buttons
    container.querySelectorAll('.dt-button').forEach(el => el.classList.remove('dt-button'));

    //  Remove dt-search and dt-input classes from the search input
    container.querySelectorAll('.dt-search').forEach(el => {
        el.classList.remove('dt-search');
        el.classList.add('dt-tables-search');
    });

    //  Add the classes from the datatable search input
    container.querySelectorAll('.dt-input').forEach(el => {
        el.classList.remove('dt-input')
        el.classList.add(...theme.input.split(/\s+/).filter(c => c?.trim()));
    });
};

/* -----------------------------------------------------------------
 * Set up the filters for the datatable
 * ------------------------------------------------------------------
 */
window.setupFilters = function (api, config, theme, remote) {
    //  Check if any column has a filter
    let hasFilters = api.columns()
        .names()
        .toArray()
        .some(name => ['input', 'select'].includes(filterType(config, remote, name)));

    //  Loop through the columns and add a filter to each column
    if (hasFilters) {
        //  Build the filter row after DataTables has initialised so it is not
        //  captured in export snapshots (DataTables reads headers at init time)
        let tableId = api.table().node().id;

        //  Build the filter row, one cell per column in true column-index order
        let filterRow = buildFilterRow(tableId, api);

        //  Address filter cells by column index, matching the build order above
        let filterCells = Array.from(filterRow.children);

        //  Keep a filter cell's visibility in step with its column's
        let syncCellVisibility = function (column, cell) {
            cell.style.display = column.visible() ? '' : 'none';
        };

        //  Loop through the columns in the datatable
        api.columns().every(function () {
            //  Get the column
            let column = this;

            //  Get the corresponding filter cell by column index
            let cell = filterCells[column.index()];

            //  If the cell is not found, skip to the next iteration
            if (!cell) { return; }

            //  Clear the cell
            cell.innerHTML = '';

            //  Hide the filter cell for columns that start out hidden (dtt-hidden)
            syncCellVisibility(column, cell);

            //  Get the name of the column
            let name = column.name();

            //  Offer no filter on a column the server cannot search
            if (remote && remote.searchable && ! remote.searchable.includes(name)) { return; }

            //  Get the kind of filter the column has
            let searchType = filterType(config, remote, name);

            //  Check if the column is in the select items
            switch (searchType) {
                case 'select':
                    createSelectFilter(cell, column, theme.select, remote);
                    break;
                case 'input':
                    createInputFilter(cell, column, theme.input, remote);
                    break;
            }
        });

        //  Keep the filter row aligned when a column is toggled later (e.g. via the colvis button)
        api.on('column-visibility.dt', function (e, settings, columnIndex) {
            //  Get the corresponding filter cell by column index
            let cell = filterCells[columnIndex];

            //  Sync its visibility if found
            if (cell) { syncCellVisibility(api.column(columnIndex), cell); }
        });
    }
};

/* -----------------------------------------------------------------
 * Set up the search params for the datatable
 * ------------------------------------------------------------------
 */
window.setupSearchParams = function (api, remote) {
    //  Get the table id
    let tableId = api.table().node().id;

    //  Get the columns in the table
    let columns = api.columns().names().toArray();

    //  Loop through the search params
    new URLSearchParams(window.location.search).forEach((value, key) => {
        //  Get the column index
        let index = columns.findIndex(name => name === key.replace(/-/g, '_'));

        //  Get the column
        let column = api.column(index);

        //  Get the element
        let element = document.querySelector(`#${tableId} [dtt-search-filter="${index}"]`);

        //  If the element is found, set the value
        if (element) {
            //  Set the value of the element
            element.value = value;

            //  Server-side tables were created with these searches already applied
            if (! remote) {
                column.search(value).draw();
            }
        }
    });
};