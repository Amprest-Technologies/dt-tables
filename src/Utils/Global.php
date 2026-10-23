<?php

if (! function_exists('dt_tables_storage_path')) {
    /**
     * The directory where DataTable JSON files are stored, one file per table.
     * Fixed and non-configurable, so a published config can never drift from it.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    function dt_tables_storage_path(): string
    {
        return resource_path('data-tables');
    }
}
