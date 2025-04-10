<?php

namespace App\Rules;

use App\Models\Department;
use Illuminate\Contracts\Validation\Rule;

class ValidateParentDepartmentId implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $all_manager_department_ids;
    public function __construct($all_manager_department_ids)
    {
        $this->all_manager_department_ids = $all_manager_department_ids;
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
        $parent_department = Department::where('id', $value)
                        ->where('departments.business_id', '=', auth()->user()->business_id)
                        ->first();

                    if (!$parent_department || !in_array($parent_department->id,$this->all_manager_department_ids)) {
                        return false;
                    }
return true;

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The selected :attribute is invalid.';
    }
}
