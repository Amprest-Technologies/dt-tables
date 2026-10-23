@extends('docs::layout')

@section('page', 'Filters')
@section('lead', 'Select and input filters, their options, and filtering through the URL.')

@section('content')
    <div class="docs-prose mb-10">
<p>
    Filters are the inputs and dropdowns under a table's header. How a table gets them depends on its mode.
</p>
    </div>

    <x-docs::section id="inline-tables" title="Inline tables">
<p>
    An inline table takes its filters from its table file. Add a column in the admin and choose its search type:
    <code>Input</code> for free text, <code>Select</code> for a dropdown of the values found in the loaded rows, or <code>None</code>.
</p>
    </x-docs::section>

    <x-docs::section id="server-side-and-api-tables" title="Server-side and API tables">
<p>
    These tables ignore the columns in their file. Their filters come from the table class, so a filter can only exist when the server is able to apply it.
</p>

<x-docs::table
    :columns="['If the column...', 'It gets']"
    :rows="[
        ['has options in `filterOptions()`', 'A dropdown'],
        ['is searchable in `columnMap()` and has no options', 'A text input'],
        ['has only a sort target, or is not in the map', 'No filter'],
    ]"
/>
    </x-docs::section>

    <x-docs::section id="dropdown-options" title="Dropdown options">
<p>
    <code>filterOptions()</code> returns the options of each dropdown, keyed by the column. An associative array uses the key as the value
    that is searched and the item as the label. A plain list uses each item for both.
</p>

<x-docs::code lang="php">
@verbatim
use App\Models\Organization;

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
</x-docs::code>

<h3>Use closures for costly options</h3>
<p>
    The options of a table are asked for after the page has loaded, and only for the dropdowns that are on the table. A closure is run only when its own
    dropdown is requested, so a long list never slows down the page or the rows. Prefer a closure for anything that needs a query.
</p>

<x-docs::callout>
    Dropdowns that are requested together, which is every dropdown of a table, share one request.
</x-docs::callout>

<h3>Naming dropdowns without building them</h3>
<p>
    The package learns which columns have a dropdown from the keys of <code>filterOptions()</code>, and it does not run the closures to find out.
    If you build <code>filterOptions()</code> in another way, override <code>filterKeys()</code> to name the columns directly.
</p>

<x-docs::code lang="php">
@verbatim
public function filterKeys(): array
{
    return ['status', 'organization'];
}
@endverbatim
</x-docs::code>
    </x-docs::section>

    <x-docs::section id="how-a-dropdown-matches" title="How a dropdown matches">
<p>
    A dropdown matches the whole value, ignoring case, against the option that was chosen. A text input matches the words that were typed anywhere in the column.
</p>
    </x-docs::section>

    <x-docs::section id="filtering-through-the-url" title="Filtering through the URL">
<p>
    Add a column key to the address and the table opens with that filter already applied. Use it to link from one page to a filtered view of another.
    A key written with dashes works too.
</p>

<x-docs::code lang="bash" title="Terminal">
@verbatim
/orders?status=paid
/orders?customer_name=Acme&status=paid
/orders?payment-method=mpesa
@endverbatim
</x-docs::code>

<p>On a server-side table the first request already carries the filter, so the table does not load twice.</p>
    </x-docs::section>

    <x-docs::section id="where-filters-cannot-reach" title="Where filters cannot reach">
<ul>
    <li>A column that is computed in PHP and cannot be searched in SQL cannot have a filter. Compute it in the query, or leave it unfiltered.</li>
    <li>An API table can only filter by the fields the API accepts.</li>
</ul>
    </x-docs::section>
@endsection
