<?php

namespace Amprest\DtTables\Models;

class DataTableColumn extends Model
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
     * The search type for the data table column.
     */
    public string $search_type;

    /**
     * The classes for the data table column.
     */
    public ?string $classes;

    /**
     * Method to set the JSON data to the json file.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function create(array $data): bool
    {
        //  Check if the json file exists
        if (! file_exists($this->jsonPath)) {
            touch($this->jsonPath);
        }

        //  Pull the table id
        [$dataTable, $data] = $this->pull('data_table', $data);

        //  Check if the table exists
        if (! $dataTable) {
            return false;
        }

        //  Get the columns
        $dataTable->columns[] = $data;

        //  Update the table
        return $dataTable->update();
    }

    /**
     * Define a method to get the JSON data from the json file.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function find(string $id): ?self
    {
        //  Check if the json file exists
        if (! file_exists($this->jsonPath)) {
            return null;
        }

        //  Get the item from the json file
        $columns = $this->all();

        //  Get the column
        $column = $columns->pluck('columns')->flatten(1)->firstWhere('id', $id);

        //  If no columns exist, return null
        if (! $column) {
            return null;
        }

        //  Else set the attributes
        $this->setAttributes($column);

        //  Return the object
        return $this;
    }

    /**
     * Update the JSON data in the json file.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function update(array $data): bool
    {
        //  Check if the json file exists
        if (! file_exists($this->jsonPath)) {
            return false;
        }

        //  Pull the table id
        [$dataTable, $data] = $this->pull('data_table', array_merge($data, ['id' => $this->id]));

        //  Get the columns
        $columns = $dataTable->columns;

        //  Get the column
        $index = $columns->search(fn ($column) => $column->id === $this->id);

        //  Replace the column
        $dataTable->columns = $columns->replace([$index => $data]);

        //  Update the table
        return $dataTable->update();
    }

    /**
     * Method to remove the JSON data from the json file.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function delete(DataTable $dataTable): bool
    {
        //  Check if the json file exists
        if (! file_exists($this->jsonPath)) {
            return false;
        }

        //  Remove the column
        $dataTable->columns = $dataTable->columns
            ->reject(fn ($column) => $column->id === $this->id)
            ->values();

        //  Update the table
        return $dataTable->update();
    }
}
