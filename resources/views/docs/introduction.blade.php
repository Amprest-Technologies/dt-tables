@extends('docs::layout')

@section('page', 'Introduction')
@section('lead', 'What DtTables is, the three ways a table can be fed, and how the pieces fit together.')

@section('content')
    <div class="docs-prose mb-10">
<p>
    DtTables turns a PHP class and a Blade component into an interactive table: search, sorting, column filters, paging, column
    visibility and Excel export, built on <a href="https://datatables.net" target="_blank" rel="noopener">DataTables</a>.
    You describe where the rows come from in a small class, and the package handles everything between the database and the browser.
</p>
    </div>

    <x-docs::section id="three-ways-to-feed-a-table" title="Three ways to feed a table">
<p>Every table is fed in one of three ways, called its <strong>mode</strong>. Pick the one that fits the size and the source of your data.</p>

<x-docs::table
    :columns="['Mode', 'Rows come from', 'Searching, sorting and paging', 'Best for']"
    :rows="[
        ['Inline', 'A query or collection, loaded up front', 'In the browser', 'Small tables, up to a few thousand rows'],
        ['Server-side', 'Your database, one page at a time', 'In SQL', 'Large tables you own'],
        ['API', 'A remote HTTP API, one page at a time', 'In the API', 'Rows owned by another service'],
    ]"
/>

<x-docs::callout type="tip">
    Not sure which to choose? Start inline. Move to server-side when a table passes a few thousand rows or the page feels slow to load.
</x-docs::callout>
    </x-docs::section>

    <x-docs::section id="how-the-pieces-fit-together" title="How the pieces fit together">
<p>A table is made of three small parts that share one name, called the table <strong>key</strong>:</p>

<x-docs::code lang="bash" title="The three parts of an invoices table">
@verbatim
app/DataTables/InvoiceTable.php        The table class: where the rows come from and how each row looks
resources/data-tables/invoices-table.json   The table file: theme, buttons, behaviour and loader
resources/views/invoices/index.blade.php    The view: renders the table with <x-data-table id="invoices-table">
@endverbatim
</x-docs::code>

<ol>
    <li>The <strong>table class</strong> is plain PHP. It runs a query and maps each result to a row.</li>
    <li>The <strong>table file</strong> holds the settings of the table. You rarely edit it by hand, because the <a href="{{ $docs->url('admin-ui') }}">admin UI</a> does it for you.</li>
    <li>The <strong>component</strong> draws the table in a Blade view and connects the other two parts.</li>
</ol>
    </x-docs::section>

    <x-docs::section id="what-you-get" title="What you get">
<ul>
    <li>Global search and per-column filters (text inputs and dropdowns).</li>
    <li>Sorting, paging and a column visibility menu.</li>
    <li>Copy and Excel export for inline tables, with an event you can listen to.</li>
    <li>Bootstrap and Tailwind themes, plus a loader and clear error messages.</li>
    <li>An admin UI and these docs, both available while your app runs locally.</li>
</ul>

<x-docs::callout>
    The admin UI and these docs are only available when <code>APP_ENV=local</code>. Tables themselves work in every environment.
</x-docs::callout>
    </x-docs::section>

    <x-docs::section id="where-to-go-next" title="Where to go next">
<ul>
    <li>New here? Follow the <a href="{{ $docs->url('quick-start') }}">Quick Start</a>.</li>
    <li>Large table? Read the <a href="{{ $docs->url('server-side-overview') }}">server-side overview</a>.</li>
    <li>Looking for an option? Browse the <a href="{{ $docs->url('configuration') }}">reference</a>.</li>
</ul>
    </x-docs::section>
@endsection
