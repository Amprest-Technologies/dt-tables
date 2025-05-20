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
        $key = $this->key ?? ($this->route('data_table')->key ?? '');

        //  Merge the key with the request
        $this->merge(['key' => Str::slug($key)]);
    }

    /**
     * Return the rules for the request.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function rules(#[RouteParameter('data_table')] $dataTable): array
    {
        //  Define the unique rule for the key
        $uniqueRule = Rule::unique(DataTable::class)->ignore($dataTable);

        //  Return the rules
        return [
            'key' => ['required', 'string', 'max:255', $uniqueRule],
            'type' => ['sometimes', 'required', Rule::in(['buttons', 'theme'])],
            'theme' => ['sometimes', 'required', Rule::in(array_keys(config('dt-tables.themes')))],
            'buttons' => ['sometimes', 'array'],
            'buttons.*' => ['sometimes', 'boolean'],
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
            'key' => $this->key,
            "settings->{$this->type}" => $this->{$this->type}(),
        ];
    }

    /**
     * Format the theme
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function theme(): string
    {
        return $this->theme ?? config('dt-tables.settings.theme');
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
