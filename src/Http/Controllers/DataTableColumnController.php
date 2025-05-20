<?php

namespace Amprest\DtTables\Http\Controllers;

use Amprest\DtTables\Http\Requests\DataTableColumnRequest;
use Amprest\DtTables\Models\DataTable;
use Amprest\DtTables\Models\DataTableColumn;
use Amprest\DtTables\Services\JsonService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

class DataTableColumnController extends Controller
{
    /**
     * Store a new table column
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function store(DataTableColumnRequest $request, DataTable $dataTable): RedirectResponse
    {
        //  Create the table columns
        $dataTable->columns()->create($request->validated());

        //  Return the view on the data table edit page
        return redirect()->back()->with([
            'alert' => [
                'type' => 'success',
                'message' => trans('dt-tables::alerts.data-table-column.created'),
            ],
        ]);
    }

    /**
     * Update the data table column.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function update(DataTableColumnRequest $request, DataTableColumn $dataTableColumn): RedirectResponse
    {
        //  Update the table
        $dataTableColumn->update($request->validated());

        //  Return the view on the data table edit page
        return redirect()->back()->with([
            'alert' => [
                'type' => 'success',
                'message' => trans('dt-tables::alerts.data-table-column.updated'),
            ],
        ]);
    }

    /**
     * Destroy a table column.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function destroy(DataTableColumn $dataTableColumn): RedirectResponse
    {
        //  Delete the table column
        $dataTableColumn->delete();

        //  Get the data table
        $dataTable = $dataTableColumn->dataTable;

        //  Update the json file
        JsonService::set($dataTable);

        //  Return the view on the data table edit page
        return redirect()->back()->with([
            'alert' => [
                'type' => 'success',
                'message' => trans('dt-tables::alerts.data-table-column.destroyed'),
            ],
        ]);
    }
}