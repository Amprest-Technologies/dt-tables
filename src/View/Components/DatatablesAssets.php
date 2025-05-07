<?php

namespace Amprest\LaravelDT\View\Components;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\View\Component;

class DatatablesAssets extends Component
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
     * Get the view / contents that represent the component.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function render(): string
    {
        //  Get the script path
        $scriptPath = package_path('resources/js/app.js');

        //  Get the style path
        $stylePath = package_path('resources/sass/app.scss');

        //  Render the assets
        return new HtmlString(
            Blade::render(<<<blade
                @vite(["$scriptPath", "$stylePath"])
            blade)
        );
    }
}
