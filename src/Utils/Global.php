<?php

if (! function_exists('package_path')) {
    /**
     * Generate a package resouce view name depending on the package.
     *
     * @author Alvin Gichira Kaburu <geekaburu@amprest.co.ke>
     */
    function package_path($path): mixed
    {
        return base_path('vendor/amprest/laravel-dt/' . $path);
    }
}