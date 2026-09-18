<?php

namespace Amprest\DtTables\Models;

class DataTable extends Model
{
    /**
     * The key for the data table.
     */
    public string $key;

    /**
     * The key this instance was loaded under, used to detect renames on update.
     */
    protected ?string $originalKey = null;

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
        //  Check if the directory exists
        ensure_directory_exists($this->directory);

        //  Prepare the data to be stored in the json file
        $data = array_merge($data, [
            'settings' => $this->settings,
            'columns' => $this->columns,
        ]);

        //  Store the table into its own json file
        $this->storeInFile($data['key'], $data);

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
        //  Check if the table's json file exists
        if (! file_exists($this->filePath($key))) {
            return null;
        }

        //  Get the table from its json file
        $table = json_decode(file_get_contents($this->filePath($key)));

        //  If the file failed to parse, return null
        if (! $table) {
            return null;
        }

        //  Else set the attributes
        $this->setAttributes($table);

        //  Remember the key this instance was loaded under, to detect renames on update
        $this->originalKey = $this->key;

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
        //  Fall back to the current attributes if no data was given
        $data = $data ?: $this->toArray();

        //  Determine the new key
        $newKey = $data['key'] ?? $this->key;

        //  Determine the old key
        $oldKey = $this->originalKey ?? $newKey;

        //  If the key changed, rename the file first; bail out if that fails
        if ($oldKey !== $newKey && ! $this->renameFile($oldKey, $newKey)) {
            return false;
        }

        //  Store the table into its json file
        $written = $this->storeInFile($newKey, $data);

        //  Track the new key once the write succeeds
        if ($written) {
            $this->originalKey = $newKey;
        }

        //  Return the result
        return $written;
    }

    /**
     * Method to remove the JSON data from the json file.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function destroy(DataTable $dataTable): bool
    {
        return $this->deleteFile($dataTable->key);
    }
}
