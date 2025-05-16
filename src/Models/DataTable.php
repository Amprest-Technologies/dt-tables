<?php

namespace Amprest\DtTables\Models;

use Amprest\DtTables\Traits\HasUlids;
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
        'identifier',
        'settings',
        'settings->buttons',
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