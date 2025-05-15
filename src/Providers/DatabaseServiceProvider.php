<?php

namespace Amprest\LaravelDT\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Define the package name.
     *
     * @var string
     */
    protected string $packageName = '';

    /**
     * Get the schema tables for the package.
     *
     * @var array
     */
    protected array $schemaTables = [
        'data_tables',
        'data_table_columns'
    ];

    /**
     * Define the register method for the service provider.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function register(): void
    {
        //  Define the package name
        $this->packageName = package_name();

        //  Set the database
        $this->setUpDatabase();
    }

    /**
     * Create the database connection for the package.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    protected function setUpDatabase(): void
    {
        //  Create the database if it doesn't exist
        $path = $this->createDatabase();

        //  Set the connection config early
        config()->set("database.connections.{$this->packageName}", [
            'driver' => 'sqlite',
            'database' => $path,
            'foreign_key_constraints' => true,
        ]);

        //  Create the database tables
        $this->createTables();
    }

    /**
     * Create the database file if it doesn't exist.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    protected function createDatabase(): string
    {
        //  Check if the database file exists
        if (! file_exists($path = base_path('laravel-dt.sqlite'))) {
            touch($path);
        }

        //  Return the path to the database file
        return $path;
    }

    /**
     * Create the tables for the package.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    protected function createTables(): void
    {
        //  Loop through the tables and create them if they don't exist
        collect($this->schemaTables)->each(function ($table) {
            //  Check if the table exists
            if (! Schema::connection($this->packageName)->hasTable($table)) {
                //  Get the migration
                $migration = collect(File::files(package_path('database/migrations')))
                    ->filter(fn ($migration) => str_contains($migration->getFilename(), "create_{$table}_table"))
                    ->first();

                //  Check if the migration exists
                if ($migration) {
                    (require $migration->getPathname())->up();
                }
            }
        });
    }
}