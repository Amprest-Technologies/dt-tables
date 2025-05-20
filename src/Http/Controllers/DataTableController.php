<?php

namespace Amprest\DtTables\Http\Controllers;

use Amprest\DtTables\Http\Requests\DataTableRequest;
use Amprest\DtTables\Models\DataTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DataTableController extends Controller
{
    /**
     * Show a list of tables.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function index(): View
    {
        //  Get the list of tables
        $dataTables = DataTable::all();

        //  Return the view with the list of tables
        return view('dt-tables::pages.data-tables.index', [
            'dataTables' => $dataTables,
        ]);
    }

    /**
     * Store a new table
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function store(DataTableRequest $request): RedirectResponse
    {
        //  Create the table
        $dataTable = DataTable::create(array_merge($request->validated(), [
            'id' => strtolower(Str::ulid()),
            'settings' => config('dt-tables.settings'),
            'columns' => [],
        ]));

        //  Return the view with the list of tables
        return redirect()->route('dt-tables.data-tables.edit', ['data_table' => $dataTable->id])->with([
            'alert' => [
                'type' => 'success',
                'message' => trans('dt-tables::alerts.data-table.created'),
            ],
        ]);
    }

    /**
     * Show the page to edit a table.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function edit(DataTable $dataTable): View
    {
        return view('dt-tables::pages.data-tables.edit', [
            'dataTable' => $dataTable,
            'columns' => $dataTable->columns,
            'settings' => $dataTable->settings,
        ]);
    }

    /**
     * Update the data table.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function update(DataTableRequest $request, DataTable $dataTable): RedirectResponse
    {
        //  Get the validated data
        $validated = $request->updateData($dataTable);

        //  Update the table
        $dataTable->update($validated);

        //  Return the view with the list of tables
        return redirect()->back()->with(['alert' => [
            'type' => 'success',
            'message' => trans('dt-tables::alerts.data-table.updated'),
        ]]);
    }

    /**
     * Destroy a table.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function destroy(DataTable $dataTable): RedirectResponse
    {
        //  Update the json file
        DataTable::destroy($dataTable);

        //  Return the view with the list of tables
        return redirect()->back()->with([
            'alert' => [
                'type' => 'success',
                'message' => trans('dt-tables::alerts.data-table.destroyed'),
            ],
        ]);
    }
}
