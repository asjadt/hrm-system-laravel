<?php

namespace App\Rules;

use App\Models\Department;
use Illuminate\Contracts\Validation\Rule;

class ValidateDepartment implements Rule
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
        $all_manager_department_ids = $this->all_manager_department_ids;

        $department = Department::where('id', $value)
        ->where('business_id', auth()->user()->business_id)
        ->first();

    if (!$department || !in_array($department->id, $all_manager_department_ids)) {
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
        return ':attribute is invalid. You don\'t have access to this department.';
    }
}
