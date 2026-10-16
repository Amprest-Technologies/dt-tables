<h2>Requirements</h2>
<ul>
    <li>PHP 8.1 or higher and a Laravel application.</li>
    <li>Bootstrap or Tailwind CSS on the pages that show tables. The package styles the table itself, and the theme you choose decides the classes of the buttons and filters.</li>
</ul>

<h2>Install the package</h2>
<p>DtTables is a private Amprest package, so tell Composer where to find it first, then require it.</p>

<x-docs-code lang="bash">
@verbatim
composer config repositories.dt-tables vcs git@github.com:Amprest-Technologies/dt-tables.git
composer require amprest/dt-tables
@endverbatim
</x-docs-code>

<p>Laravel discovers the package automatically, so there is no service provider to register and nothing to publish.</p>

<h2>What gets set up for you</h2>
<ul>
    <li><strong>A folder for table files.</strong> <code>resources/data-tables</code> is created the first time the app boots.</li>
    <li><strong>Assets.</strong> The scripts and styles are served by the package itself and added to the page before <code>&lt;/head&gt;</code> whenever a table is rendered. You do not add them to your layout or your Vite build.</li>
    <li><strong>Routes.</strong> The admin and these docs under <code>/dt-tables</code> (local only), the asset route, and the endpoints that server-side tables and export events use under <code>/api/dt-tables</code>.</li>
    <li><strong>Blade components.</strong> <code>&lt;x-data-table&gt;</code> and a few helpers.</li>
</ul>

<h2>Check that it works</h2>
<p>Open the admin in your browser. It should list your tables, or invite you to register the first one.</p>

<x-docs-code lang="bash" title="In your browser">
@verbatim
https://your-app.test/dt-tables/data-tables
@endverbatim
</x-docs-code>

<x-docs-callout type="warning">
    The admin returns a 403 unless <code>APP_ENV=local</code>. Register your tables locally and commit the files in <code>resources/data-tables</code> so they ship with your code.
</x-docs-callout>

<h2>Change the defaults</h2>
<p>
    The defaults live in the package. To change one, create <code>config/dt-tables.php</code> in your app and return only the top-level keys you want to change.
    Each key you return replaces the package's version of that key whole, so copy the entire <code>settings</code> array if you change one setting inside it.
    See <a href="{{ route('dt-tables.docs.show', ['page' => 'configuration']) }}">Configuration</a> for every key.
</p>

<x-docs-code lang="php" title="config/dt-tables.php">
@verbatim
&lt;?php

return [
    'settings' => [
        'buttons' => ['colvis'],
        'theme' => 'tailwind',
        'loader' => ['enabled' => true, 'message' => 'Loading...', 'image' => null],
        'behaviour' => [
            'page_length' => 25,
            'ordering' => true,
            'searching' => true,
            'paging' => true,
            'info' => true,
            'scroll_x' => false,
        ],
    ],
];
@endverbatim
</x-docs-code>

<h2>Working with an AI assistant</h2>
<p>
    The package ships Laravel Boost skills that teach an assistant how to write tables. Run <code>php artisan boost:update</code>
    in your app to copy them into your project's skills folder.
</p>
