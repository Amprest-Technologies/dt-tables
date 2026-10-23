<?php

namespace Nyumbani\DtTables\Tables;

use Closure;
use Illuminate\Http\Request;
use Nyumbani\DtTables\Support\ServerSideRunner;

abstract class ServerSideSource
{
    /**
     * Shared data computed once and available to all rows during mapping.
     */
    protected array $shared = [];

    /**
     * Return the runner that pages, searches and sorts this source.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    abstract public function runner(): ServerSideRunner;

    /**
     * Map each item on the requested page to a display row array.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    abstract public function handle(mixed $model, mixed $key): mixed;

    /**
     * Map each row key to what it can be searched and sorted by.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    abstract public function columnMap(): array;

    /**
     * Return the options of select filters, keyed by row key. A value may be a closure, which is only run when that filter is opened.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function filterOptions(): array
    {
        return [];
    }

    /**
     * Return the options of one select filter, running its closure if it has one.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function optionsFor(string $column): array
    {
        //  Get the options declared for the column
        $options = $this->filterOptions()[$column] ?? [];

        //  Run a closure now, so only the filter being opened is built
        return $options instanceof Closure ? $options() : $options;
    }

    /**
     * Return the row keys that get a select filter. Names the keys without running closures, so declare costly options as closures.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function filterKeys(): array
    {
        return array_keys($this->filterOptions());
    }

    /**
     * Return data computed once and shared across the rows of a page.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function shared(): array
    {
        return [];
    }

    /**
     * Return parameters for export activity logging.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function parameters(): array
    {
        return [];
    }

    /**
     * Determine whether the request may read this table's data.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function authorize(Request $request): bool
    {
        return true;
    }

    /**
     * Compute the shared data and map a page of items to rows.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function rows(iterable $models, int $offset = 0): array
    {
        //  Compute shared data once before the loop
        $this->shared = $this->shared();

        //  Map each item, keyed by its position in the whole table
        foreach ($models as $index => $model) {
            $rows[] = $this->handle($model, $offset + $index);
        }

        //  Return the rows
        return $rows ?? [];
    }
}
