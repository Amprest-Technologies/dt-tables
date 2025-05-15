<?php

namespace Amprest\LaravelDT\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DataTableRequest extends FormRequest
{
    /**
     * Return the rules for the request.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function rules(): array
    {
        return [
            'identifier' => ['required', 'string', 'max:255'],
            'settings' => ['sometimes', 'required', 'array'],
        ];
    }
}
