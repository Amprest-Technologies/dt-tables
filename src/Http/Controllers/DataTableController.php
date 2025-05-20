<?php

namespace Amprest\DtTables\Http\Controllers;

use Amprest\DtTables\Http\Requests\DataTableRequest;
use Amprest\DtTables\Models\DataTable;
use Amprest\DtTables\Services\JsonService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
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
        $dataTables = JsonService::all();

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
            'settings' => config('dt-tables.settings'),
        ]));

        //  Return the view with the list of tables  
        return redirect()->route('dt-tables.data-tables.edit', ['data_table' => $dataTable])->with([
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
    public function edit(JsonService $dataTable): View
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
        //  Update the table
        $dataTable->update($request->updateData());

        //  Return the view with the list of tables  
        return redirect()->back()->with([
            'alert' => [
                'type' => 'success',
                'message' => trans('dt-tables::alerts.data-table.updated'),
            ],
        ]);
    }

    /**
     * Destroy a table.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function destroy(JsonService $dataTable): RedirectResponse
    {
        //  Update the json file
        JsonService::destroy($dataTable);

        //  Return the view with the list of tables  
        return redirect()->back()->with([
            'alert' => [
                'type' => 'success',
                'message' => trans('dt-tables::alerts.data-table.destroyed'),
            ],
        ]);
    }
}