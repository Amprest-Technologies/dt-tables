<?php

namespace Amprest\DtTables\Http\Controllers;

use Amprest\DtTables\Models\DataTable;
use Amprest\DtTables\Support\Docs;
use Illuminate\View\View;

class HomeController
{
    /**
     * Show the landing page, which leads to the tables and the documentation.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function __invoke(Docs $docs): View
    {
        return view('dt-tables::pages.home', [
            'tableCount' => DataTable::all()->count(),
            'pageCount' => $docs->pages()->count(),
        ]);
    }
}
