<?php

namespace Amprest\DtTables\Http\Controllers;

use Amprest\DtTables\Support\Docs;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DocsController
{
    /**
     * Send the reader to the first page of the documentation.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function index(Docs $docs): RedirectResponse
    {
        return redirect()->route('dt-tables.docs.show', ['page' => $docs->pages()->first()['slug']]);
    }

    /**
     * Show one page of the documentation.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function show(string $page, Docs $docs): View
    {
        //  Find the page
        $current = $docs->find($page);

        //  Abort if the page does not exist
        abort_if(is_null($current), 404, 'Documentation page not found');

        //  Get the pages on either side of it
        [$previous, $next] = $docs->neighbours($page);

        //  Return the page
        return view('dt-tables::pages.docs.show', [
            'page' => $current,
            'sections' => $docs->sections(),
            'previous' => $previous,
            'next' => $next,
        ]);
    }
}
