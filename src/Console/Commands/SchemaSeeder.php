<?php

namespace Amprest\LaravelDT\Console\Commands;

use Amprest\LaravelDT\Models\DataTable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SchemaSeeder extends Command
{
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
        DB::transaction(function () {
            //  Get the json file from the config
            $tables = file_get_contents(package_path('database/data/datatables.json'));
    
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
}
