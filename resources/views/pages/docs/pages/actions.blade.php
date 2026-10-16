<p>
    The <code>actions</code> key of a row holds the buttons shown in the column marked with <code>dtt-actions</code>.
    It is a list, and each entry is either a button or a template.
</p>

<h2>A button</h2>
<p>
    A button is a link with a label. Build its attributes with <code>DataTable::parseAttributes()</code>, which escapes every value, rather than writing the HTML yourself.
</p>

<x-docs-code lang="php">
@verbatim
use Amprest\DtTables\Facades\DataTable;

protected function actions(House $house): array
{
    return [
        [
            'button' => [
                'label' => 'View',
                'attributes' => DataTable::parseAttributes([
                    'href' => route('houses.show', $house),
                    'class' => 'btn btn-primary btn-sm',
                    'target' => '_blank',
                ]),
            ],
        ],
    ];
}
@endverbatim
</x-docs-code>

<p>The view only needs the column in its header:</p>

<x-docs-code lang="blade">
@verbatim
<th dtt-actions class="text-center">Actions</th>
@endverbatim
</x-docs-code>

<h2>A template</h2>
<p>
    When a button is not enough, a template renders HTML for each row: a form, a modal or a dropdown. Render a Blade view once with
    <code>DataTable::renderTemplate()</code> and reuse it for every row.
</p>

<x-docs-table
    :columns="['Key', 'Meaning']"
    :rows="[
        ['`html`', 'The markup of the template.'],
        ['`rendered`', '`true` when the markup is final and shown as it is. `false` when it is an Eta template filled in per row in the browser.'],
        ['`parameters`', 'Extra values for the template, available as `it.params`.'],
    ]"
/>

<x-docs-code lang="php" title="In the table class">
@verbatim
protected function shared(): array
{
    return [
        'checkout' => DataTable::renderTemplate('tables.attendance.check-out'),
    ];
}

protected function actions(Attendance $attendance): array
{
    return [[
        'template' => [
            'rendered' => false,
            'html' => $this->shared['checkout'],
            'parameters' => ['ulid' => $attendance->ulid],
        ],
    ]];
}
@endverbatim
</x-docs-code>

<x-docs-code lang="blade" title="resources/views/tables/attendance/check-out.blade.php">
@verbatim
<form action="{{ route('attendance.check-out') }}" method="POST" class="inline">
    @csrf
    @method('PUT')
    <input type="hidden" name="ulid" value="<%= it.params.ulid %>">
    <button type="submit">Check out</button>
</form>
@endverbatim
</x-docs-code>

<p>
    Blade renders the parts that are the same for every row, such as the route and the CSRF token. Eta fills in the parts that change.
    Inside the template, <code>it.row</code> is the row, <code>it.params</code> are your parameters and <code>it.id</code> is an id that is unique to the row.
</p>

<h2>Grouping buttons</h2>
<p>Give an action <code>containerAttributes</code> to put its button or template in a wrapper you can style.</p>

<x-docs-code lang="php">
@verbatim
[
    'containerAttributes' => DataTable::parseAttributes(['class' => 'd-inline-block ms-1']),
    'button' => ['label' => 'Edit', 'attributes' => DataTable::parseAttributes(['href' => '#'])],
]
@endverbatim
</x-docs-code>

<x-docs-callout type="tip">
    Check permissions once in <code>shared()</code> and return an empty <code>actions</code> list for rows that should have no buttons.
</x-docs-callout>
