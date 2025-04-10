<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingLeaveType extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'type',
        'amount',
        'is_earning_enabled',
        "is_active",
        "is_default",
        "business_id",
        "created_by"
    ];

    public function disabled()
    {
        return $this->hasMany(DisabledSettingLeaveType::class, 'setting_leave_type_id', 'id');
    }


    public function getIsActiveAttribute($value)
    {

        $is_active = $value;
        $user = auth()->user();

        if(empty($user->business_id)) {
            if(empty($this->business_id) && $this->is_default == 1) {
                if(!$user->hasRole("superadmin")) {
                    $disabled = $this->disabled()->where([
                        "created_by" => $user->id
                   ])
                   ->first();
                   if($disabled) {
                      $is_active = 0;
                   }
                }
               }


        } else {

            if(empty($this->business_id)) {
             $disabled = $this->disabled()->where([
                  "business_id" => $user->business_id
             ])
             ->first();
             if($disabled) {
                $is_active = 0;
             }

            }


        }




        return $is_active;
    }

    public function getIsDefaultAttribute($value)
    {

        $is_default = $value;
        $user = auth()->user();

        if(!empty($user->business_id)) {
            if(empty($this->business_id) || $user->business_id !=  $this->business_id) {
                  $is_default = 1;

               }

        }



        return $is_default;
    }



    public function employment_statuses() {
        return $this->belongsToMany(EmploymentStatus::class, 'leave_type_employment_statuses', 'setting_leave_type_id', 'employment_status_id');
    }


   
}
