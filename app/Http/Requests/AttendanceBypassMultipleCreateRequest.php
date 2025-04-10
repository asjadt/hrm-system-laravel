<?php

namespace App\Http\Requests;

use App\Http\Utils\BasicUtil;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Project;
use App\Models\User;
use App\Models\WorkLocation;
use App\Rules\ValidUserId;
use App\Rules\ValidWorkLocationId;
use Illuminate\Foundation\Http\FormRequest;

class AttendanceBypassMultipleCreateRequest extends BaseFormRequest
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


        return [
            'user_ids' => 'present|array',
            'user_ids.*' => [
                "numeric",
            ],

            "start_date" => "required|date",
            "end_date" => "required|date|after_or_equal:start_date",

            'work_location_id' => [
                "required",
                'numeric',
                new ValidWorkLocationId
            ],


        ];
    }
}
