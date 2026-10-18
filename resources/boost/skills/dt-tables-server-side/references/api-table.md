# API-backed Tables (`ApiTable`)

Use `ApiTable` when the rows live behind an HTTP API (another Laravel app, a microservice, a third-party service) instead of in your own database. The browser flow, sealed token, `authorize()`, `handle()`, `filterOptions()`, `shared()`, loader and error card are identical to a `ServerSideTable`; only where the rows come from differs.

**The API must page, search and sort for itself.** The package never downloads the whole collection: it asks the API for one page, passes it the browser's search and order, and renders what comes back. If the API only offers an unpaginated "get everything" endpoint, add paging to it first, otherwise nothing is gained over an inline table.

Scaffold with `php artisan make:data-table InvoiceTable --api` (alias `-a`).

## How a Draw Flows

```
DataTables POST (draw/start/length/search/order/columns + token)
  → ServerSideController: open token → authorize() → ApiTable::runner() (ApiQuery)
  → ApiQuery turns the request into a PageRequest (remote field names)
  → ApiTable::params(PageRequest) → HTTP request to the API (client() + endpoint())
  → ApiTable::parse(Response) → Page(rows, total, filtered)
  → handle() on each item of that page → JSON {draw, recordsTotal, recordsFiltered, data, parameters}
```

## Methods

| Method | Required | Purpose |
|--------|----------|---------|
| `client(): PendingRequest` | yes | The configured HTTP client: base url, credentials, timeout, retry. Credentials live in your app; the package never stores them. |
| `endpoint(): string` | yes | Path relative to the client's base url that serves the rows. |
| `columnMap(): array` | yes | Row key => **remote field name** (see below). Decides which columns the browser lets users search and sort. |
| `handle(mixed $item, mixed $key): mixed` | yes | Maps one API item (normally an array) to a display row. Runs on the returned page only. `$key` is the row's absolute position in the whole table. |
| `method(): string` | no (`'get'`) | `'get'` sends parameters as a query string, `'post'` as a JSON body. Use post for long id lists that would overflow a URL. |
| `params(PageRequest $page): array` | no | Translates the page request into the API's parameters. Override to rename parameters or add fixed ones (a scope, a date range). |
| `parse(Response $response): Page` | no | Reads rows and counts from the response. Override when the API does not use Laravel's resource shape. |
| `fetch(PageRequest $page): Page` | no | Sends the request and maps failures to 502/504. Override only to replace the whole HTTP exchange. |
| `filterOptions()`, `shared()`, `parameters()`, `authorize()` | no | Exactly as for `ServerSideTable`. |

There is no `query()`, `keyColumn()`, `before()` or `after()`.

## `columnMap()` for APIs

Values are field names the API understands, not SQL:

```php
return [
    //  A string: the field is both searched and sorted by this name
    'recipient' => 'recipient',

    //  The array form sets search, sort and the global opt-out apart
    'status' => ['search' => 'status', 'global' => false],
    'created' => ['sort' => 'created_at', 'global' => false],
];
```

- A key with a search field is searchable and shows a filter; a key with a sort field (or a search field to fall back on) is sortable and shows an arrow. A key that is absent offers neither.
- `'global' => false` keeps a field out of the global search box (use it on ids, amounts and enums).
- Closures are not supported: they cannot be sent over HTTP. Override `params()` for logic that does not fit a field name.
- A search field with no `sort` key is also sortable by that field. The API must accept it in `sort`, or give the column a `sort` of its own.

## The Request the API Receives

`params()` returns these by default; empty values are left out.

| Param | Value |
|-------|-------|
| `page`, `per_page` | 1-based page number and page size (never above `server_side.max_length`) |
| `search` | The global search phrase, as typed (words separated by spaces) |
| `search_fields` | The remote fields the global search should match (`global` columns only) |
| `filters` | `[field => term]` for column filters. Select filters send the whole value. |
| `sort`, `dir` | The first ordering: a remote field and `asc` or `desc` |

`PageRequest` is what `params()` receives: `start`, `length`, `page()`, `search`, `searchFields`, `filters` (each with `key`, `field`, `term`, `exact`), `orders` (each with `key`, `field`, `direction`, in priority order) and `filterTerms()`. Use `exact` to tell a whole-value select filter from a contains text filter when the API cares.

## The Response the Package Reads

The default `parse()` expects the Laravel resource shape:

```json
{
  "data": [ { "id": 1, "recipient": "0711", "status": "sent" } ],
  "meta": { "total": 40000, "filtered": 312 }
}
```

- `meta.total` is every item in scope (before searching); `meta.filtered` is the count after search. If `filtered` is absent it equals `total`; if `total` is absent the page's own item count is used, which breaks the pager. Always return the total.
- To read another shape override `parse()` and return `new Page(rows: $rows, total: $total, filtered: $filtered)`.

## Errors

| Cause | Status | What the user sees |
|-------|--------|--------------------|
| API unreachable or timed out (`ConnectionException`) | 504 | The error card with "Try again" |
| API answered with a non-2xx status | 502 | The error card with "Try again" |

The table is never silently drawn empty when the API fails. Configure timeouts and retries on `client()`. A 404 from the API is an error too; return an empty `data` array for "no results".

## Complete Example

```php
<?php

namespace App\DataTables;

use Amprest\DtTables\Facades\DataTable;
use Amprest\DtTables\Support\PageRequest;
use Amprest\DtTables\Tables\ApiTable;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MessageTable extends ApiTable
{
    /**
     * Initialize the table with scalar parameters, which are sealed into the token.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function __construct(
        protected string $scopeType,
        protected array $scopeIds,
        protected string $from,
        protected string $to,
    ) {}

    /**
     * Return the client of the messages API.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function client(): PendingRequest
    {
        return Http::baseUrl(config('services.connect.url'))
            ->withToken(config('services.connect.token'))
            ->acceptJson()
            ->timeout(15);
    }

    /**
     * Return the endpoint that serves the messages.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function endpoint(): string
    {
        return 'api/messages';
    }

    /**
     * Send the sealed scope and dates with every page.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
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

    /**
     * Map each row key to the remote field it is searched and sorted by.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function columnMap(): array
    {
        return [
            'recipient' => 'recipient',
            'status' => ['search' => 'status', 'global' => false],
            'created_at' => ['sort' => 'created_at'],
        ];
    }

    /**
     * Return the options of the status filter.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function filterOptions(): array
    {
        return ['status' => ['sent' => 'Sent', 'delivered' => 'Delivered', 'read' => 'Read', 'failed' => 'Failed']];
    }

    /**
     * Map each API item on the page to a display row.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function handle(mixed $message, mixed $key): mixed
    {
        return [
            'recipient' => $message['recipient'],
            'status' => Str::headline($message['status']),
            'created_at' => $message['created_at'],
        ];
    }

    /**
     * Determine whether the request may read the messages.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function authorize(Request $request): bool
    {
        return $request->user()?->can('view messages') ?? false;
    }
}
```

```blade
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
```

Register `messages-table` (admin UI or `resources/data-tables/messages-table.json`) with `mode` `api`. Leave its `columns` empty: the filters come from the class, a `select` for every key in `filterOptions()` and an `input` for every other searchable key in `columnMap()`.

## Page-only Enrichment

`handle()` sees one page of at most `length` items, so it is the place to join API items with data from your own database. Look the extra data up once per page, not once per row and never for the whole collection. `parse()` runs before the rows are mapped and sees exactly that page, so load the lookup there:

```php
protected Collection $tenants;

/**
 * Read the page, then load the tenants it refers to in one query.
 *
 * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
 */
public function parse(Response $response): Page
{
    //  Read the page the default way
    $page = parent::parse($response);

    //  Load only the tenants on this page, keyed by id
    $this->tenants = Tenant::whereIn('id', collect($page->rows)->pluck('client_id'))->get()->keyBy('id');

    //  Return the page
    return $page;
}
```

`handle()` then reads `$this->tenants[$item['client_id']]`. Searching or sorting on an enriched field cannot be done by the API: either resolve the matching ids in `params()` and send them as a filter, or store the value in the API.

## Pitfalls

- **Constructor parameters are sealed JSON.** Scalars and arrays only: pass `organizationId` and date strings, never an `Organization` model or a `CarbonImmutable`. Anything read from the session or the request must be passed in, because the constructor runs again for every draw.
- **The constructor also runs while the view renders**, to read `columnMap()`. Keep it free of queries and HTTP calls.
- **GET query strings have a length limit.** Send long id lists with `method(): 'post'` or send a scope instead of ids.
- **The total must be real.** Return `meta.total`; without it the pager shows only the current page.
- **Do not filter the page after it arrives.** Dropping rows in `handle()` or `parse()` makes the counts and the pages wrong. Send the condition to the API.
- **Keep tokens out of the browser.** `client()` runs on the server, so the API token never reaches the page. Never put it in `:params`.
- **Copy and Excel are hidden** for API tables, as for all server-side tables.
- **Self-calls**: an API served by the same app works under Herd/php-fpm but hangs under the single-process `php artisan serve`.

## Testing

Fake the API with `Http::fake()` and post to `route('dt-tables.api.server-side.rows')` with `TableSource::seal(MessageTable::class, [...])`, then assert what the API received with `Http::assertSent()`:

```php
use Amprest\DtTables\Support\TableSource;
use Illuminate\Support\Facades\Http;

it('asks the api for the requested page', function () {
    Http::fake(['connect.test/*' => Http::response([
        'data' => [['recipient' => '0711', 'status' => 'sent', 'created_at' => '2026-01-01']],
        'meta' => ['total' => 40, 'filtered' => 12],
    ])]);

    $response = $this->postJson(route('dt-tables.api.server-side.rows'), [
        'token' => TableSource::seal(MessageTable::class, ['scopeType' => 'zone', 'scopeIds' => [1], 'from' => '2026-01-01', 'to' => '2026-01-31']),
        'draw' => 1,
        'start' => 10,
        'length' => 10,
        'search' => ['value' => 'john'],
        'columns' => [['name' => 'recipient', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '']]],
        'order' => [['column' => 0, 'dir' => 'desc']],
    ])->assertOk();

    expect($response->json('recordsFiltered'))->toBe(12);
    Http::assertSent(fn ($request) => $request['page'] === 2 && $request['search'] === 'john' && $request['sort'] === 'recipient');
});
```

Test failure handling by faking a 500 (expect 502) or a `ConnectionException` (expect 504), and `authorize()` by posting as a user without the permission (expect 403 and `Http::assertNothingSent()`).
