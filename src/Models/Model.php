<?php

namespace Amprest\DtTables\Models;

use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionProperty;

class Model
{
    /**
     * Define the json file path.
     */
    protected string $jsonPath = '';

    /**
     * Define the constructor for the DataTable class.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function __construct(array $items = [])
    {
        //  Define the json file path
        $this->jsonPath = base_path('dt-tables/config.json');

        //  Set attributes
        $this->setAttributes($items);
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
        $data = json_decode(file_get_contents($this->jsonPath));

        //  Return the collection of json data
        return collect($data)->values();
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
        file_put_contents($this->jsonPath, $data);

        //  Check if the json data was written to the file
        return file_get_contents($this->jsonPath) === $data;
    }

    /**
     * Method to set attributes
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    protected function setAttributes(array|object $items)
    {
        foreach ($items as $key => $item) {
            //  Handle array items
            if (is_array($item)) {
                $item = to_object($item);

                //  Check if the item is an array and convert it to a collection
                if (is_array($item)) {
                    $item = collect($item);
                }
            }

            //  Set the item
            $this->{$key} = $item;
        }
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
     * Pull a value from the data
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    protected function pull(string $key, array $data)
    {
        //  Get the value from the data
        $value = $data[$key] ?? null;

        //  Unset the value from the data
        unset($data[$key]);

        //  Return the value
        return [$value, $data];
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
        return to_object($atrributes->toArray(), true);
    }
}
