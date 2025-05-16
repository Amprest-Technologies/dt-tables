<?php

namespace Amprest\DtTables\Services;

class AssetService
{
    /**
     * Generate datatables payload.
     *
     * @author Alvin Gichira Kaburu <geekaburu@amprest.co.ke>
     */
    public function load(string $path): mixed
    {
        //  Determine the mime type
        $mimeType = $this->getMimeType($path);

        //  Determine the expiry time
        $expires = strtotime('+1 year');

        //  Determine the last modified time
        $lastModified = filemtime($path);

        //  Create the cache control string
        $cacheControl = 'public, max-age=31536000';

        //  if the server modification date matches the file date
        if ($this->matchesCache($lastModified)) {
            return response()->make('', 304, [
                'Expires' => $this->httpDate($expires),
                'Cache-Control' => $cacheControl,
            ]);
        }

        //  Return the flle as a response type
        return response()->file($path, [
            'Content-Type' => "$mimeType; charset=utf-8",
            'Expires' => $this->httpDate($expires),
            'Cache-Control' => $cacheControl,
            'Last-Modified' => $this->httpDate($lastModified),
        ]);
    }

    /**
     * Get the mime type of the file
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    protected function getMimeType($path): string
    {
        //  Get the file extension
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        //  Determine the mime type
        return match ($extension) {
            'js' => 'application/javascript',
            'css' => 'text/css',
            default => 'text/plain',
        };
    }

    /**
     * Check if the server modifed data is similar to when the file
     * was last modified
     *
     * @author Alvin Gichira Kaburu <geekaburu@amprest.co.ke>
     */
    protected function matchesCache($lastModified): bool
    {
        return @strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '') === $lastModified;
    }

    /**
     * Determine the http date
     *
     * @author Alvin Gichira Kaburu <geekaburu@amprest.co.ke>
     */
    protected function httpDate($timestamp): string
    {
        return sprintf('%s GMT', gmdate('D, d M Y H:i:s', $timestamp));
    }
}
