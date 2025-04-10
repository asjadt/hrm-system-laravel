<?php

namespace App\Http\Requests;

use App\Http\Utils\BasicUtil;
use App\Models\Attendance;
use Illuminate\Foundation\Http\FormRequest;

class AttendanceArrearApproveRequest extends FormRequest
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
            'attendance_ids' => "present|array",
            'attendance_ids.*' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) use($all_manager_department_ids) {


                    $exists = Attendance::where('attendances.id', $value)
                        ->where('attendances.business_id', '=', auth()->user()->business_id)
                        ->whereHas("employee.department_user.department", function($query) use($all_manager_department_ids) {
                            $query->whereIn("departments.id",$all_manager_department_ids);
                         })

                        ->whereNotIn("user_id",[auth()->user()->id])

                        ->exists();

                    if (!$exists) {
                        $fail($attribute . " is invalid.");
                    }


                },
            ],
        ];




    }
}
