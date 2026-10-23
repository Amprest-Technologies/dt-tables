@extends('docs::layout')

@section('page', 'Table Classes')
@section('lead', 'Load every row up front and let the browser search and sort them.')

@section('content')
    <div class="docs-prose mb-10">
<p>
    An inline table loads every row when the page loads, then searches, sorts and pages them in the browser. It is the simplest mode
    and works well up to a few thousand rows. Its table class extends <code>BaseTable</code>.
</p>
    </div>

    <x-docs::section id="how-a-table-is-built" title="How a table is built">
<p>Calling <code>Table::build()</code> runs the same steps every time:</p>

<x-docs::code lang="bash" title="The pipeline">
@verbatim
build()  →  new Table(...)  →  query()  →  shared()  →  before()  →  handle() for each row  →  after()
         →  ['table' => [...rows], 'parameters' => [...]]
@endverbatim
</x-docs::code>

<p>You only write the steps you need. <code>query()</code> and <code>handle()</code> are required, and the rest have sensible defaults.</p>

<x-docs::table
    :columns="['Method', 'Required', 'Purpose']"
    :rows="[
        ['`query()`', 'Yes', 'Returns the rows: an Eloquent builder, a relation or a collection.'],
        ['`handle($model, $key)`', 'Yes', 'Turns one row of the query into the array of cells the table shows.'],
        ['`shared()`', 'No', 'Computes something once for all rows, such as a permission check. Read it in `handle()` as `$this->shared[...]`.'],
        ['`before($data)`', 'No', 'Changes the whole collection before the rows are mapped, for example to sort or filter it.'],
        ['`after($data)`', 'No', 'Changes the mapped rows afterwards.'],
        ['`parameters()`', 'No', 'Data sent to the browser with the table, used by the export event.'],
    ]"
/>
    </x-docs::section>

    <x-docs::section id="a-complete-table-class" title="A complete table class">
<x-docs::code lang="php" title="app/DataTables/HouseTable.php">
@verbatim
&lt;?php

namespace App\DataTables;

use Nyumbani\DtTables\Facades\DataTable;
use Nyumbani\DtTables\Tables\BaseTable;
use App\Models\House;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;

class HouseTable extends BaseTable
{
    public function __construct(
        protected Organization $organization,
        protected string $status = 'active',
    ) {}

    public function query(): Builder
    {
        return House::query()
            ->select(['houses.id', 'houses.house_no', 'zones.name as zone_name', 'houses.rent'])
            ->join('zones', 'houses.zone_id', '=', 'zones.id')
            ->whereBelongsTo($this->organization)
            ->status($this->status);
    }

    protected function shared(): array
    {
        return ['canView' => user()->can('view houses')];
    }

    public function handle(mixed $house, mixed $key): mixed
    {
        return [
            'zone' => $house->zone_name,
            'house_no' => $house->house_no,
            'rent' => number_format($house->rent, 2),
            'actions' => $this->shared['canView'] ? $this->actions($house) : [],
        ];
    }

    protected function actions(House $house): array
    {
        return [[
            'button' => [
                'label' => 'View',
                'attributes' => DataTable::parseAttributes([
                    'href' => route('houses.show', $house),
                    'class' => 'btn btn-primary btn-sm',
                ]),
            ],
        ]];
    }
}
@endverbatim
</x-docs::code>
    </x-docs::section>

    <x-docs::section id="passing-arguments" title="Passing arguments">
<p>
    <code>build()</code> hands its arguments to the constructor, so use named arguments to keep the call readable. Declare them with
    constructor property promotion, as in the example above.
</p>

<x-docs::code lang="php" title="app/Http/Controllers/HouseController.php">
@verbatim
use App\DataTables\HouseTable;
use App\Models\House;
use App\Models\Organization;
use Illuminate\Contracts\View\View;

public function index(Organization $organization): View
{
    $this->authorize('viewAny', House::class);

    return view('houses.index', [
        'houses' => HouseTable::build(organization: $organization, status: 'active'),
    ]);
}
@endverbatim
</x-docs::code>

<x-docs::callout>
    Keep controllers thin. Authorize the request, call <code>build()</code> and pass the result to the view. Queries and formatting belong in the table class.
</x-docs::callout>
    </x-docs::section>

    <x-docs::section id="working-with-the-whole-collection" title="Working with the whole collection">
<p>
    <code>before()</code> receives the collection returned by <code>query()</code> before any row is mapped, and
    <code>after()</code> receives the mapped rows. Always return a collection.
</p>

<x-docs::code lang="php">
@verbatim
use Illuminate\Support\Collection;

protected function before(Collection $data): Collection
{
    return $data->sortBy('balance')->values();
}

protected function after(Collection $data): Collection
{
    return $data->filter(fn ($row) => $row['status'] !== 'archived')->values();
}
@endverbatim
</x-docs::code>
    </x-docs::section>

    <x-docs::section id="things-to-remember" title="Things to remember">
<ul>
    <li><code>handle()</code> always takes <code>mixed $model, mixed $key</code>. Declaring <code>int $key</code> is a fatal signature error.</li>
    <li>Compute anything expensive once in <code>shared()</code>, never inside <code>handle()</code>, which runs for every row.</li>
    <li>Return the same keys you use as <code>dtt-title</code> in the view. A key with no match shows an empty cell.</li>
    <li>Loading thousands of rows slows the page. When that happens, <a href="{{ $docs->url('server-side-overview') }}">go server-side</a>.</li>
</ul>
    </x-docs::section>
@endsection
