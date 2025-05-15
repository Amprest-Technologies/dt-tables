<?php

namespace Amprest\LaravelDT\Http\Requests;

use Amprest\LaravelDT\Models\DataTable;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DataTableRequest extends FormRequest
{
    /**
     * Return the rules for the request.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function rules(#[RouteParameter('data_table')] $dataTable): array
    {
        //  Define the unique rule for the identifier
        $uniqueRule = Rule::unique(DataTable::class)->ignore($dataTable);

        //  Return the rules
        return [
            'identifier' => ['required', 'string', 'max:255', $uniqueRule],
            'settings' => ['sometimes', 'required', 'array'],
        ];
    }
}
