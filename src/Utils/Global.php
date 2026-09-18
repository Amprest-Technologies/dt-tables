<?php

use Illuminate\Support\HtmlString;

if (! function_exists('package_path')) {
    /**
     * Generate a package resouce view name depending on the package.
     *
     * @author Alvin Gichira Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    function package_path(string $path): mixed
    {
        return base_path("vendor/amprest/dt-tables/{$path}");
    }
}

if (! function_exists('prettify')) {
    /**
     * Prettify text
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
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
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
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
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    function bag(string $value): HtmlString
    {
        return new HtmlString('<input type="hidden" name="_bag" value="'.$value.'">');
    }
}

if (! function_exists('ensure_directory_exists')) {
    /**
     * Create a directory if it doesn't already exist, tolerating a race with
     * another process that creates it in the meantime.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    function ensure_directory_exists(string $path, int $mode = 0755): void
    {
        //  Nothing to do if it's already there
        if (is_dir($path)) {
            return;
        }

        //  Attempt to create it, suppressing the warning -- a losing race is not a failure
        if (! @mkdir($path, $mode, true) && ! is_dir($path)) {
            //  A file at the target path is the likeliest cause -- tell the
            //  user how to unblock themselves instead of just naming the problem
            $reason = file_exists($path)
                ? 'a file already exists at that path -- rename or move it out of the way and try again'
                : 'permission denied or an invalid parent path';

            //  Throw an exception
            throw new RuntimeException("Unable to create the directory: {$path} ({$reason}).");
        }
    }
}

if (! function_exists('dt_tables_storage_path')) {
    /**
     * The directory where DataTable JSON files are stored, one file per table.
     * Fixed and non-configurable, so a published config can never drift from it.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    function dt_tables_storage_path(): string
    {
        return resource_path('data-tables');
    }
}
