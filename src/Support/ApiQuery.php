<?php

namespace Amprest\DtTables\Support;

use Amprest\DtTables\Http\Requests\ServerSideRowsRequest;
use Amprest\DtTables\Tables\ApiTable;

class ApiQuery implements ServerSideRunner
{
    /**
     * The table's columnMap().
     *
     * @var array<string, mixed>
     */
    protected array $map;

    /**
     * Create a runner for an API table.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function __construct(protected ApiTable $table)
    {
        $this->map = $table->columnMap();
    }

    /**
     * Run a DataTables request against the API and return its response payload.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function rows(ServerSideRowsRequest $request): array
    {
        //  Ask the API for the page
        $page = $this->table->fetch($this->pageRequest($request));

        //  Return the payload
        return [
            'draw' => $request->draw(),
            'recordsTotal' => $page->total,
            'recordsFiltered' => $page->filtered,
            'data' => $this->table->rows($page->rows, $request->start()),
            'parameters' => $this->table->parameters(),
        ];
    }

    /**
     * Translate the browser's request into the page the API is asked for.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function pageRequest(ServerSideRowsRequest $request): PageRequest
    {
        //  Collect the remote fields the global search matches against
        $searchFields = [];

        //  Loop through the columns
        foreach ($this->map as $key => $entry) {
            //  Skip columns the browser or the table keeps out of the global search
            if (! $request->isSearchable($key) || (is_array($entry) && ! ($entry['global'] ?? true))) {
                continue;
            }

            //  Add the columns that can be searched
            if (($field = $this->searchOf((string) $key)) !== null) {
                $searchFields[] = $field;
            }
        }

        //  Collect the column searches the API understands
        $filters = [];

        //  Loop through the column searches
        foreach ($request->columnSearches() as $search) {
            //  Skip columns that cannot be searched
            if (($field = $this->searchOf($search['key'])) === null) {
                continue;
            }

            //  Add the filter
            $filters[] = [
                'key' => $search['key'],
                'field' => $field,
                'term' => trim($search['term']),
                'exact' => $search['exact'],
            ];
        }

        //  Collect the orderings the API understands
        $orders = [];

        //  Loop through the orderings
        foreach ($request->orderings() as $ordering) {
            //  Skip columns that cannot be sorted
            if (($field = $this->sortOf($ordering['key'])) === null) {
                continue;
            }

            //  Add the ordering
            $orders[] = [
                'key' => $ordering['key'],
                'field' => $field,
                'direction' => $ordering['direction'],
            ];
        }

        //  Return the page request
        return new PageRequest(
            start: $request->start(),
            length: $request->length(),
            search: trim($request->globalSearch()),
            searchFields: $searchFields,
            filters: $filters,
            orders: $orders,
        );
    }

    /**
     * Get the select-filter options for a column as value/label pairs.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function options(string $column): array
    {
        //  Get the options the table declared for the column
        $options = $this->table->optionsFor($column);

        //  A plain list uses each value as its own label
        $isList = array_is_list($options);

        //  Return value/label pairs
        return array_map(
            fn ($label, $value) => ['value' => (string) ($isList ? $label : $value), 'label' => (string) $label],
            $options,
            array_keys($options),
        );
    }

    /**
     * Get the row keys the API can search and sort by, so the browser only offers those.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function columns(): array
    {
        //  Collect the searchable key
        $searchable = [];

        //  Collect the orderable keys
        $orderable = [];

        //  Loop through the keys
        foreach (array_keys($this->map) as $key) {
            //  A key with a search field can be searched
            if ($this->searchOf((string) $key) !== null) {
                $searchable[] = (string) $key;
            }

            //  A key with a sort field can be sorted
            if ($this->sortOf((string) $key) !== null) {
                $orderable[] = (string) $key;
            }
        }

        //  Return both lists, and the keys that are filtered with a select
        return [
            'searchable' => $searchable,
            'orderable' => $orderable,
            'selects' => array_map('strval', $this->table->filterKeys()),
        ];
    }

    /**
     * Get the remote field a row key is searched by.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function searchOf(?string $key): ?string
    {
        //  Keys that are not mapped cannot be searched
        $entry = $this->map[$key] ?? null;

        //  The array form names its search field explicitly
        $field = is_array($entry) ? ($entry['search'] ?? null) : $entry;

        //  Only a field name can be sent to an API
        return is_string($field) ? $field : null;
    }

    /**
     * Get the remote field a row key is sorted by.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function sortOf(?string $key): ?string
    {
        //  Keys that are not mapped cannot be sorted
        $entry = $this->map[$key] ?? null;

        //  A plain entry sorts by its field
        if (! is_array($entry)) {
            return is_string($entry) ? $entry : null;
        }

        //  The array form may set the sort field apart, otherwise the search field is used
        $field = $entry['sort'] ?? $entry['search'] ?? null;

        //  Only a field name can be sent to an API
        return is_string($field) ? $field : null;
    }
}
