<?php

namespace Amprest\DtTables\Http\Controllers\API;

use Amprest\DtTables\Http\Requests\ServerSideOptionsRequest;
use Amprest\DtTables\Http\Requests\ServerSideRequest;
use Amprest\DtTables\Http\Requests\ServerSideRowsRequest;
use Amprest\DtTables\Support\ServerSideRunner;
use Amprest\DtTables\Support\TableSource;
use Illuminate\Http\JsonResponse;

class ServerSideController
{
    /**
     * Return one page of rows for the search and order the browser sent.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function rows(ServerSideRowsRequest $request): JsonResponse
    {
        //  Get the query runner for the table the token names
        $query = $this->query($request);

        //  Return the page
        return response()->json($query->rows($request));
    }

    /**
     * Return the options of a select filter.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function options(ServerSideOptionsRequest $request): JsonResponse
    {
        //  Get the query runner for the table the token names
        $query = $this->query($request);

        //  A batch of columns is answered in one go, keyed by column
        if ($request->has('columns')) {
            return response()->json([
                'options' => collect($request->columns())
                    ->mapWithKeys(fn (string $column) => [$column => $query->options($column)])
                    ->all(),
            ]);
        }

        //  Return the options
        return response()->json([
            'options' => $query->options($request->column()),
        ]);
    }

    /**
     * Open the table named by the request's token and make sure the request may read it.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function query(ServerSideRequest $request): ServerSideRunner
    {
        //  Open the token and create the table
        $table = TableSource::open($request->token())->instance();

        //  Let the table decide whether this request may read its data
        abort_unless($table->authorize($request), 403, 'This action is unauthorized');

        //  Return the runner of the source
        return $table->runner();
    }
}
