<p>
    <code>make:data-table</code> creates a table class in <code>app/DataTables</code>, with every method stubbed for you to fill in.
</p>

<x-docs-code lang="bash">
@verbatim
php artisan make:data-table InvoiceTable
php artisan make:data-table OrderTable --server-side
php artisan make:data-table MessageTable --api
@endverbatim
</x-docs-code>

<h2>Options</h2>
<x-docs-table
    :columns="['Option', 'Creates', 'Extends']"
    :rows="[
        ['none', 'An inline table', '`BaseTable`'],
        ['`--server-side`, `-s`', 'A table that is paged, searched and sorted in your database', '`ServerSideTable`'],
        ['`--api`, `-a`', 'A table that is paged, searched and sorted by an API', '`ApiTable`'],
        ['`--force`', 'Overwrites a class that already exists', ''],
    ]"
/>

<h2>After scaffolding</h2>
<ol>
    <li>Fill in the stubbed methods. Each mode has its own required methods, listed on its page.</li>
    <li>Register a table of the same mode in the <a href="{{ route('dt-tables.data-tables.index') }}">admin</a>.</li>
    <li>Render it with <a href="{{ route('dt-tables.docs.show', ['page' => 'component']) }}">the component</a>.</li>
</ol>

<x-docs-callout type="tip">
    The class name and the table key are independent. <code>OrderTable</code> can render as <code>orders-table</code>, as long as the view uses the key.
</x-docs-callout>
