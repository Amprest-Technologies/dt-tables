<p>Find the symptom, then check the cause. Most problems come from a name that does not match somewhere.</p>

<h2>A table shows no filters</h2>
<ul>
    <li><strong>Inline table.</strong> Check that the column exists in the table's file and has a search type other than <code>None</code>, and that its key equals the <code>dtt-title</code> of the header.</li>
    <li><strong>The id does not match.</strong> The <code>id</code> on the component must equal the key of the table file. A mismatch means no file is found and no columns are applied.</li>
    <li><strong>Server-side or API table.</strong> The columns in the file are ignored. The column must be searchable in <code>columnMap()</code>, so check that it has a search target.</li>
</ul>

<h2>A server-side column has a text input, and I wanted a dropdown</h2>
<p>A dropdown needs an entry for the column in <code>filterOptions()</code>. Add one, and the input becomes a dropdown.</p>

<h2>A dropdown is empty</h2>
<ul>
    <li>Check that its closure in <code>filterOptions()</code> returns an array, and that the query behind it returns rows.</li>
    <li>If the closure throws, the dropdown opens empty and the error is logged. Read the log.</li>
</ul>

<h2>Sorting does nothing on a column</h2>
<p>A server-side column can only be sorted when <code>columnMap()</code> gives it a sort target. A column that has only a closure for its search cannot be sorted. Add a <code>sort</code> expression.</p>

<h2>A cell is empty</h2>
<p>The key returned by <code>handle()</code> must match the column key. Compare the <code>dtt-title</code> (or the header text) with the array key.</p>

<h2>The table is slow</h2>
<ul>
    <li>An inline table with thousands of rows is slow by nature. Move it to a <a href="{{ route('dt-tables.docs.show', ['page' => 'server-side-tables']) }}">server-side table</a>.</li>
    <li>For a server-side table, read <a href="{{ route('dt-tables.docs.show', ['page' => 'performance']) }}">Performance</a>.</li>
</ul>

<h2>The total number of rows looks wrong</h2>
<p>Row counts are cached for <code>count_cache</code> seconds, so a total can lag behind recent changes. Set it to <code>0</code> to count on every request.</p>

<h2>I get an error when the table loads</h2>
<x-docs-table
    :columns="['You see', 'Cause and fix']"
    :rows="[
        ['Session expired (`419`)', 'The page has been open too long, so reload it.'],
        ['Access denied (`403`)', '`authorize()` returned `false`, or `APP_KEY` changed since the page loaded. Reload the page, then check the policy.'],
        ['`422`', 'The request was not valid. Check the browser console for the message.'],
        ['Could not load data on an API table (`502` or `504`)', 'The API failed or timed out. Check the API, its URL and the timeout on `client()`.'],
    ]"
/>

<h2>A fatal error about the handle method</h2>
<p>The signature must be exactly <code>handle(mixed $model, mixed $key): mixed</code>. A typed <code>int $key</code> is not compatible with the parent class.</p>

<h2>The admin or the docs return 403</h2>
<p>They only answer when <code>APP_ENV=local</code>. Check the environment of the app that is running.</p>

<h2>Copy and Excel are missing</h2>
<ul>
    <li>Server-side and API tables never have them, because only one page is loaded.</li>
    <li>On an inline table, check the <strong>Buttons</strong> section of the table in the admin.</li>
</ul>

<h2>The assets do not load</h2>
<p>
    The assets are added before <code>&lt;/head&gt;</code>, so the layout needs a <code>head</code> tag. Pages built without one, such as a bare fragment, cannot receive them.
</p>

<h2>My component fails with InvalidArgumentException</h2>
<p><code>:source</code> and <code>:payload</code> cannot be used together. Use <code>:payload</code> for inline tables and <code>:source</code> for the rest.</p>
