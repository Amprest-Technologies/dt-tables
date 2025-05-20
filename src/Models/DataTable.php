<?php

namespace Amprest\DtTables\Models;

use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionProperty;

class DataTable
{
    /**
     * Define the json file path.
     */
    protected string $path = '';

    /**
     * Define the constructor for the DataTable class.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function __construct(
        public $id = null,
        public $key = null,
        public $settings = null,
        public $columns = null,
    ) {
        $this->path = base_path('dt-tables/config.json');
    }

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
        $data = json_decode(file_get_contents($this->path));

        //  Return the collection of json data
        return collect($data)->values();
    }

    /**
     * Method to set the JSON data to the json file.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    protected function create(array $data): self
    {
        //  Check if the json file exists
        if (! file_exists($this->path)) {
            touch($this->path);
        }

        //  Get the contents of the json file
        $json = json_decode(file_get_contents($this->path), true);

        //  Set the key
        $json[] = $data;

        //  Store the items into the json file
        $this->storeInFile($json);

        //  Check if the json data was written to the file
        return new self(...$data);
    }

    /**
     * Define a method to get the JSON data from the json file.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    protected function find(string $key): ?self
    {
        //  Check if the json file exists
        if (! file_exists($this->path)) {
            return null;
        }

        //  Get the item from the json file
        $items = collect(json_decode(file_get_contents($this->path), true));

        //  Get the item
        $item = $items->firstWhere('id', $key);

        //  If no items exist, return null
        if (! $item) {
            return null;
        }

        //  Else set the attributes
        $this->setAttributes($item);

        //  Return the object
        return $this;
    }

    /**
     * Update the JSON data in the json file.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function update(array $data): bool
    {
        //  Check if the json file exists
        if (! file_exists($this->path)) {
            return false;
        }

        //  Get the contents of the json file
        $tables = collect(json_decode(file_get_contents($this->path)));

        //  Check the remaining items
        $items = $tables->reject(fn ($item) => $item->id === $this->id);

        //  Set the new item
        $items->push($data);

        //  Store the items into the json file
        return $this->storeInFile($items->toArray());
    }

    /**
     * Method to remove the JSON data from the json file.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    protected function destroy(DataTable $dataTable)
    {
        //  Check if the json file exists
        if (! file_exists($this->path)) {
            return false;
        }

        //  Get the contents of the json file
        $items = collect(json_decode(file_get_contents($this->path)));

        //  Check the remaining items
        $items = $items->reject(fn ($item) => $item->id === $dataTable->id);

        //  Store the items into the json file
        return $this->storeInFile($items->toArray());
    }

    /**
     * Method to get the JSON data from the json file.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    protected function where($key, $value)
    {
        return $this->all()->where($key, $value);
    }

    /**
     * Method to out the JSON data into the json file.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    protected function storeInFile(array $data): bool
    {
        //  Encode the json data
        $data = json_encode($data);

        //  Put the json data to the file
        file_put_contents($this->path, $data);

        //  Check if the json data was written to the file
        return file_get_contents($this->path) === $data;
    }

    /**
     * Method to set attributes
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    protected function setAttributes(array $items)
    {
        foreach ($items as $key => $item) {
            //  Handle array items
            if (is_array($item)) {
                $item = json_decode(json_encode($item));
            }

            //  Set the item
            $this->{$key} = $item;
        }
    }

    /**
     * Get an array representation of the object.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function toArray(): array
    {
        //  Get the reflection class
        $reflection = new ReflectionClass($this);

        //  Get the public properties of the class
        $props = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        //  Get the properties of the class
        $atrributes = collect($props)->mapWithKeys(fn (ReflectionProperty $prop) => [
            $prop->getName() => $this->{$prop->getName()},
        ]);

        //  Return the attributes as an array
        return json_decode(json_encode($atrributes->toArray()), true);
    }
}
