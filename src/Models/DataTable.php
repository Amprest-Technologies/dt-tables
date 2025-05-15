<?php

namespace Amprest\LaravelDT\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'identifier',
        'settings',
    ];

    /**
     * Get the settings for the data table.
     *
     * @var array
     */
    protected $casts = [
        'settings' => 'array',
    ];
    
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