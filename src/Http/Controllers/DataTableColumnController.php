<?php

namespace Nyumbani\DtTables\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Nyumbani\DtTables\Http\Requests\DataTableColumnRequest;
use Nyumbani\DtTables\Models\DataTable;
use Nyumbani\DtTables\Models\DataTableColumn;

class DataTableColumnController
{
    /**
     * Store a new table column
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function store(DataTableColumnRequest $request, DataTable $dataTable): RedirectResponse
    {
        //  Server-side and API tables take their filters from the table class
        abort_if($dataTable->isServerSide(), 403, trans('dt-tables::alerts.data-table-column.server-side'));

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
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function update(DataTableColumnRequest $request, DataTable $dataTable, DataTableColumn $dataTableColumn): RedirectResponse
    {
        //  Server-side and API tables take their filters from the table class
        abort_if($dataTable->isServerSide(), 403, trans('dt-tables::alerts.data-table-column.server-side'));

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
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
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
