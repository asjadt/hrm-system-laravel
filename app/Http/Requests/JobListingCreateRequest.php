<?php

namespace App\Http\Requests;

use App\Http\Utils\BasicUtil;
use App\Models\Department;
use App\Models\JobPlatform;
use App\Models\JobType;
use App\Models\WorkLocation;
use App\Rules\ValidateDepartment;
use App\Rules\ValidateJobPlatform;
use App\Rules\ValidateJobType;
use App\Rules\ValidWorkLocationId;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class JobListingCreateRequest extends BaseFormRequest
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
            'title' => 'required|string',
            'description' => 'required|string',

            'minimum_salary' => 'required|numeric',
            'maximum_salary' => 'required|numeric',
            'experience_level' => 'required|string',
            'required_skills' => 'required|string',
            'application_deadline' => 'required|date',
            'posted_on' => 'required|date',

            'job_platforms' => 'required|array',

            'job_platforms.*' => [
                "required",
                'numeric',
                new ValidateJobPlatform(),
            ],




            'department_id' => [
                'nullable',
                'numeric',
                new ValidateDepartment($all_manager_department_ids)
            ],


            'job_type_id' => [
                "required",
                'numeric',
              new ValidateJobType()

            ],
            'work_location_id' => [
                "required",
                'numeric',
                new ValidWorkLocationId()
            ],



        ];
    }
}
