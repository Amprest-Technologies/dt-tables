<p>
    <code>columnMap()</code> is the link between what the browser asks for and what the database can do. It maps each column key to the SQL
    that column is searched and sorted by. Only the columns in the map can be searched or sorted.
</p>

<h2>The forms of an entry</h2>
<x-docs-code lang="php">
@verbatim
public function columnMap(): array
{
    return [
        //  A SQL expression: the column is searched and sorted by it
        'customer_name' => 'customers.name',

        //  A closure builds its own search condition, and cannot be sorted
        'total' => fn ($query, $term) => is_numeric($term)
            ? $query->where('orders.total', '>=', (float) $term)
            : $query->whereRaw('1 = 0'),

        //  The array form sets search, sort and the global search apart
        'status' => [
            'search' => 'orders.status',
            'sort' => 'orders.status',
            'global' => false,
        ],
    ];
}
@endverbatim
</x-docs-code>

<x-docs-table
    :columns="['Entry', 'Searchable', 'Sortable']"
    :rows="[
        ['`\'key\' => \'table.column\'`', 'Yes', 'Yes'],
        ['`\'key\' => fn ($query, $term) => ...`', 'Yes', 'No'],
        ['`[\'search\' => \'expression\', ...]`', 'Yes', 'Yes, by `sort` or else by the search expression'],
        ['`[\'search\' => fn ..., \'sort\' => \'expression\']`', 'Yes', 'Yes'],
        ['`[\'sort\' => \'expression\']`', 'No', 'Yes'],
        ['The key is left out', 'No', 'No'],
    ]"
/>

<p>
    A string that is more than a plain <code>table.column</code> name is used as raw SQL, for example a <code>case</code> expression.
    It comes from your code and never from the browser, so it is not escaped.
</p>

<h2>What the browser is told</h2>
<p>
    The component reads the map once and tells the browser which columns can be searched, which can be sorted and which have a dropdown.
    The browser then switches off everything else: no sort arrow appears on a column the server cannot sort, and no filter appears on a column it cannot search.
    A request for a key that is not in the map is ignored by the server too.
</p>

<x-docs-callout type="tip">
    To keep a column sortable but without a filter, give it only a sort target: <code>'amount' => ['sort' => 'orders.amount']</code>.
</x-docs-callout>

<h2>How searching works</h2>
<ul>
    <li>
        <strong>The global search box</strong> splits the text into words. Every word must match at least one searchable column.
        Matching ignores case and looks for the word anywhere in the value.
    </li>
    <li>A phrase in quotes, <code>"exact phrase"</code>, matches as a whole, and <code>!word</code> excludes rows that contain the word.</li>
    <li><code>%</code> and <code>_</code> are searched as they are typed, not as wildcards.</li>
    <li><strong>A column filter</strong> searches only its own column. A dropdown filter matches the whole value instead of part of it.</li>
    <li>A closure receives each word of the global search as <code>$term</code>, so return <code>whereRaw('1 = 0')</code> for words it cannot read and let the other columns decide.</li>
    <li><code>'global' => false</code> keeps a column out of the global search while its own filter still works. Use it for ids, amounts and codes.</li>
</ul>

<h2>How sorting works</h2>
<ul>
    <li>The browser's sort applies first, one expression per sorted column in the order they were chosen.</li>
    <li>With no sort chosen, the order from <code>query()</code> applies.</li>
    <li><code>keyColumn()</code> is always added last, so rows with the same value never repeat or skip between pages.</li>
</ul>

<h2>Keep the map and handle() in step</h2>
<p>The page is chosen in SQL and shaped in PHP. If the map derives a status from the id, then <code>handle()</code> has to derive the same status from the same row.</p>

<x-docs-code lang="php">
@verbatim
//  columnMap(): the SQL decides the status
'status' => [
    'search' => "case when orders.id % 2 = 0 then 'Pending' else 'Completed' end",
    'global' => false,
],

//  handle(): derive it from the same data, never from $key
'status' => $order->id % 2 === 0 ? 'Pending' : 'Completed',
@endverbatim
</x-docs-code>

<x-docs-callout type="note">
    For an <a href="{{ route('dt-tables.docs.show', ['page' => 'api-tables']) }}">API table</a> the values are the field names the API understands instead of SQL, and closures are not supported.
</x-docs-callout>
