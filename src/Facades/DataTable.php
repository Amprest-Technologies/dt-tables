<?php

namespace Amprest\DtTables\Facades;

use Illuminate\Support\Facades\Facade;

class DataTable extends Facade
{
    /**
     * Get the registered name of the service.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    protected static function getFacadeAccessor(): string
    {
        return 'dtTableHelper';
    }
}
