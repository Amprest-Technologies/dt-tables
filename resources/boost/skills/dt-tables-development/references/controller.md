# Controller Integration

## Calling Table Classes

Controllers call Table classes via `::build()` with named parameters matching the constructor:

```php
$data = TableClass::build(
    parameterOne: $value,
    parameterTwo: $value,
);
```

The return value is an array:

```php
[
    'table' => [...],       // Array of mapped row data
    'parameters' => [...],  // Additional configuration (e.g., export activity logging)
]
```

## Controller Pattern

Controllers should:
1. Authorize the request first
2. Extract filters/parameters from the request
3. Call the Table class with named parameters
4. Pass the result to the view

### Simple Example

```php
<?php

namespace App\Http\Controllers\Portal\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\DataTables\InvoiceTable;
use App\Traits\HasDateFilters;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    use HasDateFilters;

    /**
     * Show the list of invoices.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function __invoke(): View
    {
        //  Check for the user's permissions
        $this->authorize('viewAny', Invoice::class);

        //  Get the date
        [$year, $month] = $this->breakdownRequestDate();

        //  Get all invoices
        $invoices = InvoiceTable::build(
            organization: $this->organization,
            year: $year,
            month: $month,
        );

        //  Return the list of invoices
        return view('portal.admins.invoices.index', [
            'invoices' => $invoices,
            'year' => $year,
            'month' => $month,
        ]);
    }
}
```

### With Filters and Conditional Initialization

```php
/**
 * Display a listing of the houses.
 *
 * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
 */
public function index(Request $request): View
{
    //  Authorize the request
    $this->authorize('viewAny', House::class);

    //  Get the status filter
    $status = request()->query('filter', 'active');

    //  Abort if the status is invalid
    abort_unless(in_array($status, ['archived', 'active']), 404);

    //  Get the house type filter
    $houseType = $this->organization
        ->houseTypes()
        ->where('uuid', $request->type)
        ->first();

    //  Get the house builder instance
    $houses = HouseTable::build(
        organization: $this->organization,
        houseType: $houseType,
        status: $status,
    );

    //  Return the view
    return view('portal.admins.houses.index', [
        'houses' => $houses,
        'status' => $status,
    ]);
}
```

### With Date Range Filtering

```php
/**
 * Display a listing of all the transactions.
 *
 * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
 */
public function index(): View
{
    //  Authorize the request
    $this->authorize('viewAny', Transaction::class);

    //  Get the date range
    [$startDate, $endDate] = $this->getRequestDateRange();

    //  Get the payments
    $payments = PaymentTable::build(
        $this->organization,
        $startDate,
        $endDate,
    );

    //  Return the transactions
    return view('portal.admins.transactions.index', [
        'payments' => $payments,
        'startDate' => $startDate,
        'endDate' => $endDate,
    ]);
}
```

### Conditional Table Initialization

Tables may not always be initialized — handle cases where filters prevent table creation:

```php
//  Only build table if conditions are met
if (! $selectZone) {
    $communications = CommunicationTable::build(
        $this->organization,
        $recipientType,
    );
}

//  Pass empty array as fallback
return view('portal.admins.communications.index', [
    'communications' => $communications ?? [],
]);
```

## Key Conventions

- **Authorization first**: Always call `$this->authorize()` or `Gate::authorize()` before building the table
- **Named parameters**: Use named parameters for readability when the Table constructor has multiple arguments
- **View variable naming**: Pass the table result under a descriptive plural key (e.g., `$invoices`, `$houses`, `$tenants`)
- **Fallback values**: Use `?? []` or `?? collect()` when the table might not be initialized
- **Thin controllers**: All data query logic, row mapping, and formatting belongs in the Table class — not the controller
