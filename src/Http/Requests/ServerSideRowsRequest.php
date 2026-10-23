<?php

namespace Nyumbani\DtTables\Http\Requests;

use Illuminate\Validation\Rule;

class ServerSideRowsRequest extends ServerSideRequest
{
    /**
     * Return the rules for the request.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'draw' => ['nullable', 'integer'],
            'start' => ['nullable', 'integer', 'min:0'],
            'length' => ['nullable', 'integer', 'min:-1'],
            'search' => ['nullable', 'array'],
            'search.value' => ['nullable', 'string'],
            'columns' => ['nullable', 'array'],
            'columns.*.name' => ['nullable', 'string'],
            'columns.*.search.value' => ['nullable', 'string'],
            'order' => ['nullable', 'array'],
            'order.*.column' => ['required', 'integer', 'min:0'],
            'order.*.dir' => ['nullable', Rule::in(['asc', 'desc'])],
            'exact' => ['nullable', 'array'],
            'exact.*' => ['integer'],
        ];
    }

    /**
     * Get the draw counter DataTables expects echoed back.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function draw(): int
    {
        return (int) $this->input('draw', 0);
    }

    /**
     * Get the position of the first requested row.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function start(): int
    {
        return (int) $this->input('start', 0);
    }

    /**
     * Get the number of rows requested, never more than the configured maximum.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function length(): int
    {
        //  Get the largest page a request may ask for
        $maximum = (int) config('dt-tables.server_side.max_length', 1000);

        //  Get the requested length, where -1 means every row
        $length = (int) $this->input('length', 10);

        //  Cap the length
        return $length < 0 ? $maximum : min($length, $maximum);
    }

    /**
     * Get the phrase typed into the global search box.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function globalSearch(): string
    {
        return (string) $this->input('search.value', '');
    }

    /**
     * Determine whether the browser allows a column to be searched.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function isSearchable(string $key): bool
    {
        //  Find the column the browser sent under this key
        $column = collect($this->input('columns', []))->firstWhere('name', $key);

        //  Columns the browser did not describe are searchable
        return $this->flag($column['searchable'] ?? true);
    }

    /**
     * Get the searches typed into the column filters.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function columnSearches(): array
    {
        //  Get the columns whose filter is an exact match, such as select filters
        $exact = array_map('intval', $this->input('exact', []));

        //  Collect the searches
        $searches = [];

        //  Loop through the columns
        foreach ($this->input('columns', []) as $index => $column) {
            //  Get the term typed into the column's filter
            $term = $column['search']['value'] ?? '';

            //  Skip columns without a term or that the browser does not allow to be searched
            if (! filled($term) || ! filled($column['name'] ?? null) || ! $this->flag($column['searchable'] ?? true)) {
                continue;
            }

            //  Add the search
            $searches[] = ['key' => $column['name'], 'term' => $term, 'exact' => in_array($index, $exact, true)];
        }

        //  Return the searches
        return $searches;
    }

    /**
     * Get the columns to order by, in priority order.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function orderings(): array
    {
        //  Get the columns the browser sent
        $columns = $this->input('columns', []);

        //  Collect the orderings
        $orderings = [];

        //  Loop through the orderings
        foreach ($this->input('order', []) as $order) {
            //  Find the column being ordered
            $column = $columns[$order['column']] ?? [];

            //  Skip columns that are unknown or that the browser does not allow to be ordered
            if (! filled($column['name'] ?? null) || ! $this->flag($column['orderable'] ?? true)) {
                continue;
            }

            //  Add the ordering
            $orderings[] = [
                'key' => $column['name'],
                'direction' => ($order['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc',
            ];
        }

        //  Return the orderings
        return $orderings;
    }

    /**
     * Read a boolean the way DataTables sends it.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function flag(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
    }
}
