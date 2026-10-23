<?php

namespace Nyumbani\DtTables\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServerSideRequest extends FormRequest
{
    /**
     * Return the rules for the request.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
        ];
    }

    /**
     * Get the token that names the table being read.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function token(): string
    {
        return $this->input('token');
    }
}
