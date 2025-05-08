<?php

namespace Amprest\LaravelDT\Views\Components;

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
        //  Get the package name
        $name = config('laravel-dt.name');

        //  Get the package author
        $author = config('laravel-dt.author');

        //  Return the path
        $path = "vendor/$author/$name";

        //  Render the assets
        return new HtmlString(
            Blade::render(<<<blade
                @vite('resources/js/app.js', "$path")
                @vite('resources/sass/app.scss', "$path")
            blade)
        );
    }
}
