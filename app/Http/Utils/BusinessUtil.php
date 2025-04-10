<?php

namespace App\Http\Utils;


use App\Models\Business;
use App\Models\BusinessTime;
use App\Models\Department;
use App\Models\EmailTemplate;
use App\Models\Project;
use App\Models\RecruitmentProcess;
use App\Models\Role;
use App\Models\ServicePlanModule;
use App\Models\SettingAttendance;
use App\Models\SettingLeave;
use App\Models\SettingLeaveType;
use App\Models\SettingPaymentDate;
use App\Models\SettingPayrun;
use App\Models\User;
use App\Models\WorkLocation;
use App\Models\WorkShift;
use App\Models\WorkShiftHistory;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


trait BusinessUtil
{
    use BasicUtil, DiscountUtil, SetupUtil;




    public function businessOwnerCheck($business_id, $strict=FALSE)
    {

        $business = Business::where('id', $business_id)
        ->when(
           $strict || !request()->user()->hasRole('superadmin'),
            function ($query)  {
                $query->where(function ($query) {
                    $query

                        ->orWhere('owner_id', auth()->user()->id)
                        ->orWhere('reseller_id', auth()->user()->id)
                        ;
                });
            },
        )
        ->first();

        if (empty($business)) {
            throw new Exception("you are not the owner of the business or the requested business does not exist.", 401);
        }

        return $business;
    }


    public function loadDefaultEmailTemplates($business_id)
    {


        $email_templates = EmailTemplate::where([
            "is_active" => 1,
            "is_default" => 1,
            "business_id" => NULL
        ])->get();


        $transformed_templates = $email_templates->map(function ($template) use($business_id) {
            return [
                "name" => $template->name,
                "type" => $template->type,
                "is_active" => 1,
                "wrapper_id" => 1,
                "is_default" => 0,
                "business_id" => $business_id,
                "template" => $template->template,
                "template_variables" =>  implode(',', $template->template_variables),
                "created_at" => now(),
                "updated_at" => now(),
            ];
        });


        EmailTemplate::insert($transformed_templates->toArray());
    }



    public function loadDefaultSettingLeaveType($business = NULL)
    {
        $defaultSettingLeaveTypes = SettingLeaveType::where(function ($query) use($business) {
            $query->where(function ($query) use($business) {
                $query->where('setting_leave_types.business_id', NULL)
                    ->where('setting_leave_types.is_default', 1)
                    ->where('setting_leave_types.is_active', 1)
                    ->whereDoesntHave("disabled", function ($q) use($business) {
                        $q->whereIn("disabled_setting_leave_types.created_by", [$business->created_by]);
                    });
            })
                ->orWhere(function ($query) use($business) {
                    $query->where('setting_leave_types.business_id', NULL)
                        ->where('setting_leave_types.is_default', 0)
                        ->where('setting_leave_types.created_by', $business->created_by)
                        ->where('setting_leave_types.is_active', 1);
                });
        })
            ->get();




        foreach ($defaultSettingLeaveTypes as $defaultSettingLeave) {
            error_log($defaultSettingLeave);
            $insertableData = [
                'name' => $defaultSettingLeave->name,
                'type' => $defaultSettingLeave->type,
                'amount' => $defaultSettingLeave->amount,
                'is_earning_enabled' => $defaultSettingLeave->is_earning_enabled,
                "is_active" => 1,
                "is_default" => 0,
                "business_id" => $business->id,
            ];

            $setting_leave_type  = SettingLeaveType::create($insertableData);
        }
    }
    public function loadDefaultRecruitmentProcesses($business = NULL)
    {
        $defaultRecruitmentProcesses = RecruitmentProcess::where(function ($query) use($business) {
            $query->where(function ($query) use($business) {
                $query->where('recruitment_processes.business_id', NULL)
                    ->where('recruitment_processes.is_default', 1)
                    ->where('recruitment_processes.is_active', 1)
                    ->whereDoesntHave("disabled", function ($q) use($business) {
                        $q->whereIn("recruitment_processes.created_by", [$business->created_by]);
                    });
            })
                ->orWhere(function ($query) use($business) {
                    $query->where('recruitment_processes.business_id', NULL)
                        ->where('recruitment_processes.is_default', 0)
                        ->where('recruitment_processes.created_by', $business->created_by)
                        ->where('recruitment_processes.is_active', 1);
                });
        })
            ->get();




        foreach ($defaultRecruitmentProcesses as $defaultRecruitmentProcess) {
            error_log($defaultRecruitmentProcess);
            $insertableData = [
                'name' => $defaultRecruitmentProcess->name,
                'description'=> $defaultRecruitmentProcess->name,
                "is_active" => 1,
                "is_default" => 0,
                "business_id" => $business->id,
                "use_in_employee" => $defaultRecruitmentProcess->use_in_employee,
                "use_in_on_boarding" => $defaultRecruitmentProcess->use_in_on_boarding,
                "is_required" => $defaultRecruitmentProcess->is_required,

                "employee_order_no" => $defaultRecruitmentProcess->employee_order_no,
                "candidate_order_no" => $defaultRecruitmentProcess->candidate_order_no,
                "created_by" => $business->created_by,
            ];

            $recruitment_process  = RecruitmentProcess::create($insertableData);
        }
    }


    public function loadDefaultSettingLeave($business = NULL)
    {


        $default_setting_leave_query = [
            "business_id" => NULL,
            "is_active" => 1,
            "is_default" =>  1,
        ];



        $defaultSettingLeaves = SettingLeave::where($default_setting_leave_query)->get();

        $auth_user = auth()->user();


        if ($defaultSettingLeaves->isEmpty() && !empty($auth_user) && !auth()->user()->hasRole("superadmin")) {
            unset($default_setting_leave_query['created_by']);
            $defaultSettingLeaves = SettingLeave::where($default_setting_leave_query)->get();
        }




        foreach ($defaultSettingLeaves as $defaultSettingLeave) {
            error_log($defaultSettingLeave);
            $insertableData = [
                'start_month' => $defaultSettingLeave->start_month,
                'approval_level' => $defaultSettingLeave->approval_level,
                'allow_bypass' => $defaultSettingLeave->allow_bypass,
                "created_by" => $business->created_by,
                "is_active" => 1,
                "is_default" => 0,
                "business_id" => $business->id,
            ];


            $setting_leave  = SettingLeave::create($insertableData);

            $business_owner_role_id = Role::where([
                "name" => ("business_owner#" . $business->id)
            ])
                ->pluck("id");

            $setting_leave->special_roles()->sync($business_owner_role_id);


            $default_paid_leave_employment_statuses = $defaultSettingLeave->paid_leave_employment_statuses()->pluck("employment_status_id");
            $setting_leave->paid_leave_employment_statuses()->sync($default_paid_leave_employment_statuses);

            $default_unpaid_leave_employment_statuses = $defaultSettingLeave->unpaid_leave_employment_statuses()->pluck("employment_status_id");
            $setting_leave->unpaid_leave_employment_statuses()->sync($default_unpaid_leave_employment_statuses);
        }


    }


    public function loadDefaultAttendanceSetting($business = NULL)
    {


        $default_setting_attendance_query = [
            "business_id" => NULL,
            "is_active" => 1,
            "is_default" =>  1,
        ];



        $defaultSettingAttendances = SettingAttendance::where($default_setting_attendance_query)->get();


        if ($defaultSettingAttendances->isEmpty() && !auth()->user()->hasRole("superadmin")) {
            unset($default_setting_attendance_query['created_by']);
            $default_setting_attendance_query["is_default"] = 1;
            $defaultSettingAttendances = SettingAttendance::where($default_setting_attendance_query)->get();
        }







        foreach ($defaultSettingAttendances as $defaultSettingAttendance) {
            Log::info(json_encode($defaultSettingAttendance));
            $insertableData = [
                'punch_in_time_tolerance' => $defaultSettingAttendance->punch_in_time_tolerance,
                'work_availability_definition' => $defaultSettingAttendance->work_availability_definition,
                'punch_in_out_alert' => $defaultSettingAttendance->punch_in_out_alert,
                'punch_in_out_interval' => $defaultSettingAttendance->punch_in_out_interval,
                'alert_area' => $defaultSettingAttendance->alert_area,
                'auto_approval' => $defaultSettingAttendance->auto_approval,
                'is_geolocation_enabled' => $defaultSettingAttendance->is_geolocation_enabled,


                'service_name' => $defaultSettingAttendance->service_name,
                'api_key' => $defaultSettingAttendance->api_key,

                "created_by" => $business->created_by,
                "is_active" => 1,
                "is_default" => 0,
                "business_id" => $business->id,







            ];

            $setting_attendance  = SettingAttendance::create($insertableData);




            $business_owner_role_id = Role::where([
                "name" => ("business_owner#" . $business->id)
            ])
                ->pluck("id");
            $setting_attendance->special_roles()->sync($business_owner_role_id);
        }



    }
    public function loadDefaultPayrunSetting($business = NULL)
    {


        $default_setting_payrun_query = [
            "business_id" => NULL,
            "is_active" => 1,
            "is_default" => 1,
        ];


        $defaultSettingPayruns = SettingPayrun::where($default_setting_payrun_query)->get();


        if ($defaultSettingPayruns->isEmpty() && !auth()->user()->hasRole("superadmin")) {
            unset($default_setting_payrun_query['created_by']);
            $defaultSettingPayruns = SettingPayrun::where($default_setting_payrun_query)->get();
        }


        foreach ($defaultSettingPayruns as $defaultSettingPayrun) {
            $insertableData = [
                'payrun_period' => $defaultSettingPayrun->payrun_period,
                'consider_type' => $defaultSettingPayrun->consider_type,
                'consider_overtime' => $defaultSettingPayrun->consider_overtime,

                "created_by" => $business->created_by,
                "is_active" => 1,
                "is_default" => 0,
                "business_id" => $business->id,
            ];

            $setting_payrun  = SettingPayrun::create($insertableData);




        }
    }

    public function loadDefaultPaymentDateSetting($business = null)
    {


        $default_setting_payment_date_query = [
            'business_id' => null,
            'is_active' => 1,
            'is_default' =>  1,
        ];



        $defaultSettingPaymentDates = SettingPaymentDate::where($default_setting_payment_date_query)->get();


        if ($defaultSettingPaymentDates->isEmpty() && !auth()->user()->hasRole('superadmin')) {
            unset($default_setting_payment_date_query['created_by']);
            $defaultSettingPaymentDates = SettingPaymentDate::where($default_setting_payment_date_query)->get();
        }

        foreach ($defaultSettingPaymentDates as $defaultSettingPaymentDate) {
            $insertableData = [
                'payment_type' => $defaultSettingPaymentDate->payment_type,
                'day_of_week' => $defaultSettingPaymentDate->day_of_week,
                'day_of_month' => $defaultSettingPaymentDate->day_of_month,
                'custom_frequency_interval' => $defaultSettingPaymentDate->custom_frequency_interval,
                'custom_frequency_unit' => $defaultSettingPaymentDate->custom_frequency_unit,
                'notification_delivery_status' => $defaultSettingPaymentDate->notification_delivery_status,
                'is_active' => 1,
                'is_default' => 0,
                'business_id' => $business->id,
                'created_by' => $business->created_by,
                'role_specific_settings' => $defaultSettingPaymentDate->role_specific_settings,
            ];

            $settingPaymentDate = SettingPaymentDate::create($insertableData);


        }
    }


    public function loadDefaultEmailTemplate($business = null)
    {



        $default_email_template_query = [
            'business_id' => null,
            'is_active' => 1,
            'is_default' =>  1,
        ];



        $defaultEmailTemplates = EmailTemplate::where($default_email_template_query)->get();


        foreach ($defaultEmailTemplates as $defaultEmailTemplate) {
            $insertableData = [
                "name" => $defaultEmailTemplate->name,
                "type" => $defaultEmailTemplate->name,
                "template" => $defaultEmailTemplate->name,
                "is_active" => 1,
                "is_default" => 0,
                "business_id" => $business->id,
                'wrapper_id' => $defaultEmailTemplate->wrapper_id,
            ];

            $emailTemplate = EmailTemplate::create($insertableData);


        }
    }



    public function storeDefaultsToBusiness($business)
    {



        if(empty($business->enable_auto_business_setup)) {
            $work_location =  WorkLocation::create([
                'name' => ($business->name . " " . "Office"),
                "is_active" => 1,
                "is_default" => 0,
                "business_id" => $business->id,
                "created_by" => $business->owner_id
            ]);
            $department =  Department::create([
                "name" => $business->name,
                "location" => $business->address_line_1,
                "is_active" => 1,
                "manager_id" => $business->owner_id,
                "business_id" => $business->id,
                "work_location_id" => $work_location->id,
                "created_by" => $business->owner_id
            ]);
            Project::create([
                'name' => $business->name,
                'description',
                'start_date' => $business->start_date,
                'end_date' => NULL,
                'status' => "in_progress",
                "is_active" => 1,
                "is_default" => 1,
                "business_id" => $business->id,
                "created_by" => $business->owner_id
            ]);

            $default_work_shift_data = [
                'name' => 'Default work shift',
                'type' => 'regular',
                'description' => '',
                'is_personal' => false,
                'break_type' => 'unpaid',
                'break_hours' => 1,

                'details' => $business->times->toArray(),
                "is_business_default" => 1,
                "is_active"=>1,
                "is_default" => 1,
                "business_id" => $business->id,
            ];

            $default_work_shift = WorkShift::create($default_work_shift_data);
            $default_work_shift->details()->createMany($default_work_shift_data['details']);
            $default_work_shift->departments()->sync([$department->id]);



            $employee_work_shift_history_data = $default_work_shift->toArray();
            $employee_work_shift_history_data["work_shift_id"] = $default_work_shift->id;
            $employee_work_shift_history_data["from_date"] = $business->start_date;
            $employee_work_shift_history_data["to_date"] = NULL;
            $employee_work_shift_history =  WorkShiftHistory::create($employee_work_shift_history_data);
            $employee_work_shift_history->details()->createMany($default_work_shift_data['details']);
            $employee_work_shift_history->departments()->sync([$department->id]);


        }








        $defaultRoles = Role::where([
            "business_id" => NULL,
            "is_default" => 1,
            "is_default_for_business" => 1,
            "guard_name" => "api",
        ])->get();



        foreach ($defaultRoles as $defaultRole) {
            $insertableData = [
                'name'  => ($defaultRole->name . "#" . $business->id),
                "is_default" => 1,
                "business_id" => $business->id,
                "is_default_for_business" => 0,
                "guard_name" => "api",
            ];
            $role  = Role::create($insertableData);


            $permissions = $defaultRole->permissions;
            foreach ($permissions as $permission) {
                if (!$role->hasPermissionTo($permission)) {
                    $role->givePermissionTo($permission);
                }
            }
        }



        $this->loadDefaultSettingLeave($business);

        $this->loadDefaultAttendanceSetting($business);

        $this->loadDefaultPayrunSetting($business);

        $this->loadDefaultPaymentDateSetting($business);


    }







    public function businessImageStore($business,$business_id=NULL)
    {
        if (!empty($business["images"])) {
            $business["images"] = $this->storeUploadedFiles($business["images"], "", "business_images",NULL,$business_id);
            $this->makeFilePermanent($business["images"], "");
        }
        if (!empty($business["image"])) {
            $business["image"] = $this->storeUploadedFiles([$business["image"]], "", "business_images",NULL,$business_id)[0];
            $this->makeFilePermanent([$business["image"]], "");
        }
        if (!empty($business["logo"])) {
            $business["logo"] = $this->storeUploadedFiles([$business["logo"]], "", "business_images",NULL,$business_id)[0];
            $this->makeFilePermanent([$business["logo"]], "");
        }
        if (!empty($business["background_image"])) {
            $business["background_image"] = $this->storeUploadedFiles([$business["background_image"]], "", "business_images",NULL,$business_id)[0];
            $this->makeFilePermanent([$business["background_image"]], "");
        }
        return $business;
    }



    public function businessImageRollBack($request_data)
    {
        if (!empty($request_data["business"]["images"])) {
            try {

                $this->moveUploadedFilesBack($request_data["business"]["images"], "", "business_images");
            } catch (Exception $innerException) {
                error_log("Failed to move recruitment processes files back: " . $innerException->getMessage());
            }
        }

        if (!empty($request_data["business"]["image"])) {
            try {

                $this->moveUploadedFilesBack($request_data["business"]["image"], "", "business_images");
            } catch (Exception $innerException) {
                error_log("Failed to move recruitment processes files back: " . $innerException->getMessage());
            }
        }
        if (!empty($request_data["business"]["logo"])) {
            try {

                $this->moveUploadedFilesBack($request_data["business"]["logo"], "", "business_images");
            } catch (Exception $innerException) {
                error_log("Failed to move recruitment processes files back: " . $innerException->getMessage());
            }
        }

        if (!empty($request_data["business"]["background_image"])) {
            try {

                $this->moveUploadedFilesBack($request_data["business"]["background_image"], "", "business_images");
            } catch (Exception $innerException) {
                error_log("Failed to move recruitment processes files back: " . $innerException->getMessage());
            }
        }
    }




    public function createUserWithBusiness($request_data)
    {


        $password = $request_data['user']['password'];


        $request_data['user']['password'] = Hash::make($request_data['user']['password']);

        $request_data['user']['remember_token'] = Str::random(10);
        $request_data['user']['is_active'] = true;


        $request_data['user']['address_line_1'] = $request_data['business']['address_line_1'];
        $request_data['user']['address_line_2'] = (!empty($request_data['business']['address_line_2']) ? $request_data['business']['address_line_2'] : "");
        $request_data['user']['country'] = $request_data['business']['country'];
        $request_data['user']['city'] = $request_data['business']['city'];
        $request_data['user']['postcode'] = $request_data['business']['postcode'];
        $request_data['user']['lat'] = $request_data['business']['lat'];
        $request_data['user']['long'] = $request_data['business']['long'];




        $user =  User::create($request_data['user']);

        if (!auth()->check()) {
            Auth::login($user);

        }




        $user->assignRole('business_owner');

        $created_by_user = auth()->user();

        if (empty($created_by_user)) {

            if(!empty($request_data['business']['reseller_id'])){
              $created_by_user = $request_data['business']['reseller_id'];
            } else {

              $created_by_user = User::permission(['handle_self_registered_businesses'])->first();

            }

            $request_data["business"]["number_of_employees_allowed"] = 0;
        }
        if(empty($request_data['business']['reseller_id'])){
            $request_data['business']['reseller_id'] = $created_by_user->id;
        }


        if(empty($request_data["business"]["number_of_employees_allowed"])){
            $request_data["business"]["number_of_employees_allowed"] = 0;
        }




        $request_data['business']['status'] = "pending";
        $request_data['business']['owner_id'] = $user->id;
        $request_data['business']['created_by'] = $created_by_user->id;
        $request_data['business']['is_active'] = true;
        $request_data['business']["pension_scheme_letters"] = [];
        $request_data['business']['service_plan_discount_amount'] = $this->getDiscountAmount($request_data['business']);



        $business =  Business::create($request_data['business']);




        $user->email_verified_at = now();
        $user->business_id = $business->id;
        $token = Str::random(30);
        $user->resetPasswordToken = $token;
        $user->resetPasswordExpires = Carbon::now()->subDays(-1);
        $user->created_by = $created_by_user->id;
        $user->save();

        BusinessTime::where([
            "business_id" => $business->id
        ])
            ->delete();
        $timesArray = collect($request_data["times"])->unique("day");
        foreach ($timesArray as $business_time) {
            BusinessTime::create([
                "business_id" => $business->id,
                "day" => $business_time["day"],
                "start_at" => $business_time["start_at"],
                "end_at" => $business_time["end_at"],
                "is_weekend" => $business_time["is_weekend"],
            ]);
        }

        $this->storeDefaultsToBusiness($business);






        if (env("SEND_EMAIL") == true) {
            $this->checkEmailSender($user->id, 0);

          

        }

        $business->service_plan = $business->service_plan;

        return [
            "user" => $user,
            "business" => $business
        ];
    }
}
