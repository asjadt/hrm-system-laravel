<?php

namespace App\Rules;

use App\Models\DepartmentEmployeeRota;
use Exception;
use Illuminate\Contracts\Validation\Rule;

class ValidateDuplicateRotaDepartment implements Rule
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

        $departmentEmployeeRota =  DepartmentEmployeeRota::where([
            "department_id" => $value
        ])
        ->when(!empty($this->id), function($query)  {
            $query->whereNotIn("employee_rota_id",[$this->id]);
        })
        ->first();



       return empty($departmentEmployeeRota)?1:0;


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
