<?php

namespace Amprest\LaravelDT\Models;

use Amprest\LaravelDT\Traits\HasUlids;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class DataTableColumn extends Model
{
    use HasUlids;

    /**
     * Define the connection name for the model.
     *
     * @var string
     */
    protected $connection = 'laravel-dt';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'data_table_id',
        'key',
        'search_type',
        'data_type',
    ];

    /**
     * Return the name of the column.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    protected function name(): Attribute
    {
        return Attribute::make(get: fn () => prettify($this->key));
    }
}