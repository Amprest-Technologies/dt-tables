<?php

namespace Amprest\LaravelDT\Views\Components;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Fluent;
use Illuminate\View\Component;
use Illuminate\View\View;

class DatatableAssets extends Component
{
    /**
     * Create a new component instance.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function __construct()
    {
    
    }

    /**
     * Get the build assets for the datatable.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function assets()
    {
        //  Get the public path of the package
        $publicPath = package_path('public');

        //  Get the asset path
        $assetPath = "$publicPath/build/assets";

        //  Get the assets
        return collect(File::files($assetPath))->map(fn($file) => new Fluent([
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
        return view('laravel-dt::components.datatable-assets', [
            'assets' => $this->assets(),
        ]);
    }
}
