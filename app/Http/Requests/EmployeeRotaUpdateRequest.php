<?php

namespace App\Http\Requests;

use App\Http\Utils\BasicUtil;
use App\Models\EmployeeRota;
use App\Rules\ValidateDepartment;
use App\Rules\ValidateDuplicateRotaDepartment;
use App\Rules\ValidateDuplicateRotaUser;
use App\Rules\ValidUserId;
use Illuminate\Foundation\Http\FormRequest;

class EmployeeRotaUpdateRequest extends FormRequest
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
                    $employeeRota = EmployeeRota::where('id', $value)
                        ->where('employee_rotas.business_id', '=', auth()->user()->business_id)
                        ->where(function($query) use ($all_manager_department_ids){
                            $query->whereHas("department", function ($query) use ($all_manager_department_ids) {
                                $query->whereIn("departments.id", $all_manager_department_ids);
                            })
                            ->orWhereHas("user.department_user.department", function ($query) use ($all_manager_department_ids) {
                                $query->whereIn("departments.id", $all_manager_department_ids);
                            });
                        })
                        ->first();
                    if (!$employeeRota) {
                        $fail($attribute . " is invalid.");
                        return;
                    }

                },
            ],

            'name' => 'required|string',
            'description' => 'nullable|string',





            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',









            'details' => 'required|array',
            'details.*.day' => 'required|numeric|between:0,6',
            'details.*.break_type' => 'required|string|in:paid,unpaid',
            'details.*.break_hours' => 'required|numeric',
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
            'type.in' => 'The :attribute field must be either "regular" or "scheduled or flexible".',

        ];
    }
}
