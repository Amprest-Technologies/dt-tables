# Configuration

## dt-tables.json

The `dt-tables.json` file at the project root stores metadata for all registered DataTables. Each entry defines the table's columns, search types, buttons, and theme.

### Structure

```json
[
    {
        "id": "01jvsbg1pwpppz7gjstvw892dp",
        "key": "tenants-table",
        "settings": {
            "buttons": ["copy", "colvis", "excel"],
            "theme": "bootstrap"
        },
        "columns": [
            {
                "key": "zone",
                "search_type": "select",
                "classes": null,
                "id": "01jvsbkqpt0ps8s3w9m815genz"
            },
            {
                "key": "name",
                "search_type": "input",
                "classes": null,
                "id": "01jvsbm8msvw7ghsw62v6jq6sj"
            },
            {
                "key": "balance",
                "search_type": "none",
                "classes": null,
                "id": "01jvsbrhmsaxy09v6dq7xg79za"
            }
        ]
    }
]
```

### Fields

| Field | Description |
|-------|-------------|
| `id` | ULID identifier for the table entry |
| `key` | **Must match** the `id` attribute on `<x-data-table>` |
| `settings.buttons` | Export buttons to show: `copy`, `colvis`, `excel` |
| `settings.theme` | CSS framework: `bootstrap` or `tailwind` |
| `settings.loader` | Optional loader configuration (see below) |
| `columns[].key` | **Must match** the `dtt-title` value in Blade and the array key from `handle()` |
| `columns[].search_type` | Filter type: `input`, `select`, or `none` |
| `columns[].classes` | Optional CSS classes for the column |
| `columns[].id` | ULID identifier for the column entry |

### Search Types

| Type | Behavior |
|------|----------|
| `input` | Text input filter — searches the column with free-text |
| `select` | Dropdown filter — auto-populated from unique column values |
| `none` | No filter — column is not searchable |

### Loader Configuration

Some tables support a loading overlay while data processes:

```json
{
    "settings": {
        "loader": {
            "enabled": true,
            "message": "Nyumbani is processing, please wait...",
            "image": "img/loaders/app.svg"
        }
    }
}
```

### Registering a New Table

When creating a new DataTable, add an entry to `dt-tables.json` via the admin UI at `/dt-tables/data-tables`, or manually:

1. Generate a ULID for the `id` field
2. Set `key` to match the `id` attribute you will use on `<x-data-table>`
3. Define `settings` with desired buttons and theme
4. Add `columns` entries for each column that needs filtering — the `key` must match the `dtt-title` attribute in the Blade `<th>` element

**Important:** Not every column in `<thead>` needs a `dt-tables.json` entry. Only columns with `dtt-title` attributes that need search/filter capability need entries. Columns like row index (`dtt-row-index`), actions (`dtt-actions`), and plain display columns do not need entries.

## config/dt-tables.php

The package configuration file defines default settings:

```php
return [
    'data_source' => base_path('dt-tables.json'),

    'settings' => [
        'buttons' => ['copy', 'colvis', 'excel'],
        'theme' => 'bootstrap',
        'loader' => [
            'enabled' => true,
            'message' => 'Nyumbani is processing, please wait...',
            'image' => 'img/loaders/app.svg',
        ],
    ],

    'themes' => [
        'bootstrap' => [
            'buttons' => 'btn btn-primary btn-sm',
            'input' => 'form-control form-control-sm',
            'select' => 'form-control form-control-sm',
        ],
        'tailwind' => [
            'buttons' => 'btn btn-sm bg-primary-darker text-white',
            'input' => 'w-full py-2 border-gray-200 rounded-sm shadow-sm text-xs ...',
            'select' => 'w-full py-2 border-gray-200 rounded-sm shadow-sm text-xs ...',
        ],
    ],

    'columns' => [
        'search_types' => ['none', 'input', 'select'],
    ],
];
```

### Theme Selection

Each table can override the default theme in its `dt-tables.json` settings. The theme controls the CSS classes applied to buttons, inputs, and selects within the DataTable UI.

- Use `bootstrap` for portal admin views (Bootstrap-styled pages)
- Use `tailwind` for Tailwind CSS-styled views (e.g., access manager, customer pages)

## DataTable Facade

The `Amprest\DtTables\Facades\DataTable` facade provides helper methods:

### `DataTable::parseAttributes(array $attributes): string`

Converts an array of HTML attributes to a safe attribute string. Used for action buttons:

```php
DataTable::parseAttributes([
    'href' => route('admin.houses.show', ['house' => $house]),
    'class' => 'btn btn-primary btn-sm',
    'target' => '_blank',
])
// Output: 'href="https://..." class="btn btn-primary btn-sm" target="_blank"'
```

### `DataTable::columnClasses(array $classes): string`

Converts a conditional class array to a CSS class string (wrapper around `Arr::toCssClasses()`):

```php
DataTable::columnClasses([
    'fw-bold',
    'text-danger' => $balance < 0,
    'text-success' => $balance > 0,
])
// Output: 'fw-bold text-danger' (when $balance < 0)
```

### `DataTable::renderTemplate(string $view, array $params = []): string`

Renders a Blade view to a minified HTML string. Used for complex action templates that need client-side row data binding via EJS syntax.

```php
//  In shared() — pre-render templates once for all rows
$template = DataTable::renderTemplate('components.admins.data-tables.attendance.confirm');
```

The rendered Blade views typically contain EJS template syntax (`<% %>`, `<%= %>`) that the DataTable JS library processes client-side per row:

```blade
{{-- resources/views/components/admins/data-tables/attendance/confirm.blade.php --}}
<script type="text/javascript"><% window[it.id] = JSON.parse(JSON.stringify(it.row)); %></script>
<div x-data="window['<%= it.id %>']">
    <form action="{{ route('admin.attendance.confirm') }}" method="POST" class="inline">
        @csrf
        @method('PUT')
        <input type="hidden" name="attendance_ids[]" :value="ulid" />
        <button type="submit" class="text-red-800 hover:text-red-900 flex items-center gap-1">
            <span class="underline font-bold">Pending</span>
        </button>
    </form>
</div>
```

**How it works:**
1. Blade renders the server-side parts (routes, CSRF tokens) via `DataTable::renderTemplate()`
2. The result is stored in `shared()` and reused per row
3. The DataTable JS library processes the EJS parts (`it.id`, `it.row`) per row client-side
4. `it.row` contains the row data from `handle()` plus any `parameters` from the template action
5. Alpine.js binds the row data to the DOM (e.g., `:value="ulid"`)

**Template Blade file conventions:**
- Store in `resources/views/components/admins/data-tables/{entity}/`
- Use `it.id` for unique row element IDs
- Use `it.row` to access the row data object
- Blade handles server rendering (routes, CSRF), EJS handles client row binding
