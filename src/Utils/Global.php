<?php

use Illuminate\View\View;

/**
 * Generate a package resouce view name depending on the package.
 *
 * @author Alvin Gichira Kaburu <geekaburu@amprest.co.ke>
 */
function package_path($path): mixed
{
    return config('laravel-dt.name').'::'.$path;
}
