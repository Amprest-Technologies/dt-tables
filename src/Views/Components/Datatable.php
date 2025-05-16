<?php

namespace Amprest\DtTables\Views\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class DataTable extends Component
{
    /**
     * Get the view / contents that represent the component.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function render(): View
    {
        return view('dt-tables::components.data-table');
    }
}
