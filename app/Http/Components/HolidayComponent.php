<?php
namespace App\Http\Components;

use App\Models\Holiday;
use Carbon\Carbon;

class HolidayComponent {
    public function get_holiday_details($in_date,$user_id, $all_parent_department_ids)
    {

        $holiday =   Holiday::where([
            "business_id" => auth()->user()->business_id
        ])
            ->where('holidays.start_date', "<=", $in_date)
            ->where('holidays.end_date', ">=", $in_date . ' 23:59:59')
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
            ->first();



        return $holiday;
    }
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
            ->select("start_date","end_date")


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

    public function get_holiday_datesV2($start_date,$end_date,$user_id, $all_parent_department_ids)
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
                    return [$start_holiday_date->format('Y-m-d')];
                }

                $date_range = $start_holiday_date->daysUntil($end_holiday_date->addDay());

                return $date_range->map(function ($date) {
                    return $date->format('Y-m-d');
                });
            });

        return $holiday_dates;
    }

    public function get_weekend_dates($start_date,$end_date,$user_id, $work_shift_histories)
    {

        $weekend_dates =   collect();


        $work_shift_histories->each(function ($work_shift) use ($start_date, $end_date, &$weekend_dates, $user_id) {
            $weekends = $work_shift->details()->where("is_weekend", 1)->get();

            $weekends->each(function ($weekend) use ($start_date, $end_date, &$weekend_dates, $work_shift, $user_id) {
                $day_of_week = $weekend->day;





                $user_to_date = $work_shift->to_date ?? null;

                if ($user_to_date) {
                    $end_date_loop = $user_to_date;
                } elseif ($work_shift->to_date) {
                    $end_date_loop = $work_shift->to_date;
                } else {
                    $end_date_loop = $end_date;
                }

                $user_from_date = $work_shift->from_date;
                $start_date_loop = Carbon::parse($user_from_date)->gt($start_date) ? $user_from_date : $start_date;



                $next_day = Carbon::parse($start_date_loop)->copy()->next($day_of_week);



                while ($next_day <= $end_date_loop) {
                    $weekend_dates->push($next_day->format('d-m-Y'));
                    $next_day->addWeek(); 
                }
            });
        });

        return $weekend_dates;

    }






}
