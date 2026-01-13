<div class="dt-tables-container" @style(['display: none' => ($loader = fluent($loader))->enabled ?? false])>
    <table id="{{ $tableId }}" {{ $attributes }}>
        {{ $slot }}
    </table>
</div>

@if($loader->enabled ?? false)
    <div class="dt-tables-loader">
        <div>
            @if($loader->image ?? false)
                <div class="loader-image">
                    <img style="text-center" src="{{ asset($loader->image) }}" alt="Loading...">
                </div>
            @endif
            <div class="loader-text">
                {{ $loader->message ?? 'Loading...' }}
            </div>
        </div>
    </div>
@endif

<script type="module" defer>
    //  Get the table ID
    let tableId = @js($tableId);

    //  Get the table data
    let tableData = @js($tableData);

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

    //  Initialize the datatable
    setupDataTable(tableId, configColumns);

    //  Setup the datatable options
    let options = {
        responsive: true,
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
            setupStyling(configTheme);

            //  Set up filters
            setupFilters(api, configColumns, configTheme);

            //  Setup the search params
            setupSearchParams(api);

            //  Hide loader once the table is initialized
            hideLoader();
        },
    };

    //  If the table data is not empty, set the data
    if (tableData.length > 0) {
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
                tableId: tableId,
                action: config.name,
                params: tableParams.buttonTrigger || {},
            }),
        })
        .then(response => response.json())
        .then(data => console.log(data))
        .catch(error => console.error(error));
    });
</script>