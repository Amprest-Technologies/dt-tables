<?php

namespace Amprest\DtTables\Tables;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

abstract class ServerSideTable
{
    /**
     * Shared data computed once and available to all rows during mapping.
     */
    protected array $shared = [];

    /**
     * Return the query that selects the table's rows.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    abstract public function query(): Builder|EloquentBuilder;

    /**
     * Map each model on the requested page to a display row array.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    abstract public function handle(mixed $model, mixed $key): mixed;

    /**
     * Map each row key to the SQL it can be searched and sorted by.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    abstract public function columnMap(): array;

    /**
     * Return the column that makes every row's position unique, so paging is stable.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function keyColumn(): string
    {
        return 'id';
    }

    /**
     * Return the options of select filters, keyed by row key.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function filterOptions(): array
    {
        return [];
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
     * Compute the shared data and map a page of models to rows.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function rows(iterable $models, int $offset = 0): array
    {
        //  Compute shared data once before the loop
        $this->shared = $this->shared();

        //  Map each model, keyed by its position in the whole table
        foreach ($models as $index => $model) {
            $rows[] = $this->handle($model, $offset + $index);
        }

        //  Return the rows
        return $rows ?? [];
    }
}
