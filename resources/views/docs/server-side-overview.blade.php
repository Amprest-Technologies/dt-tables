@extends('docs::layout')

@section('page', 'Overview')
@section('lead', 'When to page, search and sort on the server, and how a request flows.')

@section('content')
    <div class="docs-prose mb-10">
<p>
    An inline table sends every row to the browser. A server-side table sends only the page that is on screen. The browser asks for a page
    each time someone pages, sorts or searches, and the server does the work. This keeps pages fast no matter how many rows there are.
</p>
    </div>

    <x-docs::section id="choosing-a-source" title="Choosing a source">
<x-docs::table
    :columns="['', 'Inline', 'Database', 'API']"
    :rows="[
        ['Table class extends', '`BaseTable`', '`ServerSideTable`', '`ApiTable`'],
        ['Rows come from', 'A query loaded up front', 'Your database', 'A remote HTTP API'],
        ['What loads with the page', 'Every row', 'Nothing', 'Nothing'],
        ['Searching, sorting, paging', 'The browser', 'SQL', 'The API'],
        ['Controller work', '`Table::build()`', 'None', 'None'],
        ['Copy and Excel', 'Yes', 'No', 'No'],
        ['Scaffold with', '`make:data-table Name`', '`make:data-table Name --server-side`', '`make:data-table Name --api`'],
    ]"
/>

<p>
    Choose <strong>Database</strong> when the rows are in your own database and the table can hold thousands of rows. Choose <strong>API</strong> when another
    service owns the rows and can page, search and sort them. Keep <strong>Inline</strong> for small tables and for tables that need to process the whole collection.
</p>
    </x-docs::section>

    <x-docs::section id="how-a-request-flows" title="How a request flows">
<x-docs::code lang="bash" title="From the page to the rows">
@verbatim
<x-data-table :source="OrderTable::class" :params="[...]">
  → the component seals the class and its params into an encrypted token
  → the browser posts draw, start, length, search, order and columns, with the token
  → the server opens the token, runs authorize(), and asks the table's runner for the page
  → database table: the page is searched, counted, sorted and read in SQL
  → API table: the page, search and order are sent to the API, and its answer is read
  → handle() maps the rows of that page only
  → the server answers with the rows and the counts
@endverbatim
</x-docs::code>
    </x-docs::section>

    <x-docs::section id="what-you-write" title="What you write">
<ol>
    <li>A table class: <a href="{{ $docs->url('server-side-tables') }}">a database table</a> or <a href="{{ $docs->url('api-tables') }}">an API table</a>.</li>
    <li>A <a href="{{ $docs->url('column-map') }}">column map</a> that says what each column can be searched and sorted by.</li>
    <li>The component, with <code>:source</code> instead of <code>:payload</code>.</li>
    <li>A table file registered with the <strong>Server-side</strong> or <strong>API</strong> mode. Its columns stay empty, because the filters come from the class.</li>
</ol>
    </x-docs::section>

    <x-docs::section id="what-is-different" title="What is different">
<ul>
    <li>Copy and Excel are removed, because an export would only contain the loaded page. <strong>Hide Columns</strong> still works.</li>
    <li>Filters are decided by the table class, not by the table file. See <a href="{{ $docs->url('filters') }}">Filters</a>.</li>
    <li>There is no <code>before()</code> or <code>after()</code>, because rows are mapped one page at a time.</li>
    <li>The constructor arguments you pass as <code>:params</code> are sealed into the token, so they must be scalars or arrays.</li>
</ul>
    </x-docs::section>
@endsection
