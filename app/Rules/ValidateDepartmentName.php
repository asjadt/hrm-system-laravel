<?php

namespace App\Rules;

use App\Models\Department;
use Illuminate\Contracts\Validation\Rule;

class ValidateDepartmentName implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $id;
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $department_exists_with_name =   Department::where([
            "name" => $value,
            "business_id" => auth()->user()->business_id
        ])
        ->when(!empty($this->id),function($query) {
            $query->whereNotIn("id",[$this->id]);
        })
        ->exists();

        return !$department_exists_with_name;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute is already taken.';
    }
}
