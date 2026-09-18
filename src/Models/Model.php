<?php

namespace Amprest\DtTables\Models;

use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionProperty;

class Model
{
    /**
     * Define the directory holding one JSON file per record.
     */
    protected string $directory;

    /**
     * Define the constructor for the DataTable class.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function __construct(array $items = [])
    {
        //  Define the directory holding the JSON files
        $this->directory = rtrim(dt_tables_storage_path(), '/');

        //  Check if the items are not empty
        if (! empty($items)) {
            $this->setAttributes($items);
        }
    }

    /**
     * Get the file path for a given key.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function filePath(string $key): string
    {
        return "{$this->directory}/{$key}.json";
    }

    /**
     * Define a method to handle dynamic method calls.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public static function __callStatic($name, $arguments)
    {
        return (new static)->$name(...$arguments);
    }

    /**
     * Method to get all the JSON data across every file in the directory.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function all(): Collection
    {
        //  Get every json file in the directory
        $files = is_dir($this->directory) ? glob($this->filePath('*')) : [];

        //  Decode each file and drop any that failed to parse
        return collect($files)
            ->map(fn ($file) => json_decode(file_get_contents($file)))
            ->filter()
            ->values();
    }

    /**
     * Method to write the JSON data for a given key to its file.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function storeInFile(string $key, array $data): bool
    {
        //  Encode the json data
        $data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        //  Put the json data to the file
        file_put_contents($this->filePath($key), $data);

        //  Check if the json data was written to the file
        return file_get_contents($this->filePath($key)) === $data;
    }

    /**
     * Method to delete the file for a given key.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function deleteFile(string $key): bool
    {
        return file_exists($this->filePath($key)) && unlink($this->filePath($key));
    }

    /**
     * Method to rename a key's file, refusing to clobber an existing destination.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function renameFile(string $oldKey, string $newKey): bool
    {
        //  Never overwrite another record's file
        if (file_exists($this->filePath($newKey))) {
            return false;
        }

        //  If there's nothing to rename, there's nothing to fail
        if (! file_exists($this->filePath($oldKey))) {
            return true;
        }

        //  Rename the file
        return rename($this->filePath($oldKey), $this->filePath($newKey));
    }

    /**
     * Method to set attributes
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
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
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function where($key, $value)
    {
        return $this->all()->where($key, $value);
    }

    /**
     * Pull a value from the data
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
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
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
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
