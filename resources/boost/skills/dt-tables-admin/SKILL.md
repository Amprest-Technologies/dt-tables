---
name: dt-tables-admin
description: "Use when working with the amprest/dt-tables package's built-in admin UI at /dt-tables/data-tables: registering a new DataTable, adding/editing/deleting columns and their search types, editing a table's name/theme/export buttons/behaviour/loader settings, or debugging why a table isn't picking up its dt-tables/{key}.json configuration. Covers the package's own routes, controllers, and Blade views under package/src/Http/Controllers and package/resources/views/pages/data-tables. Do not use for writing Table classes in app/DataTables/, the <x-data-table> component itself, or the package's JS/CSS bundles — see the dt-tables-development skill for those."
license: MIT
metadata:
  author: Amprest Technologies
---

# DataTable Admin UI

## Package Overview

`amprest/dt-tables` ships a small local-only admin UI for managing the `dt-tables/` data store without hand-editing files: registering tables, adding/editing/deleting columns, and editing per-table settings (name, theme, export buttons, behaviour, loader). Each table lives in its own `dt-tables/{key}.json` file.

## Routes (local-only)

Every route below is guarded by `Amprest\DtTables\Http\Middleware\PreventIfEnvironmentIsNotLocal` (`abort_if(! App::isLocal(), 403)`), so the entire admin UI — including the index/list page — is only reachable when `APP_ENV=local`.

| Route name | Method + Path | Controller action |
|-----------|--------------|------------------|
| `dt-tables.data-tables.index` | `GET /dt-tables/data-tables` | `DataTableController@index` |
| `dt-tables.data-tables.store` | `POST /dt-tables/data-tables` | `DataTableController@store` |
| `dt-tables.data-tables.edit` | `GET /dt-tables/data-tables/{data_table}/edit` | `DataTableController@edit` |
| `dt-tables.data-tables.update` | `PUT /dt-tables/data-tables/{data_table}/update` | `DataTableController@update` |
| `dt-tables.data-tables.destroy` | `DELETE /dt-tables/data-tables/{data_table}/destroy` | `DataTableController@destroy` |
| `dt-tables.data-tables.columns.store` | `POST /dt-tables/data-tables/columns/{data_table}` | `DataTableColumnController@store` |
| `dt-tables.data-tables.columns.update` | `PUT /dt-tables/data-tables/columns/{data_table}/{data_table_column}` | `DataTableColumnController@update` |
| `dt-tables.data-tables.columns.destroy` | `DELETE /dt-tables/data-tables/columns/{data_table}/{data_table_column}` | `DataTableColumnController@destroy` |

## Index Page (`pages/data-tables/index.blade.php`)

A form to register a new table (just a `key`, e.g. `invoices-table` — must match the `id` you'll use on `<x-data-table>`), and a list of existing tables with Edit/Delete actions.

## Edit Page (`pages/data-tables/edit.blade.php`)

Two cards, each containing independent `<form>`s that submit individually (not one big form):

1. **Columns card** — a form to add a new column (`key` + auto-defaulted `search_type: none`), and a table listing existing columns, each row with its own inline update form (`key`, `search_type` select) plus a delete form.
2. **Settings card** — five separate forms, each `PUT`-ing to `dt-tables.data-tables.update` with a different `type` query parameter:
   - `type=name` — the table's `key`
   - `type=theme` — `bootstrap` or `tailwind`
   - `type=buttons` — per-button active/inactive toggle (`copy`, `colvis`, `excel`)
   - `type=behaviour` — `page_length`, `ordering`, `searching`, `paging`, `info`, `scroll_x`
   - `type=loader` — `enabled`, `message`, `image`

`DataTableRequest::updateData()` reads `$this->type` (the query param) to know which settings key to mutate, and always operates on the full `$dataTable->toArray()` — adding a new settings section requires a new `type` case in `DataTableRequest::rules()` plus a matching formatter method on the request class.

## Inline Form Error Scoping (`bag()`)

Because multiple forms share one page, validation errors must be scoped so they surface next to the right form. The `bag()` helper (from `package/src/Utils/Global.php`) outputs a hidden `_bag` input carrying an id; `DataTableColumnRequest::prepareForValidation()` reads it into `$this->errorBag`:

```blade
{{-- In the form --}}
{!! bag('some-unique-id') !!}   {{-- <input type="hidden" name="_bag" value="some-unique-id"> --}}

{{-- Displaying errors for that form only --}}
@error('key', 'some-unique-id')
    <small class="text-red-700">{{ $message }}</small>
@enderror
```

Column rows use the column's own `id` as the bag id, so each row's inline update form gets independently scoped errors — this is required because several forms on the edit page validate the same field name (`key`).

## Layout & Alerts (`pages/layouts/app.blade.php`)

```blade
<x-data-table-assets mode="admin" />  {{-- loads toastr JS only, not the DataTables bundle --}}
```

Every controller action redirects back with a flash `alert`; the layout renders it via toastr:

```blade
@if(session()->has('alert'))
    <script type="module">
        toastr[`{{ session('alert.type') }}`](`{{ session('alert.message') }}`);
    </script>
@endif
```

`alert.type` matches a toastr method name: `success`, `error`, `warning`, `info`.

## Uniqueness Validation

`TableNameIsUnique` and `ColumnNameIsUnique` (both `ValidationRule` objects in `src/Rules`) enforce that a table's `key` is unique across all tables, and a column's `key` is unique within its parent table — both accept an `ignore` model so editing a record doesn't flag its own current value as a duplicate.

## Common Pitfalls

- Settings updates always send the whole table object (`$dataTable->toArray()`), not just the changed field — if you add a new settings section, make sure its formatter method has sensible fallbacks for tables that predate it (see the `behaviour` fallback pattern using `fluent((array) ($settings->behaviour ?? []))`).
- Column rows share `<button form="...">` across a `<tr>` and two out-of-band `<form>` elements — each needs a unique `id` derived from the column's id, or submitting one row's form will submit the wrong one.
- The whole UI, including the index page, is inaccessible outside `APP_ENV=local` — don't rely on it being reachable in staging or production; tables must be registered another way there (e.g. committing `dt-tables/{key}.json` files directly) if needed.
