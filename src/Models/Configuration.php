<?php

namespace Amprest\LaravelDatatables\Models;

class Configuration
{
    //  Define the data variable
    private $data;

    private static $filepath = 'datatables.config.json';

    /**
     * Initialize the model
     *
     * @author Alvin Gichira Kaburu
     *
     * @return null
     */
    public function __construct()
    {
        //  Get the config data from the file
        $this->data = collect(json_decode(
            $this->fetchDataFromFile(), true)
        );
    }

    /**
     * Return the config path
     *
     * @author Alvin Gichira Kaburu
     *
     * @return string
     */
    public static function getConfigPath()
    {
        //  Get the directory and check if it already exists.
        if (! is_dir($directory = base_path(dirname(self::$filepath)))) {
            //  Directory does not exist, so lets create it.
            mkdir($directory, 0755, true);
        }

        //  Return the path
        return base_path(self::$filepath);
    }

    /**
     * Create a method to return all items from the data store
     *
     * @author Alvin Gichira Kaburu
     *
     * @return object
     */
    public static function all()
    {
        //  Get all configurations
        $configurations = json_decode(self::fetchDataFromFile(), true);

        //  Cast the configurations into an object
        return json_decode(json_encode($configurations));
    }

    /**
     * Create a new table configration
     *
     * @author Alvin Gichira Kaburu
     *
     * @return object
     */
    public static function create(array $data)
    {
        //  Get all configurations
        $configurations = json_decode(self::fetchDataFromFile(), true);

        $configurations = collect($configurations)
            ->push($data = collect($data)->except('_token'))
            ->toArray();

        //  Insert the item into the json file
        file_put_contents(self::getConfigPath(), json_encode($configurations));

        //  Cast the configurations into an object
        return $data;
    }

    /**
     * Create a method to return a specific item from the data store
     *
     * @author Alvin Gichira Kaburu
     *
     * @param  int  $identifier
     * @return \Illuminate\Support\Collection
     */
    public function find($identifier)
    {
        return $this->data->where('identifier', $identifier)->first();
    }

    /**
     * Create a method to conditionally return items from the data store
     *
     * @author Alvin Gichira Kaburu
     *
     * @param  string  $key
     * @param  string  $value
     * @return $this
     */
    public function where($key, $value)
    {
        $this->data = $this->data->where($key, $value);

        return $this;
    }

    /**
     * Create a method to return items from the data store
     *
     * @author Alvin Gichira Kaburu
     *
     * @return $object
     */
    public function get()
    {
        return json_decode(json_encode($this->data));
    }

    /**
     * Update the configurations
     *
     * @author Alvin Gichira Kaburu
     *
     * @return bool
     */
    public function update($configuration)
    {
        //  Get all configurations
        $configurations = $this->data;
        $configurations = $configurations->map(function ($item, $index) use ($configuration) {
            if ($item['identifier'] == $configuration['identifier']) {
                $item = $configuration;
            }

            return $item;
        });

        //  Insert the item into the json file
        return file_put_contents(self::getConfigPath(), json_encode($configurations));
    }

    /**
     * Delete the configurations
     *
     * @author Alvin Gichira Kaburu
     *
     * @return bool
     */
    public function delete($identifier)
    {
        //  Get all configurations
        $configurations = $this->data;
        $configurations = $configurations->map(function ($configuration) use ($identifier) {
            if ($configuration['identifier'] == $identifier) {
                $configuration['deleted_at'] = now()->toDateTimeString();
            }

            return $configuration;
        });

        //  Insert the item into the json file
        return file_put_contents(self::getConfigPath(), json_encode($configurations));
    }

    /**
     * Restore a configuration
     *
     * @author Alvin Gichira Kaburu
     *
     * @return bool
     */
    public function restore($identifier)
    {
        //  Get all configurations
        $configurations = $this->data;
        $configurations = $configurations->map(function ($configuration) use ($identifier) {
            if ($configuration['identifier'] == $identifier) {
                $configuration['deleted_at'] = null;
            }

            return $configuration;
        });

        //  Insert the item into the json file
        return file_put_contents(self::getConfigPath(), json_encode($configurations));
    }

    /**
     * Force delete a configuration
     *
     * @author Alvin Gichira Kaburu
     *
     * @return bool
     */
    public function forceDelete($identifier)
    {
        //  Get all configurations
        $configurations = $this->data;
        $configurations = $configurations->filter(function ($configuration, $index) use ($identifier) {
            return $configuration['identifier'] != $identifier;
        })->values();

        //  Insert the item into the json file
        return file_put_contents(self::getConfigPath(), json_encode($configurations));
    }

    /**
     * Fetch config data from external file
     *
     * @author Alvin Gichira Kaburu
     *
     * @return bool|string
     */
    public static function fetchDataFromFile()
    {
        //  Check if the file exits, and create it if it doesn't
        if (! file_exists($path = self::getConfigPath())) {
            file_put_contents($path, json_encode([]));
        }

        //  Get the config data from the file
        return @file_get_contents($path);
    }
}
