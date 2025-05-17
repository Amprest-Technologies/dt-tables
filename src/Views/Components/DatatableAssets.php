<?php

namespace Amprest\DtTables\Views\Components;

use Illuminate\Foundation\Vite;
use Illuminate\Support\Fluent;
use Illuminate\View\Component;
use Illuminate\View\View;

class DataTableAssets extends Component
{
    /**
     * The Vite instance.
     *
     * @var \Illuminate\Foundation\Vite
     */
    public Vite $vite;

    /**
     * Get the entry points for vite.
     *
     * @var array
     */
    public array $entryPoints = [];

    /**
     * Create a new component instance.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function __construct(public string $mode = 'client')
    {
        //  Get the path of the hot file
        $path = package_path('public/hot');
        
        //  Define the entry points
        $this->entryPoints = [
            package_path("resources/js/{$mode}.js"),
            package_path("resources/css/{$mode}.css"),
        ];

        //  Build the vite instance
        $this->vite = app(Vite::class)
            ->useHotFile(is_file($path) ? $path : null)
            ->useBuildDirectory(package_path('public/build'))
            ->withEntryPoints($this->entryPoints);
    }

    /**
     * Get the build assets for the datatable.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function assets()
    {
        //  Parse the manifest file
        $files = $this->parseManifest();

        //  Get the assets
        return collect($files)->map(fn($file) => new Fluent([
            'path' => route('dt-tables.asset.show', ['name' => basename($file)]),
            'type' => pathinfo($file, PATHINFO_EXTENSION),
        ]));
    }

    /**
     * Parse the manifest file.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function parseManifest(): array
    {
        //  Get the manifest file
        $manifest = json_decode(file_get_contents(package_path('public/build/manifest.json')));

        //  Get the assets
        $assets = collect($manifest)
            ->where('isEntry', true)
            ->filter(fn($file, $asset) => str_contains($asset, $this->mode));

        //  Loop through the assets
        foreach($assets as $asset) {
            //  Get the asset path
            $files[] = package_path("public/build/assets/{$asset->file}");

            //  Get the imports
            foreach($asset->imports ?? [] as $file) {
                $files[] = package_path("public/build/assets/{$file}");
            }

            //  Get the css files
            foreach($asset->css ?? [] as $file) {
                $files[] = package_path("public/build/assets/{$file}");
            }
        }

        //  Return the files in the manifest
        return $files ?? [];
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function render(): View
    {
        return view('dt-tables::components.data-table-assets');
    }
}