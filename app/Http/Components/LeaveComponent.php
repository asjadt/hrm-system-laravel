<?php

namespace App\Http\Components;

use App\Http\Utils\BasicUtil;
use App\Models\Leave;
use App\Models\LeaveRecord;
use App\Models\SettingLeave;
use App\Models\SettingLeaveType;
use Carbon\Carbon;
use Exception;

class LeaveComponent
{

    use BasicUtil;
    protected $authorizationComponent;

    protected $departmentComponent;
    protected $workShiftHistoryComponent;
    protected $holidayComponent;
    protected $attendanceComponent;

    public function __construct(AuthorizationComponent $authorizationComponent,  DepartmentComponent $departmentComponent, WorkShiftHistoryComponent $workShiftHistoryComponent, HolidayComponent $holidayComponent, AttendanceComponent $attendanceComponent)
    {
        $this->authorizationComponent = $authorizationComponent;
        $this->departmentComponent = $departmentComponent;
        $this->workShiftHistoryComponent = $workShiftHistoryComponent;
        $this->holidayComponent = $holidayComponent;
        $this->attendanceComponent = $attendanceComponent;
    }



    public function prepare_data_on_leave_create($raw_data, $user_id)
    {
        $raw_data["user_id"] = $user_id;
        $raw_data["business_id"] = auth()->user()->business_id;
        $raw_data["is_active"] = true;
        $raw_data["created_by"] = auth()->user()->id;
        $raw_data["status"] = (auth()->user()->hasRole("business_owner") ? "approved" : "pending_approval");

        return $raw_data;
    }

    public function get_leave_start_date($raw_data)
    {
        if ($raw_data["leave_duration"] == "multiple_day") {
            $work_shift_start_date = $raw_data["start_date"];
        } else {
            $work_shift_start_date = $raw_data["date"];
        }
    }

    public function findLeave($leave_id = NULL ,$user_id, $date)
    {
        $leave =    Leave::where([
            "user_id" => $user_id
        ])
        ->when(!empty($leave_id), function($query) use($leave_id) {
            $query->whereNotIn("id",[$leave_id]);
        })
            ->whereHas('records', function ($query) use ($date) {
                $query->where('leave_records.date', ($date));
            })->first();
        return $leave;
    }



    public function getLeaveRecordDataItem(
        $work_shift_details,
        $holiday,
        $previous_leave,
        $previous_attendance,
        $date,
        $leave_duration,
        $day_type = "",
        $start_time="",
        $end_time="",
        $leave_data
        ) {

        if ((empty($work_shift_details->is_weekend) && (empty($holiday)|| empty($holiday->is_active)) && empty($previous_leave) && empty($previous_attendance))) {

        $leave_start_at = Carbon::createFromFormat('H:i:s', $work_shift_details->start_at);
        $leave_end_at = Carbon::createFromFormat('H:i:s', $work_shift_details->end_at);


        $capacity_hours = $leave_end_at->diffInHours($leave_start_at);


        if($leave_duration == "half_day") {
            if ($day_type == "first_half") {

                $leave_end_at = $leave_start_at->copy()->addHours($capacity_hours / 2);
            } else if ($day_type == "last_half") {

                $leave_start_at = $leave_end_at->copy()->subHours($capacity_hours / 2);
            }
        }
        else if($leave_duration == "hours") {

            $leave_start_at = Carbon::createFromFormat('H:i:s', $start_time);
            $leave_end_at = Carbon::createFromFormat('H:i:s', $end_time);
        }


        $leave_hours = $leave_end_at->diffInHours($leave_start_at);


        $leave_record_data["leave_hours"] =  $leave_hours;
        $leave_record_data["capacity_hours"] =  $capacity_hours;
        $leave_record_data["start_time"] = $leave_start_at;
        $leave_record_data["end_time"] = $leave_end_at;
        $leave_record_data["date"] = $date;
        $leave_record_data["id"] = !empty($leave_data["id"])?$leave_data["id"]:NULL;



        return $leave_record_data;
        }

        if($leave_duration != "multiple_day") {
            if($work_shift_details->is_weekend) {
                 throw new Exception(("there is a weekend on date " . $date),400);
            }
            if($holiday && $holiday->is_active) {
                throw new Exception(("there is a holiday on date " . $date),400);
            }
            if($previous_leave) {
                throw new Exception(("there is a leave exists on date " . $date),400);
            }
            if($previous_attendance) {
                throw new Exception(("there is an attendance exists on date " . $date),400);
            }
        }

        return [];

    }



public function validateLeaveTimes($workShiftDetails,$start_time,$end_time){

    $start_time = Carbon::parse($start_time);
    $end_time = Carbon::parse($end_time);

    $workShiftStart = Carbon::parse($workShiftDetails->start_at);
    $workShiftEnd = Carbon::parse($workShiftDetails->end_at);

    if ($start_time->lt($workShiftStart)) {
        throw new Exception(
            "Employee does not start working at $start_time. Starts at " . $workShiftDetails->start_at,
            400
        );
    }

    if ($end_time->gt($workShiftEnd)) {
        throw new Exception("Employee does not close working at $end_time", 400);
    }
}



public function generateLeaveDates ($start_date,$end_date) {
    $start_date = Carbon::parse($start_date);
    $end_date = Carbon::parse($end_date);
    $leave_dates = [];
    for ($date = $start_date; $date->lte($end_date); $date->addDay()) {
        $leave_dates[] = $date->format('Y-m-d');
    }
    return $leave_dates;
}





public function processLeave($leave_data,$leave_date,$all_parent_department_ids,&$leave_record_data_list) {

  $work_shift_history =  $this->workShiftHistoryComponent->get_work_shift_history($leave_date, $leave_data["user_id"]);

  if($work_shift_history->type == "flexible") {
throw new Exception("Leave request can not be created for flexible rota.",401);
  }




  $work_shift_details =  $this->workShiftHistoryComponent->get_work_shift_details($work_shift_history, $leave_date);

  $holiday = $this->holidayComponent->get_holiday_details($leave_date, $leave_data["user_id"], $all_parent_department_ids);

  $previous_leave = $this->findLeave(
 (!empty($leave_data["id"])?$leave_data["id"]:NULL),
  $leave_data["user_id"],
  $leave_date);

  $previous_attendance = $this->attendanceComponent->checkAttendanceExists(NULL,$leave_data["user_id"],$leave_date);


if($leave_data["leave_duration"] == "hours") {
    $this->validateLeaveTimes($work_shift_details,$leave_data["start_time"],$leave_data["end_time"]);
}

  $leave_record_data_item = $this->getLeaveRecordDataItem(
      $work_shift_details,
      $holiday,
      $previous_leave,
      $previous_attendance,
      $leave_date,
      $leave_data["leave_duration"],
      $leave_data["day_type"],
      !empty($leave_data["start_time"])?$leave_data["start_time"]:$work_shift_details->start_at,
      !empty($leave_data["end_time"])?$leave_data["end_time"]:$work_shift_details->end_at,
      $leave_data
  );
  if (!empty($leave_record_data_item)) {
      array_push($leave_record_data_list, $leave_record_data_item);
  }

}


public function processLeaveRequest($raw_data) {
    $leave_data =  !empty($raw_data["id"])?$raw_data:$this->prepare_data_on_leave_create($raw_data, $raw_data["user_id"]);
    $leave_record_data_list = [];
    $all_parent_department_ids = $this->departmentComponent->all_parent_departments_of_user($leave_data["user_id"]);

    switch ($leave_data["leave_duration"]) {
        case "multiple_day":
            $leave_dates = $this->generateLeaveDates($leave_data["start_date"],$leave_data["end_date"]);
            foreach ($leave_dates as $leave_date) {
                $this->processLeave($leave_data,$leave_date,$all_parent_department_ids,$leave_record_data_list);
            }
            break;

        case "single_day":
        case "half_day":
        case "hours":
        $leave_data["start_date"] = Carbon::parse($leave_data["date"]);
        $leave_data["end_date"] = Carbon::parse($leave_data["date"]);
        $this->processLeave($leave_data,$leave_data["date"],$all_parent_department_ids,$leave_record_data_list);
            break;

        default:

            break;
    }

    return [
        "leave_data" => $leave_data,
        "leave_record_data_list" => $leave_record_data_list
    ];
}


public function get_already_taken_leaves($start_date,$end_date,$user_id,$is_full_day_leave=NULL) {


    $already_taken_leaves =  Leave::where([
          "user_id" => $user_id
      ])

      ->when(($is_full_day_leave !== NULL), function($query) use($is_full_day_leave) {
          if($is_full_day_leave) {
              $query->whereIn("leaves.leave_duration",['single_day', 'multiple_day']);
          } else {
              $query->whereNotIn("leaves.leave_duration",['single_day', 'multiple_day']);
          }})


          ->whereHas('records', function ($query) use ($start_date, $end_date) {
              $query->where('leave_records.date', '>=', $start_date)
                  ->where('leave_records.date', '<=', $end_date . ' 23:59:59');
          })
          ->get();


          return $already_taken_leaves;
  }

  public function get_already_taken_leave_records($start_date,$end_date,$user_id) {


    $already_taken_leaves =  LeaveRecord::whereHas("leave",function($query) use($user_id){

        $query->where("leaves.user_id",$user_id);
    })

     ->where('leave_records.date', '>=', $start_date)
                  ->where('leave_records.date', '<=', $end_date . ' 23:59:59')



          ->pluck("leave_records.date");


          return $already_taken_leaves;
  }



public function get_already_taken_leave_dates($start_date,$end_date,$user_id,$is_full_day_leave=NULL) {

  $already_taken_leaves =  $this->get_already_taken_leaves($start_date,$end_date,$user_id,$is_full_day_leave);
      $already_taken_leave_dates =  $already_taken_leaves->flatMap(function ($leave) {
            return $leave->records->map(function ($record) {
                return Carbon::parse($record->date)->format('d-m-Y');
            });
        })->toArray();
        return $already_taken_leave_dates;
}

public function get_already_taken_half_day_leaves($start_date, $end_date, $user_id) {
    $already_taken_leaves = $this->get_already_taken_leaves($start_date, $end_date, $user_id, false);

    $already_taken_leave_dates = $already_taken_leaves->map(function ($leave) {
        return  $leave->records[0];
    })->toArray();

    return $already_taken_leave_dates;
}



public function validateLeaveAvailability($leave) {

    $setting_leave = SettingLeave::where('setting_leaves.business_id', auth()->user()->business_id)
    ->where('setting_leaves.is_default', 0)
    ->first();
if (empty($setting_leave)) {
    return response()->json(
        ["message" => "No leave setting found."]
    );
}
if (empty($setting_leave->start_month)) {
    $setting_leave->start_month = 1;
}




$leave_type =   SettingLeaveType::
where([
    "id"=> $leave->leave_type_id
])

    ->first();

    if(empty($leave_type)){
        return false;
    }

    $startOfMonth = Carbon::create(null, $setting_leave->start_month, 1, 0, 0, 0)->subYear();

    $already_taken_hours = LeaveRecord::whereHas('leave', function ($query) use ($leave, $leave_type) {
        $query->where([
            "user_id" => $leave->user_id,
            "leave_type_id" => $leave_type->id

        ]);
    })
        ->where("leave_records.date", ">=", $startOfMonth)
        ->get()
        ->sum(function ($record) {
            return Carbon::parse($record->end_time)->diffInHours(Carbon::parse($record->start_time));
        });





}










public function updateLeavesQuery( $all_manager_department_ids,$query)
{

    $query = $query

    ->when(!empty(request()->search_key), function ($query)  {
        return $query->where(function ($query)  {
            $term = request()->search_key;

        });
    })

    ->when(!empty(request()->user_id), function ($query)  {
        return $query->where('leaves.user_id', request()->user_id);
    })


    ->when(
        (request()->has('show_my_data') && intval(request()->show_my_data) == 1),
        function ($query)  {
            $query->where('leaves.user_id', auth()->user()->id);
        },
        function ($query) use ($all_manager_department_ids,) {

            $query->whereHas("employee.department_user.department", function ($query) use ($all_manager_department_ids) {
                $query->whereIn("departments.id", $all_manager_department_ids);

            })
            ->whereNotIn('leaves.user_id', [auth()->user()->id]);
            ;

        }
    )



    ->when(!empty(request()->leave_type_id), function ($query)  {
        return $query->where('leaves.leave_type_id', request()->leave_type_id);
    })
    ->when(!empty(request()->status), function ($query)  {
        return $query->where('leaves.status', request()->status);
    })
    ->when(!empty(request()->department_id), function ($query)  {
        return $query->whereHas("employee.department_user.department", function ($query)  {
            $query->where("departments.id", request()->department_id);
        });
    })




    ->when(!empty(request()->start_date), function ($query)  {
        $query->where('leaves.start_date', '>=', request()->start_date . ' 00:00:00');
    })
    ->when(!empty(request()->end_date), function ($query)  {
        $query->where('leaves.end_date', '<=', request()->end_date . ' 23:59:59');
    });

    return $query;
}


public function getLeaveV4Func() {
    $all_manager_department_ids = $this->departmentComponent->get_all_departments_of_manager();



    $leavesQuery =  Leave::with([
        "employee" => function ($query) {
            $query->select(
                'users.id',
                'users.first_Name',
                'users.middle_Name',
                'users.last_Name',
                'users.image'
            );
        },
        "employee.departments" => function ($query) {

            $query->select(
                'departments.id',
                'departments.name',
    
                "departments.description"
            );
        },
        "leave_type" => function ($query) {
            $query->select(
                'setting_leave_types.id',
                'setting_leave_types.name',
                'setting_leave_types.type',
                'setting_leave_types.amount',

            );
        },

    ]);
    $leavesQuery =   $this->updateLeavesQuery($all_manager_department_ids,$leavesQuery);
    $leaves = $this->retrieveData($leavesQuery, "leaves.id");




    foreach ($leaves as $leave) {
        $leave->total_leave_hours = $leave->records->sum(function ($record) {
            $startTime = Carbon::parse($record->start_time);
            $endTime = Carbon::parse($record->end_time);
            return $startTime->diffInHours($endTime);
        });
    }
    $data["data"] = $leaves;

    $data["data_highlights"] = [];



    $data["data_highlights"]["leave_approved_hours"] = $leaves->filter(function ($leave) {
        return ($leave->status == "approved");
    })->sum('total_leave_hours');

    $data["data_highlights"]["leave_approved_total_individual_days"] = $leaves->filter(function ($leave) {

        return ($leave->status == "approved");
    })->sum(function ($leave) {
        return $leave->records->count();
    });

    $data["data_highlights"]["upcoming_leaves_hours"] = $leaves->filter(function ($leave) {

        return Carbon::parse($leave->start_date)->isFuture();
    })->sum(function ($leave) {
        return $leave->records->count();
    });

    $data["data_highlights"]["upcoming_leaves_total_individual_days"] = $leaves->filter(function ($leave) {

        return Carbon::parse($leave->start_date)->isFuture();
    })->sum('total_leave_hours');



    $data["data_highlights"]["pending_leaves"] = $leaves->filter(function ($leave) {

        return ($leave->status != "approved");
    })->count();
    return $data;
}



}
