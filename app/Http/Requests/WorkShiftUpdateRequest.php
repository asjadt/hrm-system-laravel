<?php

namespace App\Http\Requests;

use App\Http\Utils\BasicUtil;
use App\Models\Department;
use App\Models\User;
use App\Models\WorkShift;
use App\Rules\ValidateDepartment;
use App\Rules\ValidUserId;
use App\Rules\ValidWorkLocationId;
use Illuminate\Foundation\Http\FormRequest;

class WorkShiftUpdateRequest extends BaseFormRequest
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
            'id' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) use($all_manager_department_ids) {
                    $work_shift = WorkShift::where('id', $value)
                        ->where('work_shifts.business_id', '=', auth()->user()->business_id)
                        ->whereHas("departments", function ($query) use ($all_manager_department_ids) {
                            $query->whereIn("departments.id", $all_manager_department_ids);
                        })
                        ->first();
                    if (!$work_shift) {
                        $fail($attribute . " is invalid.");
                        return;
                    }
                    if($work_shift->is_default == 1 && $work_shift->business_id == NULL && !auth()->user()->hasRole("superadmin")){

                            $fail($attribute . " is invalid. you are not a super admin.");
                            return;

                    }
                },
            ],

            'name' => 'required|string',
            'description' => 'nullable|string',
            'is_personal' => 'required|boolean',

            'break_type' => 'required|string|in:paid,unpaid',
            'break_hours' => 'required|numeric',


            'type' => 'required|string|in:regular,scheduled,flexible',







            'departments' => 'present|array',
            'departments.*' => [
                'numeric',
                new ValidateDepartment($all_manager_department_ids)
            ],

            'work_locations' => [
                "present",
                'array',
            ],

            "work_locations.*" =>[
                "numeric",
            new ValidWorkLocationId()
        ],


            'users' => 'present|array',
            'users.*' => [
                "numeric",
                new ValidUserId($all_manager_department_ids)

            ],
            'details' => 'required|array|min:7|max:7',
            'details.*.day' => 'required|numeric|between:0,6',
            'details.*.is_weekend' => 'required|boolean',
            'details.*.start_at' => [
                'nullable',
                'date_format:H:i:s',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $isWeekend = request('details')[$index]['is_weekend'] ?? false;

                    if (request('type') === 'scheduled' && $isWeekend == 0 && empty($value)) {
                        $fail("The $attribute field is required when type is scheduled and is_weekend is 0.");
                    }
                },
            ],
            'details.*.end_at' => [
                'nullable',
                'date_format:H:i:s',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $isWeekend = request('details')[$index]['is_weekend'] ?? false;

                    if (request('type') === 'scheduled' && $isWeekend == 0 && empty($value)) {
                        $fail("The $attribute field is required when type is scheduled and is_weekend is 0.");
                    }
                },
            ],

        ];
    }
    public function messages()
    {
        return [
            'type.in' => 'The :attribute field must be either "regular" or "scheduled".',
            'break_type.in' => 'The :attribute field must be either "paid" or "unpaid".',
        ];
    }
}
