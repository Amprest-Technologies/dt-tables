<?php

namespace Amprest\LaravelDT\Views\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Datatable extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function render(): View
    {
        return view('laravel-dt::components.data-table');
    }
}
