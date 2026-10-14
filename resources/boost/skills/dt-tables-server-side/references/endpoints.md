# Endpoints, Security and Errors

## Routes

Both routes live in `package/routes/api.php`, under the prefix `api/dt-tables` and the `api` middleware group, plus the middleware from `config('dt-tables.server_side.middleware')` (default `['web']`, which supplies the session, the user and CSRF).

| Route name | Method + Path | Purpose |
|------------|---------------|---------|
| `dt-tables.api.server-side.rows` | `POST /api/dt-tables/server-side` | One page of rows |
| `dt-tables.api.server-side.options` | `POST /api/dt-tables/server-side/options` | Options of one select filter |

`ServerSideController` is thin: it opens the token, calls the table's `authorize()`, then hands off to the runner the table returns from `runner()`: `Support\ServerSideQuery` for a `ServerSideTable` (SQL) or `Support\ApiQuery` for an `ApiTable` (HTTP). Validation lives in `ServerSideRowsRequest` and `ServerSideOptionsRequest`.

## Configuration

```php
//  config/dt-tables.php
'server_side' => [
    'middleware' => ['web'],   // add 'auth' to restrict the endpoints
    'max_length' => 1000,      // the most rows one request may return
],
```

A request with `length: -1` ("show all") is capped at `max_length`.

## The Sealed Token

`<x-data-table :source :params>` calls `TableSource::seal()`, which encrypts `{class, params}` with the app key. The browser holds an opaque string and sends it on every call, so it cannot name another class or change the parameters.

- A token that does not decrypt gives **403**.
- A token for a class that does not extend `ServerSideSource` (`ServerSideTable` or `ApiTable`) gives **403**.
- Rotating `APP_KEY` invalidates tokens on pages that are already open.
- Tokens do not expire. Use `authorize()` for rules that depend on who is asking.

## Request and Response

The browser POSTs the DataTables parameters as JSON, plus:

| Field | Meaning |
|-------|---------|
| `token` | The sealed token |
| `exact` | Indexes of columns whose filter is a whole-value match (select filters) |
| `columns[i].name` | The row key, taken from the `<th>` title |

The JSON response is `{draw, recordsTotal, recordsFiltered, data, parameters}`. `draw` echoes the request; `data` holds only this page's rows.

## Status Codes

| Status | Cause |
|--------|-------|
| 200 | Page returned |
| 403 | Bad token, class not server-side, or `authorize()` returned `false` |
| 419 | CSRF token missing or expired |
| 422 | Request body failed validation (e.g. no `token`, `start` below 0) |
| 502 | API tables only: the API answered with a non-2xx status |
| 504 | API tables only: the API was unreachable or timed out |

## What the User Sees

- **First load**: the table's own loader (image and message from `settings.loader`) shows until the first page arrives.
- **Later requests** (paging, sorting, searching): the same loader appears as an overlay on the table instead of DataTables' default "Processing" dots.
- **A failed request**: the table draws empty and an error card appears above it with a title, one line of explanation and, where it helps, a button: *Session expired* (419) and *Sign in required* (401) offer "Reload page", *Access denied* (403) offers nothing, and any other failure, including an API table's 502 and 504, offers "Try again". The card clears on the next successful load.
- CSRF: the JS sends the `XSRF-TOKEN` cookie as `X-XSRF-TOKEN`, which Laravel keeps fresh, and falls back to the token the page was rendered with.

## Testing

```php
use Amprest\DtTables\Support\TableSource;

it('pages orders', function () {
    $response = $this->postJson(route('dt-tables.api.server-side.rows'), [
        'token' => TableSource::seal(OrderTable::class, ['status' => 'paid']),
        'draw' => 1,
        'start' => 0,
        'length' => 10,
        'search' => ['value' => ''],
        'columns' => [['name' => 'customer_name', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '']]],
        'order' => [['column' => 0, 'dir' => 'asc']],
    ])->assertOk();

    expect($response->json('recordsTotal'))->toBe(Order::where('status', 'paid')->count());
});
```

`TableSource::seal()` returns the same token the component renders. Test `authorize()` by posting as a user without the permission and expecting `assertForbidden()`.

## Not Supported

- No server-side export. Copy and Excel are hidden because they only see one page.
- A table backed by an HTTP API extends `ApiTable` instead of `ServerSideTable`; see `api-table.md`.
