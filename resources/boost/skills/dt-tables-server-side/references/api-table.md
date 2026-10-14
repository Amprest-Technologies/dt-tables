# API-backed tables (`ApiTable`)

Use `ApiTable` when the rows live behind an HTTP API, not in your database. The browser flow, sealed token, `authorize()`, `handle()`, `filterOptions()`, loader and error card are the same as for `ServerSideTable`; only the data source differs. The API must page, search and sort itself: the package sends it the page and renders what comes back.

Scaffold with `php artisan make:data-table InvoiceTable --api`.

## Required methods

| Method | Purpose |
|---|---|
| `client(): PendingRequest` | The configured HTTP client (base url, token, timeout). Credentials stay in your app, never in the package. |
| `endpoint(): string` | Path relative to the client's base url. |
| `columnMap(): array` | Row key => **remote field name**. |
| `handle($item, $key)` | Map one API item (an array) to a row. Runs on the returned page only. |

## `columnMap()` for APIs

Values are field names the API understands, not SQL:

```php
return [
    'recipient' => ['search' => 'recipient', 'sort' => 'recipient'],
    'status'    => ['search' => 'status', 'global' => false],   // sortable by status too
    'created'   => ['sort' => 'created_at', 'global' => false],
    'name'      => 'name',                                      // string = search and sort
];
```

- A key with a field is searchable; a key with a sort field (or a search field to fall back on) is sortable. The browser only offers those.
- `'global' => false` keeps a field out of the global search.
- Closures are not supported (they cannot be sent to an API); override `params()` instead.

## Request parameters

`params(PageRequest $page)` turns the browser's request into the API's query. The default sends:

| Param | Value |
|---|---|
| `page`, `per_page` | 1-based page and size (`PageRequest::page()`, `->length`) |
| `search`, `search_fields` | Global phrase and the remote fields it matches |
| `filters` | `[field => term]` for column searches |
| `sort`, `dir` | First ordering |

`PageRequest` also exposes `start`, `filters` (with `key` and `exact` flags) and the full `orders` list. Override `params()` to rename or add parameters (a sealed scope, date range, etc.), and `method()` to return `'post'` to send them in a JSON body (useful for long id lists).

## Reading the response

`parse(Response): Page` defaults to Laravel's resource shape: items in `data`, the total in `meta.total`, and the count after searching in `meta.filtered` (falls back to the total). Override it for any other shape and return `new Page(rows: [...], total: 100, filtered: 12)`.

## Errors

- Unreachable API (`ConnectionException`) → HTTP 504.
- Non-2xx response → HTTP 502.

The browser shows the normal error card ("Try again"); the table never silently renders as empty. Retries and timeouts belong on `client()`.

## Example

```php
class MessageTable extends ApiTable
{
    public function __construct(private string $scopeId) {}

    public function client(): PendingRequest
    {
        return Http::baseUrl(config('services.connect.url'))->withToken(config('services.connect.token'));
    }

    public function endpoint(): string
    {
        return 'api/messages';
    }

    public function params(PageRequest $page): array
    {
        return [...parent::params($page), 'scope_id' => $this->scopeId];
    }

    public function columnMap(): array
    {
        return ['recipient' => 'recipient', 'status' => ['search' => 'status', 'global' => false]];
    }

    public function handle(mixed $item, mixed $key): array
    {
        return ['recipient' => $item['recipient'], 'status' => $item['status']];
    }
}
```

```blade
<x-data-table id="messages-table" :source="App\DataTables\MessageTable::class" :params="['scopeId' => $organization->id]" />
```

Constructor `:params` are sealed into the token, so they must be scalars or arrays, and the constructor runs on every request, so avoid heavy work in it.

## Testing

Fake the API with `Http::fake()` and post to `route('dt-tables.api.server-side.rows')` with `TableSource::seal(MessageTable::class, [...])`; assert on `Http::assertSent()` for the parameters.
