<?php

namespace App\Http\Requests;

use App\Rules\ValidSettingLeaveType;
use Illuminate\Foundation\Http\FormRequest;

class LeaveSelfCreateRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }


    public function rules()
    {

        return [
            'leave_duration' => 'required|in:single_day,multiple_day,half_day,hours',
            'day_type' => 'nullable|in:first_half,last_half',

            'leave_type_id' => [
                'required',
                'numeric',
                new ValidSettingLeaveType($this->user_id,NULL),
            ],




            'date' => 'nullable|required_if:leave_duration,single_day,half_day,hours|date',
            'note' => 'required|string',
            'start_date' => 'nullable|required_if:leave_duration,multiple_day|date',
            'end_date' => 'nullable|required_if:leave_duration,multiple_day|date|after_or_equal:start_date',
            'start_time' => 'nullable|required_if:leave_duration,hours|date_format:H:i:s',
            'end_time' => 'nullable|required_if:leave_duration,hours|date_format:H:i:s|after_or_equal:start_time',
            'attachments' => 'present|array',
            'attachments.*' => 'string',
            "hourly_rate" => "required|numeric"
        ];
    }

    public function messages()
{
    return [
        'leave_duration.required' => 'The leave duration field is required.',
        'leave_duration.in' => 'Invalid value for leave duration. Valid values are: single_day, multiple_day, half_day, hours.',
        'day_type.in' => 'Invalid value for day type. Valid values are: first_half, last_half.',
  
    ];
}

}
