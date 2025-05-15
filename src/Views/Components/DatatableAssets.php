<?php

namespace Amprest\LaravelDT\Views\Components;

use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\File;
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
     * Create a new component instance.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function __construct()
    {
        //  Get the path of the hot file
        $path = package_path('public/hot');

        //  Define the entry points
        $entryPoints = [
            package_path('resources/js/app.js'),
            package_path('resources/css/app.css'),
        ];

        //  Build the vite instance
        $this->vite = app(Vite::class)
            ->useHotFile(is_file($path) ? $path : null)
            ->useBuildDirectory(package_path('public/build'))
            ->withEntryPoints($entryPoints);
    }

    /**
     * Get the build assets for the datatable.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function assets()
    {
        //  Get the asset path
        $path = package_path('public/build/assets');

        //  Create the asset path if it does not exist
        $files = ! file_exists($path) ? [] : File::files($path);

        //  Get the assets
        return collect($files)->map(fn($file) => new Fluent([
            'path' => route('laravel-dt.asset.show', ['name' => $file->getFilename()]),
            'type' => $file->getExtension(),
        ]));
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function render(): View
    {
        return view('laravel-dt::components.data-table-assets');
    }
}