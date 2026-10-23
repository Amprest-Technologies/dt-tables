@extends('docs::layout')

@section('page', 'Row Cells')
@section('lead', 'The shapes a cell can take: plain values, styled cells and rendered HTML.')

@section('content')
    <div class="docs-prose mb-10">
<p>
    <code>handle()</code> returns an array with one entry per column. Each entry can be a plain value, or an array that styles the cell
    or renders HTML in it. The same shapes work in every mode.
</p>
    </div>

    <x-docs::section id="the-shapes-of-a-cell" title="The shapes of a cell">
<x-docs::table
    :columns="['Shape', 'Example', 'Result']"
    :rows="[
        ['A plain value', '`\'zone\' => $house->zone_name`', 'Shown as text.'],
        ['A value with classes', '`\'rent\' => [\'value\' => $rent, \'classes\' => \'fw-bold\']`', 'Shown as text, and the classes are added to the cell.'],
        ['A value with HTML', '`\'name\' => [\'value\' => $name, \'display\' => $html]`', 'The HTML is shown, while the value is used for searching, sorting and export.'],
        ['A Fluent object', '`fluent([...])`', 'The same as an array, when you prefer object access.'],
    ]"
/>
    </x-docs::section>

    <x-docs::section id="styling-a-cell" title="Styling a cell">
<p>Add <code>classes</code> to a cell to style it. <code>DataTable::columnClasses()</code> builds the string from a list of conditions.</p>

<x-docs::code lang="php">
@verbatim
use Nyumbani\DtTables\Facades\DataTable;

'status' => [
    'value' => ucfirst($invoice->status),
    'classes' => DataTable::columnClasses([
        'fw-bold',
        'text-success' => $invoice->status === 'paid',
        'text-danger' => $invoice->status === 'overdue',
    ]),
],
@endverbatim
</x-docs::code>
    </x-docs::section>

    <x-docs::section id="showing-html-in-a-cell" title="Showing HTML in a cell">
<p>
    <code>display</code> is an HTML template. It is rendered in the browser with Eta, and <code>it.row</code> holds the row that is being shown.
    Use <code>&lt;%= %&gt;</code> to print an escaped value and <code>&lt;%~ %&gt;</code> to print raw HTML.
</p>

<x-docs::code lang="php">
@verbatim
'reference' => [
    'value' => $invoice->reference,
    'display' => '<a href="/invoices/<%= it.row.id %>"><%= it.row.reference.value %></a>',
],
@endverbatim
</x-docs::code>

<x-docs::callout type="note">
    The browser searches, sorts and exports the <code>value</code>, not the <code>display</code>, so searching for a reference finds the row even though the cell shows a link.
</x-docs::callout>
    </x-docs::section>

    <x-docs::section id="keep-the-value-meaningful" title="Keep the value meaningful">
<p>
    For server-side tables, the value you show must come from the same data the database searches. If a column is searched by a SQL expression,
    <code>handle()</code> has to derive what it shows from that same data. See <a href="{{ $docs->url('column-map') }}">The Column Map</a>.
</p>
    </x-docs::section>
@endsection
