<?php

namespace Amprest\DtTables\Models;

class DataTable extends Model
{
    /**
     * The id for the data table.
     */
    public string $id;

    /**
     * The key for the data table.
     */
    public string $key;

    /**
     * The settings for the data table.
     */
    public mixed $settings = [
        'buttons' => ['copy', 'colvis', 'excel'],
        'theme' => 'bootstrap',
        'loader' => [
            'enabled' => true,
            'message' => 'Loading data, please wait...',
            'image' => 'img/loader.svg',
        ],
        'behaviour' => [
            'page_length' => 10,
            'ordering' => true,
            'searching' => true,
            'paging' => true,
            'info' => true,
            'scroll_x' => false,
        ],
    ];

    /**
     * The columns for the data table.
     */
    public mixed $columns = [];

    /**
     * Method to set the JSON data to the json file.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function create(array $data): self
    {
        //  Check if the json file exists
        if (! file_exists($this->jsonPath)) {
            touch($this->jsonPath);
        }

        //  Prepare the data to be stored in the json file
        $data = array_merge($data, [
            'id' => strtolower(str()->ulid()),
            'settings' => $this->settings,
            'columns' => $this->columns,
        ]);

        //  Get the contents of the json file
        $tables = $this->all()->push($data)->toArray();

        //  Store the items into the json file
        $this->storeInFile($tables);

        //  Check if the json data was written to the file
        return new self($data);
    }

    /**
     * Define a method to get the JSON data from the json file.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function find(string $key): ?self
    {
        //  Check if the json file exists
        if (! file_exists($this->jsonPath)) {
            return null;
        }

        //  Get the table from the json file
        $table = $this->all()->firstWhere('id', $key);

        //  If no items exist, return null
        if (! $table) {
            return null;
        }

        //  Else set the attributes
        $this->setAttributes($table);

        //  Return the object
        return $this;
    }

    /**
     * Update the JSON data in the json file.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function update(array $data = []): bool
    {
        //  Check if the json file exists
        if (! file_exists($this->jsonPath)) {
            return false;
        }

        //  Get the contents of the json file
        $tables = $this->all();

        //  Get the index
        $index = $tables->search(fn ($item) => $item->id === $this->id);

        //  Check the remaining items
        $tables = $tables->replace([$index => $data ?: $this->toArray()]);

        //  Store the tables into the json file
        return $this->storeInFile($tables->toArray());
    }

    /**
     * Method to remove the JSON data from the json file.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function destroy(DataTable $dataTable)
    {
        //  Check if the json file exists
        if (! file_exists($this->jsonPath)) {
            return false;
        }

        //  Get the contents of the json file
        $tables = $this->all();

        //  Check the remaining items
        $tables = $tables->reject(fn ($table) => $table->id === $dataTable->id)->values();

        //  Store the items into the json file
        return $this->storeInFile($tables->toArray());
    }
}
