<?php

namespace App\Http\Utils;

use App\Models\Department;
use App\Models\ErrorLog;
use App\Models\Leave;
use App\Models\LeaveApproval;
use App\Models\Role;
use App\Models\SettingLeave;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Log;

trait LeaveUtil
{
    use ErrorUtil, BasicUtil;

    public function processLeaveApproval($leave,$is_approved=0) {

        if(auth()->user()->hasRole("business_owner") ) {
            if($is_approved) {
                $leave->status = "approved";
            }else {
                $leave->status = "rejected";
            }
             $leave->save();
             return;

        }
        $user = auth()->user();
        $leave = Leave::where([
            "id" => $leave->id,
            "business_id" => auth()->user()->business_id
        ])
            ->first();


        if (!$leave->employee) {

            throw new Exception("No Employee for the leave found",400);

        }




        $setting_leave = SettingLeave::where([
            "business_id" => auth()->user()->business_id,
            "is_default" => 0
        ])->first();





        if ($setting_leave->approval_level == "single") {




            $special_user_ids = $setting_leave->special_users()->pluck("users.id");
            $is_special_user =  $special_user_ids->contains($user->id);

            if(!$is_special_user){
                $special_role_ids =  $setting_leave->special_roles()->pluck("role_id");

                $role_names = $user->getRoleNames()->toArray();

                $role_ids =  Role::whereIn("name", $role_names)->pluck("roles.id");



                $special_role = $special_role_ids->contains(function ($value) use ($role_ids) {
                    return in_array($value, $role_ids->toArray());
                });

                if(!$special_role) {
                      return ;
            }

            }



            if($is_approved) {
                $leave->status = "approved";
            }else {
                $leave->status = "rejected";
            }







        








        }
        if ($setting_leave->approval_level == "multiple") {





        $all_parent_departments_manager_of_user   = $this->all_parent_departments_manager_of_user($leave->user_id,$user->busines_id);




                 $not_approved_manager_found =   LeaveApproval::where([
                        'leave_id' => $leave->id,
                        'is_approved' => 0,

                    ])
                    ->whereIn("created_by",$all_parent_departments_manager_of_user)
                    ->exists();

                    if(!$not_approved_manager_found) {
                        $leave->status = "rejected";
                    } else {
                        if($is_approved) {
                            $leave->status = "approved";
                        }else {
                            $leave->status = "rejected";
                        }
                    }



            if(auth()->user()->hasRole("business_owner") ) {
                if($is_approved) {
                    $leave->status = "approved";
                }else {
                    $leave->status = "rejected";
                }

            }




        }

        $leave->save();


    }


}
