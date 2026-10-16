<p>This walkthrough builds an inline table of invoices. It takes five steps, and the same steps apply to every mode.</p>

<h2>1. Register the table</h2>
<p>
    Open the <a href="{{ route('dt-tables.data-tables.index') }}">admin</a>, choose <strong>New table</strong>, enter the key
    <code>invoices-table</code> and pick the <strong>Inline</strong> mode. A file is created at
    <code>resources/data-tables/invoices-table.json</code> and its settings page opens.
</p>

<h2>2. Create the table class</h2>
<p>Scaffold the class with Artisan.</p>

<x-docs-code lang="bash">
@verbatim
php artisan make:data-table InvoiceTable
@endverbatim
</x-docs-code>

<p>Fill in <code>query()</code> to choose the rows and <code>handle()</code> to describe how each one looks.</p>

<x-docs-code lang="php" title="app/DataTables/InvoiceTable.php">
@verbatim
&lt;?php

namespace App\DataTables;

use Amprest\DtTables\Tables\BaseTable;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;

class InvoiceTable extends BaseTable
{
    public function __construct(protected string $status = 'open') {}

    public function query(): Builder
    {
        return Invoice::query()
            ->with('tenant')
            ->where('status', $this->status)
            ->latest();
    }

    public function handle(mixed $invoice, mixed $key): mixed
    {
        return [
            'reference' => $invoice->reference,
            'tenant' => $invoice->tenant->name,
            'status' => ucfirst($invoice->status),
            'amount' => number_format($invoice->amount, 2),
        ];
    }
}
@endverbatim
</x-docs-code>

<h2>3. Build it in a controller</h2>
<p><code>build()</code> runs the query, maps every row and returns the payload the component needs.</p>

<x-docs-code lang="php" title="app/Http/Controllers/InvoiceController.php">
@verbatim
public function index(): View
{
    return view('invoices.index', [
        'invoices' => InvoiceTable::build(status: 'open'),
    ]);
}
@endverbatim
</x-docs-code>

<h2>4. Render it in a view</h2>
<p>
    The <code>id</code> must match the key you registered. Each <code>&lt;th&gt;</code> becomes a column, and its name must match a key returned by <code>handle()</code>.
</p>

<x-docs-code lang="blade" title="resources/views/invoices/index.blade.php">
@verbatim
<x-data-table id="invoices-table" title="Invoices" class="table table-sm" :payload="$invoices">
    <thead>
        <tr>
            <th dtt-row-index>#</th>
            <th dtt-title="reference">Reference</th>
            <th dtt-title="tenant">Tenant</th>
            <th dtt-title="status">Status</th>
            <th dtt-title="amount" dtt-align="right">Amount</th>
        </tr>
    </thead>
</x-data-table>
@endverbatim
</x-docs-code>

<h2>5. Add filters</h2>
<p>
    Back in the admin, open the <strong>Columns</strong> section of <code>invoices-table</code> and add the columns you want to filter,
    for example <code>tenant</code> as an <strong>Input</strong> and <code>status</code> as a <strong>Select</strong>.
    Reload the page and the filters appear under the header.
</p>

<x-docs-callout type="tip">
    A column without an entry is still shown, sorted and searched by the global search. It just has no filter of its own.
</x-docs-callout>

<h2>What next?</h2>
<ul>
    <li>Style cells and add buttons: <a href="{{ route('dt-tables.docs.show', ['page' => 'row-cells']) }}">Row Cells</a> and <a href="{{ route('dt-tables.docs.show', ['page' => 'actions']) }}">Actions</a>.</li>
    <li>Outgrown inline? Move to <a href="{{ route('dt-tables.docs.show', ['page' => 'server-side-tables']) }}">a database table</a>.</li>
</ul>
