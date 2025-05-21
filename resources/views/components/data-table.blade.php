<table id="{{ $tableId }}" {{ $attributes }}>
    {{ $slot }}
</table>

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
            //  Set up styling
            setupStyling(configTheme);

            //  Set up filters
            setupFilters(this.api(), configColumns, configTheme);
        },
    };

    //  If the payload is not empty, set the data
    if (payload.length > 0) {
        options.data = payload;
    }
    
    //  Define the table
    new DataTable(`#${tableId}`, options);
</script>