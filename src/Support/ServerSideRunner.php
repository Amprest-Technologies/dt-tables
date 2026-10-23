<?php

namespace Nyumbani\DtTables\Support;

use Nyumbani\DtTables\Http\Requests\ServerSideRowsRequest;

interface ServerSideRunner
{
    /**
     * Run a DataTables request and return its response payload.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function rows(ServerSideRowsRequest $request): array;

    /**
     * Get the select-filter options for a column as value/label pairs.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function options(string $column): array;

    /**
     * Get the searchable, orderable and select-filtered row keys, so the browser only offers those.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function columns(): array;
}
