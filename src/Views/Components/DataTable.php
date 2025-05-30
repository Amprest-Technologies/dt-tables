<?php

namespace Amprest\DtTables\Views\Components;

use Amprest\DtTables\Models\DataTable as DataTableModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Fluent;
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
        public array|Fluent $loader = [],
    ) {
        //  Set the tableId to the id if not provided
        $this->tableId ??= $this->id;

        //  Handle the payload
        $this->payload = collect(is_null($this->payload) ? [] : $this->payload)->toArray();

        //  Set up the component
        $this->setUp();
    }

    /**
     * Method to set up the component.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    protected function setUp(): void
    {
        //  Sync the table properties
        $table = DataTableModel::where('key', $this->tableId)->first();

        //  Get the columns
        $this->columns = $table->columns ?? [];

        //  Get the buttons
        $this->buttons = $table->settings->buttons
            ?? config('dt-tables.settings.buttons', []);

        //  Get the theme framework
        $framework = $table->settings->theme
            ?? config('dt-tables.settings.theme', 'bootstrap');

        //  Get the theme
        $this->theme = array_merge(
            ['name' => $framework],
            config("dt-tables.themes.{$framework}", [])
        );

        //  Set the loader
        $this->loader = Fluent::make($table->settings->loader
            ?? config('dt-tables.settings.loader', []));
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
