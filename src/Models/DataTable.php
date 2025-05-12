<?php

namespace Amprest\LaravelDT\Models;

use Illuminate\Database\Eloquent\Model;

class DataTable extends Model
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
        'name',
    ];   
}