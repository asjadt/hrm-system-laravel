<?php

namespace App\Http\Utils;

use App\Mail\SpammerFoundMail;
use App\Models\Business;
use App\Models\Department;
use App\Models\EmailerLog;
use App\Models\EmployeePensionHistory;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;

trait EmailLogUtil
{

/**
 * Store email sender log.
 *
 * @param int $user_id The ID of the user.
 * @param bool $is_login_attempts Whether the email is for login attempts or not.
 * @return void
 */


public function storeEmailSender($user_id,$is_login_attempts) {
EmailerLog::create([
    "user_id" => $user_id,
    "is_login_attempts" => $is_login_attempts,
]);
}


/**
 * Check email sender and handle limits.
 *
 * @param int $user_id The ID of the user.
 * @param bool $is_login_attempts Whether the email is for login attempts or not.
 * @return void
 * @throws Exception
 */
public function checkEmailSender($user_id,$is_login_attempts) {

    EmailerLog::where('created_at', '<', Carbon::now()->subMonth())->delete();


    $user = User::where([
        "id" => $user_id
    ])
    ->first();


    if($is_login_attempts) {

        $last_attempts_in_15_minutes = EmailerLog::where([
            "user_id" => $user_id,
            "is_login_attempts" => $is_login_attempts,
        ])
        ->where('created_at', '>=', Carbon::now()->subMinutes(15))
        ->count();

        if($last_attempts_in_15_minutes >= 2) {
          throw new Exception("You can not send more than 2 attemps in 15 minutes",401);
        }

        $last_attempts_today = EmailerLog::where([
            "user_id" => $user_id,
            "is_login_attempts" => $is_login_attempts,
        ])
        ->where('created_at', today())
        ->count();



        if($user->hasRole("business_owner")){
            if($last_attempts_today >= 4) {
                throw new Exception("Please Contact to the Reseller",401);
              }
        }
    } else {


        if(empty($user->business_id)) {

            $last_attempts_today = EmailerLog::where([
                "user_id" => $user_id,
                "is_login_attempts" => $is_login_attempts,
            ])
            ->where('created_at', today())
            ->count();

            if($last_attempts_today >= 50) {
                Mail::to(["asjadtariq@gmail.com","drrifatalashwad0@gmail.com"])->send(new SpammerFoundMail($user));

                throw new Exception("Please Contact to the support",401);
              }


        } else {

            $last_attempts_today = EmailerLog::where([
                "is_login_attempts" => $is_login_attempts,
            ])
            ->whereHas("user", function($query) use($user){
               $query->where("users.business_id",$user->business_id);
            })
            ->where('created_at', today())
            ->count();

           
            if($last_attempts_today >= 50) {
                Mail::to(["asjadtariq@gmail.com","drrifatalashwad0@gmail.com"])->send(new SpammerFoundMail($user));
                throw new Exception("Please Contact to the support",401);
              }
        }

    }

}







}
