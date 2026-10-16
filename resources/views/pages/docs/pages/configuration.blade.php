<p>
    The package works without any configuration. To change a default, create <code>config/dt-tables.php</code> in your app and return only the top-level keys you want to change.
    A key you return replaces the package's whole version of that key, so copy all of <code>settings</code> if you change one setting inside it.
</p>

<x-docs-code lang="php" title="The defaults">
@verbatim
&lt;?php

return [
    'settings' => [
        'buttons' => ['copy', 'colvis', 'excel'],
        'theme' => 'bootstrap',
        'loader' => [
            'enabled' => true,
            'message' => 'Loading data, please wait...',
            'image' => null,
        ],
        'behaviour' => [
            'page_length' => 10,
            'ordering' => true,
            'searching' => true,
            'paging' => true,
            'info' => true,
            'scroll_x' => false,
        ],
    ],

    'server_side' => [
        'middleware' => ['web'],
        'max_length' => 1000,
        'count_cache' => 60,
    ],

    'themes' => [
        'bootstrap' => [
            'buttons' => 'btn btn-primary btn-sm',
            'input' => 'form-control form-control-sm',
            'select' => 'form-control form-control-sm',
        ],
        'tailwind' => [
            'buttons' => '',
            'input' => '',
            'select' => '',
        ],
    ],

    'columns' => [
        'search_types' => ['none', 'input', 'select'],
    ],
];
@endverbatim
</x-docs-code>

<h2>settings</h2>
<p>
    These are the defaults for every table. A table's own file wins over them. They are also used for a table that has no file yet.
    See <a href="{{ route('dt-tables.docs.show', ['page' => 'table-files']) }}">Table Files</a> for what each setting does.
</p>

<h2>server_side</h2>
<x-docs-table
    :columns="['Key', 'Default', 'Meaning']"
    :rows="[
        ['`middleware`', '`[\'web\']`', 'The middleware of the server-side endpoints. Add `auth` to restrict them.'],
        ['`max_length`', '`1000`', 'The most rows one request may return.'],
        ['`count_cache`', '`60`', 'Seconds a row count is reused. `0` counts on every request.'],
    ]"
/>

<h2>themes</h2>
<p>
    A theme is a set of CSS classes for the buttons, the text inputs and the dropdowns of a table. Add your own, and it appears in the admin's theme list.
</p>

<x-docs-code lang="php" title="config/dt-tables.php">
@verbatim
'themes' => [
    'bootstrap' => [
        'buttons' => 'btn btn-primary btn-sm',
        'input' => 'form-control form-control-sm',
        'select' => 'form-control form-control-sm',
    ],
    'brand' => [
        'buttons' => 'rounded bg-amber-700 px-2 py-1 text-white',
        'input' => 'w-full rounded border-gray-300 text-sm',
        'select' => 'w-full rounded border-gray-300 text-sm',
    ],
],
@endverbatim
</x-docs-code>

<x-docs-callout type="note">
    Because a key replaces the package's version whole, list every theme you still want, including <code>bootstrap</code> and <code>tailwind</code>.
</x-docs-callout>

<h2>columns</h2>
<p><code>search_types</code> lists the filter types the admin offers for an inline column.</p>

<h2>Where tables are stored</h2>
<p>
    Table files always live in <code>resources/data-tables</code>. The folder is fixed and not configurable, so a configuration file can never point the package at the wrong place.
</p>
