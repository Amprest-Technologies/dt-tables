<?php

namespace Nyumbani\DtTables\Docs;

use Nyumbani\Docs\Docs;
use Nyumbani\Docs\DocsRegistry;

class DtTablesDocs
{
    /**
     * The slug the docs are served under, which is also how the registry finds them.
     */
    public const SLUG = 'nyumbani-dt-tables';

    /**
     * Describe the sections of the documentation and the pages in each, in reading order.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public static function describe(): Docs
    {
        return new Docs(self::SLUG, 'DtTables', [
            ['title' => 'Getting started', 'pages' => [
                'introduction' => 'Introduction',
                'installation' => 'Installation',
                'quick-start' => 'Quick start',
            ]],
            ['title' => 'The admin', 'pages' => [
                'admin-ui' => 'The admin UI',
                'table-files' => 'Table files',
            ]],
            ['title' => 'Rendering tables', 'pages' => [
                'component' => 'The component',
                'header-attributes' => 'Header attributes',
            ]],
            ['title' => 'Inline tables', 'pages' => [
                'inline-tables' => 'Table classes',
                'row-cells' => 'Row cells',
                'actions' => 'Actions',
                'export-events' => 'Export events',
            ]],
            ['title' => 'Server-side tables', 'pages' => [
                'server-side-overview' => 'Overview',
                'server-side-tables' => 'Database tables',
                'column-map' => 'The column map',
                'filters' => 'Filters',
                'api-tables' => 'API tables',
                'security' => 'Security and errors',
                'performance' => 'Performance',
            ]],
            ['title' => 'Reference', 'pages' => [
                'configuration' => 'Configuration',
                'artisan-command' => 'Artisan command',
                'helpers' => 'Helpers',
                'troubleshooting' => 'Troubleshooting',
            ]],
        ], 'dt-tables::docs');
    }

    /**
     * Find the registered docs.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public static function registered(): ?Docs
    {
        //  The docs package is a dev dependency, so it may not be installed
        return class_exists(DocsRegistry::class) ? app(DocsRegistry::class)->find(self::SLUG) : null;
    }

    /**
     * Get the url of a docs page, or null when the docs are not installed or registered.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public static function url(?string $page = null): ?string
    {
        return self::registered()?->url($page);
    }
}
