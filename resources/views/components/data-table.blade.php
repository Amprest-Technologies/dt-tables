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
        layout: {
            topStart: { buttons: buttons(config.settings.buttons) },
            topEnd: 'pageLength',
            bottomStart: 'info',
            bottomEnd: 'paging'
        },
        columns: columns(tableId, config.columns),
        initComplete: function () {
            //  Setup the filters
            setupFilters(this.api(), config.columns);
        },
    };

    //  If the payload is not empty, set the data
    if(payload.length > 0) {
        options.data = payload;
    }
    
    //  Define the table
    let table = new DataTable(`#${tableId}`, options);
</script>