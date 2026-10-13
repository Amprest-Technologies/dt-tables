<div class="dt-tables-container" data-dt-table="{{ $id }}" @style([
    'display: none' => ($loader = fluent($loader))->enabled ?? false
])>
    <table id="{{ $id }}" {{ $attributes }}>
        {{ $slot }}
    </table>
</div>

@if($loader->enabled ?? false)
    <div class="dt-tables-loader" data-dt-table="{{ $id }}">
        {!! $loaderMarkup !!}
    </div>
@endif

<script type="module" defer>
    //  Get the DOM id of this table element
    let tableId = @js($id);

    //  Get the key of the table configuration it uses
    let configKey = @js($tableId);

    //  Get the table data
    let tableData = @js($tableData);

    //  Get the server-side settings; empty for tables fed inline
    let serverSide = @js($serverSide);

    //  Get the table parameters
    let tableParams = @js($tableParams);

    //  Get the buttons
    let configButtons = @js($buttons);

    //  Get the columns
    let configColumns = @js($columns);

    //  Get the theme options
    let configTheme = @js($theme);

    //  Get the title of the table
    let title = @js($attributes->get('title', '*'));

    //  Get the helper that talks to the server, for tables that page on the server
    let remote = serverSide.rowsUrl 
        ? remoteTable(serverSide, @js(csrf_token())) 
        : null;

    //  Setup the datatable options
    let options = {
        pageLength: @js($pageLength),
        ordering: @js($ordering),
        searching: @js($searching),
        paging: @js($paging),
        info: @js($info),
        scrollX: @js($scrollX),
        layout: {
            top: {
                className: 'top-row',
                features: [
                    { buttons: buttons(configButtons, configTheme, title) },
                    { search: { placeholder: 'Type to search', text: '_INPUT_' } }
                ]
            },
            bottom: { className: 'bottom-row', features: ['info', 'paging']},            
            topStart: null,
            topEnd: null,
            bottomStart: null,
            bottomEnd: null,
        },
        language: {
            paginate: {
                next: 'Next',
                previous: 'Previous'
            }
        },
        columns: columns(tableId, configColumns),
        initComplete: function () {
            //  Get the api
            let api = this.api();

            //  Set up styling
            setupStyling(api, configTheme);

            //  Set up filters
            setupFilters(api, configColumns, configTheme, remote);

            //  Setup the search params
            setupSearchParams(api, remote);

            //  Hide loader once the table is initialized
            hideLoader(tableId);
        },
    };

    //  Server-side tables ask the server for one page at a time; local ones get their rows up front
    if (remote) {
        //  Setup the server-side options
        let serverOptions = serverSideOptions(
            tableId,
            remote,
            configColumns,
            options.columns, params => tableParams = params
        );

        //  Assign the options
        Object.assign(options, serverOptions);

        //  Show the table's own loader, rather than the default text
        if (@js($loaderMarkup) !== '') {
            options.language.processing = `<div class="loader-content">${@js($loaderMarkup)}</div>`;
        }

    //  Local tables get their rows up front
    } else if (tableData.length > 0) {
        options.data = tableData;
    }

    //  Define the table
    let table = new DataTable(`#${tableId}`, options);

    //  Launch an event when the table buttons are clicked
    table.on('buttons-action', function (e, buttonApi, dataTable, node, config) {
        //  If the button is not a copy or excel button, do nothing
        if (!['copy', 'excel'].includes(config.name)) {
            return;
        }
        
        //  Get button action
        let action = @js(route('dt-tables.api.button-triggered'));

        //  Trigger the button event
        fetch(action, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({
                tableId: configKey,
                action: config.name,
                params: tableParams.buttonTrigger || {},
            }),
        })
        .then(response => response.json())
        .then(data => console.log(data))
        .catch(error => console.error(error));
    });
</script>