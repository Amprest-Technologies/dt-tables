<?php

namespace Amprest\DtTables\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class MakeDataTableCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected $name = 'make:data-table';

    /**
     * The console command description.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected $description = 'Create a new DataTable class';

    /**
     * The type of class being generated.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected $type = 'DataTable';

    /**
     * Get the stub file for the generator.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function getStub(): string
    {
        return package_path('stubs/data-table.stub');
    }

    /**
     * Get the default namespace for the class.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\\DataTables';
    }
}
