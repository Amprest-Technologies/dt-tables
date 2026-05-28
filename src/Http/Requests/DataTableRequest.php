<?php

namespace Amprest\DtTables\Http\Requests;

use Amprest\DtTables\Models\DataTable;
use Amprest\DtTables\Rules\TableNameIsUnique;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DataTableRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
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
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function rules(#[RouteParameter('data_table')] DataTable $dataTable): array
    {
        //  Define the unique rule for the key
        $uniqueRule = new TableNameIsUnique(ignore: $dataTable);

        //  Return the rules
        return [
            'key' => ['required', 'string', 'max:255', $uniqueRule],
            'type' => ['sometimes', 'required', Rule::in(['buttons', 'theme', 'loader', 'behaviour', 'name'])],
            'theme' => ['sometimes', 'required', Rule::in(array_keys(config('dt-tables.themes')))],
            'buttons' => ['sometimes', 'required', 'array'],
            'buttons.*' => ['sometimes', 'boolean'],
            'loader' => ['sometimes', 'required', 'array'],
            'loader.enabled' => ['sometimes', 'boolean'],
            'loader.message' => ['sometimes', 'nullable', 'string', 'max:255'],
            'loader.image' => ['sometimes', 'nullable', 'string', 'max:255'],
            'page_length' => ['sometimes', 'required', 'integer', Rule::in([10, 25, 50, 100, -1])],
            'ordering' => ['sometimes', 'required', 'boolean'],
            'searching' => ['sometimes', 'required', 'boolean'],
            'paging' => ['sometimes', 'required', 'boolean'],
            'info' => ['sometimes', 'required', 'boolean'],
            'scroll_x' => ['sometimes', 'required', 'boolean'],
        ];
    }

    /**
     * Get the data used for updating the data table.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function updateData(DataTable $dataTable): array
    {
        //  Check if the update type is name
        if ($this->type === 'name') {
            //  Define the key
            $dataTable->key = $this->key;

            //  Retuen as an array
            return $dataTable->toArray();
        }

        //  Get the type of data to update
        $dataTable->settings->{$this->type} = $this->{$this->type}();

        //  Return the data as an array
        return $dataTable->toArray();
    }

    /**
     * Format the buttons
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function buttons(): array
    {
        return collect($this->buttons ?? [])
            ->filter()
            ->keys()
            ->toArray();
    }

    /**
     * Format the theme
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function theme(): string
    {
        return $this->theme ?? config('dt-tables.settings.theme');
    }

    /**
     * Format the loader settings.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function loader(): array
    {
        return [
            'enabled' => $this->boolean('loader.enabled', config('dt-tables.settings.loader.enabled')),
            'message' => $this->string('loader.message', config('dt-tables.settings.loader.message')),
            'image' => $this->string('loader.image', config('dt-tables.settings.loader.image')),
        ];
    }

    /**
     * Format the behaviour settings.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function behaviour(): array
    {
        return [
            'page_length' => (int) $this->integer('page_length', config('dt-tables.settings.behaviour.page_length', 10)),
            'ordering' => $this->boolean('ordering', config('dt-tables.settings.behaviour.ordering', true)),
            'searching' => $this->boolean('searching', config('dt-tables.settings.behaviour.searching', true)),
            'paging' => $this->boolean('paging', config('dt-tables.settings.behaviour.paging', true)),
            'info' => $this->boolean('info', config('dt-tables.settings.behaviour.info', true)),
            'scroll_x' => $this->boolean('scroll_x', config('dt-tables.settings.behaviour.scroll_x', false)),
        ];
    }
}
