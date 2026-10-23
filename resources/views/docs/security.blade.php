@extends('docs::layout')

@section('page', 'Security and Errors')
@section('lead', 'The sealed token, authorization, status codes and what users see when a request fails.')

@section('content')
    <div class="docs-prose mb-10">
<p>
    Server-side and API tables answer requests from the browser, so it matters who can ask and for what. This page explains the protections that are built in and the ones you add.
</p>
    </div>

    <x-docs::section id="the-endpoints" title="The endpoints">
<x-docs::table
    :columns="['Route name', 'Method and path', 'Purpose']"
    :rows="[
        ['`dt-tables.api.server-side.rows`', '`POST /api/dt-tables/server-side`', 'One page of rows.'],
        ['`dt-tables.api.server-side.options`', '`POST /api/dt-tables/server-side/options`', 'The options of one or more dropdown filters.'],
    ]"
/>
    </x-docs::section>

    <x-docs::section id="the-sealed-token" title="The sealed token">
<p>
    The component encrypts the table class and its parameters with your app key into a token. The browser only holds that token and sends it with every request,
    so it cannot ask for another class or change the parameters.
</p>
<ul>
    <li>A token that does not decrypt, or that names a class that is not a server-side table, is answered with a <code>403</code>.</li>
    <li>Rotating <code>APP_KEY</code> invalidates the tokens of pages that are already open. People only need to reload.</li>
    <li>Tokens do not expire. Use <code>authorize()</code> for rules that depend on who is asking.</li>
</ul>
    </x-docs::section>

    <x-docs::section id="authorizing-requests" title="Authorizing requests">
<p>
    <code>authorize()</code> runs on every rows and options request. Return <code>false</code> and the request is answered with a <code>403</code>.
</p>

<x-docs::code lang="php">
@verbatim
use Illuminate\Http\Request;

public function authorize(Request $request): bool
{
    return $request->user()?->can('view orders') ?? false;
}
@endverbatim
</x-docs::code>
    </x-docs::section>

    <x-docs::section id="middleware" title="Middleware">
<p>
    The endpoints use the <code>web</code> middleware by default, which supplies the session, the signed-in user and CSRF protection.
    Add <code>auth</code> to require a signed-in user for every server-side table at once.
</p>

<x-docs::code lang="php" title="config/dt-tables.php">
@verbatim
'server_side' => [
    'middleware' => ['web', 'auth'],
    'max_length' => 1000,
],
@endverbatim
</x-docs::code>

<p>
    <code>max_length</code> caps the rows of one request. When someone chooses to show all rows, they receive this many at most.
</p>
    </x-docs::section>

    <x-docs::section id="status-codes" title="Status codes">
<x-docs::table
    :columns="['Status', 'Cause']"
    :rows="[
        ['`200`', 'The page was returned.'],
        ['`403`', 'The token is bad, the class is not a server-side table, or `authorize()` returned `false`.'],
        ['`419`', 'The CSRF token is missing or has expired.'],
        ['`422`', 'The request body is not valid, for example it has no token.'],
        ['`502`', 'API tables only. The API answered with an error status.'],
        ['`504`', 'API tables only. The API could not be reached or timed out.'],
    ]"
/>
    </x-docs::section>

    <x-docs::section id="what-people-see" title="What people see">
<ul>
    <li><strong>First load.</strong> The table's loader shows until the first page arrives.</li>
    <li><strong>Later requests.</strong> The same loader appears over the table while a page loads.</li>
    <li>
        <strong>A failed request.</strong> The table is drawn empty and an error card appears above it, so an empty table is never mistaken for no data.
        A <code>419</code> or <code>401</code> offers <em>Reload page</em>. A <code>403</code> offers nothing, because retrying cannot help.
        Anything else, including a <code>502</code> or <code>504</code>, offers <em>Try again</em>. The card clears the next time a page loads.
    </li>
</ul>
    </x-docs::section>

    <x-docs::section id="testing-a-table" title="Testing a table">
<p><code>TableSource::seal()</code> returns the same token the component renders, so tests can post to the endpoint directly.</p>

<x-docs::code lang="php" title="tests/Feature/OrderTableTest.php">
@verbatim
use App\DataTables\OrderTable;
use App\Models\Order;
use App\Models\User;
use Nyumbani\DtTables\Support\TableSource;

it('pages the paid orders', function () {
    $this->actingAs(User::factory()->create())
        ->postJson(route('dt-tables.api.server-side.rows'), [
            'token' => TableSource::seal(OrderTable::class, ['status' => 'paid']),
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'search' => ['value' => ''],
            'columns' => [['name' => 'customer_name', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '']]],
            'order' => [['column' => 0, 'dir' => 'asc']],
        ])
        ->assertOk()
        ->assertJsonPath('recordsTotal', Order::where('status', 'paid')->count());
});
@endverbatim
</x-docs::code>

<p>Test <code>authorize()</code> by posting as a user without the permission and expecting <code>assertForbidden()</code>.</p>
    </x-docs::section>
@endsection
