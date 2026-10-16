<p>
    An API table reads its rows from a remote HTTP API. Use one when another service, such as a microservice or a third-party system, owns the data.
    Everything else works as it does for a <a href="{{ route('dt-tables.docs.show', ['page' => 'server-side-tables']) }}">database table</a>:
    the same component, token, filters, loader and error messages.
</p>

<x-docs-callout type="warning">
    The API must page, search and sort for itself. The package never downloads the whole collection. It asks the API for one page and passes it the search and the order.
    If the API can only return everything at once, add paging to it first.
</x-docs-callout>

<h2>Create the table</h2>
<x-docs-code lang="bash">
@verbatim
php artisan make:data-table MessageTable --api
@endverbatim
</x-docs-code>

<x-docs-code lang="php" title="app/DataTables/MessageTable.php">
@verbatim
&lt;?php

namespace App\DataTables;

use Amprest\DtTables\Support\PageRequest;
use Amprest\DtTables\Tables\ApiTable;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MessageTable extends ApiTable
{
    public function __construct(
        protected string $scopeType,
        protected array $scopeIds,
        protected string $from,
        protected string $to,
    ) {}

    public function client(): PendingRequest
    {
        return Http::baseUrl(config('services.messages.url'))
            ->withToken(config('services.messages.token'))
            ->acceptJson()
            ->timeout(15);
    }

    public function endpoint(): string
    {
        return 'api/messages';
    }

    public function params(PageRequest $page): array
    {
        return [
            ...parent::params($page),
            'scope_type' => $this->scopeType,
            'scope_id' => $this->scopeIds,
            'start_date' => $this->from,
            'end_date' => $this->to,
        ];
    }

    public function columnMap(): array
    {
        return [
            'recipient' => 'recipient',
            'status' => ['search' => 'status', 'global' => false],
            'created_at' => ['sort' => 'created_at'],
        ];
    }

    public function filterOptions(): array
    {
        return ['status' => ['sent' => 'Sent', 'delivered' => 'Delivered', 'failed' => 'Failed']];
    }

    public function handle(mixed $message, mixed $key): mixed
    {
        return [
            'recipient' => $message['recipient'],
            'status' => ucfirst($message['status']),
            'created_at' => $message['created_at'],
        ];
    }

    public function authorize(Request $request): bool
    {
        return $request->user()?->can('view messages') ?? false;
    }
}
@endverbatim
</x-docs-code>

<x-docs-code lang="blade" title="resources/views/messages/index.blade.php">
@verbatim
<x-data-table
    id="messages-table"
    :source="App\DataTables\MessageTable::class"
    :params="['scopeType' => 'zone', 'scopeIds' => $zoneIds, 'from' => $from, 'to' => $to]"
    title="Messages"
    class="table table-sm"
>
    <thead>
        <tr>
            <th dtt-row-index>#</th>
            <th dtt-title="recipient">Recipient</th>
            <th dtt-title="status">Status</th>
            <th dtt-title="created_at">Created</th>
        </tr>
    </thead>
</x-data-table>
@endverbatim
</x-docs-code>

<p>Register <code>messages-table</code> in the admin with the <strong>API</strong> mode.</p>

<h2>The methods</h2>
<x-docs-table
    :columns="['Method', 'Required', 'Purpose']"
    :rows="[
        ['`client()`', 'Yes', 'The HTTP client: base URL, credentials, timeout and retries. Credentials stay in your app and never reach the browser.'],
        ['`endpoint()`', 'Yes', 'The path, relative to the client base URL, that serves the rows.'],
        ['`columnMap()`', 'Yes', 'Each column key mapped to the field name the API understands.'],
        ['`handle($item, $key)`', 'Yes', 'Maps one item of the page, normally an array, to its cells.'],
        ['`method()`', 'No', '`get` by default. Use `post` for long lists that would overflow a URL.'],
        ['`params($page)`', 'No', 'Translates the page request into the API parameters. Override it to rename parameters or add fixed ones.'],
        ['`parse($response)`', 'No', 'Reads the rows and counts from the response. Override it when the API uses another shape.'],
        ['`filterOptions()`, `shared()`, `parameters()`, `authorize()`', 'No', 'The same as for a database table.'],
    ]"
/>

<h2>The request the API receives</h2>
<p>By default <code>params()</code> sends these, and leaves out anything empty:</p>
<x-docs-table
    :columns="['Parameter', 'Value']"
    :rows="[
        ['`page`, `per_page`', 'The page number, starting at 1, and the page size.'],
        ['`search`', 'The global search text.'],
        ['`search_fields`', 'The fields the global search should match.'],
        ['`filters`', 'The column filters as field and term pairs. Dropdown filters send the whole value.'],
        ['`sort`, `dir`', 'The first column the table is sorted by, and `asc` or `desc`.'],
    ]"
/>

<p>
    <code>params()</code> receives a <code>PageRequest</code>. Besides the values above it has <code>start</code>, <code>length</code>, the list of
    <code>filters</code> (each with its <code>key</code>, <code>field</code>, <code>term</code> and whether it is <code>exact</code>) and the list of
    <code>orders</code>, in priority order.
</p>

<h2>The response the package reads</h2>
<p>By default the package expects the shape of a Laravel resource collection:</p>

<x-docs-code lang="json">
@verbatim
{
    "data": [
        { "id": 1, "recipient": "0711000000", "status": "sent" }
    ],
    "meta": { "total": 40000, "filtered": 312 }
}
@endverbatim
</x-docs-code>

<ul>
    <li><code>meta.total</code> is every item in scope, and <code>meta.filtered</code> is the count after searching. When <code>filtered</code> is missing it equals <code>total</code>.</li>
    <li>Always return the total. Without it the table cannot work out how many pages there are.</li>
    <li>For another shape, override <code>parse()</code> and return a <code>Page</code>.</li>
</ul>

<x-docs-code lang="php">
@verbatim
use Amprest\DtTables\Support\Page;

public function parse(Response $response): Page
{
    return new Page(
        rows: $response->json('items', []),
        total: (int) $response->json('count'),
        filtered: $response->json('matches'),
    );
}
@endverbatim
</x-docs-code>

<h2>A full example, end to end</h2>
<p>
    This follows one draw of the <code>MessageTable</code> above. Someone has typed <code>john</code> in the global search, chosen <em>Sent</em> in the status filter,
    sorted by <em>Created</em> from newest, and moved to the second page of ten.
</p>

<h3>1. What the browser sends</h3>
<p>The browser posts to the rows endpoint with the sealed token. This is the standard DataTables request, plus <code>exact</code>, which lists the dropdown filters by column index.</p>

<x-docs-code lang="json" title="POST /api/dt-tables/server-side">
@verbatim
{
    "token": "eyJpdiI6Ik9x...sealed...",
    "draw": 3,
    "start": 10,
    "length": 10,
    "search": { "value": "john", "regex": false },
    "columns": [
        { "data": "recipient", "name": "recipient", "searchable": true, "orderable": true, "search": { "value": "", "regex": false } },
        { "data": "status", "name": "status", "searchable": true, "orderable": true, "search": { "value": "sent", "regex": false } },
        { "data": "created_at", "name": "created_at", "searchable": false, "orderable": true, "search": { "value": "", "regex": false } }
    ],
    "order": [ { "column": 2, "dir": "desc", "name": "created_at" } ],
    "exact": [1]
}
@endverbatim
</x-docs-code>

<h3>2. What the package asks the API</h3>
<p>
    The package turns that into the request below. The page number is worked out from <code>start</code> and <code>length</code>, the column filters are keyed by remote field,
    and your own <code>params()</code> adds the scope and the dates. <code>created_at</code> has no search target, so it is not part of the search, and
    <code>status</code> is left out of <code>search_fields</code> because the map sets <code>'global' => false</code>.
</p>

<x-docs-code lang="bash" title="GET https://messages.example.com/api/messages">
@verbatim
GET /api/messages
    ?page=2
    &per_page=10
    &search=john
    &search_fields[0]=recipient
    &filters[status]=sent
    &sort=created_at
    &dir=desc
    &scope_type=zone
    &scope_id[0]=1
    &scope_id[1]=2
    &start_date=2026-01-01
    &end_date=2026-01-31

Accept: application/json
Authorization: Bearer ••••••••
@endverbatim
</x-docs-code>

<p>With <code>method(): 'post'</code> the same values are sent as a JSON body instead of a query string.</p>

<x-docs-code lang="json" title="The same request with method() returning post">
@verbatim
{
    "page": 2,
    "per_page": 10,
    "search": "john",
    "search_fields": ["recipient"],
    "filters": { "status": "sent" },
    "sort": "created_at",
    "dir": "desc",
    "scope_type": "zone",
    "scope_id": [1, 2],
    "start_date": "2026-01-01",
    "end_date": "2026-01-31"
}
@endverbatim
</x-docs-code>

<h3>3. What the API answers</h3>
<p>
    Twelve messages match, so page 2 holds the last two. <code>total</code> is every message in scope and <code>filtered</code> is the number that matched the search.
    Extra fields, such as <code>provider_ref</code> here, are simply ignored unless <code>handle()</code> reads them.
</p>

<x-docs-code lang="json" title="200 OK">
@verbatim
{
    "data": [
        {
            "id": 8841,
            "recipient": "0711 000 123",
            "status": "sent",
            "created_at": "2026-01-14 09:32:10",
            "provider_ref": "wamid.HBgM..."
        },
        {
            "id": 8795,
            "recipient": "0722 000 456",
            "status": "sent",
            "created_at": "2026-01-13 16:05:41",
            "provider_ref": "wamid.HBgN..."
        }
    ],
    "meta": {
        "total": 40000,
        "filtered": 12
    }
}
@endverbatim
</x-docs-code>

<h3>4. What goes back to the browser</h3>
<p>
    <code>handle()</code> maps each item to the cells of a row, and the counts are passed through. <code>draw</code> is echoed so the browser can ignore
    answers that arrive out of order. <code>parameters</code> is whatever the table's <code>parameters()</code> returned, empty here.
</p>

<x-docs-code lang="json" title="200 OK">
@verbatim
{
    "draw": 3,
    "recordsTotal": 40000,
    "recordsFiltered": 12,
    "data": [
        { "recipient": "0711 000 123", "status": "Sent", "created_at": "2026-01-14 09:32:10" },
        { "recipient": "0722 000 456", "status": "Sent", "created_at": "2026-01-13 16:05:41" }
    ],
    "parameters": []
}
@endverbatim
</x-docs-code>

<x-docs-callout type="tip">
    The browser never sees the API address, the token, the scope or the dates. It only holds the sealed token, and everything in step 2 is built on the server.
</x-docs-callout>

<h2>When the API fails</h2>
<x-docs-table
    :columns="['Cause', 'Status', 'What people see']"
    :rows="[
        ['The API could not be reached or timed out', '504', 'An error card above the table with a Try again button.'],
        ['The API answered with an error status', '502', 'The same error card.'],
    ]"
/>
<p>The table is never silently drawn empty when the API fails. Return an empty <code>data</code> list when there are simply no results.</p>

<h2>Adding data from your own database</h2>
<p>
    <code>handle()</code> sees one page at a time, so it is the place to join API items with data you own. Look the data up once for the page, not once per row.
    <code>parse()</code> runs before the rows are mapped, so load it there.
</p>

<x-docs-code lang="php">
@verbatim
protected Collection $tenants;

public function parse(Response $response): Page
{
    $page = parent::parse($response);

    $this->tenants = Tenant::whereIn('id', collect($page->rows)->pluck('client_id'))
        ->get()
        ->keyBy('id');

    return $page;
}
@endverbatim
</x-docs-code>

<p>The API cannot search or sort a field it does not hold. Resolve the matching ids in <code>params()</code> and send them as a filter, or store the value in the API.</p>

<h2>Things to remember</h2>
<ul>
    <li>Constructor parameters are sealed JSON. Pass ids and date strings, never a model or a date object.</li>
    <li>Do not drop rows after the page arrives. That makes the counts and the pages wrong. Send the condition to the API.</li>
    <li>Keep the API token in <code>client()</code>, which runs on the server. Never put it in <code>:params</code>.</li>
    <li>Long id lists belong in a <code>post</code> body, or send a scope instead of the ids.</li>
    <li>An API served by the same app works under Herd or php-fpm, but hangs under the single-process <code>php artisan serve</code>.</li>
</ul>

<h2>Testing</h2>
<p>Fake the API with <code>Http::fake()</code> and post to the rows endpoint with a sealed token.</p>

<x-docs-code lang="php" title="tests/Feature/MessageTableTest.php">
@verbatim
use Amprest\DtTables\Support\TableSource;
use Illuminate\Support\Facades\Http;

it('asks the api for the requested page', function () {
    Http::fake(['messages.test/*' => Http::response([
        'data' => [['recipient' => '0711', 'status' => 'sent', 'created_at' => '2026-01-01']],
        'meta' => ['total' => 40, 'filtered' => 12],
    ])]);

    $response = $this->postJson(route('dt-tables.api.server-side.rows'), [
        'token' => TableSource::seal(MessageTable::class, [
            'scopeType' => 'zone', 'scopeIds' => [1], 'from' => '2026-01-01', 'to' => '2026-01-31',
        ]),
        'draw' => 1,
        'start' => 10,
        'length' => 10,
        'search' => ['value' => 'john'],
        'columns' => [['name' => 'recipient', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '']]],
        'order' => [['column' => 0, 'dir' => 'desc']],
    ])->assertOk();

    expect($response->json('recordsFiltered'))->toBe(12);

    Http::assertSent(fn ($request) => $request['page'] === 2 && $request['search'] === 'john');
});
@endverbatim
</x-docs-code>
