<?php

namespace App\Observers;

use App\Models\Leave;
use App\Models\LeaveHistory;
use Exception;

class LeaveObserver
{
    /**
     * Handle the Leave "created" event.
     *
     * @param  \App\Models\Leave  $leave
     * @return void
     */
    public function created(Leave $leave)
    {

    }
    public function create(Leave $leave)
    {

        $leaveHistoryData = $leave->toArray();
        $leaveHistoryData["leave_id"] = $leave->id;
        $leaveHistoryData["actor_id"] = auth()->user()->id;
        $leaveHistoryData["action"] = "create";
        $leaveHistoryData["is_approved"] = NULL;
        $leaveHistoryData["leave_created_at"] = $leave->created_at;
        $leaveHistoryData["leave_updated_at"] = $leave->updated_at;

       $leave_history = LeaveHistory::create($leaveHistoryData);
        $leave_history->records()->createMany($leave->records->toArray());
    }

    /**
     * Handle the Leave "updated" event.
     *
     * @param  \App\Models\Leave  $leave
     * @return void
     */
    public function updated(Leave $leave)
    {

    }

    /**
     * Handle the Leave "deleted" event.
     *
     * @param  \App\Models\Leave  $leave
     * @return void
     */
    public function deleted(Leave $leave)
    {

    }

    /**
     * Handle the Leave "restored" event.
     *
     * @param  \App\Models\Leave  $leave
     * @return void
     */
    public function restored(Leave $leave)
    {

    }

    /**
     * Handle the Leave "force deleted" event.
     *
     * @param  \App\Models\Leave  $leave
     * @return void
     */
    public function forceDeleted(Leave $leave)
    {
        
    }
}
