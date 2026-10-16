<p>
    A database table extends <code>ServerSideTable</code>. You describe the rows with a query, and the package adds the searching, sorting
    and paging to it in SQL.
</p>

<h2>Create the table</h2>
<x-docs-code lang="bash">
@verbatim
php artisan make:data-table OrderTable --server-side
@endverbatim
</x-docs-code>

<x-docs-code lang="php" title="app/DataTables/OrderTable.php">
@verbatim
&lt;?php

namespace App\DataTables;

use Amprest\DtTables\Facades\DataTable;
use Amprest\DtTables\Tables\ServerSideTable;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class OrderTable extends ServerSideTable
{
    public function __construct(protected string $status = 'all') {}

    public function query(): Builder
    {
        return Order::query()
            ->select(['orders.id', 'customers.name as customer_name', 'orders.total', 'orders.status', 'orders.reference'])
            ->join('customers', 'orders.customer_id', '=', 'customers.id')
            ->when($this->status !== 'all', fn ($query) => $query->where('orders.status', $this->status))
            ->latest('orders.id');
    }

    public function columnMap(): array
    {
        return [
            'id' => ['search' => 'orders.id', 'global' => false],
            'customer_name' => 'customers.name',
            'total' => ['sort' => 'orders.total'],
            'status' => ['search' => 'orders.status', 'global' => false],
            'reference' => 'orders.reference',
        ];
    }

    public function keyColumn(): string
    {
        return 'orders.id';
    }

    public function filterOptions(): array
    {
        return [
            'status' => ['pending' => 'Pending', 'paid' => 'Paid', 'refunded' => 'Refunded'],
            'customer_name' => fn () => Customer::orderBy('name')->pluck('name', 'name')->all(),
        ];
    }

    public function handle(mixed $order, mixed $key): mixed
    {
        return [
            'id' => $order->id,
            'customer_name' => $order->customer_name,
            'total' => ['value' => number_format($order->total, 2), 'classes' => 'fw-bold'],
            'status' => [
                'value' => ucfirst($order->status),
                'classes' => DataTable::columnClasses([
                    'text-success' => $order->status === 'paid',
                    'text-danger' => $order->status === 'refunded',
                ]),
            ],
            'reference' => $order->reference,
        ];
    }

    public function authorize(Request $request): bool
    {
        return $request->user()?->can('view orders') ?? false;
    }
}
@endverbatim
</x-docs-code>

<h2>Render it</h2>
<x-docs-code lang="blade" title="resources/views/orders/index.blade.php">
@verbatim
<x-data-table id="orders-table" :source="App\DataTables\OrderTable::class" :params="['status' => 'paid']" title="Orders" class="table table-sm">
    <thead>
        <tr>
            <th dtt-row-index>#</th>
            <th dtt-title="id" dtt-align="left">ID</th>
            <th dtt-title="customer_name">Customer</th>
            <th dtt-title="total" dtt-align="right">Total</th>
            <th dtt-title="status">Status</th>
            <th dtt-title="reference">Reference</th>
        </tr>
    </thead>
</x-data-table>
@endverbatim
</x-docs-code>

<p>Register <code>orders-table</code> in the admin with the <strong>Server-side</strong> mode. No controller is needed.</p>

<h2>The methods</h2>
<x-docs-table
    :columns="['Method', 'Required', 'Purpose']"
    :rows="[
        ['`query()`', 'Yes', 'Selects the rows. Do not call `limit()`, `offset()` or a final `orderBy()`, because the package owns those. A default order such as `latest()` applies until someone sorts.'],
        ['`handle($model, $key)`', 'Yes', 'Maps one row of the page to its cells. `$key` is the position of the row in the whole table.'],
        ['`columnMap()`', 'Yes', 'Says what each column is searched and sorted by. See The Column Map.'],
        ['`keyColumn()`', 'No', 'The unique column that keeps paging stable. Defaults to `id`, so set it when a join makes `id` ambiguous.'],
        ['`filterOptions()`', 'No', 'The options of the dropdown filters. See Filters.'],
        ['`filterKeys()`', 'No', 'The columns that get a dropdown, without building their options.'],
        ['`shared()`', 'No', 'Computed once per page and read as `$this->shared[...]`.'],
        ['`parameters()`', 'No', 'Data returned with every page.'],
        ['`authorize($request)`', 'No', 'Return `false` to answer with a 403. Runs on every request.'],
    ]"
/>

<h2>Constructor parameters</h2>
<p>
    <code>:params</code> become the constructor arguments, matched by name. They are sealed into the token and replayed on every request,
    so pass scalars or arrays, such as an id, and load the model inside the class.
</p>

<x-docs-callout type="warning">
    The constructor also runs once while the view renders, so the package can read <code>columnMap()</code>. Keep it free of queries and other heavy work.
</x-docs-callout>

<h2>Common mistakes</h2>
<ul>
    <li>Do not derive a searchable value from <code>$key</code>. The position of a row changes as people page and sort.</li>
    <li>When <code>columnMap()</code> uses a SQL expression, <code>handle()</code> must show a value that comes from the same row data. Otherwise a search finds rows that look wrong.</li>
    <li>Declare <code>handle(mixed $model, mixed $key)</code> exactly. An <code>int $key</code> is a fatal error.</li>
</ul>
