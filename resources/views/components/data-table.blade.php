<table id="{{ $tableId }}" {{ $attributes }}>
    {{ $slot }}
</table>

<script type="module" defer>
    //  Get the table ID
    let tableId = @js($tableId);

    //  Build table config
    let config = @js($table);
    
    //  Initialize the datatable
    setupDataTable(tableId);

    //  Define the table
    let table = new DataTable(`#${tableId}`, {
        data: @js($payload),
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
    });
</script>