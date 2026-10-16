---
name: dt-tables-server-side
description: "Use when a DataTable has too many rows to load at once and should page, search and sort on the server instead, either in the database (ServerSideTable) or through an HTTP API (ApiTable): creating ServerSideTable classes in app/DataTables/ (php artisan make:data-table --server-side), ApiTable classes for tables whose rows come from a remote API (php artisan make:data-table --api; client(), endpoint(), params(), parse(), PageRequest, Page, 502/504 errors, page-only enrichment), defining query(), columnMap() (SQL expressions, search closures, the global opt-out, or remote field names for APIs), keyColumn(), filterOptions() and authorize(), rendering with <x-data-table :source :params>, the server-side endpoints and config('dt-tables.server_side') settings, and debugging searches, sorting, counts, loader/error messages or 403/419 responses. Do not use for inline tables built with BaseTable::build() and :payload, or for the admin UI — see the dt-tables-development and dt-tables-admin skills."
license: MIT
metadata:
  author: Amprest Technologies
---

# Server-side DataTables with amprest/dt-tables

## When to Use It

| | Inline table (`BaseTable`) | Server-side SQL (`ServerSideTable`) | Server-side API (`ApiTable`) |
|---|---|---|---|
| Rows come from | A query or collection loaded up front | Your database | A remote HTTP API |
| Controller work | `Table::build()` and `:payload` | None: the view passes `:source` | None: the view passes `:source` |
| What is loaded | Every row, mapped and sent with the page | Only the requested page | Only the requested page |
| Search, sort, paging | In the browser | In SQL | In the API (the package sends the page, search and order) |
| `columnMap()` values | n/a | SQL expressions or closures | Remote field names |
| Copy / Excel | Whole table | Hidden (only one page loaded) | Hidden |

Choose server-side when the table can hold thousands of rows. Pick `ServerSideTable` when the rows are in your own database and `ApiTable` when another service owns them (and can page, search and sort them). Keep inline for small tables and for tables that need `before()`/`after()` collection processing.

## Architecture Flow

```
<x-data-table :source="Table::class" :params="[...]">
  → component seals {class, params} into an encrypted token
  → DataTables POSTs draw/start/length/search/order/columns + token
  → ServerSideController: open token → authorize() → $table->runner()
  → ServerSideTable: ServerSideQuery searches, counts, orders and pages in SQL
  → ApiTable: ApiQuery builds a PageRequest → params() → HTTP to the API → parse() → Page
  → handle() on that page's items only
  → JSON {draw, recordsTotal, recordsFiltered, data, parameters}
```

Both table types extend `ServerSideSource`, which holds `handle()`, `columnMap()`, `filterOptions()`, `shared()`, `parameters()` and `authorize()`, and each returns a `ServerSideRunner`. The token, validation, loader and error card are shared.

## Quick Start

1. Scaffold the class (creates `app/DataTables/OrderTable.php`):
   - Database: `php artisan make:data-table OrderTable --server-side`
   - Remote API: `php artisan make:data-table OrderTable --api`
2. Fill in `query()`, `columnMap()`, `handle()` (and `filterOptions()` for select filters). For an API table fill in `client()`, `endpoint()`, `columnMap()` and `handle()` instead, plus `params()` if the API names things differently.
3. Render it:

```blade
<x-data-table id="orders-table" :source="App\DataTables\OrderTable::class" :params="['status' => 'paid']" title="Orders" class="table table-sm">
    <thead>
        <tr>
            <th dtt-row-index>#</th>
            <th dtt-title="customer_name">Customer</th>
            <th dtt-title="total">Total</th>
            <th dtt-actions>Actions</th>
        </tr>
    </thead>
</x-data-table>
```

4. Register `orders-table` (admin UI or `resources/data-tables/orders-table.json`) with `mode` `server_side` or `api`. The JSON `columns` are ignored for these modes and the admin cannot edit them: the filters always come from the class, a `select` for every key in `filterOptions()` and an `input` for every other searchable key in `columnMap()`. Give a key no search target to leave it unfiltered.

## Quick Reference

### 1. The Table Class → `references/table-class.md`

- Extend `Amprest\DtTables\Tables\ServerSideTable` (rows in your database)
- Required: `query()`, `handle()`, `columnMap()`
- Optional: `keyColumn()`, `filterOptions()`, `filterKeys()` (names the select keys without building the options; override it when the options are costly to build), `shared()`, `parameters()`, `authorize()`
- `query()` must not call `limit()`, `offset()` or a final `orderBy` (a default `latest()` is fine)

### 2. The Column Map → `references/column-map.md`

- Maps each row key to the SQL it is searched and sorted by
- Entry forms: string, closure, or `['search' => ..., 'sort' => ..., 'global' => bool]`
- Decides which columns the browser offers sorting and filtering on

### 3. API-Backed Tables → `references/api-table.md`

- Extend `Amprest\DtTables\Tables\ApiTable` when another service owns the rows and pages, searches and sorts them
- Required: `client()`, `endpoint()`, `columnMap()` (remote field names), `handle()`
- Optional: `params()` (translate the `PageRequest`), `parse()` (read rows and counts), `method()` (`'post'` for long lists), `filterOptions()`, `authorize()`
- The default request sends `page`, `per_page`, `search`, `search_fields`, `filters`, `sort`, `dir`; the default response is `data` plus `meta.total` and `meta.filtered`
- A failing API gives 502 or 504 and the error card, never an empty table
- Covers page-only enrichment from your own database, sealed scalar params, and testing with `Http::fake()`

### 4. Endpoints, Security and Errors → `references/endpoints.md`

- Routes, middleware, the sealed token, `max_length`, status codes
- The loading overlay and the error card shown when a request fails
- Testing a server-side table with Pest

## Common Pitfalls

- A key in `columnMap()` is the same key `handle()` returns and the `<th dtt-title>` uses. Keep all three in step; a key missing from the map is simply not searchable or sortable (the browser hides the arrow and filter).
- The `columnMap()` SQL and `handle()` must produce the same value from the same row data. Never derive a searchable value from `$key` (the row's position) — it changes as the user pages or sorts.
- Set `keyColumn()` when a join makes `id` ambiguous (`'orders.id'`), otherwise paging can repeat or skip rows.
- Constructor parameters (`:params`) must be JSON-safe scalars or arrays; they are sealed into the token and replayed with `new Class(...$params)` on every request. Pass an id, not a model.
- The constructor also runs once while the view renders (to read `columnMap()`), so keep it free of heavy work.
- `:source` and `:payload` cannot be combined; the component throws `InvalidArgumentException`.
- `authorize()` defaults to `true` and the endpoints use the `web` middleware by default. Add `auth` through `config('dt-tables.server_side.middleware')` and use `authorize()` for per-table rules.
- Global search runs a `like '%word%'` condition across every searchable column and cannot use an index. Set `'global' => false` on ids, amounts and other columns people do not search by.
- Themes work as for inline tables: the table's `settings.theme` (or `config('dt-tables.settings.theme')`) picks `bootstrap` or `tailwind` classes for the filters and buttons. The loader overlay and the error card use their own neutral styles in `client.css`, so they need no framework classes.
- `handle()` signature is `handle(mixed $model, mixed $key): mixed`. `int $key` is a fatal signature error.
- **API tables:** the API must page, search and sort itself and return `meta.total`; never filter the page after it arrives, because the counts and pages would be wrong. Pass scopes and dates as scalars in `:params`, keep credentials in `client()`, and send long id lists with `method(): 'post'`. See `references/api-table.md`.
