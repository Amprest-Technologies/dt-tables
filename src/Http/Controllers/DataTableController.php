<?php

namespace Nyumbani\DtTables\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Nyumbani\DtTables\Http\Requests\DataTableRequest;
use Nyumbani\DtTables\Models\DataTable;
use Nyumbani\DtTables\Support\TablePresenter;

class DataTableController
{
    /**
     * Show a list of tables.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function index(TablePresenter $presenter): View
    {
        //  Summarise every table for its card
        $tables = DataTable::all()->map(fn ($table) => $presenter->card($table));

        //  Return the view with the list of tables
        return view('dt-tables::pages.data-tables.index', [
            'tables' => $tables,
            'modes' => $presenter->modes(),
            'counts' => $tables->countBy('mode'),
            'perPage' => TablePresenter::PER_PAGE,
        ]);
    }

    /**
     * Store a new table
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function store(DataTableRequest $request): RedirectResponse
    {
        //  Create the table
        $dataTable = DataTable::create([
            'key' => $request->safe()->key,
            'mode' => $request->safe()->mode,
        ]);

        //  Return the view with the list of tables
        return redirect()
            ->route('dt-tables.data-tables.edit', ['data_table' => $dataTable->key])
            ->with([
                'alert' => [
                    'type' => 'success',
                    'message' => trans('dt-tables::alerts.data-table.created'),
                ],
            ]);
    }

    /**
     * Show the page to edit a table.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function edit(DataTable $dataTable, TablePresenter $presenter): View
    {
        return view('dt-tables::pages.data-tables.edit', [
            'dataTable' => $dataTable,
            'modes' => $presenter->modes(),
            'page' => $presenter->edit($dataTable),
        ]);
    }

    /**
     * Update the data table.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function update(DataTableRequest $request, DataTable $dataTable): RedirectResponse
    {
        //  Get the validated data
        $validated = $request->updateData($dataTable);

        //  Update the table
        $dataTable->update($validated);

        //  Redirect by key (not back()) since a name update renames the file,
        //  which would leave back() pointing at the now-stale old-key edit URL
        return redirect()
            ->route('dt-tables.data-tables.edit', ['data_table' => $dataTable->key])
            ->with(['alert' => [
                'type' => 'success',
                'message' => trans('dt-tables::alerts.data-table.updated'),
            ]]);
    }

    /**
     * Destroy a table.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function destroy(DataTable $dataTable): RedirectResponse
    {
        //  Update the json file
        DataTable::destroy($dataTable);

        //  Return to the list, since the table's own page no longer exists
        return redirect()->route('dt-tables.data-tables.index')->with([
            'alert' => [
                'type' => 'success',
                'message' => trans('dt-tables::alerts.data-table.destroyed'),
            ],
        ]);
    }
}
