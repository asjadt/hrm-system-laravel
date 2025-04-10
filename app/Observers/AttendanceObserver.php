<?php

namespace App\Observers;

use App\Models\Attendance;
use App\Models\AttendanceHistory;

class AttendanceObserver
{
    /**
     * Handle the Attendance "created" event.
     *
     * @param  \App\Models\Attendance  $attendance
     * @return void
     */
    public function created(Attendance $attendance)
    {



    }

    /**
     * Handle the Attendance "updated" event.
     *
     * @param  \App\Models\Attendance  $attendance
     * @return void
     */

    public function updated_action(Attendance $attendance,$action)
    {
        if (!empty($action)) {
            $attendance_history_data = $attendance->toArray();
            $attendance_history_data = $attendance->toArray();
            $attendance_history_data['attendance_id'] = $attendance->id;
            $attendance_history_data['actor_id'] = auth()->user()->id;
            $attendance_history_data['action'] = $action;
            $attendance_history_data['attendance_created_at'] = $attendance->created_at;
            $attendance_history_data['attendance_updated_at'] = $attendance->updated_at;

       $attendance_history = AttendanceHistory::create($attendance_history_data);
      $attendance_history->projects()->sync($attendance->projects()->pluck("projects.id")->toArray());

        }
    }



    /**
     * Handle the Attendance "deleted" event.
     *
     * @param  \App\Models\Attendance  $attendance
     * @return void
     */
    public function deleted(Attendance $attendance)
    {

        $attendance_history_data = $attendance->toArray();
        $attendance_history_data['attendance_id'] = NULL;
        $attendance_history_data['actor_id'] = auth()->user()->id;
        $attendance_history_data['action'] = "delete";
        $attendance_history_data['attendance_created_at'] = $attendance->created_at;
        $attendance_history_data['attendance_updated_at'] = $attendance->updated_at;


        AttendanceHistory::create($attendance_history_data);
    }

    /**
     * Handle the Attendance "restored" event.
     *
     * @param  \App\Models\Attendance  $attendance
     * @return void
     */
    public function restored(Attendance $attendance)
    {

    }

    /**
     * Handle the Attendance "force deleted" event.
     *
     * @param  \App\Models\Attendance  $attendance
     * @return void
     */
    public function forceDeleted(Attendance $attendance)
    {
        
    }
}
