<?php

namespace App\Http\Requests;

use App\Models\RecruitmentProcess;
use App\Rules\ValidateRecruitmentProcessName;
use Illuminate\Foundation\Http\FormRequest;

class RecruitmentProcessCreateRequest extends BaseFormRequest
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
            'description' => 'nullable|string',
            'name' => [
                "required",
                'string',
                new   ValidateRecruitmentProcessName($this->id)

            ],
            "use_in_employee" => "required|boolean",
            "use_in_on_boarding" => "required|boolean",




        ];


return $rules;
    }
}
