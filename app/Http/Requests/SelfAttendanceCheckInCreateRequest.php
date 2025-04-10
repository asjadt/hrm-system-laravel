<?php

namespace App\Http\Requests;

use App\Models\Attendance;
use App\Rules\UniqueAttendanceDate;
use App\Rules\ValidProjectId;
use App\Rules\ValidWorkLocationId;
use Illuminate\Foundation\Http\FormRequest;

class SelfAttendanceCheckInCreateRequest extends FormRequest
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
        return [



            'in_geolocation' => 'required|string',

            'attendance_records' => 'required|array',

            'attendance_records.*.in_latitude' => 'nullable|string',
            'attendance_records.*.in_longitude' => 'nullable|string',
            'attendance_records.*.in_ip_address' => 'nullable|string',

            'attendance_records.*.out_latitude' => 'nullable|string',
            'attendance_records.*.out_longitude' => 'nullable|string',
            'attendance_records.*.out_ip_address' => 'nullable|string',




            'attendance_records.*.in_time' => 'required|date_format:H:i:s',

            'in_date' => [
                'required',
                'date',
                new UniqueAttendanceDate(NULL, $this->user_id),
            ],

            "project_ids" => "present|array",

            'project_ids.*' => [
                'numeric',
                new ValidProjectId,
            ],



            'work_location_id' => [
                "required",
                'numeric',
                new ValidWorkLocationId
            ],
        ];
    }
}
