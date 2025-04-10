<?php

namespace App\Http\Requests;

use App\Http\Utils\BasicUtil;
use App\Models\BusinessTime;
use App\Models\Department;
use App\Models\Designation;

use App\Models\RecruitmentProcess;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkLocation;
use App\Models\WorkShift;
use App\Rules\ValidateDepartment;
use App\Rules\ValidateDesignationId;
use App\Rules\ValidateRecruitmentProcessId;
use App\Rules\ValidEmploymentStatus;
use App\Rules\ValidUserId;
use App\Rules\ValidWorkLocationId;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class UserUpdateV2Request extends BaseFormRequest
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
                "required",
                "numeric",
                new ValidUserId($all_manager_department_ids),
            ],
            'first_Name' => 'required|string|max:255',
            'middle_Name' => 'nullable|string|max:255',

            'last_Name' => 'required|string|max:255',
            "NI_number" => "required|string",

            'user_id' => [
                "required",
                'string',
                function ($attribute, $value, $fail) {
                    $user_id_exists =  User::
                    whereNotIn("id",[$this->id])
                    ->where([
                        'user_id'=> $value,
                        "created_by" => auth()->user()->id
                     ]
                     )->exists();
                     if ($user_id_exists){
                          $fail("The employee id has already been taken.");
                       }


                },
            ],


            'email' => 'required|string|unique:users,email,' . $this->id . ',id',

            'password' => 'nullable|string|min:6',
            'phone' => 'nullable|string',
            'image' => 'nullable|string',
            'address_line_1' => 'required|string',
            'address_line_2' => 'nullable',
            'country' => 'required|string',
            'city' => 'required|string',
            'postcode' => 'nullable|string',
            'lat' => 'nullable|string',
            'long' => 'nullable|string',

            'role' => [
                "required",
                'string',
                function ($attribute, $value, $fail) {
                    $role  = Role::where(["name" => $value])->first();


                    if (!$role){

                             $fail("Role does not exists.");
                             return;

                    }

                    if(!empty(auth()->user()->business_id)) {
                        if (empty($role->business_id)){

                          $fail("You don't have this role");
                          return;

                      }
                        if ($role->business_id != auth()->user()->business_id){

                            $fail("You don't have this role");
                            return;

                        }
                    } else {
                        if (!empty($role->business_id)){

                          $fail("You don't have this role");
                          return;

                      }
                    }


                },
            ],



            'work_shift_id' => [
                "nullable",
                'numeric',
                function ($attribute, $value, $fail) {


                    if(!empty($value)){


                         $business_times =    BusinessTime::where([
                    "is_weekend" => 1,
                    "business_id" => auth()->user()->business_id,
                ])->get();


                        $exists = WorkShift::where('id', $value)
                        ->where([
                            "work_shifts.business_id" => auth()->user()->business_id
                        ])
                        ->orWhere(function($query) use($business_times) {
                            $query->where([
                                "is_active" => 1,
                                "business_id" => NULL,
                                "is_default" => 1
                            ])

                        ;

                        })

                        ->exists();

                    if (!$exists) {
                        $fail($attribute . " is invalid.");
                    }
                    }

                },
            ],

            'departments' => 'required|array|size:1',
            'departments.*' =>  [
                'numeric',
                new ValidateDepartment($all_manager_department_ids)
            ],
            'recruitment_processes' => "present|array",
            'recruitment_processes.*.recruitment_process_id' => [
                "required",
                'numeric',
                new ValidateRecruitmentProcessId()
            ],
            'recruitment_processes.*.description' => "nullable|string",
            'recruitment_processes.*.attachments' => "present|array",


        'work_location_ids' => [
            "present",
            'array',
        ],

        "work_location_ids.*" =>[
            "numeric",
        new ValidWorkLocationId()],




            'gender' => 'nullable|string|in:male,female,other',
            'is_in_employee' => "required|boolean",
            'designation_id' => [
                "required",
                'numeric',
                new ValidateDesignationId()
            ],
            'employment_status_id' => [
                "required",
                'numeric',
                new ValidEmploymentStatus()
            ],

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
            'date_of_birth' => "required|date",
            'salary_per_annum' => "required|numeric",
            'weekly_contractual_hours' => 'required|numeric',
            "minimum_working_days_per_week" => 'required|numeric|max:7',
            "overtime_rate" => 'nullable|numeric',
            'emergency_contact_details' => "present|array",



            "immigration_status" => "required|in:british_citizen,ilr,immigrant,sponsored",

            'is_sponsorship_offered' => "nullable|boolean",


            "is_active_visa_details" => "required|boolean",
            "is_active_right_to_works" => "required|boolean",





            "sponsorship_details.date_assigned" => 'nullable|required_if:immigration_status,sponsored|date',
            "sponsorship_details.expiry_date" => 'nullable|required_if:immigration_status,sponsored|date',

            "sponsorship_details.note" => 'nullable|required_if:immigration_status,sponsored|string',
            "sponsorship_details.certificate_number" => 'nullable|required_if:immigration_status,sponsored|string',
            "sponsorship_details.current_certificate_status" => 'nullable|required_if:immigration_status,sponsored|in:unassigned,assigned,visa_applied,visa_rejected,visa_grantes,withdrawal',
            "sponsorship_details.is_sponsorship_withdrawn" => 'nullable|required_if:immigration_status,sponsored|boolean',





            'passport_details.passport_number' => 'nullable|required_if:immigration_status,sponsored,immigrant|string',
            'passport_details.passport_issue_date' => 'nullable|required_if:immigration_status,sponsored,immigrant|date',
            'passport_details.passport_expiry_date' => 'nullable|required_if:immigration_status,sponsored,immigrant|date',
            'passport_details.place_of_issue' => 'nullable|required_if:immigration_status,sponsored,immigrant|string',




            'visa_details.BRP_number' => 'nullable|required_if:is_active_visa_details,1|string',
            'visa_details.visa_issue_date' => 'nullable|required_if:is_active_visa_details,1|date',
            'visa_details.visa_expiry_date' => 'nullable|required_if:is_active_visa_details,1|date',
            'visa_details.place_of_issue' => 'nullable|required_if:is_active_visa_details,1|string',
            'visa_details.visa_docs' => 'nullable|required_if:is_active_visa_details,1|array',
            'visa_details.visa_docs.*.file_name' => 'nullable|required_if:is_active_visa_details,1|string',
            'visa_details.visa_docs.*.description' => 'nullable|string',



            'right_to_works.right_to_work_code' => 'nullable|required_if:is_active_right_to_works,1|string',
            'right_to_works.right_to_work_check_date' => 'nullable|required_if:is_active_right_to_works,1|date',
            'right_to_works.right_to_work_expiry_date' => 'nullable|required_if:is_active_right_to_works,1|date',
            'right_to_works.right_to_work_docs' => 'nullable|required_if:is_active_right_to_works,1|array',
            'right_to_works.right_to_work_docs.*.file_name' => 'nullable|required_if:is_active_right_to_works,1|string',
            'right_to_works.right_to_work_docs.*.description' => 'nullable|string',

        ];
    }

    public function messages()
    {
        return [

            'immigration_status.in' => 'Invalid value for status. Valid values are: british_citizen, ilr, immigrant, sponsored.',

            'sponsorship_details.current_certificate_status.in' => 'Invalid value for status. Valid values are: unassigned,assigned,visa_applied,visa_rejected,visa_grantes,withdrawal.',

    
        ];
    }
}
