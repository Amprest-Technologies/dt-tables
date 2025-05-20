<?php

namespace Amprest\DtTables\Models;

use Amprest\DtTables\Services\JsonService;
use Amprest\DtTables\Traits\HasUlids;
use Exception;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DataTable extends Model
{
    use HasUlids;

    /**
     * Define the connection name for the model.
     *
     * @var string
     */
    protected $connection = 'dt-tables';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'settings',
        'settings->theme',
        'settings->buttons',
    ];

    /**
     * Get the settings for the data table.
     *
     * @var array
     */
    protected $casts = [
        'settings' => AsArrayObject::class,
    ];

    /**
     * Add observers to the model
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    protected static function boot(): void
    {
        //  Call the parent boot method
        parent::boot();

        //  Create an auto incrementing invoice number
        static::saved(function ($model) {
            //  Check if the json was set
            $success = JsonService::set($model);

            //  Abort if the json was not set
            throw_if(!$success, new Exception('Failed to set the json data to the file'));
        });
    }
    
    /**
     * Get the columns for the data table.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function columns(): HasMany
    {
        return $this->hasMany(DataTableColumn::class);
    }
}