<?php

namespace App\Http\Requests;

use App\Models\Attendance;
use App\Rules\UniqueAttendanceDate;
use App\Rules\ValidProjectId;
use App\Rules\ValidWorkLocationId;
use Illuminate\Foundation\Http\FormRequest;

class SelfAttendanceCheckOutCreateRequest extends FormRequest
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
            'id' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    $exists = Attendance::where('id', $value)
                    ->where("user_id",auth()->user()->id)
                        ->exists();
                    if (!$exists) {
                        $fail($attribute . " is invalid.");
                    }
                },
            ],

            "project_ids" => "present|array",

            'project_ids.*' => [
                'numeric',
                new ValidProjectId,
            ],

            'note' => 'nullable|string',
            'out_geolocation' => 'required|string',
            'attendance_records' => 'required|array',

            'attendance_records.*.note' => 'nullable|string',

            'attendance_records.*.in_latitude' => 'nullable|string',
            'attendance_records.*.in_longitude' => 'nullable|string',
            'attendance_records.*.in_ip_address' => 'nullable|string',

            'attendance_records.*.out_latitude' => 'nullable|string',
            'attendance_records.*.out_longitude' => 'nullable|string',
            'attendance_records.*.out_ip_address' => 'nullable|string',


            'attendance_records.*.in_time' => 'required|date_format:H:i:s',
            'attendance_records.*.out_time' => [
                'nullable',
                'date_format:H:i:s',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1]; 
                    $inTime = request('attendance_records')[$index]['in_time'] ?? false;

                    if ($value !== null && strtotime($value) < strtotime($inTime)) {
                        $fail($attribute . " must be after or equal to in_time.");
                    }


                },
            ],
            'does_break_taken' => "required|boolean",
            'break_hours' => "required|numeric",




        ];

    }
}
