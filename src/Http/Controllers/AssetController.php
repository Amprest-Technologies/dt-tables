<?php

namespace Nyumbani\DtTables\Http\Controllers;

use Nyumbani\DtTables\DtTablesServiceProvider;
use Nyumbani\DtTables\Services\AssetService;

class AssetController
{
    /**
     * Load the datatable assets.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function __invoke(AssetService $service, string $name): mixed
    {
        //  Only the built scripts and styles are served
        abort_unless(preg_match('/^[\w.-]+\.(js|css)$/', $name) === 1, 404);

        //  Get the file path
        $filePath = DtTablesServiceProvider::path("public/build/assets/{$name}");

        //  A file that was never built is not found
        abort_unless(is_file($filePath), 404);

        //  Load the file
        return $service->load($filePath);
    }
}
