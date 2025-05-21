<?php

use Illuminate\Support\HtmlString;

if (! function_exists('package_path')) {
    /**
     * Generate a package resouce view name depending on the package.
     *
     * @author Alvin Gichira Kaburu <geekaburu@amprest.co.ke>
     */
    function package_path($path): mixed
    {
        return base_path("vendor/amprest/dt-tables/{$path}");
    }
}

if (! function_exists('prettify')) {
    /**
     * Prettify text
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    function prettify(string $text): string
    {
        return $text
            ? ucwords(str_replace('-', ' ', str_replace('_', ' ', strtolower($text))))
            : $text;
    }
}

if (! function_exists('to_object')) {
    /**
     * Convert a string to an object
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    function to_object(mixed $value, ?bool $associative = null): array|object
    {
        return json_decode(json_encode($value), $associative);
    }
}
    
if (! function_exists('bag')) {
    /**
     * Return an error bag instance
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    function bag(string $value): HtmlString
    {
        return new HtmlString('<input type="hidden" name="_bag" value="'.$value.'">');
    }
}
