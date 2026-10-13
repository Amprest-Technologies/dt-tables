# Server-side Table Class

## ServerSideTable Methods

All server-side tables extend `Amprest\DtTables\Tables\ServerSideTable`. Scaffold with `php artisan make:data-table {Name} --server-side` (alias `-s`).

| Method | Required | Purpose |
|--------|----------|---------|
| `query(): Builder\|EloquentBuilder` | yes | Selects the rows. No `limit()`, `offset()` or final order — the package owns those. A default order you add (`latest()`) applies until the browser sorts. |
| `handle(mixed $model, mixed $key): mixed` | yes | Maps one model on the requested page to a display row. `$key` is the row's absolute position in the whole table (page 3 of size 10 starts at 20). |
| `columnMap(): array` | yes | Maps each row key to the SQL it is searched and sorted by. See `column-map.md`. |
| `keyColumn(): string` | no (`'id'`) | The unique column appended as the last sort so paging is stable. Set it when a join makes `id` ambiguous. |
| `filterOptions(): array` | no | Options of `select` filters, keyed by row key. |
| `shared(): array` | no | Computed once per page and read in `handle()` through `$this->shared['key']`. |
| `parameters(): array` | no | Sent back with every page; `parameters()['buttonTrigger']` feeds `DtButtonTriggered`. |
| `authorize(Request $request): bool` | no (`true`) | Return `false` to answer 403. Runs on every rows and options request. |

There is no `before()` or `after()`: rows are mapped one page at a time, so a whole-collection hook has nothing to work on. Move that logic into `query()` or the SQL expressions.

## Constructor Parameters

`:params` on the component become constructor arguments, matched by name:

```blade
<x-data-table id="orders-table" :source="App\DataTables\OrderTable::class" :params="['status' => 'paid']">
```

```php
public function __construct(protected string $status = 'all') {}
```

Parameters are JSON-encoded into the sealed token, so they must be scalars or arrays. Pass an id and load the model inside the class when you need one.

## Complete Example

```php
<?php

namespace App\DataTables;

use Amprest\DtTables\Facades\DataTable;
use Amprest\DtTables\Tables\ServerSideTable;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class OrderTable extends ServerSideTable
{
    /**
     * Initialize the order table.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function __construct(protected string $status = 'all') {}

    /**
     * Return the query that selects the orders.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function query(): Builder
    {
        return Order::query()
            ->select(['orders.id', 'customers.name as customer_name', 'orders.total', 'orders.status', 'orders.reference'])
            ->join('customers', 'orders.customer_id', '=', 'customers.id')
            ->when($this->status !== 'all', fn ($query) => $query->where('orders.status', $this->status))
            ->latest('orders.id');
    }

    /**
     * Map each row key to the SQL it is searched and sorted by.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function columnMap(): array
    {
        return [
            'id' => ['search' => 'orders.id', 'global' => false],
            'customer_name' => 'customers.name',
            'total' => ['search' => 'orders.total', 'global' => false],
            'status' => ['search' => 'orders.status', 'global' => false],
            'reference' => 'orders.reference',
        ];
    }

    /**
     * Return the column that makes every row's position unique.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function keyColumn(): string
    {
        return 'orders.id';
    }

    /**
     * Return the options of the select filters.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function filterOptions(): array
    {
        return [
            'status' => ['pending' => 'Pending', 'paid' => 'Paid', 'refunded' => 'Refunded'],
        ];
    }

    /**
     * Map each order on the page to a display row.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function handle(mixed $order, mixed $key): mixed
    {
        return [
            'id' => $order->id,
            'customer_name' => $order->customer_name,
            'total' => ['value' => number_format($order->total, 2), 'classes' => 'text-end fw-bold'],
            'status' => [
                'value' => prettify($order->status),
                'classes' => DataTable::columnClasses([
                    'fw-bold',
                    'text-success' => $order->status === 'paid',
                    'text-danger' => $order->status === 'refunded',
                ]),
            ],
            'reference' => $order->reference,
            'actions' => [[
                'button' => [
                    'label' => 'View',
                    'attributes' => DataTable::parseAttributes(['href' => route('orders.show', $order->id), 'class' => 'btn btn-primary btn-sm']),
                ],
            ]],
        ];
    }

    /**
     * Determine whether the request may read the orders.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function authorize(Request $request): bool
    {
        return $request->user()?->can('view orders') ?? false;
    }
}
```

## Row Format

`handle()` returns the same cell shapes as an inline table: a plain value, `['value' => ..., 'display' => ..., 'classes' => ...]`, and an `actions` list of `button` and `template` entries (see the `dt-tables-development` skill). Every `<th dtt-title>` key must appear in the returned row; a missing key shows an empty cell.

## Differences From Inline Tables

- No controller: nothing calls `::build()`. The component receives `:source` and the browser fetches pages.
- Copy and Excel buttons are removed from server-side tables; `DtButtonTriggered` therefore does not fire for them. `colvis` still works.
- Column filters work against the database. A `select` filter loads its options from `filterOptions()` instead of the loaded rows, and matches the whole value.
- A filter or sort arrow only appears for columns that `columnMap()` can search or sort (see `column-map.md`).
