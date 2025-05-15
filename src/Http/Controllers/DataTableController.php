<?php

namespace Amprest\LaravelDT\Http\Controllers;

use Amprest\LaravelDT\Models\DataTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $tables = DataTable::latest()->get();

        //  Return the view with the list of tables
        return view('laravel-dt::pages.data-tables.index', [
            'tables' => $tables,
        ]);
    }

    /**
     * Store a new table
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function store(Request $request): RedirectResponse
    {
        //  Validate the request
        $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
        ]);

        //  Create the table
        DataTable::create(array_merge($request->all(), [
            'settings' => config('laravel-dt.defaults.settings'),
        ]));

        //  Return the view with the list of tables  
        return redirect()->back();
    }
}