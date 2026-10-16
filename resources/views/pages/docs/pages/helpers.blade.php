<p>
    A few helpers make table classes shorter and safer.
</p>

<h2>The DataTable facade</h2>
<p>Import it with <code>use Amprest\DtTables\Facades\DataTable;</code>.</p>

<h3>parseAttributes</h3>
<p>Turns an array into a string of HTML attributes, escaping every value. Use it to build the buttons of the <a href="{{ route('dt-tables.docs.show', ['page' => 'actions']) }}">actions column</a>.</p>

<x-docs-code lang="php">
@verbatim
DataTable::parseAttributes([
    'href' => route('houses.show', $house),
    'class' => 'btn btn-primary btn-sm',
    'target' => '_blank',
]);

//  href="https://..." class="btn btn-primary btn-sm" target="_blank"
@endverbatim
</x-docs-code>

<h3>columnClasses</h3>
<p>Builds a string of CSS classes from a list of conditions. A class without a key always applies, and a class with a key applies when its condition is true.</p>

<x-docs-code lang="php">
@verbatim
DataTable::columnClasses([
    'fw-bold',
    'text-danger' => $balance < 0,
    'text-success' => $balance > 0,
]);

//  "fw-bold text-danger" when the balance is below zero
@endverbatim
</x-docs-code>

<h3>renderTemplate</h3>
<p>
    Renders a Blade view to a compact string. Use it in <code>shared()</code> to render the template of an action once for every row.
    See <a href="{{ route('dt-tables.docs.show', ['page' => 'actions']) }}">Actions</a>.
</p>

<x-docs-code lang="php">
@verbatim
$template = DataTable::renderTemplate('tables.attendance.check-out', ['label' => 'Check out']);
@endverbatim
</x-docs-code>

<h2>Global functions</h2>
<x-docs-table
    :columns="['Function', 'What it does']"
    :rows="[
        ['`prettify($text)`', 'Turns `payment_method` or `payment-method` into `Payment Method`.'],
        ['`dt_tables_storage_path()`', 'Returns the folder that holds the table files, `resources/data-tables`.'],
    ]"
/>

<h2>Classes you may meet</h2>
<x-docs-table
    :columns="['Class', 'Used for']"
    :rows="[
        ['`Amprest\DtTables\Support\TableSource`', '`seal()` creates the token of a server-side table, which is useful in tests.'],
        ['`Amprest\DtTables\Support\PageRequest`', 'What an API table receives in `params()`: the page, search and order the browser asked for.'],
        ['`Amprest\DtTables\Support\Page`', 'What an API table returns from `parse()`: the rows, the total and the filtered count.'],
        ['`Amprest\DtTables\Events\DtButtonTriggered`', 'The event that fires when an inline table is copied or exported.'],
    ]"
/>
