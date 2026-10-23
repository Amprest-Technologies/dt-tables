<?php

namespace Nyumbani\DtTables\Support;

class Page
{
    /**
     * Create a page of rows with the source's total and, when it reports one, the count after searching.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function __construct(
        public array $rows,
        public int $total,
        public ?int $filtered = null,
    ) {
        $this->filtered ??= $this->total;
    }
}
