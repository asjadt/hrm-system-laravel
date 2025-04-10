<?php

namespace App\Http\Requests;

use App\Http\Utils\BasicUtil;
use App\Models\Department;
use App\Models\Project;
use App\Rules\ValidateDepartment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class ProjectUpdateRequest extends BaseFormRequest
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
                function ($attribute, $value, $fail) {
                    $project = Project::where('id', $value)
                        ->where('projects.business_id', '=', auth()->user()->business_id)
                        ->first();

                    if (!$project) {
                        $fail($attribute . " is invalid.");
                    }

                    if (!$project->can_update) {
                        $fail($attribute . " is invalid. You can not update this project.");
                    }


                },
            ],
            'name' => 'required|string',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:pending,in_progress,completed',

            'departments' => 'present|array',
            'departments.*' => [
                'numeric',
                new ValidateDepartment($all_manager_department_ids)
            ],
        ];
    }

    public function messages()
    {
        return [
            'end_date.after_or_equal' => 'End date must be after or equal to the start date.',
            'status.in' => 'Invalid value for status. Valid values are: pending, progress, completed.',
            'department_id.exists' => 'Invalid department selected.',
            
        ];
    }
}
