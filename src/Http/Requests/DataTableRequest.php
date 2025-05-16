<?php

namespace Amprest\DtTables\Http\Requests;

use Amprest\DtTables\Models\DataTable;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DataTableRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    protected function prepareForValidation(): void
    {
        //  Define the indentifier
        $identifier = $this->identifier ?? ($this->route('data_table')->identifier ?? '');

        //  Merge the identifier with the request
        $this->merge(['identifier' => Str::slug($identifier)]);
    }


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
            'type' => ['sometimes', 'required', Rule::in(['buttons'])],
        ];
    }

    /**
     * Get the data used for updating the data table.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function updateData(): array
    {
        return [
            'identifier' => $this->identifier,
            "settings->{$this->type}" => $this->{$this->type}()
        ];
    }

    /**
     * Format the buttons
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function buttons(): array
    {
        return collect($this->buttons ?? [])
            ->filter()
            ->keys()
            ->toArray();
    }
}
