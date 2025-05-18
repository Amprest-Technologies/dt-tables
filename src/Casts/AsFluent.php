<?php

namespace Amprest\DtTables\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Fluent;

class AsFluent implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return Fluent::make(json_decode($value, true));
    }

    /**
     * Prepare the given value for storage.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return json_encode($value);
    }
}
