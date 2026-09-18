<?php

namespace Amprest\DtTables\Support;

use Throwable;

class LegacyDataMigrator
{
    /**
     * Define the constructor for the LegacyDataMigrator class.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function __construct(
        protected string $legacyPath,
        protected string $directory,
    ) {}

    /**
     * Migrate the legacy single-file dt-tables.json into one file per table.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function handle(): void
    {
        //  Nothing to migrate if the legacy file is gone
        if (! file_exists($this->legacyPath)) {
            return;
        }

        //  Migrate
        try {
            $this->migrate();

            //  Never let a migration failure take down the app's boot cycle
        } catch (Throwable $exception) {
            logger()->error('dt-tables: legacy data migration failed.', ['exception' => $exception]);
        }
    }

    /**
     * Perform the migration.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function migrate(): void
    {
        //  Read the legacy file
        $raw = file_get_contents($this->legacyPath);

        //  Decode the JSON
        $tables = json_decode($raw, true);

        //  If the file has content but failed to decode, bail without touching anything
        if (is_null($tables) && ! in_array(trim($raw), ['', '[]'], true)) {
            //  Log a warning
            logger()->warning('dt-tables: legacy dt-tables.json could not be parsed; skipping migration.');

            //  Skip migration
            return;
        }

        //  If the file is empty or null, skip migration
        $tables = $tables ?: [];

        //  Create the migration directory
        ensure_directory_exists($this->directory);

        //  Migrate each table
        foreach ($tables as $table) {
            $this->migrateTable($table);
        }

        //  Never delete the legacy file, only move it aside
        rename($this->legacyPath, "{$this->legacyPath}.bak");
    }

    /**
     * Migrate a single legacy table entry to its own file.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function migrateTable(array $table): void
    {
        //  Skip malformed entries defensively
        if (empty($table['key'])) {
            return;
        }

        //  The table-level id is no longer part of the schema
        unset($table['id']);

        //  Create a file for the table
        $path = "{$this->directory}/{$table['key']}.json";

        //  Never overwrite a file that already exists
        if (file_exists($path)) {
            return;
        }

        //  Write the table to the file
        file_put_contents($path, json_encode($table, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
