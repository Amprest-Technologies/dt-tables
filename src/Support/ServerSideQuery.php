<?php

namespace Amprest\DtTables\Support;

use Amprest\DtTables\Http\Requests\ServerSideRowsRequest;
use Amprest\DtTables\Tables\ServerSideTable;
use Closure;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;

class ServerSideQuery
{
    /**
     * The table's columnMap().
     *
     * @var array<string, mixed>
     */
    protected array $map;

    /**
     * Create a query runner for a table.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function __construct(protected ServerSideTable $table)
    {
        $this->map = $table->columnMap();
    }

    /**
     * Run a DataTables request and return its response payload.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function rows(ServerSideRowsRequest $request): array
    {
        //  Get the table's query
        $query = $this->table->query();

        //  Remember the order the table asked for, to use when the browser does not order
        $defaultOrders = $this->baseQuery($query)->orders ?? [];

        //  Strip the paging and ordering
        $query->limit(null)->offset(null)->reorder();

        //  Apply the searches to a copy, so the unsearched rows can still be counted
        $searched = $this->search(clone $query, $request);

        //  Count the rows left after searching
        $filtered = $this->count($searched);

        //  Count every row, which is the same number when nothing is being searched
        $total = $this->isSearching($request) ? $this->count($query) : $filtered;

        //  Order the rows
        $this->order($searched, $request, $defaultOrders);

        //  Take the requested page
        $models = $searched->offset($request->start())->limit($request->length())->get();

        //  Return the payload
        return [
            'draw' => $request->draw(),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $this->table->rows($models, $request->start()),
            'parameters' => $this->table->parameters(),
        ];
    }

    /**
     * Get the select-filter options for a column as value/label pairs.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function options(string $column): array
    {
        //  Get the options the table declared for the column
        $options = $this->table->filterOptions()[$column] ?? [];

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
     * Get the row keys the server can search and sort by, so the browser only offers those.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function columns(): array
    {
        //  Get the searchable keys
        $searchable = [];

        //  Get the orderable keys
        $orderable = [];

        //  Loop through the keys
        foreach (array_keys($this->map) as $key) {
            //  A key with a search target can be searched
            if ($this->searchOf((string) $key) !== null) {
                $searchable[] = (string) $key;
            }

            //  A key with a sort expression can be sorted
            if ($this->sortOf((string) $key) !== null) {
                $orderable[] = (string) $key;
            }
        }

        //  Return both lists
        return [
            'searchable' => $searchable,
            'orderable' => $orderable,
        ];
    }

    /**
     * Determine whether any search is narrowing the rows.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function isSearching(ServerSideRowsRequest $request): bool
    {
        //  A global search
        if (filled($request->globalSearch())) {
            return true;
        }

        //  Or a search on a column that is mapped
        foreach ($request->columnSearches() as $search) {
            if ($this->searchOf($search['key']) !== null) {
                return true;
            }
        }

        //  R
        return false;
    }

    /**
     * Apply the global search and the column searches.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function search(Builder|EloquentBuilder $query, ServerSideRowsRequest $request): Builder|EloquentBuilder
    {
        //  Every word of the global search must match at least one column
        foreach (TableSearch::terms($request->globalSearch()) as $term) {
            $this->whereAny($query, $this->globalTargets($request), $term['word'], $term['negative']);
        }

        //  Each column search narrows the rows further
        foreach ($request->columnSearches() as $search) {
            $this->searchColumn($query, $search);
        }

        //  Return the searched query
        return $query;
    }

    /**
     * Get what the global search matches against: the mapped columns that have not opted out.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function globalTargets(ServerSideRowsRequest $request): array
    {
        //  Collect the targets
        $targets = [];

        foreach ($this->map as $key => $entry) {
            //  Skip columns the browser or the table keeps out of the global search
            if (! $request->isSearchable($key) || (is_array($entry) && ! ($entry['global'] ?? true))) {
                continue;
            }

            //  Skip columns that cannot be searched
            if (($target = $this->searchOf($key)) === null) {
                continue;
            }

            //  Add the target
            $targets[] = $target;
        }

        //  Return the targets
        return $targets;
    }

    /**
     * Apply one column's search.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function searchColumn(Builder|EloquentBuilder $query, array $search): void
    {
        //  Columns that are not mapped cannot be searched
        if (($target = $this->searchOf($search['key'])) === null) {
            return;
        }

        //  Select filters match the whole value
        if ($search['exact']) {
            $this->whereAny($query, [$target], trim($search['term']), false, exact: true);

            return;
        }

        //  Otherwise every word must be contained
        foreach (TableSearch::terms($search['term']) as $term) {
            $this->whereAny($query, [$target], $term['word'], $term['negative']);
        }
    }

    /**
     * Add a condition requiring a word to match at least one of the columns.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function whereAny(Builder|EloquentBuilder $query, array $targets, string $word, bool $negative, bool $exact = false): void
    {
        //  Nothing to match against means nothing matches
        if ($targets === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        //  Group the conditions so they are ORed together
        $group = function ($group) use ($targets, $word, $exact) {
            foreach ($targets as $target) {
                //  A closure builds its own condition
                if ($target instanceof Closure) {
                    $group->orWhere(fn ($inner) => $target($inner, $word));

                    continue;
                }

                //  Get the lowercased text of the expression
                $text = $this->text($group, $target);

                //  Compare the whole text, or look for the word inside it
                $exact
                    ? $group->orWhereRaw("{$text} = ?", [mb_strtolower($word)])
                    : $group->orWhereRaw("{$text} like ? escape '!'", ['%'.TableSearch::escapeLike($word).'%']);
            }
        };

        //  A negated word excludes rows matching any column
        $negative ? $query->whereNot($group) : $query->where($group);
    }

    /**
     * Order the rows: the browser's ordering, else the table's own, always ending on the key column.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function order(Builder|EloquentBuilder $query, ServerSideRowsRequest $request, array $defaultOrders): void
    {
        //  Track whether the browser ordered by any column
        $ordered = false;

        //
        foreach ($request->orderings() as $ordering) {
            //  Skip columns that cannot be sorted
            if (($expression = $this->sortOf($ordering['key'])) === null) {
                continue;
            }

            //  Order by the expression
            $query->orderBy($this->raw($query, $expression), $ordering['direction']);

            //  Remember that the browser ordered
            $ordered = true;
        }

        //  Fall back to the order the table asked for
        if (! $ordered) {
            $this->baseQuery($query)->orders = $defaultOrders;
        }

        //  Finish with the key column so paging is stable
        $query->orderBy($this->table->keyColumn());
    }

    /**
     * Count the rows of a query, wrapping it when grouping or unions require.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function count(Builder|EloquentBuilder $query): int
    {
        return (int) $this->baseQuery($query)->getCountForPagination();
    }

    /**
     * Get the search target of a row key: an expression or a closure.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function searchOf(?string $key): string|Closure|null
    {
        //  Keys that are not mapped cannot be searched
        $entry = $this->map[$key] ?? null;

        //  The array form names its search target explicitly
        return is_array($entry) ? ($entry['search'] ?? null) : $entry;
    }

    /**
     * Get the sort expression of a row key.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function sortOf(?string $key): ?string
    {
        //  Keys that are not mapped cannot be sorted
        $entry = $this->map[$key] ?? null;

        //  A plain entry sorts by its expression
        if (! is_array($entry)) {
            return is_string($entry) ? $entry : null;
        }

        //  The array form may set the sort expression apart, otherwise a search expression is used
        return $entry['sort'] ?? (is_string($entry['search'] ?? null) ? $entry['search'] : null);
    }

    /**
     * Build the SQL that yields the lowercased text of an expression.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function text(Builder|EloquentBuilder $query, string $expression): string
    {
        //  Pick a text type the database accepts
        $type = match ($this->baseQuery($query)->getConnection()->getDriverName()) {
            'mysql', 'mariadb' => 'char',
            'sqlsrv' => 'nvarchar(4000)',
            default => 'text',
        };

        //  Lowercase the text, treating null as empty
        return 'lower(coalesce(cast('.$this->wrap($query, $expression)." as {$type}), ''))";
    }

    /**
     * Wrap an expression for the query's grammar, leaving raw expressions untouched.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function wrap(Builder|EloquentBuilder $query, string $expression): string
    {
        return preg_match('/^[\w.]+$/', $expression)
            ? $this->baseQuery($query)->getGrammar()->wrap($expression)
            : $expression;
    }

    /**
     * Wrap an expression so it can be passed to orderBy().
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function raw(Builder|EloquentBuilder $query, string $expression): Expression|string
    {
        return preg_match('/^[\w.]+$/', $expression)
            ? $expression
            : $this->baseQuery($query)->raw($expression);
    }

    /**
     * Get the underlying query builder.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function baseQuery(Builder|EloquentBuilder $query): Builder
    {
        return $query instanceof EloquentBuilder
            ? $query->getQuery()
            : $query;
    }
}
