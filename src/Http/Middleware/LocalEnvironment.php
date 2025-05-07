<?php

namespace Amprest\LaravelDatatables\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LocalEnvironment
{
    /**
     * Handle Function
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     *
     * @author Paul Gitau <kinyanjuipaul34@gmail.com>
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if environment is local
        if (app()->environment('local')) {
            return $next($request);
        }

        // Abort if environment is not local
        return redirect()->back();
    }
}
