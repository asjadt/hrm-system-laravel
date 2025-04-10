<?php

namespace App\Http\Components;

use App\Http\Utils\BasicUtil;
use App\Models\Attendance;
use App\Models\SettingAttendance;
use Carbon\Carbon;
use Exception;

class AttendanceComponent
{
    use BasicUtil;

    public function checkAttendanceExists($id = "", $user_id, $date)
    {
        $exists = Attendance::when(!empty($id), function ($query) use ($id) {
            $query->whereNotIn('id', [$id]);
        })
            ->where('attendances.user_id', $user_id)
            ->where('attendances.in_date', $date)
            ->where('attendances.business_id', auth()->user()->business_id)
            ->exists();;
        return $exists;
    }



    public function get_already_taken_attendance_dates($user_id, $start_date, $end_date)
    {
        $already_taken_attendances =  Attendance::where([
            "user_id" => $user_id
        ])
            ->where('attendances.in_date', '>=', $start_date)
            ->where('attendances.in_date', '<=', $end_date . ' 23:59:59')
            ->get();



        $already_taken_attendance_dates = $already_taken_attendances->map(function ($attendance) {
            return Carbon::parse($attendance->in_date)->format('d-m-Y');
        });

        return $already_taken_attendance_dates;
    }


    public function updateAttendanceQuery($all_manager_department_ids, $attendancesQuery)
    {

        $attendancesQuery = $attendancesQuery
            ->where(
                [
                    "attendances.business_id" => auth()->user()->business_id
                ]
            )
            ->when(!empty(request()->search_key), function ($query) {
                return $query->where(function ($query) {
                    $term = request()->search_key;

                });
            })
            ->when(!empty(request()->user_id), function ($query) {
                $idsArray = explode(',', request()->user_id);
                return $query->whereIn('attendances.user_id', $idsArray);
            })




            ->when(!empty(request()->overtime), function ($query) {
                $number_query = explode(',', str_replace(' ', ',', request()->overtime));
                return $query->where('attendances.overtime_hours', $number_query);
            })


            ->when(!empty(request()->schedule_hour), function ($query) {
                $number_query = explode(',', str_replace(' ', ',', request()->schedule_hour));
                return $query->where('attendances.capacity_hours', $number_query);
            })

            ->when(!empty(request()->break_hour), function ($query) {
                $number_query = explode(',', str_replace(' ', ',', request()->break_hour));
                return $query->where('attendances.break_hours', $number_query);
            })

            ->when(!empty(request()->worked_hour), function ($query) {
                $number_query = explode(',', str_replace(' ', ',', request()->worked_hour));
                return $query->where('attendances.total_paid_hours', $number_query[0], $number_query[1]);
            })

            ->when(!empty(request()->work_location_id), function ($query) {
                return $query->where('attendances.user_id', request()->work_location_id);
            })

            ->when(!empty(request()->project_id), function ($query) {
                $idsArray = explode(',', request()->project_id);
                return $query->whereHas('projects', function ($query) use ($idsArray) {
                    $query->whereIn("projects.id", $idsArray);
                });
            })
            ->when(!empty(request()->work_location_id), function ($query) {
                return $query->where('attendances.work_location_id', request()->work_location_id);
            })

            ->when(!empty(request()->status), function ($query) {
                return $query->where('attendances.status', request()->status);
            })
            ->when(!empty(request()->department_id), function ($query) {
                return $query->whereHas("employee.department_user.department", function ($query) {
                    $query->where("departments.id", request()->department_id);
                });
            })





            ->when(
                (request()->has('show_my_data') && intval(request()->show_my_data) == 1),
                function ($query) {
                    $query->where('attendances.user_id', auth()->user()->id);
                },
                function ($query) use ($all_manager_department_ids,) {

                    $query->whereHas("employee.department_user.department", function ($query) use ($all_manager_department_ids) {
                        $query->whereIn("departments.id", $all_manager_department_ids);
                    })
                        ->whereNotIn('attendances.user_id', [auth()->user()->id]);
                }
            )





            ->when(!empty(request()->start_date), function ($query) {
                return $query->where('attendances.in_date', ">=", request()->start_date);
            })
            ->when(!empty(request()->end_date), function ($query) {
                return $query->where('attendances.in_date', "<=", (request()->end_date . ' 23:59:59'));
            });

        return $attendancesQuery;
    }

    public function get_attendance_setting()
    {
        $setting_attendance = SettingAttendance::where([
            "business_id" => auth()->user()->business_id
        ])
            ->first();
        if (empty($setting_attendance)) {
            throw new Exception("Please define attendance setting first", 400);
        }
        return $setting_attendance;
    }


    public function getAttendanceV2Data()
    {


        $all_manager_department_ids = $this->get_all_departments_of_manager();




        $attendancesQuery = Attendance::with([
            "employee" => function ($query) {
                $query->select(
                    'users.id',
                    'users.first_Name',
                    'users.middle_Name',
                    'users.last_Name'
                );
            },
            "employee.departments" => function ($query) {
                $query->select('departments.id', 'departments.name');
            },
            "work_location",
            "projects"
        ]);




        $attendancesQuery = $this->updateAttendanceQuery($all_manager_department_ids, $attendancesQuery);


        $attendances = $this->retrieveData($attendancesQuery, "attendances.id");

        $data['data'] = $attendances;


        $data['data_highlights']['total_active_hours'] = $attendances->sum('total_paid_hours');


        $data['data_highlights']['total_extra_hours'] = $attendances->sum('overtime_hours');


        $behavior_counts = [
            'absent' => $attendances->filter(fn($attendance) => ($attendance->behavior === 'absent' || $attendance->is_present === 0))->count(),
            'regular' => $attendances->filter(fn($attendance) => $attendance->behavior === 'regular')->count(),
            'early' => $attendances->filter(fn($attendance) => $attendance->behavior === 'early')->count(),
            'late' => $attendances->filter(fn($attendance) => $attendance->behavior === 'late')->count(),
        ];


        $max_behavior = max($behavior_counts);
        if ($attendances->isEmpty()) {
            $data['data_highlights']['behavior'] = $behavior_counts;
            $data['data_highlights']['average_behavior'] = "no data";
            $data['data_highlights']['total_schedule_hours_gone'] = 0;
        } else {
            $data['data_highlights']['behavior'] = $behavior_counts;
            $data['data_highlights']['average_behavior'] = array_search($max_behavior, $behavior_counts);
            $data['data_highlights']['total_schedule_hours_gone'] = $attendances->sum('capacity_hours');
        }




    
        $data['data_highlights']["total_available_hours"] = $data['data_highlights']['total_active_hours'] - $data['data_highlights']['total_extra_hours'];









        return $data;
    }
}
