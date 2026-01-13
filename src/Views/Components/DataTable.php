<?php

namespace Amprest\DtTables\Views\Components;

use Amprest\DtTables\Models\DataTable as DataTableModel;
use Illuminate\Support\Facades\App;
use Illuminate\View\Component;
use Illuminate\View\View;

class DataTable extends Component
{
    /**
     * Initialize a new component instance.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function __construct(
        public ?string $id = null,
        public ?string $tableId = null,
        public array $payload = [],
        public array $tableData = [],
        public array $tableParams = [],
        public array $columns = [],
        public array $buttons = [],
        public array $theme = [],
        public array $loader = [],
    ) {
        //  Set the tableId to the id if not provided
        $this->tableId ??= $this->id;

        //  Get the payload
        $payload = is_null($this->payload) ? [] : $this->payload;

        //  Define the payload
        $this->tableData = collect($payload['table'] ?? [])->toArray();

        //  Define the parameters
        $this->tableParams = $payload['parameters'] ?? [];

        //  Set up the component
        $this->setUp();
    }

    /**
     * Method to set up the component.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function setUp(): void
    {
        //  Sync the table properties
        $table = DataTableModel::where('key', $this->tableId)->first();

        //  Get the settings
        $settings = fluent($table->settings ?? []);

        //  Get the columns
        $this->columns = $table->columns ?? [];

        //  Get the buttons
        $this->buttons = $settings->buttons ?? config('dt-tables.settings.buttons', []);

        //  Get the theme framework
        $framework = $settings->theme ?? config('dt-tables.settings.theme', 'bootstrap');

        //  Get the theme configuration
        $themeConfig = config("dt-tables.themes.{$framework}", []);

        //  Get the theme
        $this->theme = array_merge(['name' => $framework], $themeConfig);

        //  Set the loader
        $this->loader = isset($settings->loader)
            ? (array) $settings->loader
            : config('dt-tables.settings.loader', []);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function render(): View
    {
        //  Trigger asset injection
        App::instance('dt-table.assets-enabled', true);

        //  Return the view
        return view('dt-tables::components.data-table');
    }
}
