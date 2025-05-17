<table id="{{ $tableId }}" {{ $attributes }}>
    {{ $slot }}
</table>

<script type="module" defer>
    //  Get the table ID
    let tableId = @js($tableId);

    //  Get the payload
    let payload = @js($payload);

    //  Build table config
    let config = @js($table);
    
    //  Initialize the datatable
    setupDataTable(tableId);

    //  Setup the datatable options
    let options = {
        responsive: true,
        layout: layout(config.settings.buttons),
        columns: columns(tableId, config.columns),
        language: {
            lengthMenu: '_MENU_ _ENTRIES_',
            entries: { _: 'Entries', 1: 'Entry' }
        },
        initComplete: function () {
            //  Set up styling
            setupStyling();

            //  Set up filters
            setupFilters(this.api(), config.columns);
        },
    };

    //  If the payload is not empty, set the data
    if (payload.length > 0) {
        options.data = payload;
    }
    
    //  Define the table
    new DataTable(`#${tableId}`, options);
</script>