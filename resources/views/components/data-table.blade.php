<div class="dt-tables-container" @style(['display: none' => $loader->enabled ?? false])>
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

    //  Get the payload
    let payload = @js($payload);

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

    //  If the payload is not empty, set the data
    if (payload.length > 0) {
        options.data = payload;
    }
    
    //  Define the table
    new DataTable(`#${tableId}`, options);
</script>