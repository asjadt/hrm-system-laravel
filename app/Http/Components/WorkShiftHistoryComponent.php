<?php

namespace App\Http\Components;

use App\Models\WorkShift;
use App\Models\WorkShiftHistory;
use Carbon\Carbon;
use Exception;

class WorkShiftHistoryComponent
{
    public function get_work_shift_history($in_date,$user_id,$throwError=true)
    {
        $work_shift_history =  WorkShiftHistory::
           where(function($query) use($in_date,$user_id) {
          $query ->where("from_date", "<=", $in_date)
          ->where(function ($query) use ($in_date) {
              $query->where("to_date", ">=", $in_date)
                  ->orWhereNull("to_date");
          })
          ->where("user_id", $user_id);
            })

            ->orderByDesc("work_shift_histories.id")


            ->first();
        if (!$work_shift_history) {
            if($throwError) {
                throw new Exception("Please define workshift first",401);
            } else {
                return false;
            }
        }

        return $work_shift_history;


    }
    public function get_work_shift_histories($start_date,$end_date,$user_id,$throwError)
    {
     $work_shift_histories =   WorkShiftHistory::
              with("details")
            ->where("from_date", "<=", $end_date)
            ->where(function ($query) use ($start_date) {
                $query->where("to_date", ">=", $start_date)
                    ->orWhereNull("to_date");
            })
            ->where("user_id", $user_id)


            ->orderByDesc("work_shift_histories.id")
            ->get();

        if ($work_shift_histories->isEmpty()) {
            if($throwError) {
                throw new Exception("Please define workshift first",401);
            } else {
                return false;
            }
        }

        return $work_shift_histories;
    }


    public function get_work_shift_historiesV2($start_date,$end_date,$user_id,$throwError)
    {
     $work_shift_histories =   WorkShiftHistory::
            where("from_date", "<=", $end_date)
            ->where(function ($query) use ($start_date) {
                $query->where("to_date", ">=", $start_date)
                    ->orWhereNull("to_date");
            })
            ->where("user_id",$user_id)

 ->with("details")
 ->orderByDesc("work_shift_histories.id")
            ->get();

        if ($work_shift_histories->isEmpty()) {
            if($throwError) {
                throw new Exception("Please define workshift first",401);
            } else {
                return false;
            }
        }

        return $work_shift_histories;
    }




    public function get_work_shift_details($work_shift_history,$in_date,$thowError=true)
    {
        $day_number = Carbon::parse($in_date)->dayOfWeek;
        $work_shift_details =  $work_shift_history->details()->where([
            "day" => $day_number
        ])
        ->first();


        if (empty($work_shift_details) && $thowError) {
            throw new Exception(("No work shift details found  day " . $day_number . " work shift id:" .  $work_shift_history->id), 400);
        }

        return $work_shift_details;
    }

    public function updateWorkShiftsQuery($all_manager_department_ids,$query) {
    $query = $query->when(!empty(auth()->user()->business_id), function ($query) use ( $all_manager_department_ids) {
        return $query
        ->where(function($query) use($all_manager_department_ids) {
          return  $query->where(function($query) use($all_manager_department_ids) {
                $query
                ->where([
                    "work_shifts.business_id" => auth()->user()->business_id
                ])
                ->whereHas("departments", function ($query) use ($all_manager_department_ids) {
                    $query->whereIn("departments.id", $all_manager_department_ids);
                });

            });


        });

    })

    ->when(empty(auth()->user()->business_id), function ($query)  {
        return $query->where([
            "work_shifts.is_default" => 1,
            "work_shifts.business_id" => NULL
        ]);
    })
        ->when(!empty(request()->search_key), function ($query) {
            return $query->where(function ($query) {
                $term = request()->search_key;
                $query->where("work_shifts.name", "like", "%" . $term . "%")
                    ->orWhere("work_shifts.description", "like", "%" . $term . "%");
            });
        })


        ->when(!empty(request()->name), function ($query)  {
            $term = request()->name;
            return $query->where("work_shifts.name", "like", "%" . $term . "%");
        })
        ->when(!empty(request()->description), function ($query)  {
            $term = request()->description;
            return $query->where("work_shifts.description", "like", "%" . $term . "%");
        })

        ->when(!empty(request()->type), function ($query)  {
            return $query->where('work_shifts.type', request()->type);
        })

        ->when(request()->filled("is_active"), function ($query)  {
            return $query->where('work_shifts.is_active', request()->boolean("is_active"));
         })


        ->when(request()->filled("is_personal"), function ($query)  {
            return $query->where('work_shifts.is_personal', request()->boolean("is_personal"));
        })
        ->when(!isset(request()->is_personal), function ($query)  {
            return $query->where('work_shifts.is_personal', 0);
        })
        ->when(request()->filled("is_default"), function ($query)  {
            return $query->where('work_shifts.is_default', request()->boolean("is_default"));
        })

        ->when(!empty(request()->start_date), function ($query)  {
            return $query->where('work_shifts.created_at', ">=", request()->start_date);
        })

        ->when(!empty(request()->end_date), function ($query)  {
            return $query->where('work_shifts.created_at', "<=", (request()->end_date . ' 23:59:59'));
        });
        return $query;
    }





    public function getWorkShiftByUserId ($user_id) {

        $work_shift =   WorkShift::with("details")
        ->where(function($query) use($user_id) {
            $query->where([
                "business_id" => auth()->user()->business_id
            ])->whereHas('users', function ($query) use ($user_id) {
                $query->where('users.id', $user_id);
            });
        })
   

        ->first();

         if (empty($work_shift)) {
            throw new Exception("no work shift found for the user",404);
         }
         return $work_shift;
    }


    public function getWorkShiftById($work_shift_id) {
      $work_shift =  WorkShift::where([
            "id" => $work_shift_id,
        ])
            ->where(function ($query) {
                $query->where([

                    "business_id" => auth()->user()->business_id
                ]);
            })
            ->orderByDesc("id")
            ->first();

        if (empty($work_shift)) {
            throw new Exception("no work shift found", 403);
        }

        if (empty($work_shift->is_active)) {
            throw new Exception("Please activate the work shift named '" . $work_shift->name . "'", 400);
        }

        return $work_shift;
    }

    public function getCurrentOverlappingWorkShift($user_id, $from_date,$history_id=NULL) {
        $overlapped_work_shift_history =  WorkShiftHistory::
            when(!empty($history_id), function($query) use($history_id) {
                $query->whereNotIn("id",[$history_id]);
            })
            ->where("from_date", "=", $from_date )
            ->where("user_id",$user_id)
            ->first();
        return $overlapped_work_shift_history;
    }

    public function getFutureWorkShift($user_id, $from_date,$history_id=NULL) {
        $future_work_shift_history =  WorkShiftHistory::
            when(!empty($history_id), function($query) use($history_id) {
                $query->whereNotIn("id",[$history_id]);
            })
            ->where("from_date", ">", $from_date )
        ->where("user_id",$user_id)
        ->orderByDesc("from_date")
        ->first();

        return $future_work_shift_history;
    }
    public function getPastWorkShift($user_id, $from_date,$history_id=NULL) {
        $future_work_shift_history =  WorkShiftHistory::
            when(!empty($history_id), function($query) use($history_id) {
                $query->whereNotIn("id",[$history_id]);
            })
            ->where("to_date", "<", $from_date )
        ->where("user_id",$user_id)
        ->orderByDesc("from_date")
        ->first();

        return $future_work_shift_history;
    }

    public function getInnerWorkShift($user_id, $from_date,$history_id=NULL) {
        $future_work_shift_history =  WorkShiftHistory::
            when(!empty($history_id), function($query) use($history_id) {
                $query->whereNotIn("id",[$history_id]);
            })
           ->whereDate("from_date", "<=", $from_date)
        ->where(function ($query) use ($from_date) {
            $query->whereDate("to_date", ">=", $from_date)
                ->orWhereNull("to_date");
        })
        ->where("user_id",$user_id)
        ->first();

        return $future_work_shift_history;
    }


}
