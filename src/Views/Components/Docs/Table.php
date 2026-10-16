<?php

namespace Amprest\DtTables\Views\Components\Docs;

use Illuminate\Support\HtmlString;
use Illuminate\View\Component;
use Illuminate\View\View;

class Table extends Component
{
    /**
     * Create a table from its column titles and its rows, each row a list of cells.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function __construct(public array $columns = [], public array $rows = []) {}

    /**
     * Escape a cell and turn the text between backticks into inline code.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function cell(string $text): HtmlString
    {
        return new HtmlString(preg_replace('/`([^`]+)`/', '<code>$1</code>', e($text)));
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function render(): View
    {
        return view('dt-tables::components.docs.table');
    }
}
