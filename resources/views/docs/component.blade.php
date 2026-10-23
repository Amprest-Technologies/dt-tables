@extends('docs::layout')

@section('page', 'The Component')
@section('lead', 'Render a table in a Blade view with the x-data-table component.')

@section('content')
    <div class="docs-prose mb-10">
<p>
    <code>&lt;x-data-table&gt;</code> draws a table in a Blade view. It reads the table's file, builds the buttons and filters, and
    starts the table in the browser. The package adds the scripts and styles to the page for you.
</p>
    </div>

    <x-docs::section id="inline-table" title="Inline table">
<p>Give the component a <code>:payload</code>, which is what <code>build()</code> returned in your controller.</p>

<x-docs::code lang="blade" title="resources/views/invoices/index.blade.php">
@verbatim
<x-data-table id="invoices-table" title="Invoices" class="table table-sm" :payload="$invoices">
    <thead>
        <tr>
            <th dtt-row-index>#</th>
            <th dtt-title="reference">Reference</th>
            <th dtt-title="amount" dtt-align="right">Amount</th>
            <th dtt-actions>Actions</th>
        </tr>
    </thead>
</x-data-table>
@endverbatim
</x-docs::code>
    </x-docs::section>

    <x-docs::section id="server-side-or-api-table" title="Server-side or API table">
<p>
    Give it a <code>:source</code> instead, the class name of a <a href="{{ $docs->url('server-side-tables') }}">server-side</a> or
    <a href="{{ $docs->url('api-tables') }}">API</a> table. No controller work is needed, because the browser asks for one page at a time.
</p>

<x-docs::code lang="blade" title="resources/views/orders/index.blade.php">
@verbatim
@use('App\DataTables\OrderTable')

<x-data-table
    id="orders-table"
    :source="OrderTable::class"
    :params="['status' => 'paid']"
    title="Orders"
    class="table table-sm"
>
    <thead>
        <tr>
            <th dtt-row-index>#</th>
            <th dtt-title="customer_name">Customer</th>
            <th dtt-title="total">Total</th>
        </tr>
    </thead>
</x-data-table>
@endverbatim
</x-docs::code>
    </x-docs::section>

    <x-docs::section id="attributes" title="Attributes">
<x-docs::table
    :columns="['Attribute', 'Type', 'Description']"
    :rows="[
        ['`id`', 'string', 'The id of the table element, and the key of its table file. Required.'],
        ['`:payload`', 'array', 'The rows of an inline table, as returned by `Table::build()`.'],
        ['`:source`', 'class-string', 'A server-side or API table class. Use it instead of `:payload`.'],
        ['`:params`', 'array', 'With `:source`, the arguments passed to the table class constructor. Scalars and arrays only.'],
        ['`title`', 'string', 'The title of the table, used in the name of exported files.'],
        ['`class`', 'string', 'The classes of the table element, for example `table table-sm`.'],
        ['`table-id`', 'string', 'The key of the table file, when it differs from the `id`. See below.'],
    ]"
/>

<x-docs::callout type="warning">
    <code>:payload</code> and <code>:source</code> cannot be used together. The component throws an <code>InvalidArgumentException</code>.
</x-docs::callout>

<h3>Using the same table twice</h3>
<p>
    Two tables on one page cannot share an <code>id</code>. When both should use the same table file, give each its own
    <code>id</code> and point them at the file with <code>table-id</code>.
</p>

<x-docs::code lang="blade">
@verbatim
<x-data-table id="open-invoices" table-id="invoices-table" :payload="$open">...</x-data-table>
<x-data-table id="paid-invoices" table-id="invoices-table" :payload="$paid">...</x-data-table>
@endverbatim
</x-docs::code>
    </x-docs::section>

    <x-docs::section id="columns" title="Columns">
<p>
    The table header is plain HTML. Each <code>&lt;th&gt;</code> becomes a column, and the <code>dtt-*</code> attributes describe it.
    Read <a href="{{ $docs->url('header-attributes') }}">Header Attributes</a> for the full list.
</p>

<h3>Showing columns conditionally</h3>
<p>The header is Blade, so you can include a column only when it is needed. Return the matching key from the table class in the same case.</p>

<x-docs::code lang="blade">
@verbatim
<th dtt-title="reference">Reference</th>
@if($showZones)
    <th dtt-title="zone">Zone</th>
@endif
@endverbatim
</x-docs::code>
    </x-docs::section>

    <x-docs::section id="themes" title="Themes">
<p>
    A table's theme decides the classes given to its buttons and filters, so they look at home in your pages. Pick it in the admin
    or in the table file. See <a href="{{ $docs->url('configuration') }}">Configuration</a> to add your own.
</p>
    </x-docs::section>

    <x-docs::section id="the-loader" title="The loader">
<p>
    While a table loads, a loader replaces it. Server-side tables show the same loader over the table whenever a new page loads.
    Change its message and image in the admin, under <strong>Loader</strong>.
</p>
    </x-docs::section>
@endsection
