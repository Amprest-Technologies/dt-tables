<?php

namespace Amprest\DtTables\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Symfony\Component\HttpFoundation\Response;

class AutoInjectDtTableAssets
{
    /**
     * Inject the DataTable assets into the response.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function handle(Request $request, Closure $next): Response
    {
        //  Get the response
        $response = $next($request);

        //  Get the conditions
        $condition = App::has('dt-table.assets-enabled')
            && App::get('dt-table.assets-enabled', false);

        //  Check if dt-table asset injection is enabled
        if (! $condition) {
            return $response;
        }

        //  Get the html content
        $html = $response->getContent();

        //  Only inject if </head> exists
        if (str_contains($html, '</head>')) {
            //  Render the blade component
            $injection = Blade::render("<x-data-table-assets />");

            //  Inject just before closing </head>
            $html = str_replace('</head>', "$injection\n</head>", $html);

            //  Set the new content
            $response->setContent($html);
        }

        //  Return the response
        return $response;
    }
}
