<?php

namespace Amprest\DtTables\Tables;

use Illuminate\Support\Collection;

abstract class BaseTable
{
    /**
     * Shared data computed once and available to all rows during mapping.
     */
    protected array $shared = [];

    /**
     * Return the query to be executed for the table.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    abstract public function query(): mixed;

    /**
     * Map each model to a display row array.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    abstract public function handle(mixed $model, mixed $key): mixed;

    /**
     * Return data computed once and shared across all rows.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function shared(): array
    {
        return [];
    }

    /**
     * Pre-process the collection before row mapping.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function before(Collection $data): Collection
    {
        return $data;
    }

    /**
     * Post-process the mapped collection.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function after(Collection $data): Collection
    {
        return $data;
    }

    /**
     * Return parameters for export activity logging.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function parameters(): array
    {
        return [];
    }

    /**
     * Run the full pipeline and return the table payload.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public static function build(mixed ...$args): array
    {
        //  Instantiate the table class with provided arguments
        $instance = new static(...$args);

        //  Normalise query result to a Collection
        $query = $instance->query();

        //  If the query is already a Collection, use it directly; otherwise, execute the query to get results
        $results = $query instanceof Collection ? $query : $query->get();

        //  Compute shared data once before the loop
        $instance->shared = $instance->shared();

        //  Pre-process the collection before mapping rows; values() ensures sequential int keys for handle()
        $results = $instance->before($results)->values();

        //  Map each model to a display row array
        foreach ($results as $key => $model) {
            $table[] = $instance->handle($model, $key);
        }

        //  Post-process the mapped collection
        $table = $instance->after(collect($table ?? []));

        //  Return the final payload with table data and parameters for export logging
        return [
            'table' => $table->toArray(),
            'parameters' => $instance->parameters(),
        ];
    }

    /**
     * Return a new instance without running the pipeline.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public static function make(mixed ...$args): static
    {
        return new static(...$args);
    }

    /**
     * Get the raw table data.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function raw(): array
    {
        return self::build()['table'] ?? [];
    }
}
