<?php

namespace Amprest\LaravelDT\Models;

use Illuminate\Database\Eloquent\Model;

class DataTableColumn extends Model
{
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
}