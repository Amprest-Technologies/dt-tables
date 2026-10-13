<?php

namespace Amprest\DtTables\Views\Components;

use Amprest\DtTables\Models\DataTable as DataTableModel;
use Amprest\DtTables\Support\ServerSideQuery;
use Amprest\DtTables\Support\TableSource;
use Illuminate\Support\Facades\App;
use Illuminate\View\Component;
use Illuminate\View\View;
use InvalidArgumentException;

class DataTable extends Component
{
    /**
     * The markup of the loader, shown before the first draw and while a page loads.
     */
    public string $loaderMarkup = '';

    /**
     * Initialize a new component instance.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function __construct(
        public ?string $id = null,
        public ?string $tableId = null,
        public ?string $source = null,
        public array $params = [],
        public array $serverSide = [],
        public array $payload = [],
        public array $tableData = [],
        public array $tableParams = [],
        public array $columns = [],
        public array $buttons = [],
        public array $theme = [],
        public array $loader = [],
        public int $pageLength = 10,
        public bool $ordering = true,
        public bool $searching = true,
        public bool $paging = true,
        public bool $info = true,
        public bool $scrollX = false,
    ) {
        //  The id is the DOM id and tableId is the config key; each falls back to the other
        $this->tableId ??= $this->id;

        //  The id is the DOM id
        $this->id ??= $this->tableId;

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
     * Build the server-side settings for a table fed by a source class.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function setUpServerSide(): void
    {
        //  Nothing to do for inline tables
        if (is_null($this->source)) {
            return;
        }

        //  A table is fed either inline or from a source, never both
        if (! empty($this->payload)) {
            throw new InvalidArgumentException(
                'The [source] and [payload] attributes cannot be used together'
            );
        }

        //  Seal the class and its parameters so the browser cannot change them
        $token = TableSource::seal($this->source, $this->params);

        //  Get the table source
        $source = (new TableSource($this->source, $this->params))->instance();

        //  Ask the table which columns the server can search and sort by
        $columns = (new ServerSideQuery($source))->columns();

        //  Define the server-side settings
        $this->serverSide = [
            'rowsUrl' => route('dt-tables.api.server-side.rows'),
            'optionsUrl' => route('dt-tables.api.server-side.options'),
            'token' => $token,
            ...$columns,
        ];
    }

    /**
     * Method to set up the component.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function setUp(): void
    {
        //  Sync the table properties
        $table = DataTableModel::find($this->tableId);

        //  Get the settings
        $settings = fluent($table->settings ?? []);

        //  Get the columns
        $this->columns = collect($table->columns ?? [])->toArray();

        //  Get the buttons
        $this->buttons = $settings->buttons ?? config('dt-tables.settings.buttons', []);

        //  Set up server-side loading when a source table class is given
        $this->setUpServerSide();

        //  Copy and Excel only see the loaded page, so server-side tables hide them
        if ($this->serverSide) {
            $this->buttons = array_values(array_diff((array) $this->buttons, ['copy', 'excel']));
        }

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

        //  Build the loader markup
        $this->loaderMarkup = $this->buildLoaderMarkup();

        //  Get the behaviour
        $behaviour = fluent((array) ($settings->behaviour ?? []));

        //  Set the page length
        $this->pageLength = (int) ($behaviour->page_length
            ?? config('dt-tables.settings.behaviour.page_length', 10));

        //  Set the ordering
        $this->ordering = (bool) ($behaviour->ordering ?? config('dt-tables.settings.behaviour.ordering', true));

        //  Set the searching
        $this->searching = (bool) ($behaviour->searching ?? config('dt-tables.settings.behaviour.searching', true));

        //  Set the paging
        $this->paging = (bool) ($behaviour->paging ?? config('dt-tables.settings.behaviour.paging', true));

        //  Set the info
        $this->info = (bool) ($behaviour->info ?? config('dt-tables.settings.behaviour.info', true));

        //  Set the scrollX
        $this->scrollX = (bool) ($behaviour->scroll_x ?? config('dt-tables.settings.behaviour.scroll_x', false));
    }

    /**
     * Build the markup of the loader from its settings.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function buildLoaderMarkup(): string
    {
        //  Get the loader settings
        $loader = fluent($this->loader);

        //  No markup is needed when the loader is disabled
        if (! ($loader->enabled ?? false)) {
            return '';
        }

        //  Add the image when one is set
        $markup = ($loader->image ?? false)
            ? '<div class="loader-image"><img src="'.e(asset($loader->image)).'" alt="Loading..."></div>'
            : '';

        //  Add the message and return the markup
        return $markup.'<div class="loader-text">'.e($loader->message ?? 'Loading...').'</div>';
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
