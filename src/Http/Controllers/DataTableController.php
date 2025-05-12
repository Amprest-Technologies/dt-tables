<?php

namespace Amprest\LaravelDT\Http\Controllers;

use Amprest\LaravelDT\Models\DataTable;
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
        $tables = DataTable::all();

        //  Return the view with the list of tables
        return view('laravel-dt::pages.data-tables.index', [
            'tables' => $tables,
        ]);
    }
}