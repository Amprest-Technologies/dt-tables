<?php

namespace Amprest\DtTables\Services;

use Amprest\DtTables\Models\DataTable;
use Illuminate\Support\Collection;

class JsonService
{
    /**
     * Define the json file path.
     *
     * @var string
     */
    protected string $path = 'dt-tables/config.json';

    /**
     * Define the constructor for the JsonService class.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function __construct(
        public $id = null,
        public $key = null,
        public $columns = null,
        public $settings = null,
    ){}

    /**
     * Define a method to handle dynamic method calls.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public static function __callStatic($name, $arguments)
    {
        return (new static)->$name(...$arguments);
    }

    /**
     * Method to get all the JSON data from the json file.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    protected function all(): Collection
    {
        //  Get the json data
        $data = json_decode(file_get_contents(base_path($this->path)));

        //  Return the collection of json data
        return collect($data)->values();
    }

    /**
     * Define a method to get the JSON data from the json file.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    protected function find(string $key): ?self
    {
        //  Check if the json file exists
        if (! file_exists($path = base_path($this->path))) {
            return null;
        }

        //  Get the item from the json file
        $items = collect(json_decode(file_get_contents($path), true));

        //  Get the item
        $item = $items->firstWhere('id', $key);

        //  If no items exist, return null
        if (!$item) {
            return null;
        }
        
        //  Else set the attributes
        $this->setAttributes($item);

        //  Return the object
        return $this;
    }

    /**
     * Method to set the JSON data to the json file.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    protected function set(DataTable $dataTable): bool
    {   
        //  Check if the json file exists
        if (!file_exists($path = base_path($this->path))) {
            touch($path);
        }

        //  Get the contents of the json file
        $json = json_decode(file_get_contents($path), true);

        //  Set the key
        $json[$dataTable->key] = $dataTable->toResource()->resolve();

        //  Encode the json data
        $json = json_encode($json);

        //  Put the json data to the file
        file_put_contents($path, $json);

        //  Check if the json data was written to the file
        return file_get_contents($path) === $json;
    }

    /**
     * Method to remove the JSON data from the json file.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    protected function destroy(JsonService $dataTable)
    {
        //  Check if the json file exists
        if (!file_exists($path = base_path($this->path))) {
            return false;
        }

        //  Get the contents of the json file
        $items = collect(json_decode(file_get_contents($path)));

        //  Check the remaining items
        $items = $items->reject(fn($item) => $item->id === $dataTable->id);

        //  Encode the json data
        $json = json_encode($items);

        //  Put the json data to the file
        file_put_contents($path, $json);

        //  Check if the json data was written to the file
        return file_get_contents($path) === $json;
    }

    /**
     * Method to set attributes
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    protected function setAttributes(array $items)
    {
        foreach($items as $key => $item) {
            //  Handle array items
            if (is_array($item)) {
                $item = json_decode(json_encode($item));
            }

            //  Set the item
            $this->{$key} = $item;
        }
    }
}