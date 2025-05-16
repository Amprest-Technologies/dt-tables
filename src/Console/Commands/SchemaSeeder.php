<?php

namespace Amprest\DtTables\Console\Commands;

use Amprest\DtTables\Models\DataTable;
use Illuminate\Console\Command;
use Illuminate\Console\Prohibitable;
use Illuminate\Support\Facades\DB;

class SchemaSeeder extends Command
{
    use Prohibitable;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schema-seeder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //  Truncate all tables in the database
        $this->truncateTables();

        //  Launch a transaction
        DB::transaction(function () {
            //  Get the json file from the config
            $tables = file_get_contents(package_path('database/data/data-tables.json'));
    
            //  Decode the json file
            $tables = collect(json_decode($tables, true));
    
            //  Get the tables from the json file
            foreach($tables as $table) {
                //  Set the table details
                $currentTable = DataTable::create($table['details']);
    
                //  Create the columns
                $currentTable->columns()->createMany($table['columns']);
            }
        });
    }

    /**
     * Truncate all tables in the database.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    protected function truncateTables(): void
    {
        //  Get the package name
        $connection = DB::connection(package_name());

        //  Disable foreign key constraints in SQLite
        $connection->statement('PRAGMA foreign_keys = OFF;');

        //  Get the tables
        $tables = $connection->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%';");

        //  Get all table names
        collect($tables)->each(fn ($table) => $connection->table($table->name)->truncate());

        //  Enable foreign key constraints in SQLite
        $connection->statement('PRAGMA foreign_keys = ON;');
    }
}
