<?php

namespace Amprest\DtTables\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

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
        return package_path(match (true) {
            $this->option('api') => 'stubs/data-table.api.stub',
            $this->option('server-side') => 'stubs/data-table.server-side.stub',
            default => 'stubs/data-table.stub',
        });
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

    /**
     * Get the console command options.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function getOptions(): array
    {
        return [
            ...parent::getOptions(),
            ['server-side', 's', InputOption::VALUE_NONE, 'Create a table that pages, searches and sorts in the database'],
            ['api', 'a', InputOption::VALUE_NONE, 'Create a table that pages, searches and sorts through an API'],
        ];
    }
}
