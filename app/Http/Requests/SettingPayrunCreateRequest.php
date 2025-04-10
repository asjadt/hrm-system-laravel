<?php

namespace App\Http\Requests;

use App\Http\Utils\BasicUtil;
use App\Models\Department;
use App\Models\User;
use App\Rules\ValidateDepartment;
use App\Rules\ValidUserId;
use Illuminate\Foundation\Http\FormRequest;

class SettingPayrunCreateRequest extends BaseFormRequest
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
            'payrun_period' => 'required|in:monthly,weekly',
            'consider_type' => 'required|in:hour,daily_log,none',
            'consider_overtime' => 'required|boolean',
            'restricted_users' => 'present|array',


            'restricted_users.*' => [
                "numeric",
                new ValidUserId($all_manager_department_ids)

            ],

            'restricted_departments' => 'present|array',
            'restricted_departments.*' => [
                'numeric',
                new ValidateDepartment($all_manager_department_ids)
            ]

        ];
    }


    public function messages()
    {
        return [
            'payrun_period.required' => 'The :attribute field is required.',
            'payrun_period.in' => 'The :attribute must be either "monthly" or "weekly".',
            'consider_type.required' => 'The :attribute field is required.',
            'consider_type.in' => 'The :attribute must be either "hour", "daily_log", or "none".',
            'consider_overtime.required' => 'The :attribute field is required.',
            'consider_overtime.boolean' => 'The :attribute field must be a boolean.',
        ];
    }



}
