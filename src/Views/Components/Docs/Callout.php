<?php

namespace Amprest\DtTables\Views\Components\Docs;

use Illuminate\View\Component;
use Illuminate\View\View;

class Callout extends Component
{
    /**
     * Create a callout that is a note, a tip or a warning.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function __construct(public string $type = 'note') {}

    /**
     * Get the label shown at the top of the callout.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function label(): string
    {
        return ucfirst($this->type);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function render(): View
    {
        return view('dt-tables::components.docs.callout');
    }
}
