<?php

namespace Amprest\DtTables\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class PreventIfEnvironmentIsNotLocal
{
    /**
     * Handle an incoming request.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function handle(Request $request, Closure $next)
    {
        //  Abort if the environment is not local
        abort_if(! App::isLocal(), 403);

        //  Proceed with the request
        return $next($request);
    }
}
