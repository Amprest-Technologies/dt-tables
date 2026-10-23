<?php

namespace Nyumbani\DtTables\Http\Controllers;

use Illuminate\View\View;
use Nyumbani\DtTables\Docs\DtTablesDocs;
use Nyumbani\DtTables\Models\DataTable;

class HomeController
{
    /**
     * Show the landing page, which leads to the tables and the documentation.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function __invoke(): View
    {
        return view('dt-tables::pages.home', [
            'tableCount' => DataTable::all()->count(),
            'docs' => DtTablesDocs::registered(),
        ]);
    }
}
