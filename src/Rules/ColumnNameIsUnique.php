<?php

namespace Amprest\DtTables\Rules;

use Amprest\DtTables\Models\DataTable;
use Amprest\DtTables\Models\DataTableColumn;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ColumnNameIsUnique implements ValidationRule
{
    /**
     * Define the constructor for the TableNameIsUnique class.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function __construct(
        public DataTable $dataTable,
        public ?DataTableColumn $ignore = null,
    ) {}

    /**
     * Validate the value of the attribute.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        //  Define the exists rule
        $exists = $this->dataTable->columns
            ->where($attribute, $value)
            ->when($this->ignore, fn ($query) => $query->where('id', '!=', $this->ignore->id))
            ->isNotEmpty();

        //  Check if the table name exists
        if ($exists) {
            $fail('The :attribute has already been taken.');
        }
    }
}
