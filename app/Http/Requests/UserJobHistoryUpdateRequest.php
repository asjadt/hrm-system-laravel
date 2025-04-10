<?php

namespace App\Http\Requests;

use App\Http\Utils\BasicUtil;
use App\Models\Department;
use App\Models\User;
use App\Models\UserJobHistory;
use App\Rules\ValidUserId;
use Illuminate\Foundation\Http\FormRequest;

class UserJobHistoryUpdateRequest extends BaseFormRequest
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
            'user_id' => [
                'required',
                'numeric',
                new ValidUserId($all_manager_department_ids)
            ],
            'id' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    $exists = UserJobHistory::where('id', $value)
                        ->where('user_job_histories.user_id', '=', $this->user_id)
                        ->exists();

                    if (!$exists) {
                        $fail($attribute . " is invalid.");
                    }
                },
            ],
            'company_name' => 'required|string',
            'country' => 'required|string',
            'job_title' => 'required|string',
            'employment_start_date' => 'required|date',
            'employment_end_date' => 'nullable|date|after_or_equal:employment_start_date',
            'responsibilities' => 'nullable|string',
            'supervisor_name' => 'nullable|string',
            'contact_information' => 'nullable|string',

            'work_location' => 'nullable|string',
            'achievements' => 'nullable|string',
        ];
    }
}
