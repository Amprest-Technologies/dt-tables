<?php

namespace Amprest\DtTables\Support;

use Amprest\DtTables\Models\DataTable;
use Illuminate\Support\Str;

class TablePresenter
{
    /**
     * Describe each mode, for the badges, the filter tabs and the pickers.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function modes(): array
    {
        return [
            'inline' => [
                'label' => 'Inline',
                'summary' => 'Rows are built in PHP and sent with the page. Best for small datasets, with filters configured per column here.',
                'badge' => 'bg-gray-100 text-gray-700 ring-gray-400/40',
                'dot' => 'bg-gray-400',
            ],
            'server_side' => [
                'label' => 'Server-side',
                'summary' => 'Rows are searched, sorted and paged in the database, one page per request. Filters come from your table class.',
                'badge' => 'bg-secondary-soft text-secondary-light ring-secondary-light/30',
                'dot' => 'bg-secondary-light',
            ],
            'api' => [
                'label' => 'API',
                'summary' => 'Rows are read page by page from an HTTP API. Filters come from your table class.',
                'badge' => 'bg-primary-soft text-primary-darker ring-primary-dark/30',
                'dot' => 'bg-primary',
            ],
        ];
    }

    /**
     * Summarise a table for its card in the list, reading a missing or unknown mode as inline.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function card(object $table): array
    {
        //  Read the mode, where one that is not known is inline
        $mode = in_array($table->mode ?? null, DataTable::MODES, true) ? $table->mode : 'inline';

        //  Read the page length, where -1 shows every row
        $pageLength = data_get($table, 'settings.behaviour.page_length', 10);

        //  Return the summary
        return [
            'key' => $table->key,
            'name' => prettify($table->key),
            'mode' => $mode,
            'theme' => data_get($table, 'settings.theme', 'bootstrap'),
            'filters' => $mode === 'inline' ? count((array) data_get($table, 'columns', [])) : 'From class',
            'filtersLabel' => $mode === 'inline' ? 'Columns' : 'Filters',
            'pageLength' => $pageLength == -1 ? 'All' : $pageLength,
            'buttons' => count((array) data_get($table, 'settings.buttons', [])) ?: 'None',
        ];
    }

    /**
     * Build everything the edit page shows: its sections, the one to open first and the guide of the table's mode.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function edit(DataTable $table): array
    {
        //  Build the sections
        $sections = $this->sections($table);

        //  Return the page
        return [
            'sections' => $sections,
            'initial' => $this->sectionWithErrors($sections) ?? 'general',
            'guide' => $this->guide($table),
        ];
    }

    /**
     * Point to the documentation page that explains the table's mode.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function guide(DataTable $table): array
    {
        //  Pick the page that explains how the table is fed
        $slug = match ($table->mode) {
            'server_side' => 'server-side-tables',
            'api' => 'api-tables',
            default => 'inline-tables',
        };

        //  Return the link
        return [
            'label' => $this->modes()[$table->mode]['label'].' guide',
            'url' => route('dt-tables.docs.show', ['page' => $slug]),
        ];
    }

    /**
     * Build the sections of the edit page in the order they are listed.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function sections(DataTable $table): array
    {
        return [
            [
                'id' => 'general',
                'title' => 'General',
                'description' => $table->isServerSide()
                    ? 'How the table is fed and styled. Its filters come from the table class.'
                    : 'How the table is fed and how it is styled.',
                'partial' => 'forms',
                'forms' => [$this->modeForm($table), $this->themeForm($table)],
            ],
            ...($table->isServerSide() ? [] : [$this->columnsSection($table)]),
            [
                'id' => 'buttons',
                'title' => 'Buttons',
                'description' => 'The buttons shown above the table.',
                'partial' => 'forms',
                'forms' => [$this->buttonsForm($table)],
            ],
            [
                'id' => 'behaviour',
                'title' => 'Behaviour',
                'description' => 'Paging, ordering, searching and scrolling.',
                'partial' => 'forms',
                'forms' => [$this->behaviourForm($table)],
            ],
            [
                'id' => 'loader',
                'title' => 'Loader',
                'description' => 'What shows while the table loads.',
                'partial' => 'forms',
                'forms' => [$this->loaderForm($table)],
            ],
            [
                'id' => 'danger',
                'title' => 'Danger zone',
                'description' => 'Rename or delete the table.',
                'partial' => 'danger',
                'forms' => [$this->nameForm($table)],
                'deleteAction' => route('dt-tables.data-tables.destroy', ['data_table' => $table->key]),
                'confirm' => "Delete table {$table->key}? This cannot be undone.",
            ],
        ];
    }

    /**
     * Build the section that manages the columns of an inline table.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function columnsSection(DataTable $table): array
    {
        return [
            'id' => 'columns',
            'title' => 'Columns',
            'description' => 'Keys and per-column search filters.',
            'partial' => 'columns',
            'storeAction' => route('dt-tables.data-tables.columns.store', ['data_table' => $table->key]),
            'newKey' => session('errors')?->hasBag('new-column') ? old('key') : '',
            'columns' => collect($table->columns)->map(fn ($column) => $this->column($table, $column))->all(),
        ];
    }

    /**
     * Build the row of one column, keeping what was typed only on the row that failed validation.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function column(DataTable $table, object $column): array
    {
        //  Find out whether this column's own form failed validation
        $failed = session('errors')?->hasBag($column->id) ?? false;

        //  Return the row
        return [
            'id' => $column->id,
            'key' => $failed ? old('key') : prettify($column->key),
            'searchType' => $failed ? old('search_type') : $column->search_type,
            'searchTypes' => collect(config('dt-tables.columns.search_types'))
                ->mapWithKeys(fn ($type) => [$type => prettify($type)])
                ->all(),
            'updateForm' => "update-{$column->id}-form",
            'deleteForm' => "delete-{$column->id}-form",
            'updateAction' => route('dt-tables.data-tables.columns.update', [
                'data_table' => $table->key,
                'data_table_column' => $column->id,
            ]),
            'deleteAction' => route('dt-tables.data-tables.columns.destroy', [
                'data_table' => $table->key,
                'data_table_column' => $column->id,
            ]),
            'confirm' => "Delete column {$column->key}? This cannot be undone.",
        ];
    }

    /**
     * Build the form that changes the mode.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function modeForm(DataTable $table): array
    {
        return $this->form($table, 'mode', 'Mode', 'Switching to server-side or API ignores the columns saved for this table, which stay in its file, and removes Copy and Excel. The table class and the source attribute must match the mode.', [
            $this->select('mode', 'Mode', old('mode', $table->mode), collect($this->modes())->map(fn ($mode) => $mode['label'])->all()),
        ]);
    }

    /**
     * Build the form that changes the theme.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function themeForm(DataTable $table): array
    {
        return $this->form($table, 'theme', 'Theme', 'The CSS framework the table is styled for.', [
            $this->select(
                'theme',
                'Theme',
                old('theme', data_get($table, 'settings.theme', config('dt-tables.settings.theme'))),
                collect(array_keys(config('dt-tables.themes')))->mapWithKeys(fn ($theme) => [$theme => Str::headline($theme)])->all(),
            ),
        ]);
    }

    /**
     * Build the form that switches the buttons on and off, where server-side tables only have column visibility.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function buttonsForm(DataTable $table): array
    {
        //  Get the buttons that are on
        $active = (array) data_get($table, 'settings.buttons', []);

        //  Get the buttons that can be offered
        $available = $table->isServerSide() ? ['colvis'] : config('dt-tables.settings.buttons');

        //  Build a field per button
        $fields = collect($available)->map(fn ($button) => $this->select(
            "buttons[{$button}]",
            prettify($button),
            (string) (int) old("buttons.{$button}", in_array($button, $active, true)),
            $this->toggle('Active', 'Inactive'),
            "buttons.{$button}",
        ))->all();

        //  Return the form
        return $this->form($table, 'buttons', 'Buttons', null, $fields);
    }

    /**
     * Build the form that sets how the table pages, orders, searches and scrolls.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function behaviourForm(DataTable $table): array
    {
        //  Get the saved behaviour
        $behaviour = fluent((array) data_get($table, 'settings.behaviour', []));

        //  Server-side tables cannot offer every row at once
        $lengths = $table->isServerSide() ? [10, 25, 50, 100] : [10, 25, 50, 100, -1];

        //  Start with the page length
        $fields = [
            $this->select(
                'page_length',
                'Page Length',
                old('page_length', $behaviour->get('page_length', 10)),
                collect($lengths)->mapWithKeys(fn ($length) => [$length => $length === -1 ? 'All' : $length])->all(),
            ),
            $this->flag($behaviour, 'ordering', 'Ordering', true),
            $this->flag($behaviour, 'searching', 'Global Search', true),
        ];

        //  Server-side tables always page
        if (! $table->isServerSide()) {
            $fields[] = $this->flag($behaviour, 'paging', 'Pagination', true);
        }

        //  Add the rest
        $fields[] = $this->flag($behaviour, 'info', 'Row Count Info', true);
        $fields[] = $this->flag($behaviour, 'scroll_x', 'Horiz. Scroll', false);

        //  Return the form
        return $this->form($table, 'behaviour', 'Behaviour', null, $fields);
    }

    /**
     * Build the form that sets the loader shown while the table loads.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function loaderForm(DataTable $table): array
    {
        //  Get the saved loader
        $loader = fluent((array) data_get($table, 'settings.loader', []));

        //  Return the form
        return $this->form($table, 'loader', 'Loader', null, [
            $this->select(
                'loader[enabled]',
                'Enabled',
                (string) (int) old('loader.enabled', (bool) $loader->get('enabled', false)),
                $this->toggle('Yes', 'No'),
                'loader.enabled',
            ),
            $this->text('loader[message]', 'Message', old('loader.message', $loader->get('message')), 'Loading message...', 'loader.message'),
            $this->text('loader[image]', 'Image Path', old('loader.image', $loader->get('image')), 'img/loaders/app.svg', 'loader.image'),
        ]);
    }

    /**
     * Build the form that renames the table, which renames its file.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function nameForm(DataTable $table): array
    {
        return $this->form($table, 'name', 'Rename', 'Renaming moves the table\'s file, so update the id wherever the table is rendered.', [
            $this->text('key', 'Table key', session('errors')?->has('key') ? old('key') : $table->key, 'e.g. invoices-table'),
        ]);
    }

    /**
     * Describe a form that saves one group of settings.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function form(DataTable $table, string $type, string $title, ?string $notice, array $fields): array
    {
        return [
            'title' => $title,
            'notice' => $notice,
            'fields' => $fields,
            'action' => route('dt-tables.data-tables.update', ['data_table' => $table->key, 'type' => $type]),
        ];
    }

    /**
     * Describe a select field, whose errors are read under the key given or else its name.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function select(string $name, string $label, mixed $value, array $options, ?string $error = null): array
    {
        return [
            'kind' => 'select',
            'name' => $name,
            'label' => $label,
            'value' => $value,
            'options' => $options,
            'error' => $error ?? $name,
        ];
    }

    /**
     * Describe a text field, whose errors are read under the key given or else its name.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function text(string $name, string $label, mixed $value, string $placeholder, ?string $error = null): array
    {
        return [
            'kind' => 'text',
            'name' => $name,
            'label' => $label,
            'value' => $value,
            'placeholder' => $placeholder,
            'error' => $error ?? $name,
        ];
    }

    /**
     * Describe an enabled or disabled select for one of the behaviour switches.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function flag(object $behaviour, string $name, string $label, bool $default): array
    {
        return $this->select(
            $name,
            $label,
            (string) (int) old($name, (bool) $behaviour->get($name, $default)),
            $this->toggle('Enabled', 'Disabled'),
        );
    }

    /**
     * Describe the two options of an on and off select.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function toggle(string $on, string $off): array
    {
        return ['1' => $on, '0' => $off];
    }

    /**
     * Find the section holding the first error of the last submission, so the page opens there.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function sectionWithErrors(array $sections): ?string
    {
        //  Read the errors of the last submission
        $errors = session('errors');

        //  Without errors there is nothing to open
        if (! $errors) {
            return null;
        }

        //  Look through the sections
        foreach ($sections as $section) {
            //  The column forms keep their errors in bags named after their column
            if ($section['partial'] === 'columns' && collect($errors->getBags())->except('default')->isNotEmpty()) {
                return $section['id'];
            }

            //  The other sections keep them under their field names
            foreach ($section['forms'] ?? [] as $form) {
                foreach ($form['fields'] as $field) {
                    if ($errors->has($field['error'])) {
                        return $section['id'];
                    }
                }
            }
        }

        //  No section holds an error
        return null;
    }
}
