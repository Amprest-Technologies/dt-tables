<?php

namespace Amprest\DtTables\Http\Controllers;

use Amprest\DtTables\Services\AssetService;

class AssetController
{
    /**
     * Load the datatable assets.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function __invoke(AssetService $service, string $name): mixed
    {
        //  Get the file path
        $filePath = package_path("public/build/assets/{$name}");

        //  Load the file
        return $service->load($filePath);
    }
}
