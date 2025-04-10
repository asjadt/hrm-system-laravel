<?php

namespace App\Rules;

use App\Models\LeaveRecord;
use Illuminate\Contracts\Validation\Rule;

class ValidateLeaveRecordId implements Rule
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
        $exists = LeaveRecord::where('leave_records.id', $value)


        ->whereHas("leave",function($query) {
            $query->where('leaves.business_id', '=', auth()->user()->business_id);
        })

        ->whereHas("leave.employee",function($query) {

            $query->whereNotIn("users.id",[auth()->user()->id]);

        })
        ->whereHas("leave.employee.department_user.department",function($query) {

                $query->whereIn("departments.id",$this->all_manager_department_ids);

        })



        ->exists();
        return $exists;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The validation error message.';
    }
}
