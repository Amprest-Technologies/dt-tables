<?php

namespace Amprest\DtTables\Traits;

use Illuminate\Support\Str;

trait HasUlids
{
    /**
     * Create a hook in the boot method
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    protected static function bootHasUlids(): void
    {
        static::creating(function ($model) {
            $key = $model->getRouteKeyName();
            $model->{$key} = $model->{$key} ?: (string) strtolower(Str::ulid());
        });
    }

    /**
     *  Get the route key for the model.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function getRouteKeyName(): string
    {
        return 'ulid';
    }
}
