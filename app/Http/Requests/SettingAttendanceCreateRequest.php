<?php

namespace App\Http\Requests;

use App\Http\Utils\BasicUtil;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Rules\ValidUserId;
use Illuminate\Foundation\Http\FormRequest;

class SettingAttendanceCreateRequest extends BaseFormRequest
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
            'punch_in_time_tolerance' => 'nullable|numeric|min:0',
            'work_availability_definition' => 'nullable|numeric|min:0',
            'punch_in_out_alert' => 'nullable|boolean',
            'punch_in_out_interval' => 'nullable|numeric|min:0',
            'alert_area' => 'nullable|array',
            'alert_area.*' => 'string',
            'service_name' => 'nullable|string',
            'api_key'  => 'nullable|string',

            'auto_approval' => 'nullable|boolean',
            'is_geolocation_enabled' => 'nullable|boolean',

            'special_users' => 'present|array',
            'special_users.*' => [
                "numeric",
                new ValidUserId($all_manager_department_ids)


            ],


            'special_roles' => 'present|array',

            'special_roles.*' => [
                'numeric',
                function ($attribute, $value, $fail) {
                    $role = Role::where("id", $value)
                        ->first();


                    if (!$role) {

                        $fail("Role does not exists.");
                    }
                    if (empty(auth()->user()->business_id)) {
                        if (!(empty($role->business_id) || $role->is_default == 1)) {

                            $fail("Role belongs to another business.");
                        }
                    } else {
                        if ($role->business_id != auth()->user()->business_id) {
                    
                            $fail("Role belongs to another business.");
                        }
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'punch_in_out_alert.boolean' => 'The :attribute field must be a boolean.',
            'alert_area.array' => 'The :attribute field must be an array.',
            'alert_area.*.string' => 'Each item in :attribute must be a string.',
            'auto_approval.boolean' => 'The :attribute field must be a boolean.',

        ];
    }
}
