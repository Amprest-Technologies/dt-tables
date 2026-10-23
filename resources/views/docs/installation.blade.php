@extends('docs::layout')

@section('page', 'Installation')
@section('lead', 'Install the package and learn what it sets up for you.')

@section('content')
    <x-docs::section id="requirements" title="Requirements">
<ul>
    <li>PHP 8.1 or higher and a Laravel application.</li>
    <li>Bootstrap or Tailwind CSS on the pages that show tables. The package styles the table itself, and the theme you choose decides the classes of the buttons and filters.</li>
</ul>
    </x-docs::section>

    <x-docs::section id="install-the-package" title="Install the package">
<p>DtTables is on Packagist, so require it like any other package. Every Nyumbani app already has it, because <code>nyumbani/essentials</code> requires it.</p>

<x-docs::code lang="bash" title="Terminal">
@verbatim
composer require nyumbani/dt-tables
@endverbatim
</x-docs::code>

<p>Laravel discovers the package automatically, so there is no service provider to register and nothing to publish.</p>
    </x-docs::section>

    <x-docs::section id="what-gets-set-up-for-you" title="What gets set up for you">
<ul>
    <li><strong>A folder for table files.</strong> <code>resources/data-tables</code> is created the first time the app boots.</li>
    <li><strong>Assets.</strong> The scripts and styles are served by the package itself and added to the page before <code>&lt;/head&gt;</code> whenever a table is rendered. You do not add them to your layout or your Vite build.</li>
    <li><strong>Routes.</strong> The admin under <code>/dt-tables</code> (local only), these docs under <code>/nyumbani-dt-tables/docs</code> when <code>nyumbani/docs</code> is installed (local only), the asset route, and the endpoints that server-side tables and export events use under <code>/api/dt-tables</code>.</li>
    <li><strong>Blade components.</strong> <code>&lt;x-data-table&gt;</code> and a few helpers.</li>
</ul>
    </x-docs::section>

    <x-docs::section id="check-that-it-works" title="Check that it works">
<p>Open the admin in your browser. It should list your tables, or invite you to register the first one.</p>

<x-docs::code lang="bash" title="In your browser">
@verbatim
https://your-app.test/dt-tables/data-tables
@endverbatim
</x-docs::code>

<x-docs::callout type="warning">
    The admin returns a 403 unless <code>APP_ENV=local</code>. Register your tables locally and commit the files in <code>resources/data-tables</code> so they ship with your code.
</x-docs::callout>
    </x-docs::section>

    <x-docs::section id="change-the-defaults" title="Change the defaults">
<p>
    The defaults live in the package. To change one, create <code>config/dt-tables.php</code> in your app and return only the top-level keys you want to change.
    Each key you return replaces the package's version of that key whole, so copy the entire <code>settings</code> array if you change one setting inside it.
    See <a href="{{ $docs->url('configuration') }}">Configuration</a> for every key.
</p>

<x-docs::code lang="php" title="config/dt-tables.php">
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
</x-docs::code>
    </x-docs::section>

    <x-docs::section id="working-with-an-ai-assistant" title="Working with an AI assistant">
<p>
    The package ships Laravel Boost skills that teach an assistant how to write tables. Run <code>php artisan boost:update</code>
    in your app to copy them into your project's skills folder.
</p>
    </x-docs::section>
@endsection
