<?php

namespace Amprest\DtTables\Models;

use Amprest\DtTables\Services\JsonService;
use Amprest\DtTables\Traits\HasUlids;
use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataTableColumn extends Model
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
        'data_table_id',
        'key',
        'search_type',
        'classes',
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
            //  Set the json data to the file
            $success = JsonService::set($model->dataTable);

            //  Abort if the json was not set
            throw_if(!$success, new Exception('Failed to set the json data to the file'));
        });
    }

    /**
     * Return the name of the column.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    protected function name(): Attribute
    {
        return Attribute::make(get: fn () => prettify($this->key));
    }

    /**
     * Return the data table that the column belongs to.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    protected function dataTable(): BelongsTo
    {
        return $this->belongsTo(DataTable::class);
    }
}