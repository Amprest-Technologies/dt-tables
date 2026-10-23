<?php

namespace Nyumbani\DtTables\Support;

use RuntimeException;

class Filesystem
{
    /**
     * Create a directory if it doesn't already exist, tolerating a race with
     * another process that creates it in the meantime.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public static function ensureDirectoryExists(string $path, int $mode = 0755): void
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
