# Table Class Structure

## BaseTable Pipeline

All Table classes extend `Amprest\DtTables\Tables\BaseTable`. The pipeline runs via the static `build()` method:

```
::build(args) → new static(args) → query() → get() → shared() → before() → handle() per row → after() → return ['table' => [...], 'parameters' => [...]]
```

### Static Invocation

- `::build(...)` creates an instance and runs the full pipeline, returning the payload array
- `::make(...)` creates and returns the instance directly (for advanced use)

## Creating a New Table Class

Scaffold the file with `php artisan make:data-table {Name}` — this creates `app/DataTables/{Name}.php` with all methods stubbed.

### Step 1: Create the File

Create a new file in `app/DataTables/`. Name it `{Entity}Table.php`:

```php
<?php

namespace App\DataTables;

use Amprest\DtTables\Facades\DataTable;
use Amprest\DtTables\Tables\BaseTable;
use App\Models\House;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;

class HouseTable extends BaseTable
{
    /**
     * Initialize the house table.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function __construct(
        protected Organization $organization,
        protected ?HouseType $houseType = null,
        protected string $status = 'active',
    ) {}
}
```

**Key rules:**
- Use constructor property promotion for all parameters
- Nullable parameters default to `null`
- Constructor visibility: `protected` or `public`

### Step 2: Define the Query

Override `query()` to return a Builder, Relation, or Collection:

```php
/**
 * Return a builder instance for the house table.
 *
 * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
 */
public function query(): Builder
{
    //  Define the select parameters
    $selectParams = [
        'houses.id',
        'houses.uuid',
        'zones.name as zone_name',
        'houses.house_no',
        'house_types.name as house_type',
    ];

    //  Return the query builder
    return House::query()
        ->select($selectParams)
        ->join('zones', 'houses.zone_id', '=', 'zones.id')
        ->join('house_types', 'houses.house_type_id', '=', 'house_types.id')
        ->with('tenant:tenants.id,tenants.uuid,tenants.house_id')
        ->status($this->status)
        ->whereBelongsTo($this->organization);
}
```

**Query return types:**
- `Builder` — most common, for Eloquent queries
- `BelongsToMany` or other relation types — for relationship-based queries
- `Collection` — for external API data or pre-built collections
- `mixed` — when the type varies

### Step 3: Define Shared Data

Override `shared()` to compute data needed across all rows (permissions, rendered templates):

```php
/**
 * Return the shared parameters for the table.
 *
 * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
 */
protected function shared(): array
{
    return [
        'permission' => user()->can('view houses'),
    ];
}
```

For tables with form-based actions, use `DataTable::renderTemplate()` to pre-render Blade views with EJS template syntax:

```php
protected function shared(): array
{
    return [
        'confirmStatus' => DataTable::renderTemplate('components.admins.data-tables.attendance.confirm'),
        'checkoutAction' => DataTable::renderTemplate('components.admins.data-tables.attendance.check-out'),
    ];
}
```

**Why shared?** Templates rendered via `DataTable::renderTemplate()` and permission checks are expensive. Computing them once in `shared()` avoids repeated calls per row.

Access in `handle()` via `$this->shared['key']` (the `$shared` property, populated once by `build()` before the loop).

### Step 4: Map Each Row

Override `handle()` to transform each model into a display array:

```php
/**
 * Get the records to be displayed in the table.
 *
 * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
 */
public function handle(mixed $house, int $key): mixed
{
    return [
        'id' => $house->id,
        'zone' => $house->zone_name,
        'house_no' => [
            'value' => $house->house_no,
            'classes' => 'text-start',
        ],
        'house_type' => $house->house_type,
        'occupied' => is_null($house->tenant) ? 'No' : 'Yes',
        'rent' => $house->rent,
        'deposit' => $house->deposit,
        'actions' => $this->handleActions($house, ...$this->shared),
    ];
}
```

**Column value formats:**

| Format | Example | Description |
|--------|---------|-------------|
| Simple string/number | `'zone' => $house->zone_name` | Plain text cell |
| Array with classes | `['value' => $val, 'classes' => 'fw-bold text-danger']` | Styled cell |
| Array with raw/display | `['value' => $val, 'raw' => $html, 'display' => $html]` | Cell with HTML content |
| Fluent object | `fluent(['value' => $val, 'classes' => '...'])` | Object-style cell |

**Conditional CSS classes** use `DataTable::columnClasses()`:

```php
'status' => [
    'value' => prettify($status),
    'classes' => DataTable::columnClasses([
        'fw-bold',
        'text-danger' => $status === 'archived',
        'text-success' => $status === 'active',
    ]),
],
```

**Returning Fluent objects** — use `fluent([...])` for object-style access in views:

```php
public function handle(mixed $visit, int $key): mixed
{
    return fluent([
        'visitor_name' => $visit->visitor->name,
        'status' => $visit->status,
        'actions' => $this->handleActions($visit, ...$this->shared),
    ]);
}
```

### Step 5: Define Action Buttons

Actions are arrays of button definitions using `DataTable::parseAttributes()`:

```php
/**
 * Handle the actions.
 *
 * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
 */
protected function handleActions(House $house, bool $permission): array
{
    //  Check if the permission is granted
    if ($permission) {
        $actions[] = [
            'button' => [
                'label' => 'View',
                'attributes' => DataTable::parseAttributes([
                    'href' => route('admin.houses.show', ['house' => $house]),
                    'class' => 'btn btn-primary btn-sm',
                ]),
            ],
        ];
    }

    //  Return the actions
    return $actions ?? [];
}
```

**Action formats:**

| Format | Use Case |
|--------|----------|
| `'button' => ['label' => ..., 'attributes' => DataTable::parseAttributes([...])]` | Standard link/button |
| `'template' => ['rendered' => true, 'html' => $html]` | Pre-rendered HTML (static content) |
| `'template' => ['rendered' => false, 'html' => $template, 'parameters' => [...]]` | EJS template with row-specific data for client-side rendering |

**Template action with client-side binding** — when `rendered` is `false`, the `html` contains an EJS template and `parameters` passes row-specific data:

```php
$actions[] = [
    'template' => [
        'rendered' => ! $canCheckout,
        'html' => $checkoutHtml,
        'parameters' => [
            'ulid' => $attendance->ulid,
        ],
    ],
];
```

The EJS template blade file (e.g. `components/admins/data-tables/attendance/check-out.blade.php`):

```blade
<script type="text/javascript"><% window[it.id] = JSON.parse(JSON.stringify(it.row)); %></script>
<div x-data="window['<%= it.id %>']">
    <form action="{{ route('admin.attendance.check-out') }}" method="POST" class="inline">
        @csrf
        @method('PUT')
        <input type="hidden" name="attendance_ids[]" :value="ulid" />
        <button type="submit" class="text-red-900 hover:text-red-800 font-medium">Check Out</button>
    </form>
</div>
```

**Key points for EJS templates:**
- `<% %>` executes JS serverside (template engine), `<%= %>` outputs values
- `it.id` is a unique row identifier, `it.row` contains all row data from `handle()`
- The `parameters` array values are merged into `it.row` for client-side access
- `x-data` binds Alpine.js to the row data, allowing `:value="ulid"` binding
- Routes and CSRF tokens are rendered server-side by Blade; row data binds client-side

**Shared data spreading** — use `...$this->shared` to unpack shared values as method arguments:

```php
'actions' => $this->handleActions($model, ...$this->shared),

protected function handleActions(Model $model, string $icon, bool $permission): array
```

Or access directly:

```php
$permission = $this->shared['permission'];
$icon = $this->shared['icon'];
```

### Step 6: Optional Lifecycle Hooks

**`before(Collection $data): Collection`** — Pre-process the collection before row mapping:

```php
protected function before(Collection $data): Collection
{
    return $data
        ->when($this->status === 'active', fn ($data) => $data->sortBy('account_balance'))
        ->values();
}
```

**`after(Collection $data): Collection`** — Post-process the mapped collection:

```php
protected function after(Collection $data): Collection
{
    return $data
        ->when($this->paymentStatus !== 'all', fn ($data) => $data->filter(
            fn ($item) => strtolower($item->payment_status->value) === $this->paymentStatus
        ))
        ->values();
}
```

### Step 7: Define Parameters (Optional)

Override `parameters()` for export activity logging:

```php
/**
 * Define the parameters for the table.
 *
 * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
 */
protected function parameters(): array
{
    //  Get the model
    $model = (zone() ?? $this->organization);

    //  Return the parameters
    return [
        'buttonTrigger' => [
            'activityLog' => [
                'userId' => user()->id,
                'key' => 'houses-table-exported',
                'trans' => 'logs.data-table.houses-table',
                'subject' => [
                    'model' => $model::class,
                    'id' => $model->getKey(),
                ],
            ],
        ],
    ];
}
```

## Complete Table Class Example

```php
<?php

namespace App\DataTables;

use Amprest\DtTables\Facades\DataTable;
use Amprest\DtTables\Tables\BaseTable;
use App\Models\Organization;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ZoneTable extends BaseTable
{
    /**
     * Initialize the zone table.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function __construct() {}

    /**
     * Return a builder instance for the zone table.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function query(): BelongsToMany
    {
        //  Get the relations
        $relations = ['users', 'location', 'houses'];

        //  Return the number of zones
        return user()->zones()->withCount($relations)->oldest('name');
    }

    /**
     * Get the list of zones.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function handle(mixed $zone, int $key): mixed
    {
        //  Get the permission
        $permission = $this->shared['permission'];

        //  Return the zone attributes
        return [
            'name' => $zone->name,
            'group' => $zone->group,
            'status' => [
                'value' => prettify($status = $zone->status),
                'classes' => DataTable::columnClasses([
                    'fw-bold text-success',
                    'text-danger' => $status == 'archived',
                ]),
            ],
            'houses' => $zone->houses_count,
            'users' => $zone->users_count,
            'actions' => $permission ? $this->handleActions($zone) : [],
        ];
    }

    /**
     * Get the shared attributes for the zone table.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function shared(): array
    {
        return [
            'permission' => user()->can('view zones'),
        ];
    }

    /**
     * Handle the actions.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function handleActions(Zone $zone): array
    {
        //  Get the actions
        $actions[] = [
            'button' => [
                'label' => 'View',
                'attributes' => DataTable::parseAttributes([
                    'href' => route('admin.administration.zones.edit', ['zone' => $zone]),
                    'class' => 'btn btn-primary btn-sm',
                ]),
            ],
        ];

        //  Return the actions
        return $actions ?? [];
    }

    /**
     * Define the parameters for the table.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function parameters(): array
    {
        //  Get the model
        $model = organization();

        //  Return the parameters
        return [
            'buttonTrigger' => [
                'activityLog' => [
                    'userId' => user()->id,
                    'key' => 'zones-table-exported',
                    'trans' => 'logs.data-table.zones-table',
                    'subject' => [
                        'model' => $model::class,
                        'id' => $model->getKey(),
                    ],
                ],
            ],
        ];
    }
}
```
