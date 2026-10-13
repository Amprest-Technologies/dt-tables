---
name: dt-tables-server-side
description: "Use when a DataTable has too many rows to load at once and should page, search and sort in the database instead: creating ServerSideTable classes in app/DataTables/ (php artisan make:data-table --server-side), defining query(), columnMap() (SQL expressions, search closures, the global opt-out), keyColumn(), filterOptions() and authorize(), rendering with <x-data-table :source :params>, the server-side endpoints and config('dt-tables.server_side') settings, and debugging searches, sorting, counts, loader/error messages or 403/419 responses. Do not use for inline tables built with BaseTable::build() and :payload, or for the admin UI — see the dt-tables-development and dt-tables-admin skills."
license: MIT
metadata:
  author: Amprest Technologies
---

# Server-side DataTables with amprest/dt-tables

## When to Use It

| Inline table (`BaseTable`) | Server-side table (`ServerSideTable`) |
|---|---|
| Controller runs `Table::build()` and passes `:payload` | No controller work: the view passes `:source` (the class) |
| Every row is loaded, mapped and sent with the page | Only the requested page is queried and mapped |
| Search and sort happen in the browser | Search, sort, paging and counts happen in SQL |
| Copy and Excel export the whole table | Copy and Excel buttons are hidden (they would only see one page) |

Choose server-side when the table can hold thousands of rows. Keep inline for small tables and for tables that need `before()`/`after()` collection processing or data that is not in SQL.

## Architecture Flow

```
<x-data-table :source="Table::class" :params="[...]">
  → component seals {class, params} into an encrypted token
  → DataTables POSTs draw/start/length/search/order/columns + token
  → ServerSideController: open token → authorize() → ServerSideQuery
  → search, count, order, page in SQL → handle() on that page's models only
  → JSON {draw, recordsTotal, recordsFiltered, data, parameters}
```

## Quick Start

1. Scaffold the class: `php artisan make:data-table OrderTable --server-side` (creates `app/DataTables/OrderTable.php`).
2. Fill in `query()`, `columnMap()`, `handle()` (and `filterOptions()` for select filters).
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

4. Register `orders-table` (admin UI or `resources/data-tables/orders-table.json`) to give columns a `search_type` of `input`, `select` or `none`.

## Quick Reference

### 1. The Table Class → `references/table-class.md`

- Extend `Amprest\DtTables\Tables\ServerSideTable`
- Required: `query()`, `handle()`, `columnMap()`
- Optional: `keyColumn()`, `filterOptions()`, `shared()`, `parameters()`, `authorize()`
- `query()` must not call `limit()`, `offset()` or a final `orderBy` (a default `latest()` is fine)

### 2. The Column Map → `references/column-map.md`

- Maps each row key to the SQL it is searched and sorted by
- Entry forms: string, closure, or `['search' => ..., 'sort' => ..., 'global' => bool]`
- Decides which columns the browser offers sorting and filtering on

### 3. Endpoints, Security and Errors → `references/endpoints.md`

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
