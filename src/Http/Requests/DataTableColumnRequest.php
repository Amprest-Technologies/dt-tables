<?php

namespace Amprest\DtTables\Http\Requests;

use Amprest\DtTables\Rules\ColumnNameIsUnique;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DataTableColumnRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    protected function prepareForValidation(): void
    {
        //  Get the key from the request
        $this->errorBag = $this->{'_bag'} ?? $this->errorBag;

        //  Merge inexisting data with the request
        $this->merge([
            'key' => Str::slug($this->key, '_'),
            'search_type' => $this->search_type ?? 'none',
            'classes' => $this->classes ?? null,
        ]);
    }

    /**
     * Return the rules for the request.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function rules(
        #[RouteParameter('data_table')] $dataTable,
        #[RouteParameter('data_table_column')] $dataTableColumn = null
    ): array
    {        
        //  Define the unique rule for the key
        $uniqueRule = new ColumnNameIsUnique($dataTable, ignore: $dataTableColumn);

        //  Return the rules
        return [
            'key' => ['required', 'string', 'max:255', $uniqueRule],
            'search_type' => ['required', Rule::in(config('dt-tables.columns.search_types'))],
            'classes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
