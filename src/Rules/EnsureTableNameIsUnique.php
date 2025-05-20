<?php

namespace Amprest\DtTables\Rules;

use Amprest\DtTables\Models\DataTable;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EnsureTableNameIsUnique implements ValidationRule
{
    /**
     * Define the constructor for the EnsureTableNameIsUnique class.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function __construct(
        public string $key,
        public ?DataTable $ignore = null,
    ) {}

    /**
     * Validate the value of the attribute.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        //  Define the exists rule
        $exists = DataTable::where('key', $this->key)
            ->when($this->ignore, fn ($query) => $query->where('id', '!=', $this->ignore->id))
            ->isNotEmpty();

        //  Check if the table name exists
        if ($exists) {
            $fail('The :attribute has already been taken.');
        }
    }
}
