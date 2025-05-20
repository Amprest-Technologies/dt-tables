<?php

namespace Amprest\DtTables\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DataTableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function toArray(Request $request): array
    {
        return [
            'key' => $this->key,
            'settings' => $this->settings,
            'columns' => DataTableColumnResource::collection($this->columns),
        ];
    }
}
