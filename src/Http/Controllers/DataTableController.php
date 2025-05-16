<?php

namespace Amprest\LaravelDT\Http\Controllers;

use Amprest\LaravelDT\Http\Requests\DataTableRequest;
use Amprest\LaravelDT\Models\DataTable;
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
        $dataTables = DataTable::latest()->get();

        //  Return the view with the list of tables
        return view('laravel-dt::pages.data-tables.index', [
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
        DataTable::create(array_merge($request->validated(), [
            'settings' => config('laravel-dt.defaults.settings'),
        ]));

        //  Return the view with the list of tables  
        return redirect()->back()->with([
            'alert' => [
                'type' => 'success',
                'message' => trans('laravel-dt::alerts.data-table.created'),
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
        return view('laravel-dt::pages.data-tables.edit', [
            'dataTable' => $dataTable,
            'columns' => $dataTable->columns,
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
                'message' => trans('laravel-dt::alerts.data-table.updated'),
            ],
        ]);
    }

    /**
     * Destroy a table.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function destroy(DataTable $dataTable): RedirectResponse
    {
        //  Delete the table columns
        $dataTable->columns()->delete();

        //  Delete the table
        $dataTable->delete();

        //  Return the view with the list of tables  
        return redirect()->back()->with([
            'alert' => [
                'type' => 'success',
                'message' => trans('laravel-dt::alerts.data-table.destroyed'),
            ],
        ]);
    }
}