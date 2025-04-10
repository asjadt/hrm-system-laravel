<?php

namespace App\Http\Requests;

use App\Models\JobPlatform;
use App\Rules\ValidateJobPlatformName;
use Illuminate\Foundation\Http\FormRequest;

class JobPlatformUpdateRequest extends BaseFormRequest
{
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
        $rules = [
            'id' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {

                    $job_platform_query_params = [
                        "id" => $this->id,
                    ];
                    $job_platform = JobPlatform::where($job_platform_query_params)
                        ->first();
                    if (!$job_platform) {

                            $fail("no job platform found");
                            return 0;

                    }
                    if (empty(auth()->user()->business_id)) {

                        if(auth()->user()->hasRole('superadmin')) {
                            if(($job_platform->business_id != NULL || $job_platform->is_default != 1)) {

                                $fail("You do not have permission to update this job platform due to role restrictions.");

                          }

                        } else {
                            if(($job_platform->business_id != NULL || $job_platform->is_default != 0 || $job_platform->created_by != auth()->user()->id)) {

                                $fail("You do not have permission to update this job platform due to role restrictions.");

                          }
                        }

                    } else {
                        if(($job_platform->business_id != auth()->user()->business_id || $job_platform->is_default != 0)) {

                            $fail("You do not have permission to update this job platform due to role restrictions.");
                        }
                    }

                },


            ],
            'name' => [
                "required",
                'string',
                new ValidateJobPlatformName($this->id)
            ],

            'description' => 'nullable|string',
        ];

      

return $rules;
    }
}
