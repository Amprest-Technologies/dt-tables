<?php

namespace Amprest\DtTables\Facades;

use Amprest\DtTables\Services\HelpersService;
use Illuminate\Support\Facades\Facade;

/**
 * @see HelpersService
 */
class DataTable extends Facade
{
    /**
     * Get the registered name of the service.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected static function getFacadeAccessor(): string
    {
        return 'dtTableHelper';
    }
}
