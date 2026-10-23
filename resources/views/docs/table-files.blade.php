@extends('docs::layout')

@section('page', 'Table Files')
@section('lead', 'How a table is stored, and what each field of its JSON file does.')

@section('content')
    <div class="docs-prose mb-10">
<p>
    Each table is stored in its own JSON file at <code>resources/data-tables/{key}.json</code>. The folder sits in your repository, so a table's
    settings are versioned with the rest of your code. The admin edits these files, and you can edit them by hand too.
</p>
    </div>

    <x-docs::section id="an-inline-table" title="An inline table">
<x-docs::code lang="json" title="resources/data-tables/invoices-table.json">
@verbatim
{
    "key": "invoices-table",
    "mode": "inline",
    "settings": {
        "buttons": ["copy", "colvis", "excel"],
        "theme": "bootstrap",
        "loader": {
            "enabled": true,
            "message": "Loading data, please wait...",
            "image": "img/loader.svg"
        },
        "behaviour": {
            "page_length": 10,
            "ordering": true,
            "searching": true,
            "paging": true,
            "info": true,
            "scroll_x": false
        }
    },
    "columns": [
        { "id": "01jvsbkqpt0ps8s3w9m815genz", "key": "tenant", "search_type": "input" },
        { "id": "01jvsbm8msvw7ghsw62v6jq6sj", "key": "status", "search_type": "select" }
    ]
}
@endverbatim
</x-docs::code>
    </x-docs::section>

    <x-docs::section id="a-server-side-or-api-table" title="A server-side or API table">
<p>These tables take their filters from the table class, so <code>columns</code> stays empty and only the colvis button applies.</p>

<x-docs::code lang="json" title="resources/data-tables/orders-table.json">
@verbatim
{
    "key": "orders-table",
    "mode": "server_side",
    "settings": {
        "buttons": ["colvis"],
        "theme": "bootstrap"
    },
    "columns": []
}
@endverbatim
</x-docs::code>
    </x-docs::section>

    <x-docs::section id="fields" title="Fields">
<x-docs::table
    :columns="['Field', 'Description']"
    :rows="[
        ['`key`', 'The name of the table. It must match the file name and the `id` on the component.'],
        ['`mode`', 'One of `inline`, `server_side` or `api`. A file without a mode is read as `inline`, so older files keep working.'],
        ['`settings.buttons`', 'The buttons to show: `copy`, `colvis` and `excel`.'],
        ['`settings.theme`', 'The framework the filters and buttons are styled for. Any key under `themes` in the configuration, `bootstrap` or `tailwind` by default.'],
        ['`settings.loader`', 'Whether a loader shows while the table loads (`enabled`), its `message`, and the path of an `image` under your public folder.'],
        ['`settings.behaviour`', 'Paging, ordering, searching, the info text and horizontal scrolling. See below.'],
        ['`columns[].key`', 'Inline tables only. The row key the filter applies to.'],
        ['`columns[].search_type`', 'Inline tables only. `input`, `select` or `none`.'],
        ['`columns[].id`', 'A unique id the admin uses to find a column. Generate one if you add a column by hand.'],
    ]"
/>

<h3>Behaviour settings</h3>
<x-docs::table
    :columns="['Setting', 'Default', 'Meaning']"
    :rows="[
        ['`page_length`', '10', 'Rows per page: 10, 25, 50, 100, or -1 for all rows. Server-side tables cannot show all rows.'],
        ['`ordering`', 'true', 'Let people sort by clicking a column header.'],
        ['`searching`', 'true', 'Show the global search box.'],
        ['`paging`', 'true', 'Split the rows into pages. Server-side tables always page.'],
        ['`info`', 'true', 'Show the row count text below the table.'],
        ['`scroll_x`', 'false', 'Scroll the table sideways instead of squeezing wide tables.'],
    ]"
/>
    </x-docs::section>

    <x-docs::section id="how-settings-are-resolved" title="How settings are resolved">
<p>
    A setting in a table's own file wins. Anything the file leaves out falls back to <code>config('dt-tables.settings')</code>,
    and the config is also used for a table that has no file yet.
</p>

<x-docs::callout type="tip">
    Files saved before modes existed carry no <code>mode</code>. They keep working as inline tables, and saving them in the admin adds the field.
</x-docs::callout>
    </x-docs::section>
@endsection
