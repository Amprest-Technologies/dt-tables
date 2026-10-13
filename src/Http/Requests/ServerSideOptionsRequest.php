<?php

namespace Amprest\DtTables\Http\Requests;

class ServerSideOptionsRequest extends ServerSideRequest
{
    /**
     * Return the rules for the request.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'column' => ['required', 'string'],
        ];
    }

    /**
     * Get the key of the column whose filter options are wanted.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function column(): string
    {
        return $this->input('column');
    }
}
