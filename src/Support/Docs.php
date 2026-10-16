<?php

namespace Amprest\DtTables\Support;

use Illuminate\Support\Collection;

class Docs
{
    /**
     * Describe the sections of the documentation and the pages in each, in reading order.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function sections(): array
    {
        return [
            [
                'title' => 'Getting Started',
                'pages' => [
                    ['slug' => 'introduction', 'title' => 'Introduction', 'description' => 'What DtTables is, the three ways a table can be fed, and how the pieces fit together.'],
                    ['slug' => 'installation', 'title' => 'Installation', 'description' => 'Install the package and learn what it sets up for you.'],
                    ['slug' => 'quick-start', 'title' => 'Quick Start', 'description' => 'Build and render your first table in five steps.'],
                ],
            ],
            [
                'title' => 'The Admin',
                'pages' => [
                    ['slug' => 'admin-ui', 'title' => 'The Admin UI', 'description' => 'Register tables and change their settings without editing files.'],
                    ['slug' => 'table-files', 'title' => 'Table Files', 'description' => 'How a table is stored, and what each field of its JSON file does.'],
                ],
            ],
            [
                'title' => 'Rendering Tables',
                'pages' => [
                    ['slug' => 'component', 'title' => 'The Component', 'description' => 'Render a table in a Blade view with the x-data-table component.'],
                    ['slug' => 'header-attributes', 'title' => 'Header Attributes', 'description' => 'The dtt attributes that describe each column in the table header.'],
                ],
            ],
            [
                'title' => 'Inline Tables',
                'pages' => [
                    ['slug' => 'inline-tables', 'title' => 'Table Classes', 'description' => 'Load every row up front and let the browser search and sort them.'],
                    ['slug' => 'row-cells', 'title' => 'Row Cells', 'description' => 'The shapes a cell can take: plain values, styled cells and rendered HTML.'],
                    ['slug' => 'actions', 'title' => 'Actions', 'description' => 'Add buttons and templates to the actions column of each row.'],
                    ['slug' => 'export-events', 'title' => 'Export Events', 'description' => 'React when someone copies or exports a table.'],
                ],
            ],
            [
                'title' => 'Server-side Tables',
                'pages' => [
                    ['slug' => 'server-side-overview', 'title' => 'Overview', 'description' => 'When to page, search and sort on the server, and how a request flows.'],
                    ['slug' => 'server-side-tables', 'title' => 'Database Tables', 'description' => 'Serve a table from your database, one page per request.'],
                    ['slug' => 'column-map', 'title' => 'The Column Map', 'description' => 'Tell the package what each column is searched and sorted by.'],
                    ['slug' => 'filters', 'title' => 'Filters', 'description' => 'Select and input filters, their options, and filtering through the URL.'],
                    ['slug' => 'api-tables', 'title' => 'API Tables', 'description' => 'Serve a table from a remote HTTP API that pages, searches and sorts for itself.'],
                    ['slug' => 'security', 'title' => 'Security and Errors', 'description' => 'The sealed token, authorization, status codes and what users see when a request fails.'],
                    ['slug' => 'performance', 'title' => 'Performance', 'description' => 'Keep large tables fast: counts, global search, joins and options.'],
                ],
            ],
            [
                'title' => 'Reference',
                'pages' => [
                    ['slug' => 'configuration', 'title' => 'Configuration', 'description' => 'Every key of config/dt-tables.php.'],
                    ['slug' => 'artisan-command', 'title' => 'Artisan Command', 'description' => 'Scaffold table classes with make:data-table.'],
                    ['slug' => 'helpers', 'title' => 'Helpers', 'description' => 'The DataTable facade and the global helper functions.'],
                    ['slug' => 'troubleshooting', 'title' => 'Troubleshooting', 'description' => 'Common problems and how to fix them.'],
                ],
            ],
        ];
    }

    /**
     * List every page in reading order, each with the title of its section.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function pages(): Collection
    {
        return collect($this->sections())->flatMap(
            fn (array $section) => collect($section['pages'])->map(fn (array $page) => [...$page, 'section' => $section['title']])
        )->values();
    }

    /**
     * Find a page by its slug.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function find(string $slug): ?array
    {
        return $this->pages()->firstWhere('slug', $slug);
    }

    /**
     * Get the page before and the page after a page, either of which is null at the ends.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function neighbours(string $slug): array
    {
        //  Find where the page sits in the reading order
        $index = $this->pages()->search(fn (array $page) => $page['slug'] === $slug);

        //  Return the pages on either side
        return [$this->pages()->get($index - 1), $this->pages()->get($index + 1)];
    }
}
