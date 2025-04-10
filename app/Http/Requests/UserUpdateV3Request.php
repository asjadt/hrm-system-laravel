<?php

namespace App\Http\Requests;

use App\Http\Utils\BasicUtil;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkShift;
use App\Rules\ValidateDepartment;
use App\Rules\ValidateDesignationId;
use App\Rules\ValidEmploymentStatus;
use App\Rules\ValidUserId;
use App\Rules\ValidWorkLocationId;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class UserUpdateV3Request extends FormRequest
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

        $user = User::where([
            'id' => $this->id,
        ])->first();




        $rule = [
            'id' => [
                "required",
                "numeric",
                new ValidUserId($all_manager_department_ids),
            ],
            'first_Name' => 'required|string|max:255',
            'middle_Name' => 'nullable|string|max:255',
            'last_Name' => 'required|string|max:255',
            'email' => 'required|string|unique:users,email,' . $this->id . ',id',
            'phone' => 'nullable|string',
            'image' => 'nullable|string',
            'date_of_birth' => "required|date",
            "NI_number" => "required|string",
            'joining_date' => [
                "required",
                'date',
                function ($attribute, $value, $fail) {

                   $joining_date = Carbon::parse($value);
                   $start_date = Carbon::parse(auth()->user()->business->start_date);

                   if ($joining_date->lessThan($start_date)) {
                       $fail("The $attribute must not be before the start date of the business.");
                   }

                },
            ],

            'salary_per_annum' => "required|numeric",
            "overtime_rate" => 'nullable|numeric',
            "minimum_working_days_per_week" => 'required|numeric|max:7',
            'weekly_contractual_hours' => 'required|numeric',
            'gender' => 'nullable|string|in:male,female,other',


            'work_location_ids' => [
                "required",
                'array',
            ],

            "work_location_ids.*" =>[
                "present",
            new ValidWorkLocationId()],

            'designation_id' => [
                "required",
                'numeric',

            ],

            'employment_status_id' => [
                "required",
                'numeric',
            ],

            'work_shift_id' => [
                "nullable",
                'numeric',

            ],

            'departments' => 'required|array|size:1',
            'departments.*' =>  [
                'numeric',
                new ValidateDepartment($all_manager_department_ids)
            ],

        ];

        if(!empty($user)) {


            if($user->designation_id != $this->designation_id){
                $rule["designation_id"][] =  new ValidateDesignationId();
             }

             if($user->employment_status_id != $this->employment_status_id){
                $rule["employment_status_id"][] =  new ValidEmploymentStatus();
             }

             if($user->work_shift_id != $this->work_shift_id){
                $rule["work_shift_id"][] =    function ($attribute, $value, $fail) {
                    if(!empty($value)){
                        $exists = WorkShift::where('id', $value)
                        ->where([
                            "work_shifts.business_id" => auth()->user()->business_id
                        ])
                        ->orWhere(function($query)  {
                            $query->where([
                                "is_active" => 1,
                                "business_id" => NULL,
                                "is_default" => 1
                            ]);
                        })
                        ->exists();
                    if (!$exists) {
                        $fail($attribute . " is invalid.");
                    }
                    }

                };
             }
        }

        return $rule;
    }
}
