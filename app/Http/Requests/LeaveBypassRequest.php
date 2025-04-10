<?php

namespace App\Http\Requests;

use App\Http\Utils\BasicUtil;
use App\Models\Department;
use App\Models\Leave;
use App\Rules\ValidateLeaveId;
use Illuminate\Foundation\Http\FormRequest;

class LeaveBypassRequest extends BaseFormRequest
{
    use BasicUtil;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $all_manager_department_ids = $this->get_all_departments_of_manager();
        return [
            'leave_id' => [
                'required',
                'numeric',
                new ValidateLeaveId($all_manager_department_ids),
            ],

            "add_in_next_payroll" => "required|boolean"

        ];
    }
}
