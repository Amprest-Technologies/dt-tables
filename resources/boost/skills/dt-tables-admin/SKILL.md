---
name: dt-tables-admin
description: "Use when working with the amprest/dt-tables package's own web UI under /dt-tables: the landing page (/dt-tables), the table admin at /dt-tables/data-tables (registering a DataTable and its mode, the cards list with search and mode tabs, the edit page with its sidebar sections, columns and search types, name/mode/theme/buttons/behaviour/loader settings, rename and delete), the documentation at /dt-tables/docs (adding or editing a docs page, the docs-code, docs-callout and docs-table components, the Docs navigation class), or debugging why a table isn't picking up its resources/data-tables/{key}.json configuration. Covers the package's own routes, controllers, TablePresenter, Blade views and admin.js under package/src and package/resources. Do not use for writing Table classes in app/DataTables/, the <x-data-table> component itself, or the DataTables JS/CSS bundle (client.js) — see the dt-tables-development and dt-tables-server-side skills for those."
license: MIT
metadata:
  author: Amprest Technologies
---

# DataTable Admin UI and Docs

## Package Overview

`amprest/dt-tables` ships a small local-only web UI under `/dt-tables`:

- a **landing page** (`/dt-tables`) that leads to the tables and the docs,
- an **admin** (`/dt-tables/data-tables`) for managing the `resources/data-tables/` store without hand-editing files, and
- **documentation** (`/dt-tables/docs`), a Laravel-docs-style guide with one page per topic.

Each table lives in its own `resources/data-tables/{key}.json` file. The UI shares one layout (`pages/layouts/app.blade.php`: sticky navy header with Tables and Docs tabs, `max-w-7xl` content), one admin bundle (`resources/js/admin.js` and `resources/css/admin.css`, loaded with `<x-data-table-assets mode="admin" />`) and the Nyumbani sales palette defined as `@theme` tokens in `resources/css/utils/tailwind.css` (amber `primary-*`, navy `secondary-*`, plus the `-soft` tints). The type scale and line heights are overridden in the same `@theme` block, so change them there rather than per view.

## Routes (local-only)

Every route below except the asset route is inside the `PreventIfEnvironmentIsNotLocal` group (`abort_if(! App::isLocal(), 403)`), so the whole UI is only reachable when `APP_ENV=local`.

| Route name | Method + Path | Controller action |
|-----------|--------------|------------------|
| `dt-tables.home` | `GET /dt-tables` | `HomeController` |
| `dt-tables.docs.index` | `GET /dt-tables/docs` | `DocsController@index` (redirects to the first page) |
| `dt-tables.docs.show` | `GET /dt-tables/docs/{page}` | `DocsController@show` |
| `dt-tables.data-tables.index` | `GET /dt-tables/data-tables` | `DataTableController@index` |
| `dt-tables.data-tables.store` | `POST /dt-tables/data-tables` | `DataTableController@store` |
| `dt-tables.data-tables.edit` | `GET /dt-tables/data-tables/{data_table}/edit` | `DataTableController@edit` |
| `dt-tables.data-tables.update` | `PUT /dt-tables/data-tables/{data_table}/update` | `DataTableController@update` |
| `dt-tables.data-tables.destroy` | `DELETE /dt-tables/data-tables/{data_table}/destroy` | `DataTableController@destroy` (redirects to the list) |
| `dt-tables.data-tables.columns.store` | `POST /dt-tables/data-tables/columns/{data_table}` | `DataTableColumnController@store` |
| `dt-tables.data-tables.columns.update` | `PUT /dt-tables/data-tables/columns/{data_table}/{data_table_column}` | `DataTableColumnController@update` |
| `dt-tables.data-tables.columns.destroy` | `DELETE /dt-tables/data-tables/columns/{data_table}/{data_table_column}` | `DataTableColumnController@destroy` |

The asset route `dt-tables.asset.show` (`GET /dt-tables/assets/{name}`) is outside the guard and the `web` middleware, because the bundles are static and cached for a year.

## No Inline PHP in Views

Views never contain `@php` blocks: the controller or `Support\TablePresenter` prepares the data and the view only loops and prints. Put new view data in `TablePresenter` (or a component class), not in Blade.

## The Presenter (`src/Support/TablePresenter.php`)

One class prepares every admin page:

- `modes()`: the label, summary and badge classes of `inline`, `server_side` and `api` (inline is neutral gray, server-side navy, API amber, since the palette only has two hues).
- `card($table)`: the summary of one table for the list (mode, theme, page size, button count, `Columns` for inline or `Filters: From class` for the others). A missing or unknown mode reads as `inline`.
- `edit($table)`: `['sections' => [...], 'initial' => id, 'guide' => [...]]` for the edit page. `initial` is the section holding the first error of the last submission, else `general`. `guide` is the label and docs URL of the page that explains the table's mode.

A section is `['id', 'title', 'description', 'partial', ...]`. The `partial` picks the view under `pages/data-tables/sections/`:

| Section id | Partial | Shown for |
|-----------|---------|-----------|
| `general` | `forms` | all (mode and theme forms) |
| `columns` | `columns` | inline tables only |
| `buttons` | `forms` | all (server-side tables only offer `colvis`) |
| `behaviour` | `forms` | all (server-side: no "All" page length, no Pagination switch) |
| `loader` | `forms` | all |
| `danger` | `danger` | all (rename form plus the delete button) |

Server-side and API tables have no Columns section: their filters come from the table class, and the column routes return 403 for them (`DataTable::isServerSide()`).

A form is `['title', 'notice', 'fields', 'action']`, built by `form()`; a field is built by `select()` or `text()`, each carrying its `error` key so `@error($field['error'])` works for nested names such as `loader.message` and `buttons.copy`. All of them are rendered by `partials/form.blade.php` and `partials/field.blade.php`, so adding a setting means adding a field, not a view.

## List Page (`pages/data-tables/index.blade.php`)

Cards in a `gap-1` grid, each with the mode badge, theme, a three-figure summary, Edit and Delete (with a `confirm()`). A search box and mode tabs (with counts) filter the cards client-side, and the matching cards are paged six at a time (`TablePresenter::PER_PAGE`, passed to the view as `perPage` and read from `data-per-page` on the grid). `admin.js` (`setupTableFilter`) hides the cards outside the current page and draws the pager (a summary, Previous, Next and one button per page, cloned from the `#pager-page` template so its classes are generated by Tailwind); the pager is hidden when one page is enough, and a new search or mode starts again from page 1. The "New table" button opens a native `<dialog>` with the `key` input and three mode cards (the mode is required). With no tables an empty state explains the three modes. The dialog reopens by itself after a failed submit through a `data-open` attribute on the `<dialog>`.

## Edit Page (`pages/data-tables/edit.blade.php`)

A sidebar of sections with one section visible at a time, and a "{mode} guide" button that links to the docs page of the table's mode. Each form saves on its own: a `PUT` to `dt-tables.data-tables.update` with a different `type` query parameter:

   - `type=name` — the table's `key` (in the Danger zone; it renames the file)
   - `type=mode` — the table's `mode` (required)
   - `type=theme` — any key under `config('dt-tables.themes')`
   - `type=buttons` — per-button active/inactive toggle (`copy`, `colvis`, `excel`)
   - `type=behaviour` — `page_length`, `ordering`, `searching`, `paging`, `info`, `scroll_x`
   - `type=loader` — `enabled`, `message`, `image`

`DataTableRequest::updateData()` reads `$this->type` to know which settings key to mutate, and always operates on the full `$dataTable->toArray()` — adding a new settings section needs a new `type` case in `DataTableRequest::rules()`, a matching formatter method on the request class, and a form in `TablePresenter`.

`admin.js` (`setupSectionNav`) switches sections. The section to open is chosen in this order: a section with errors, the address hash, the section last used for the table (`sessionStorage`, wrapped in try/catch), then the one the server opened.

## Error Scoping and Old Input

Several forms share the page, so errors are scoped with named bags. The `bag()` helper (from `package/src/Utils/Global.php`) outputs a hidden `_bag` input; `DataTableColumnRequest::prepareForValidation()` reads it into `$this->errorBag`:

```blade
{!! bag('some-unique-id') !!}   {{-- <input type="hidden" name="_bag" value="some-unique-id"> --}}

@error('key', 'some-unique-id')
    <small class="text-red-700">{{ $message }}</small>
@enderror
```

Column rows use the column's own `id` as the bag id, and the add-column form uses `new-column`. Old input must also be scoped, because `old('key')` is the same for every form that has a `key` field: the presenter only reads `old()` for a column row whose bag has errors (`session('errors')?->hasBag($id)`), for the add-column field when the `new-column` bag has errors, and for the rename field when the default bag has a `key` error. Never read `old('key')` unconditionally, or a failed column save pre-fills the rename form and Save would rename the table.

## Documentation (`/dt-tables/docs`)

`Support\Docs::sections()` is the single list of sections and pages (`slug`, `title`, `description`) in reading order. `DocsController@show` renders `pages/docs/show.blade.php` (sidebar, article, "On this page" list, previous/next) and includes the page view `pages/docs/pages/{slug}.blade.php`. The docs tests fail if a page file is missing from `Docs` or a page in `Docs` has no file.

To add a page: create `resources/views/pages/docs/pages/{slug}.blade.php` (the body only: the layout prints the title and description) and add its entry to `Docs::sections()`.

Write pages with the shared components, never raw markup for these:

| Component | Use |
|-----------|-----|
| `<x-docs-code lang="php\|blade\|json\|bash\|js" title="path/or/label">` | A highlighted, copyable code block |
| `<x-docs-callout type="note\|tip\|warning">` | A call-out |
| `<x-docs-table :columns="[...]" :rows="[[...]]">` | A table; text between backticks becomes inline code |

Rules for snippets:

- Wrap every snippet in `@verbatim ... @endverbatim`, so Blade does not run `{{ }}`, directives or `<x-...>` tags inside it.
- Write an opening PHP tag as `&lt;?php`. Blade executes a literal one even inside `@verbatim`; `Code::dedent()` turns the entity back.
- In prose, escape `<x-data-table>` as `&lt;x-data-table&gt;` and avoid a bare `@directive` or `{{` outside a snippet.
- `:rows` cells are PHP strings, so avoid apostrophes or escape them as `\'`.
- Check every example against the code before documenting it.

Highlighting and the "On this page" list are built by `admin.js` (`setupDocs`, using `highlight.js` core with PHP, XML (for Blade), JSON, bash and JavaScript). Tailwind only scans `resources/views/pages` and `resources/views/components/docs`, so a class that appears only in JS or in a PHP class is never generated; put classes in the views.

## Layout & Alerts (`pages/layouts/app.blade.php`)

```blade
<x-data-table-assets mode="admin" />  {{-- loads the admin bundle (toastr, highlight.js, page scripts), not the DataTables bundle --}}
```

Every controller action redirects with a flash `alert`; the layout renders it via toastr:

```blade
@if(session()->has('alert'))
    <script type="module">
        toastr[`{{ session('alert.type') }}`](`{{ session('alert.message') }}`);
    </script>
@endif
```

`alert.type` matches a toastr method name: `success`, `error`, `warning`, `info`. Page scripts live in `resources/js/admin.js` and are guarded to return early on pages without their elements; do not add inline `<script>` blocks to the pages. The layout still has an unused `@stack('scripts')`.

## Uniqueness Validation

`TableNameIsUnique` and `ColumnNameIsUnique` (both `ValidationRule` objects in `src/Rules`) enforce that a table's `key` is unique across all tables, and a column's `key` is unique within its parent table — both accept an `ignore` model so editing a record doesn't flag its own current value as a duplicate.

## Server-side Tables

Tables rendered with `<x-data-table :source>` (see the `dt-tables-server-side` skill) use the same `resources/data-tables/{key}.json` entries, with differences: their `columns` are ignored (filters come from `columnMap()` and `filterOptions()`), the admin offers no columns section, and Copy/Excel and the "All" page length are not offered.

## Common Pitfalls

- Settings updates always send the whole table object (`$dataTable->toArray()`), not just the changed field — if you add a new settings section, give its field a sensible default for tables that predate it (the presenter reads settings with `data_get`/`fluent` fallbacks).
- Column rows share `<button form="...">` across a `<tr>` and two out-of-band `<form>` elements — each needs a unique `id` derived from the column's id, or submitting one row's form will submit the wrong one.
- Deleting a table redirects to the list, never `back()`: from the edit page `back()` would land on the page of a table that no longer exists.
- The whole UI, including the landing page and the docs, is inaccessible outside `APP_ENV=local` — don't rely on it in staging or production; commit the `resources/data-tables/{key}.json` files so tables exist there.
- When package behaviour changes, update the docs pages and these skills in the same change.
