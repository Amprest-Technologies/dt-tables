<?php

namespace Amprest\DtTables\Providers;

use Amprest\DtTables\Models\DataTable;
use Amprest\DtTables\Models\DataTableColumn;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define the register method for the service provider.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function register(): void
    {
        //
    }

    /**
     * Define the boot method for the service provider.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function boot(): void
    {
        //  Define the route bindings for the data table
        Route::bind('data_table', function (string $value) {
            //  Return the result of the find
            $dataTable = DataTable::find($value);

            //  Abort if no result is found
            abort_if(is_null($dataTable), 404, 'Data table not found');

            //  Return the data table
            return $dataTable;
        });

        //  Define the route bindings for the data table
        Route::bind('data_table_column', function (string $value) {
            //  Return the result of the find
            $column = DataTableColumn::find($value);

            //  Abort if no result is found
            abort_if(is_null($column), 404, 'Data table column not found');

            //  Return the data table
            return $column;
        });
    }
}
