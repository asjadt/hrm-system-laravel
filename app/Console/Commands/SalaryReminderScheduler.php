<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\SettingPaymentDate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SalaryReminderScheduler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salary_reminder:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send salary reminder';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */

    public function getWeeklyDateRange($dayOfWeek) {

        $today = Carbon::today();


        $diff = $today->dayOfWeek - $dayOfWeek;


        if ($diff < 0) {
            $diff += 7;
        }


        $lastDay = $today->subDays($diff);

        $prevLastDay = $lastDay->copy()->subWeek();


        return [
            'last_day' => $lastDay->toDateString(),
            'previous_last_day' => $prevLastDay->toDateString()
        ];
    }

    public function getMonthlyDateRange($dayOfMonth) {

        $today = Carbon::today();


        $lastDayOfMonth = $today->endOfMonth();


        if ($dayOfMonth > $lastDayOfMonth->day) {
            $today->addMonth();
        }


        $today->day($dayOfMonth);


        $lastDay = $today->copy();


        $prevLastDay = $lastDay->copy()->subMonth();


        return [
            'last_day' => $lastDay->toDateString(),
            'previous_last_day' => $prevLastDay->toDateString()
        ];
    }


    public function getHelplessEmployees($business_id,$start_date,$end_date) {

     $helplessEmployees =  User::
        where([
            "business_id" => $business_id
        ])
        ->where("joining_date", "<=", ($end_date . ' 23:59:59'))
        ->whereDoesntHave("payrolls",function($query) use($start_date, $end_date) {
            $query->where("payrolls",">", ($start_date .  ' 00:00:00'))
            ->where("payrolls","<=", ($end_date . ' 23:59:59'))
            ;
        })
        ->get();

        return $helplessEmployees;
    }

    public function handle()
    {
     $settingPaymentDates =   SettingPaymentDate::get();

     foreach($settingPaymentDates as $settingPaymentDate){

        if(empty($settingPaymentDate->business_id)){
            continue;
        }

        $period = "";

        if($settingPaymentDate->payment_type == "weekly") {
     $dateRange =  $this->getWeeklyDateRange($settingPaymentDate->day_of_week);
     $period = "week";

        } else if($settingPaymentDate->payment_type == "monthly") {
     $dateRange =  $this->getMonthlyDateRange($settingPaymentDate->day_of_month);
     $period = "month";

        } else if($settingPaymentDate->payment_type == "custom") {

        }

  $helplessEmployees =  $this->getHelplessEmployees($settingPaymentDate->business_id,$dateRange["previous_last_day"],$dateRange["last_day"]);

  foreach($helplessEmployees as $employee){
    $this->sendNotification($employee,$settingPaymentDate->business_id,$period);

  }



     }
        return 0;
    }

    public function sendNotification($user, $business_id, $period)
    {



        $notification_description =   "Salary for " . ($user->first_Name . ' ' . $user->middle_Name  . " " . $user->last_Name) .  " for the current ". $period . " is pending. Please address this promptly. Thank you.";
        $notification_link = ("employee") . "/" . ($user->id);










        Log::info(($notification_description));
        Log::info(($notification_link));




        $all_parent_departments_manager_ids = $this->all_parent_departments_manager_of_user($user->id, $user->business_id);


        Log::warning((json_encode($all_parent_departments_manager_ids)));


        foreach ($all_parent_departments_manager_ids as $manager_id) {
            Notification::create([
                "entity_id" => $user->id,
                "entity_name" => "Salary",
                'notification_title' => "Salary Pending Notification",
                'notification_description' => $notification_description,
                'notification_link' => $notification_link,
                "sender_id" => 1,
                "receiver_id" => $manager_id,
                "business_id" => $business_id,
                "is_system_generated" => 1,
                "status" => "unread",
            ]);
        }
    }





}
