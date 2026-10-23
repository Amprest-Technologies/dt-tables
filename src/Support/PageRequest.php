<?php

namespace Nyumbani\DtTables\Support;

class PageRequest
{
    /**
     * Create the description of the page a remote source is asked for, with remote field names for the searches and orderings.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function __construct(
        public int $start = 0,
        public int $length = 10,
        public string $search = '',
        public array $searchFields = [],
        public array $filters = [],
        public array $orders = [],
    ) {}

    /**
     * Get the number of the page that holds the first requested row, starting at 1.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function page(): int
    {
        return intdiv($this->start, max($this->length, 1)) + 1;
    }

    /**
     * Get the column searches as remote field => term pairs.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function filterTerms(): array
    {
        return collect($this->filters)
            ->pluck('term', 'field')
            ->all();
    }
}
