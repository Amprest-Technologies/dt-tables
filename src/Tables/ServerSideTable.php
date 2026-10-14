<?php

namespace Amprest\DtTables\Tables;

use Amprest\DtTables\Support\ServerSideQuery;
use Amprest\DtTables\Support\ServerSideRunner;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;

abstract class ServerSideTable extends ServerSideSource
{
    /**
     * Return the query that selects the table's rows.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    abstract public function query(): Builder|EloquentBuilder;

    /**
     * Return the column that makes every row's position unique, so paging is stable.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function keyColumn(): string
    {
        return 'id';
    }

    /**
     * Return the runner that pages, searches and sorts the query in the database.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function runner(): ServerSideRunner
    {
        return new ServerSideQuery($this);
    }
}
