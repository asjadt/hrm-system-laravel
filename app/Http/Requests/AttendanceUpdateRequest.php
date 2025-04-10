<?php

namespace App\Http\Requests;

use App\Http\Utils\BasicUtil;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Project;
use App\Models\User;
use App\Models\WorkLocation;
use App\Rules\UniqueAttendanceDate;
use App\Rules\ValidProjectId;
use App\Rules\ValidUserId;
use App\Rules\ValidWorkLocationId;
use Illuminate\Foundation\Http\FormRequest;

class AttendanceUpdateRequest extends BaseFormRequest
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
                    $exists = Attendance::where('id', $value)
                    ->where("user_id",$this->user_id)
                        ->exists();
                    if (!$exists) {
                        $fail($attribute . " is invalid.");
                    }
                },
            ],
            'note' => 'nullable|string',
            'in_geolocation' => 'nullable|string',
            'out_geolocation' => 'nullable|string',

            'user_id' => [
                'required',
                'numeric',
                new ValidUserId($all_manager_department_ids),
            ],
            'attendance_records' => 'required|array',
            'attendance_records.*.in_time' => 'required|date_format:H:i:s',
            'attendance_records.*.out_time' => [
                'required',
                'date_format:H:i:s',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1]; 
                    $inTime = request('attendance_records')[$index]['in_time'] ?? false;

                    if ($value !== null && strtotime($value) < strtotime($inTime)) {
                        $fail($attribute . " must be after or equal to in_time.");
                    }


                },
            ],

            'in_date' => [
                'required',
                'date',
                new UniqueAttendanceDate($this->id, $this->user_id),
            ],

            'does_break_taken' => "required|boolean",

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
