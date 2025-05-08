@props(['data'])

<table {{ $attributes }}>
    {{ $slot }}
</table>

<script type="module" defer>
    //  Get the table ID
    let tableId = @js($attributes->get('id'));

    //  Initialize the datatable
    setupDataTable(tableId);

    //  Define the table
    let table = new DataTable(`#${tableId}`, {
        data: @js($data),
        responsive: true,
        layout: layout(),
        columns: columns(tableId),
        initComplete: function () {
            //  Setup the filters
            setupFilters(this.api());
        },
    });
</script>