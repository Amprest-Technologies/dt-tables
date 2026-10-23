@extends('docs::layout')

@section('page', 'The Admin UI')
@section('lead', 'Register tables and change their settings without editing files.')

@section('content')
    <div class="docs-prose mb-10">
<p>
    The admin is a small web interface for registering tables and changing their settings, so you never have to edit a table file by hand.
    Open it at <a href="{{ route('dt-tables.data-tables.index') }}"><code>/dt-tables/data-tables</code></a>.
</p>

<x-docs::callout type="warning">
    The admin and these docs only answer when <code>APP_ENV=local</code>. Anywhere else they return a 403, so register tables locally and commit the files.
</x-docs::callout>
    </div>

    <x-docs::section id="the-table-list" title="The table list">
<p>
    Every registered table appears as a card showing its mode, theme, and a short summary. Use the search box to find a table by key, and the tabs
    to show one mode at a time. Tables are shown six to a page, and the pager under the cards appears when more than six match. Searching or
    choosing a mode starts again from the first page. <strong>Edit</strong> opens the table, and <strong>Delete</strong> removes its file after a confirmation.
</p>
    </x-docs::section>

    <x-docs::section id="registering-a-table" title="Registering a table">
<p>Choose <strong>New table</strong>, enter a key and pick a mode.</p>
<ul>
    <li>The <strong>key</strong> is the name of the table. It becomes the file name and the <code>id</code> you use on the component, so keep it lowercase with dashes, such as <code>invoices-table</code>.</li>
    <li>The <strong>mode</strong> is required. It says how the table is fed, and it decides which settings the admin offers. See <a href="{{ $docs->url('introduction') }}">the three modes</a>.</li>
</ul>

<x-docs::callout>
    The mode only shapes the admin. What actually makes a table run server-side is the <code>source</code> attribute on the component, so the two must agree.
</x-docs::callout>
    </x-docs::section>

    <x-docs::section id="editing-a-table" title="Editing a table">
<p>
    A table opens on a page with a section list on the left. Each section saves on its own, so changing the loader never touches the theme.
</p>

<x-docs::table
    :columns="['Section', 'What it controls']"
    :rows="[
        ['General', 'The mode and the theme (Bootstrap or Tailwind).'],
        ['Columns', 'Inline tables only. The columns that have a filter, and whether it is a text input or a select.'],
        ['Buttons', 'Which buttons show above the table: Copy, Hide Columns and Excel.'],
        ['Behaviour', 'Page length, ordering, global search, pagination, the row count text and horizontal scrolling.'],
        ['Loader', 'Whether a loader shows while the table loads, its message and its image.'],
        ['Danger zone', 'Rename the table, which renames its file, or delete it.'],
    ]"
/>

<h3>Columns and filters</h3>
<p>
    For an inline table, add a column for every key you want to filter. Each column has a search type: <code>Input</code> for free text,
    <code>Select</code> for a dropdown of the values in the table, or <code>None</code> for no filter.
</p>

<x-docs::callout>
    Server-side and API tables have no Columns section. Their filters come from the table class, so they can never drift away from what the server can search.
    See <a href="{{ $docs->url('filters') }}">Filters</a>.
</x-docs::callout>

<h3>Changing the mode</h3>
<p>
    You can switch a table to another mode at any time. Columns saved for an inline table are kept in its file but ignored by server-side and API tables,
    and Copy and Excel are removed because they could only export the loaded page. Update the table class and the component to match.
</p>

<h3>Renaming a table</h3>
<p>Renaming moves the table file. Remember to change the <code>id</code> wherever the table is rendered.</p>
    </x-docs::section>

    <x-docs::section id="next" title="Next">
<p>See what the admin writes in <a href="{{ $docs->url('table-files') }}">Table Files</a>.</p>
    </x-docs::section>
@endsection
