<?php

namespace Nyumbani\DtTables\Facades;

use Illuminate\Support\Facades\Facade;
use Nyumbani\DtTables\Services\HelpersService;

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
