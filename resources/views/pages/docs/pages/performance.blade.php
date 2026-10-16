<p>
    A server-side table is as fast as the query behind it. These are the habits that keep large tables quick.
</p>

<h2>Counts are cached</h2>
<p>
    To show how many pages there are, the table counts its rows. On a large table the count costs about as much as a page, so the package keeps each count
    for a short time. Paging and re-filtering then do not count again.
</p>

<x-docs-code lang="php" title="config/dt-tables.php">
@verbatim
'server_side' => [
    'count_cache' => 60,   // seconds a count is reused; 0 counts on every request
],
@endverbatim
</x-docs-code>

<x-docs-callout>
    The row total can lag by up to that many seconds after rows are added or removed. API tables are not counted by the package, so this setting does not apply to them.
</x-docs-callout>

<h2>Keep the global search narrow</h2>
<p>
    The global search looks for the typed word anywhere in every searchable column, which cannot use an index. Search only the columns people actually search by,
    and opt the rest out with <code>'global' => false</code>.
</p>

<x-docs-code lang="php">
@verbatim
'id' => ['search' => 'orders.id', 'global' => false],
'total' => ['sort' => 'orders.total'],
'customer_name' => 'customers.name',
@endverbatim
</x-docs-code>

<h2>Build dropdown options lazily</h2>
<p>
    Return a closure for any dropdown that needs a query, so the work happens only when someone opens it. See <a href="{{ route('dt-tables.docs.show', ['page' => 'filters']) }}">Filters</a>.
</p>

<h2>Index what you join and sort by</h2>
<ul>
    <li>Index the foreign keys used in <code>query()</code> joins, and the columns used in its conditions.</li>
    <li>Sorting by a column from a joined table must sort the whole joined set before a page is taken, so it is the slowest request. An index on that column pays off.</li>
    <li>A composite index, such as <code>(tenant_id, amount)</code>, speeds up page loads, sorts and exact filters together.</li>
    <li>Set <code>keyColumn()</code> when a join makes <code>id</code> ambiguous, so paging is stable.</li>
</ul>

<h2>Select only what you show</h2>
<p>Name the columns in <code>select()</code> instead of loading every column of every model, and avoid queries inside <code>handle()</code>. Use <code>shared()</code> to compute anything once per page.</p>

<h2>Smaller pages are faster</h2>
<p>
    Each page is a query. Keep <code>page_length</code> modest, and keep <code>max_length</code> as low as your largest legitimate request needs.
</p>

<h2>Assets are cached</h2>
<p>
    The scripts and styles are served without cookies and cached by the browser for a year, so they cost almost nothing after the first visit.
</p>
