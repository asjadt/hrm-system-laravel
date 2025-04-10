<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobType extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        "is_active",
        "is_default",
        "business_id",
        "created_by"
    ];

    public function disabled()
    {
        return $this->hasMany(DisabledJobType::class, 'job_type_id', 'id');
    }


    public function getIsActiveAttribute($value)
    {

        $is_active = $value;
        $user = auth()->user();


        if($user) {
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
        }






        return $is_active;
    }

    public function getIsDefaultAttribute($value)
    {

        $is_default = $value;
        $user = auth()->user();

        if($user) {
            if(!empty($user->business_id)) {
                if(empty($this->business_id) || $user->business_id !=  $this->business_id) {
                      $is_default = 1;

                   }

            }
        }





        return $is_default;
    }




}
