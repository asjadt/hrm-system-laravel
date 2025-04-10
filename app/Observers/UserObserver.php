<?php

namespace App\Observers;

use App\Models\SalaryHistory;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function created(User $user)
    {
        $authUser = auth()->user();
        if($authUser) {
            $business =   auth()->user()->business;
            SalaryHistory::create([
                'user_id' => $user->id,
                'salary_per_annum' => $user->salary_per_annum,
                'weekly_contractual_hours' => $user->weekly_contractual_hours,
                'minimum_working_days_per_week' => $user->minimum_working_days_per_week,
                'overtime_rate' => $user->overtime_rate,
                'from_date' =>  $business?$business->start_date:now(),
                'to_date' => NULL,
            ]);
        }




    }

    /**
     * Handle the User "updated" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */


     public function updating(User $user)
     {

         $changedFields = $user->getDirty();

         if (array_intersect(array_keys($changedFields), [
             'salary_per_annum',
             'weekly_contractual_hours',
             'minimum_working_days_per_week',
             'overtime_rate',
         ])) {


             SalaryHistory::where([
                 "user_id" => $user->id,
                 'to_date' => NULL,
             ])
             ->update([
                 'to_date' => now(),
             ]);

             SalaryHistory::create([
                 'user_id' => $user->id,
                 'salary_per_annum' => $user->salary_per_annum,
                 'weekly_contractual_hours' => $user->weekly_contractual_hours,
                 'minimum_working_days_per_week' => $user->minimum_working_days_per_week,
                 'overtime_rate' => $user->overtime_rate,
                 'from_date' => now(),
                 'to_date' => NULL,
             ]);
         }


     }


    public function saved(User $user)
    {

    }

    /**
     * Handle the User "deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function deleted(User $user)
    {

    }

    /**
     * Handle the User "restored" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function restored(User $user)
    {

    }

    /**
     * Handle the User "force deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        
    }



}
