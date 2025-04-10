<?php

namespace App\Http\Requests;

use App\Models\SettingLeaveType;
use App\Rules\UniqueSettingLeaveTypeName;
use App\Rules\ValidEmploymentStatus;
use Illuminate\Foundation\Http\FormRequest;

class SettingLeaveTypeCreateRequest extends BaseFormRequest
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
                new UniqueSettingLeaveTypeName(NULL),
            ],
            'type' => 'required|string|in:paid,unpaid',
            'amount' => 'required|numeric',
            'is_active' => 'required|boolean',
            'is_earning_enabled' => 'required|boolean',




            "employment_statuses" => "present|array",
            'employment_statuses.*' => [
                'numeric',
                new ValidEmploymentStatus()
            ],



        ];

   

           return $rules;


    }

    public function messages()
    {
        return [
            'type.in' => 'The :attribute field must be either "paid" or "unpaid".',
        ];
    }
}
