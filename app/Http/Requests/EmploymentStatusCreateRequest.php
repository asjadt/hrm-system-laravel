<?php

namespace App\Http\Requests;

use App\Models\EmploymentStatus;
use App\Rules\ValidEmploymentStatusName;
use Illuminate\Foundation\Http\FormRequest;

class EmploymentStatusCreateRequest extends BaseFormRequest
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
            'name' => [
                "required",
                'string',
                new ValidEmploymentStatusName(NULL)
            ],
            'description' => 'nullable|string',
            'color' => 'required|string',
        ];

 


return $rules;








    }
}
