<?php

namespace Amprest\DtTables\Http\Controllers;

use Amprest\DtTables\Http\Requests\DataTableColumnRequest;
use Amprest\DtTables\Models\DataTable;
use Amprest\DtTables\Models\DataTableColumn;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class DataTableColumnController
{
    /**
     * Store a new table column
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function store(DataTableColumnRequest $request, DataTable $dataTable): RedirectResponse
    {
        //  Create the table columns
        DataTableColumn::create(array_merge($request->validated(), [
            'id' => strtolower(Str::ulid()),
            'data_table' => $dataTable,
        ]));

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
    public function update(DataTableColumnRequest $request, DataTable $dataTable, DataTableColumn $dataTableColumn): RedirectResponse
    {
        //  Update the table
        $dataTableColumn->update(array_merge($request->validated(), [
            'data_table' => $dataTable,
        ]));

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
    public function destroy(DataTable $dataTable, DataTableColumn $dataTableColumn): RedirectResponse
    {
        //  Delete the table column
        $dataTableColumn->delete($dataTable);

        //  Return the view on the data table edit page
        return redirect()->back()->with([
            'alert' => [
                'type' => 'success',
                'message' => trans('dt-tables::alerts.data-table-column.destroyed'),
            ],
        ]);
    }
}
