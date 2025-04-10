<?php

namespace App\Http\Utils;

use App\Models\Department;
use App\Models\EmployeePensionHistory;
use App\Models\Holiday;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

trait HolidayComponent
{
    public function get_holiday_dates($start_date,$end_date,$user_id, $all_parent_department_ids)
    {

        $holidays = Holiday::where([
            "business_id" => auth()->user()->business_id
        ])
            ->where('holidays.start_date', ">=", $start_date)
            ->where('holidays.end_date', "<=", $end_date . ' 23:59:59')
            ->where([
                "is_active" => 1
            ])
            ->where(function ($query) use ($user_id, $all_parent_department_ids) {
                $query->whereHas("users", function ($query) use ($user_id) {
                    $query->where([
                        "users.id" => $user_id
                    ]);
                })
                    ->orWhereHas("departments", function ($query) use ($all_parent_department_ids) {
                        $query->whereIn("departments.id", $all_parent_department_ids);
                    })

                    ->orWhere(function ($query) {
                        $query->whereDoesntHave("users")
                            ->whereDoesntHave("departments");
                    });
            })


            ->get();

            $holiday_dates = $holidays->flatMap(function ($holiday) {
                $start_holiday_date = Carbon::parse($holiday->start_date);
                $end_holiday_date = Carbon::parse($holiday->end_date);

                if ($start_holiday_date->eq($end_holiday_date)) {
                    return [$start_holiday_date->format('d-m-Y')];
                }
 
                $date_range = $start_holiday_date->daysUntil($end_holiday_date->addDay());

                return $date_range->map(function ($date) {
                    return $date->format('d-m-Y');
                });
            });

        return $holiday_dates;
    }



}
