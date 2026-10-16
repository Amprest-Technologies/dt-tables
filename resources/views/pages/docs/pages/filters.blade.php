<p>
    Filters are the inputs and dropdowns under a table's header. How a table gets them depends on its mode.
</p>

<h2>Inline tables</h2>
<p>
    An inline table takes its filters from its table file. Add a column in the admin and choose its search type:
    <code>Input</code> for free text, <code>Select</code> for a dropdown of the values found in the loaded rows, or <code>None</code>.
</p>

<h2>Server-side and API tables</h2>
<p>
    These tables ignore the columns in their file. Their filters come from the table class, so a filter can only exist when the server is able to apply it.
</p>

<x-docs-table
    :columns="['If the column...', 'It gets']"
    :rows="[
        ['has options in `filterOptions()`', 'A dropdown'],
        ['is searchable in `columnMap()` and has no options', 'A text input'],
        ['has only a sort target, or is not in the map', 'No filter'],
    ]"
/>

<h2>Dropdown options</h2>
<p>
    <code>filterOptions()</code> returns the options of each dropdown, keyed by the column. An associative array uses the key as the value
    that is searched and the item as the label. A plain list uses each item for both.
</p>

<x-docs-code lang="php">
@verbatim
public function filterOptions(): array
{
    return [
        //  value => label
        'status' => ['pending' => 'Pending', 'paid' => 'Paid'],

        //  a plain list: the value and the label are the same
        'method' => ['Cash', 'Card', 'Mobile'],

        //  a closure, only run when the dropdown opens
        'organization' => fn () => Organization::orderBy('name')->pluck('name', 'name')->all(),
    ];
}
@endverbatim
</x-docs-code>

<h3>Use closures for costly options</h3>
<p>
    The options of a table are asked for after the page has loaded, and only for the dropdowns that are on the table. A closure is run only when its own
    dropdown is requested, so a long list never slows down the page or the rows. Prefer a closure for anything that needs a query.
</p>

<x-docs-callout>
    Dropdowns that are requested together, which is every dropdown of a table, share one request.
</x-docs-callout>

<h3>Naming dropdowns without building them</h3>
<p>
    The package learns which columns have a dropdown from the keys of <code>filterOptions()</code>, and it does not run the closures to find out.
    If you build <code>filterOptions()</code> in another way, override <code>filterKeys()</code> to name the columns directly.
</p>

<x-docs-code lang="php">
@verbatim
public function filterKeys(): array
{
    return ['status', 'organization'];
}
@endverbatim
</x-docs-code>

<h2>How a dropdown matches</h2>
<p>
    A dropdown matches the whole value, ignoring case, against the option that was chosen. A text input matches the words that were typed anywhere in the column.
</p>

<h2>Filtering through the URL</h2>
<p>
    Add a column key to the address and the table opens with that filter already applied. Use it to link from one page to a filtered view of another.
    A key written with dashes works too.
</p>

<x-docs-code lang="bash">
@verbatim
/orders?status=paid
/orders?customer_name=Acme&status=paid
/orders?payment-method=mpesa
@endverbatim
</x-docs-code>

<p>On a server-side table the first request already carries the filter, so the table does not load twice.</p>

<h2>Where filters cannot reach</h2>
<ul>
    <li>A column that is computed in PHP and cannot be searched in SQL cannot have a filter. Compute it in the query, or leave it unfiltered.</li>
    <li>An API table can only filter by the fields the API accepts.</li>
</ul>
