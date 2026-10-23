@extends('docs::layout')

@section('page', 'Header Attributes')
@section('lead', 'The dtt attributes that describe each column in the table header.')

@section('content')
    <div class="docs-prose mb-10">
<p>
    The header of a table is ordinary HTML, and a few <code>dtt</code> attributes on each <code>&lt;th&gt;</code> tell DtTables how that column behaves.
</p>

<x-docs::table
    :columns="['Attribute', 'Value', 'What it does']"
    :rows="[
        ['`dtt-title`', 'a row key', 'Names the column. The key must match the key your table class returns for that column.'],
        ['`dtt-row-index`', 'none', 'Shows a running row number, 1, 2, 3 and so on.'],
        ['`dtt-actions`', 'none', 'Marks the column that holds the buttons of each row. It cannot be searched or sorted, and it is left out of exports.'],
        ['`dtt-align`', '`left`, `center` or `right`', 'Aligns the header and the cells of the column.'],
        ['`dtt-hidden`', 'none', 'Keeps the column in the data but hides it from the page. Handy for ids that an action needs.'],
    ]"
/>
    </div>

    <x-docs::section id="naming-a-column" title="Naming a column">
<p>
    Every column has a key, which links the header to the data. DtTables reads it from <code>dtt-title</code>. Without one, it uses the
    header text in lowercase, with spaces turned into underscores, so <code>Tenant Name</code> becomes <code>tenant_name</code>.
</p>

<x-docs::code lang="blade">
@verbatim
{{-- The key is "tenant_name" in both cases --}}
<th>Tenant Name</th>
<th dtt-title="tenant_name">Tenant</th>
@endverbatim
</x-docs::code>

<p>The key you give a column is used in three places, and they must all agree:</p>
<ol>
    <li>the <code>dtt-title</code> (or the header text) in the view,</li>
    <li>the key returned for that column by <code>handle()</code> in the table class,</li>
    <li>for inline tables, the column <code>key</code> in the table's file, and for server-side and API tables, the key in <code>columnMap()</code>.</li>
</ol>

<x-docs::callout type="tip">
    Use <code>dtt-title</code> whenever the header text is dynamic or contains HTML, for example <code>Amount ({{ '{{' }} $currency }})</code>. The text alone would not make a reliable key.
</x-docs::callout>
    </x-docs::section>

    <x-docs::section id="alignment" title="Alignment">
<x-docs::code lang="blade">
@verbatim
<th dtt-title="id" dtt-align="left">ID</th>
<th dtt-title="amount" dtt-align="right">Amount</th>
@endverbatim
</x-docs::code>
    </x-docs::section>

    <x-docs::section id="hidden-columns" title="Hidden columns">
<p>A hidden column still holds its data, so an action can use it, and people can bring it back from the <strong>Hide Columns</strong> menu.</p>

<x-docs::code lang="blade">
@verbatim
<th dtt-title="uuid" dtt-hidden>UUID</th>
@endverbatim
</x-docs::code>
    </x-docs::section>

    <x-docs::section id="keeping-a-column-out-of-exports" title="Keeping a column out of exports">
<p>The actions column is already left out. Add the <code>exclude-from-export</code> class to leave out any other column of an Excel export.</p>

<x-docs::code lang="blade">
@verbatim
<th dtt-title="notes" class="exclude-from-export">Notes</th>
@endverbatim
</x-docs::code>
    </x-docs::section>
@endsection
