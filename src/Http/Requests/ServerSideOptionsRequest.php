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
            'column' => ['required_without:columns', 'string'],
            'columns' => ['required_without:column', 'array', 'max:50'],
            'columns.*' => ['string'],
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

    /**
     * Get the keys of the columns whose filter options are wanted in one request.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function columns(): array
    {
        return array_values(array_unique((array) $this->input('columns', [])));
    }
}
