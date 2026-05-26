# Blade Component Usage

## The `<x-data-table>` Component

The `<x-data-table>` component renders a fully interactive DataTable with search, sort, filter, pagination, and export features.

### Required Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `id` | `string` | Unique identifier — **must match** the `key` in `dt-tables.json` |
| `:payload` | `array` | The data array returned by `Table::build()` |

### Optional Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `title` | `string` | Table title used in export filenames |
| `class` | `string` | HTML classes for the `<table>` element |

### Basic Usage

```blade
<x-data-table id="invoices-table" title="List of Invoices" class="table table-sm table-hover" :payload="$invoices">
    <thead>
        <tr>
            <th dtt-row-index style="width: 1%" class="text-center">#</th>
            <th>Created At</th>
            <th class="text-start">Invoice No</th>
            <th>Type</th>
            <th>Status</th>
            <th dtt-title="amount">Amount ({{ currency() }})</th>
            <th dtt-actions class="text-center">Actions</th>
        </tr>
    </thead>
</x-data-table>
```

## Header Attributes (`dtt-*` Directives)

These special attributes on `<th>` elements control DataTable behavior:

| Attribute | Value | Purpose |
|-----------|-------|---------|
| `dtt-row-index` | *(none)* | Auto-generates row numbers (1, 2, 3...) |
| `dtt-title="key"` | Column key string | Enables column-specific filtering/sorting. The key must match a column key in `dt-tables.json` |
| `dtt-actions` | *(none)* | Marks the actions column (excluded from search, receives action buttons) |
| `exclude-from-export` | *(none, on class)* | Hides the column from Excel/copy exports |

### When to Use `dtt-title`

The package identifies column names from the `<th>` inner text. Use `dtt-title="key"` in two cases:

1. **Searchable/filterable columns** — the key links the column to its `dt-tables.json` search configuration
2. **Dynamic `<th>` content** — when the header text contains variables or Blade expressions (e.g., `Amount ({{ currency() }})`), the package cannot reliably extract the column name from the rendered text. Add `dtt-title="key"` so the package knows what column to render.

```blade
{{-- Static text — dtt-title optional (only needed if searchable) --}}
<th>Worker</th>

{{-- Dynamic text — dtt-title required so the package knows the column name --}}
<th dtt-title="daily_rate">Daily Rate (@currency())</th>
<th dtt-title="amount">Amount ({{ currency() }})</th>
```

### Column Key Matching

The `dtt-title` value must match both:
1. A key in the row array returned by `handle()` in the Table class
2. A column `key` in the `dt-tables.json` configuration

```php
// In Table class handle():
return [
    'zone' => $house->zone_name,        // ← key is 'zone'
    'house_no' => $house->house_no,      // ← key is 'house_no'
];
```

```blade
<!-- In Blade view: -->
<th dtt-title="zone">Zone</th>           <!-- matches 'zone' key -->
<th dtt-title="house_no">House No</th>   <!-- matches 'house_no' key -->
```

## Conditional Columns

Columns can be conditionally shown based on context:

```blade
<thead>
    <tr>
        <th dtt-row-index style="width: 1%" class="text-center">#</th>

        {{-- Only show for relocated/archived tenants --}}
        @if(in_array($status, ['relocated', 'archived']))
            <th>@prettify($status) At</th>
        @endif

        {{-- Only show zone column when multiple zones are selected --}}
        @if($organization->hasMultipleZonesSelected())
            <th dtt-title="zone">@prettify($zoneDisplayName)</th>
        @endif

        <th>Name</th>
        <th dtt-title="house_no" class="text-start">House No</th>
        <th dtt-actions class="text-center">Actions</th>
    </tr>
</thead>
```

## Complete View Example

```blade
@extends('layouts.portal')
@section('title', 'List of Invoices')
@push('actions')
    <form action="{{ route('admin.invoices.index') }}" method="GET" class="date-picker-form">
        <x-date-picker
            name="dates"
            class="form-control form-control-sm"
            placeholder="Select the date you need"
            :month-mode="true"
            :default-date="$year.'-'.$month"
        />
    </form>
@endpush
@section('content')
    <x-data-table id="invoices-table" title="List of Invoices" class="table table-sm table-hover" :payload="$invoices">
        <thead>
            <tr>
                <th dtt-row-index style="width: 1%" class="text-center">#</th>
                <th>Created At</th>
                <th class="text-start">Invoice No</th>
                @if($organization->hasMultipleZonesSelected())
                    <th dtt-title="zone">@prettify($zoneDisplayName)</th>
                @endif
                <th dtt-title="tenant_name">Tenant Name</th>
                <th dtt-title="house_no" class="text-start">House No</th>
                <th>Type</th>
                <th>Status</th>
                <th dtt-title="amount">Amount ({{ $currency = currency() }})</th>
                <th dtt-title="balance">Balance ({{ $currency }})</th>
                <th dtt-actions class="text-center">Actions</th>
            </tr>
        </thead>
    </x-data-table>
@endsection
```

## How the Component Works Internally

The `<x-data-table>` component (`Amprest\DtTables\Views\Components\DataTable`):

1. Accepts `:payload` and extracts `$payload['table']` as `$tableData` and `$payload['parameters']` as `$tableParams`
2. Looks up `dt-tables.json` for a matching `key` to load column configuration (search types, themes, buttons)
3. Renders a `<table>` with the slotted `<thead>`, hidden initially if a loader is configured
4. Injects an inline `<script type="module">` that initializes the DataTable with:
   - Row data from `$tableData`
   - Column config from `dt-tables.json`
   - Search/filter setup per column based on `search_type`
   - Export buttons (copy, colvis, excel)
   - Button trigger event listeners for activity logging
5. The `<tbody>` is generated client-side by the DataTable JS library from the `tableData` array
