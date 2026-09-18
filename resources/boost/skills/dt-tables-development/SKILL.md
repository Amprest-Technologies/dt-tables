---
name: dt-tables-development
description: "Use this skill whenever creating, modifying, or debugging DataTables using the amprest/dt-tables package. Covers creating Table classes in app/DataTables/, defining queries with Eloquent, mapping rows via handle(), configuring column search types, rendering tables in Blade views with <x-data-table>, passing data from controllers, using the DataTable facade for attributes and styling, and registering tables as dt-tables/{key}.json files. Also covers export configuration, action buttons, shared data patterns, conditional columns, the before()/after() lifecycle hooks, and listening for the DtButtonTriggered event fired when copy/excel export buttons are clicked. Do not use for generic HTML tables, Livewire tables, or JavaScript-only table libraries. For the package's built-in admin UI at /dt-tables/data-tables (registering tables, editing columns/settings), use the dt-tables-admin skill instead."
license: MIT
metadata:
  author: Amprest Technologies
---

# DataTable Development with amprest/dt-tables

## Package Overview

The `amprest/dt-tables` package provides a server-side DataTable system with:
- PHP Table classes in `app/DataTables/` that define data queries and row mapping
- A `<x-data-table>` Blade component for rendering
- Built-in search, sort, filter, pagination, and export features
- Bootstrap and Tailwind CSS theme support

## Architecture Flow

```
Controller → Table::build(...) → BaseTable pipeline → View → <x-data-table :payload="$data">
```

1. **Controller**: Authorizes, extracts filters, calls `Table::build()` with named parameters
2. **Table Class**: Runs `query()` → `shared()` → `before()` → `handle()` per row → `after()` → returns `['table' => [...], 'parameters' => [...]]`
3. **Blade View**: Renders `<x-data-table>` with `:payload`, defines `<thead>` with `dtt-*` attributes

## Quick Reference

### 1. Table Class Structure → `references/table-class.md`

- Extend `Amprest\DtTables\Tables\BaseTable`, use constructor property promotion
- Override `query()`, `handle()`, `shared()`, `parameters()`
- Optional `before()` and `after()` hooks for pre/post-processing
- Use `DataTable::parseAttributes()` for action buttons
- Use `DataTable::columnClasses()` for conditional CSS classes

### 2. Controller Integration → `references/controller.md`

- Call `Table::build(...)` with named parameters
- Pass result directly to the view
- Keep controllers thin — all data logic lives in the Table class

### 3. Blade Component Usage → `references/blade-component.md`

- Use `<x-data-table>` with `id`, `:payload`, and `class` attributes
- Define `<thead>` with `dtt-row-index`, `dtt-title`, `dtt-actions` attributes
- Wrap in `<x-cards.content :collection="$data">` for empty-state handling

### 4. Configuration → `references/configuration.md`

- Register tables as `dt-tables/{key}.json` files with column search types
- Configure themes and buttons in `config/dt-tables.php`

### 5. Button Trigger Events → `references/button-trigger-events.md`

- `DtButtonTriggered` fires when a `copy` or `excel` export button is clicked (not `colvis`)
- Listen for it in `app/Listeners` — Laravel auto-discovers `handle(DtButtonTriggered $event)`
- Use `parameters()['buttonTrigger']` in the Table class to pass context (e.g. activity-log data) through to the listener

## Common Pitfalls

- The `id` attribute on `<x-data-table>` must match the `key` field inside `dt-tables/{key}.json` (and the filename itself) for column configuration to apply.
- `::build()` accepts named arguments that are forwarded to the constructor: `Table::build(user: $user, status: $status)`.
- The `handle()` method receives `($model, $key)` — always accept both parameters.
- Always check permissions in `shared()` once, then reference `$this->shared['permission']` in `handle()` to avoid repeated queries. Note: `$this->shared` accesses the property (populated by `build()`), not the `shared()` method.
- Use `DataTable::renderTemplate()` in `shared()` to pre-render Blade template views with EJS syntax for client-side row binding — avoids re-rendering per row.
- When returning `Fluent` objects from `handle()`, use `fluent([...])` helper.
- Action buttons require `DataTable::parseAttributes()` for safe HTML attribute generation — never build attribute strings manually.
- For form-based actions (POST/PUT), use the `template` action format with `DataTable::renderTemplate()` and Blade views containing EJS syntax.
- Scaffold new Table classes with `php artisan make:data-table {Name}` — this creates the file in `app/DataTables/` with all required methods stubbed.
- `DtButtonTriggered` only fires for `copy`/`excel` button clicks, never `colvis` — see `references/button-trigger-events.md`.
