<?php

namespace Amprest\LaravelDT\Http\Requests;

use Amprest\LaravelDT\Models\DataTableColumn;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Database\Query\Builder;
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
        //  Get the identifier from the request
        $this->errorBag = $this->{'_bag'} ?? $this->errorBag;

        //  Merge inexisting data with the request
        $this->merge([
            'key' => Str::slug($this->key, '_'),
            'search_type' => $this->search_type ?? 'input',
            'data_type' => $this->data_type ?? 'string',
        ]);
    }

    /**
     * Return the rules for the request.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function rules(#[RouteParameter('data_table')] $dataTable): array
    {
        //  Define the unique rule for the identifier
        $uniqueRule = Rule::unique(DataTableColumn::class)->where(
            fn (Builder $query) => $query->whereBelongsTo($dataTable)
        );

        //  Return the rules
        return [
            'key' => ['required', 'string', 'max:255', $uniqueRule],
            'search_type' => ['required', Rule::in(config('laravel-dt.columns.search_types'))],
            'data_type' => ['required', Rule::in(config('laravel-dt.columns.data_types'))],
        ];
    }
}
