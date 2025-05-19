<?php

namespace Amprest\DtTables\Views\Components;

use Amprest\DtTables\Models\DataTable as DataTableModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\View\Component;
use Illuminate\View\View;

class DataTable extends Component
{
    /**
     * Initialize a new component instance.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function __construct(
        public ?string $id = null,
        public ?string $tableId = null,
        public array|Collection|null $payload = [],
        public array|Collection $columns = [],
        public array|Collection $buttons = [],
        public array $theme = [],
    )
    {
        //  Set the tableId to the id if not provided
        $this->tableId ??= $this->id;

        //  Handle the payload
        $this->payload = collect(is_null($this->payload) ? [] : $this->payload)->toArray();

        //  Get the table properties
        $table = DataTableModel::query()
            ->with('columns')
            ->where('identifier', $this->tableId)
            ->first()?->toResource() ?? null;

        //  Get the columns
        $this->columns = $table->columns ?? [];

        //  Get the buttons
        $this->buttons = $table->settings->buttons
            ?? config('dt-tables.settings.buttons', []);

        //  Get the theme framework
        $framework = $table->settings->theme
            ?? config('dt-tables.settings.theme', 'bootstrap5');

        //  Get the theme
        $this->theme = config("dt-tables.themes.{$framework}", []);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function render(): View
    {
        //  Trigger asset injection
        App::instance('dt-table.assets-enabled', true);

        //  Return the view
        return view('dt-tables::components.data-table');
    }
}
