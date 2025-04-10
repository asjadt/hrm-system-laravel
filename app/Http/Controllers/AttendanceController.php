<?php

namespace App\Http\Controllers;

use App\Exports\AttendancesExport;
use App\Http\Components\AttendanceComponent;
use App\Http\Components\HolidayComponent;
use App\Http\Components\LeaveComponent;
use App\Http\Components\UserManagementComponent;
use App\Http\Requests\AttendanceApproveRequest;
use App\Http\Requests\AttendanceArrearApproveRequest;
use App\Http\Requests\AttendanceBypassMultipleCreateRequest;
use App\Http\Requests\AttendanceCreateRequest;
use App\Http\Requests\AttendanceMultipleCreateRequest;
use App\Http\Requests\AttendanceUpdateRequest;
use App\Http\Requests\SelfAttendanceCheckInCreateRequest;
use App\Http\Requests\SelfAttendanceCheckOutCreateRequest;
use App\Http\Utils\AttendanceUtil;
use App\Http\Utils\BasicNotificationUtil;
use App\Http\Utils\BasicUtil;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\PayrunUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Attendance;
use App\Models\AttendanceArrear;
use App\Models\AttendanceHistory;
use App\Models\AttendanceHistoryProject;
use App\Models\AttendanceProject;
use App\Models\LeaveRecord;
use App\Models\Payroll;
use App\Models\PayrollAttendance;

use App\Models\User;
use App\Models\UserProject;
use App\Observers\AttendanceObserver;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDF;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{
    use ErrorUtil, UserActivityUtil, BusinessUtil, PayrunUtil, BasicNotificationUtil, AttendanceUtil, BasicUtil;


    protected $attendanceComponent;
    protected $holidayComponent;
    protected $leaveComponent;
    protected $userManagementComponent;
    public function __construct(AttendanceComponent $attendanceComponent, HolidayComponent $holidayComponent, LeaveComponent $leaveComponent, UserManagementComponent $userManagementComponent)
    {
        $this->attendanceComponent = $attendanceComponent;
        $this->holidayComponent = $holidayComponent;
        $this->leaveComponent = $leaveComponent;
        $this->userManagementComponent = $userManagementComponent;
    }



    public function createSelfAttendanceCheckOut(SelfAttendanceCheckOutCreateRequest $request)
    {

        DB::beginTransaction();
        try {

            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            $request_data = $request->validated();


            $user = auth()->user();
            if (!$user || !$user->business_id) {

                throw new Exception("User or business ID not found.");
            }


            $attendance_query_params = [
                "id" => $request_data["id"],
                "business_id" => $user->business_id,
            ];


            $attendance = Attendance::where($attendance_query_params)->first();

            if (!$attendance) {

                throw new Exception("Attendance record not found.");
            }


            $attendance_data = $attendance->toArray();



            $request_data_update = array_replace($attendance_data, $request_data);








            $request_data_update["is_present"] =  $this->calculate_total_present_hours($request_data_update["attendance_records"]) > 0;



            $setting_attendance = $this->get_attendance_setting();


            $attendance_data = $this->process_attendance_data($request_data_update, $setting_attendance, $request_data_update["user_id"]);



            if ($attendance) {
                $attendance->fill(collect($attendance_data)->only([
                    'note',
                    "in_geolocation",
                    "out_geolocation",
                    'user_id',
                    'in_date',
                    'does_break_taken',
                    "behavior",
                    "capacity_hours",
                    "work_hours_delta",
                    "break_type",
                    "break_hours",
                    "total_paid_hours",
                    "regular_work_hours",
                    "work_shift_start_at",
                    "work_shift_end_at",
                    "work_shift_history_id",
                    "holiday_id",
                    "leave_record_id",
                    "is_weekend",
                    "overtime_hours",
                    "punch_in_time_tolerance",
                    "status",
                    'work_location_id',
                    "is_active",
                    "business_id",
                    "created_by",
                    "regular_hours_salary",
                    "overtime_hours_salary",
                    "attendance_records",
                    "is_present"
                ])->toArray());
                $attendance->save();
            }


            $attendance->projects()->sync($request_data["project_ids"]);

            $observer = new AttendanceObserver();
            $observer->updated_action($attendance, 'update');

            $this->adjust_payroll_on_attendance_update($attendance, 0);

            $this->send_notification($attendance, $attendance->employee, "Attendance updated", "update", "attendance");


            $responseData = [
                "project_ids" => $attendance->projects()->pluck("projects.id")
            ];

            $attendance->work_location = $attendance->work_location;
            $responseData = array_merge($responseData, $attendance->toArray());


            DB::commit();
            return response($responseData, 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError($e, 500, $request);
        }
    }


    public function createSelfAttendanceCheckIn(SelfAttendanceCheckInCreateRequest $request)
    {

        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            $request_data = $request->validated();
            $request_data["user_id"] = auth()->user()->id;
            $request_data["does_break_taken"] = 0;


            $request_data["attendance_records"] = collect($request_data["attendance_records"])
                ->map(function ($item) {
                    $item["out_time"] = $item["in_time"];
                    return $item;
                })
                ->toArray();

            $request_data["is_present"] =  $this->calculate_total_present_hours($request_data["attendance_records"]) > 0;




            $setting_attendance = $this->get_attendance_setting();


            $attendance_data = $this->process_attendance_data($request_data, $setting_attendance, $request_data["user_id"]);



            $attendance =  Attendance::create($attendance_data);

            $attendance->projects()->sync($request_data["project_ids"]);

            $observer = new AttendanceObserver();
            $observer->updated_action($attendance, 'create');


            $this->adjust_payroll_on_attendance_update($attendance, 0);


            $this->send_notification($attendance, $attendance->employee, "Attendance Taken", "create", "attendance");

            DB::commit();

            $responseData = [
                "project_ids" => $attendance->projects()->pluck("projects.id")
            ];


            $attendance->work_location = $attendance->work_location;
            $responseData = array_merge($responseData, $attendance->toArray());


            return response($responseData, 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError($e, 500, $request);
        }
    }



    public function createAttendance(AttendanceCreateRequest $request)
    {

        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            $request_data = $request->validated();

            $user_id = intval($request_data["user_id"]);

            $request_user_id = auth()->user()->id;


            if ((!auth()->user()->hasPermissionTo('attendance_create') && ($request_user_id !== $user_id))) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }



            $request_data["is_present"] =  $this->calculate_total_present_hours($request_data["attendance_records"]) > 0;



            $setting_attendance = $this->get_attendance_setting();


            $attendance_data = $this->process_attendance_data($request_data, $setting_attendance, $request_data["user_id"]);




            $attendance =  Attendance::create($attendance_data);

            $attendance->projects()->sync($request_data["project_ids"]);

            $observer = new AttendanceObserver();
            $observer->updated_action($attendance, 'create');


            $this->adjust_payroll_on_attendance_update($attendance, 0);


            $this->send_notification($attendance, $attendance->employee, "Attendance Taken", "create", "attendance");

            DB::commit();
            return response($attendance, 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError($e, 500, $request);
        }
    }


    public function createMultipleAttendance(AttendanceMultipleCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            if (!$request->user()->hasPermissionTo('attendance_create')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $request_data = $request->validated();
            $setting_attendance = $this->get_attendance_setting();

            $user = User::where([
                "id" =>   $request_data["user_id"]
            ])
                ->first();

            if (!$user) {
                throw new Exception("Some thing went wrong getting user.");
            }



            $attendances_data = collect($request_data["attendance_details"])->map(function ($item) use ($request_data, $setting_attendance, $user) {



                if (empty($item["project_ids"])) {
                    $item["project_ids"] = [UserProject::where([
                        "user_id" => $user->id
                    ])
                        ->first()->project_id];
                }
                if (empty($item["work_location_id"])) {
                    $item["work_location_id"] = $user->work_locations[0]->id;
                }

                if (empty($item["is_present"])) {
                    $item["attendance_records"] = [
                        [
                            "in_time" => "00:00:00",
                            "out_time" => "00:00:00",
                        ]
                    ];
                }

                $item = $this->process_attendance_data($item, $setting_attendance, $request_data["user_id"]);

                return  $item;
            });




            $employee = User::where([
                "id" => $request_data["user_id"]
            ])
                ->first();

            if (!$employee) {
                return response()->json([
                    "message" => "someting_went_wrong", 500
                ]);
            }

            $created_attendances = [];
            foreach ($attendances_data as $attendance_data) {
                $created_attendance = $employee->attendances()->create($attendance_data);

                if ($created_attendance) {
                    $created_attendance->projects()->sync($attendance_data["project_ids"]);

                    $observer = new AttendanceObserver();
                    $observer->updated_action($created_attendance, 'create');

                    $this->adjust_payroll_on_attendance_update($created_attendance, 0);

                    $created_attendances[] = $created_attendance;
                }
            }

            $this->send_notification($employee->attendances()
            ->orderByDesc("attendances.id")
            ->take(count($attendances_data))->get(), $employee, "Attendance Taken", "create", "attendance");


            DB::commit();
            if (!empty($created_attendances)) {
                return response(['attendances' => $created_attendances], 201);
            } else {

                return response(['error' => 'Failed to create attendance records'], 500);
            }
            return response([], 201);
        } catch (Exception $e) {

            DB::rollBack();
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }


    public function updateAttendance(AttendanceUpdateRequest $request)
    {

        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            if (!$request->user()->hasPermissionTo('attendance_update')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }


            $request_data = $request->validated();
            $request_data["is_present"] =  $this->calculate_total_present_hours($request_data["attendance_records"]) > 0;



            $setting_attendance = $this->get_attendance_setting();


            $attendance_data = $this->process_attendance_data($request_data, $setting_attendance, $request_data["user_id"]);


            $attendance_query_params = [
                "id" => $request_data["id"],
                "business_id" => auth()->user()->business_id
            ];

            $attendance = Attendance::where($attendance_query_params)->first();
            if ($attendance) {
                $attendance->fill(collect($attendance_data)->only([
                    'note',
                    "in_geolocation",
                    "out_geolocation",
                    'user_id',
                    'in_date',
                    'does_break_taken',

                    "behavior",
                    "capacity_hours",
                    "work_hours_delta",
                    "break_type",
                    "break_hours",
                    "total_paid_hours",
                    "regular_work_hours",
                    "work_shift_start_at",
                    "work_shift_end_at",
                    "work_shift_history_id",
                    "holiday_id",
                    "leave_record_id",
                    "is_weekend",

                    "overtime_hours",
                    "punch_in_time_tolerance",
                    "status",
                    'work_location_id',
                    "is_present",
                    "is_active",


                    "regular_hours_salary",
                    "overtime_hours_salary",
                ])->toArray());
                $attendance->save();
            }


            $attendance->projects()->sync($request_data["project_ids"]);



            $observer = new AttendanceObserver();
            $observer->updated_action($attendance, 'update');


            $this->adjust_payroll_on_attendance_update($attendance, 0);





            $this->send_notification($attendance, $attendance->employee, "Attendance updated", "update", "attendance");
            DB::commit();

            return response($attendance, 201);
        } catch (Exception $e) {
            DB::rollBack();
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }


    public function approveAttendance(AttendanceApproveRequest $request)
    {

        DB::beginTransaction();
        try {

            $this->storeActivity($request, "DUMMY activity", "DUMMY description");


            if (!$request->user()->hasPermissionTo("attendance_approve")) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }


            $request_data = $request->validated();



            $setting_attendance = $this->get_attendance_setting();
            $attendance_query_params = [
                "id" => $request_data["attendance_id"],
                "business_id" => auth()->user()->business_id
            ];
            $attendance = $this->find_attendance($attendance_query_params);





            $user = User::where([
                "id" =>  auth()->user()->id
            ])
                ->first();


            if ($this->is_special_user($user, $setting_attendance) || $this->is_special_role($user, $setting_attendance) || $user->hasRole("business_owner")) {
                $attendance->status = $request_data["is_approved"] ? "approved" : "rejected";
            }


            $attendance->save();






            $observer = new AttendanceObserver();
            $observer->updated_action($attendance, $request_data["is_approved"] ? "approve" : "reject");






            $this->adjust_payroll_on_attendance_update($attendance, $request_data["add_in_next_payroll"]);


            if (!empty($request_data["add_in_next_payroll"]) && !empty($request_data["is_approved"])) {
                AttendanceArrear::where([
                    "attendance_id" => $attendance->id
                ])
                    ->update(["status" => "approved"]);
            }


            $message = $attendance->status == "approved" ? "Attendance approved" : "Attendance rejected";


            $this->send_notification($attendance, $attendance->employee, $message, $attendance->status, "attendance");



            DB::commit();
            return response($attendance, 200);
        } catch (Exception $e) {
            DB::rollBack();
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }


    public function approveAttendanceArrear(AttendanceArrearApproveRequest $request)
    {

        DB::beginTransaction();
        try {

            $this->storeActivity($request, "DUMMY activity", "DUMMY description");


            if (!$request->user()->hasPermissionTo("attendance_approve")) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }


            $request_data = $request->validated();

            foreach ($request_data["attendance_ids"] as $attendance_id) {

                $attendance_arrear = AttendanceArrear::where([
                    "attendance_id" => $attendance_id
                ])
                    ->first();

                if ($attendance_arrear) {
                    if ($attendance_arrear->status == "pending_approval") {
                        $attendance_arrear->status = "approved";
                        $attendance_arrear->save();
                    }
                }
            }





            DB::commit();
            return response($attendance_arrear, 200);
        } catch (Exception $e) {
            DB::rollBack();
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }


    public function getAttendances(Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('attendance_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

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

            $attendancesQuery = $this->attendanceComponent->updateAttendanceQuery($all_manager_department_ids, $attendancesQuery);

            $attendances = $this->retrieveData($attendancesQuery, "attendances.id");

            if (!empty($request->response_type) && in_array(strtoupper($request->response_type), ['PDF', 'CSV'])) {
                if (strtoupper($request->response_type) == 'PDF') {
                    $pdf = PDF::loadView('pdf.attendances', ["attendances" => $attendances]);
                    return $pdf->download(((!empty($request->file_name) ? $request->file_name : 'attendance') . '.pdf'));
                } elseif (strtoupper($request->response_type) === 'CSV') {

                    return Excel::download(new AttendancesExport($attendances), ((!empty($request->file_name) ? $request->file_name : 'attendance') . '.csv'));
                }
            } else {
                return response()->json($attendances, 200);
            }

            return response()->json($attendances, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }

    public function getAttendancesV2(Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            if (!$request->user()->hasPermissionTo('attendance_view') && !request()->boolean("show_my_data")) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }


        $setting_attendance = $this->get_attendance_setting();

            $start_date = !empty($request->start_date) ? $request->start_date : Carbon::now()->startOfYear()->format('Y-m-d');
            $end_date = !empty($request->end_date) ? $request->end_date : Carbon::now()->endOfYear()->format('Y-m-d');

            $data = $this->attendanceComponent->getAttendanceV2Data();


            $scheduleInformations = collect(!empty($request->user_id)?[$request->user_id]:$data["data"]->pluck("user_id")->unique())->map(function($user_id) use($start_date,$end_date) {
                return $this->userManagementComponent->getScheduleInformationData($user_id,$start_date,$end_date);
           });

            $data["data_highlights"]["total_schedule_hours"] = collect($scheduleInformations)->sum("total_capacity_hours");


            $data['data_highlights']['total_leave_hours'] = max(
                0,
                $data['data_highlights']['total_schedule_hours'] - $data['data_highlights']['total_active_hours']
            );


   if ($data['data_highlights']["total_available_hours"] == 0 || $data['data_highlights']['total_schedule_hours'] == 0) {
    $data['data_highlights']['total_work_availability_per_centum'] = 0;
} else {
    $data['data_highlights']['total_work_availability_per_centum'] = ($data['data_highlights']["total_available_hours"] / $data['data_highlights']['total_schedule_hours']) * 100;
}


   if (!empty($setting_attendance->work_availability_definition)) {
    if (empty($data["data"])) {
        $data['data_highlights']['work_availability'] = 'no data';
    } elseif ($data['data_highlights']['total_work_availability_per_centum'] >= $setting_attendance->work_availability_definition) {
        $data['data_highlights']['work_availability'] = 'good';
    } else {
        $data['data_highlights']['work_availability'] = 'bad';
    }
} else {
    $data['data_highlights']['work_availability'] = 'good';
}




            return response()->json($data, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }

    public function getAttendancesV3(Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('attendance_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $all_manager_department_ids = $this->get_all_departments_of_manager();
            $business_id =  $request->user()->business_id;

            $start_date = !empty($request->start_date) ? $request->start_date : Carbon::now()->startOfYear()->format('Y-m-d');
            $end_date = !empty($request->end_date) ? $request->end_date : Carbon::now()->endOfYear()->format('Y-m-d');


            $employees = User::with(
                ["departments"]
            )
                ->whereHas("department_user.department", function ($query) use ($all_manager_department_ids) {
                    $query->whereIn("departments.id", $all_manager_department_ids);
                })

                ->where(
                    [
                        "users.business_id" => $business_id
                    ]
                )

                ->when(!empty($request->search_key), function ($query) use ($request) {
                    return $query->where(function ($query) use ($request) {
                        $term = $request->search_key;
                    });
                })

                ->when(!empty($request->user_id), function ($query) use ($request) {
                    return $query->whereHas("attendances", function ($q) use ($request) {
                        $idsArray = explode(',', $request->user_id);
                        $q->whereIn('attendances.user_id', $idsArray);
                    });
                })
                ->when(empty($request->user_id), function ($query) use ($request) {
                    $query->whereNotIn("users.id", [auth()->user()->id]);
                })
                ->when(!empty($request->order_by) && in_array(strtoupper($request->order_by), ['ASC', 'DESC']), function ($query) use ($request) {
                    return $query->orderBy("users.id", $request->order_by);
                }, function ($query) {
                    return $query->orderBy("users.id", "DESC");
                })
                ->select(
                    "users.id",
                    "users.first_Name",
                    "users.middle_Name",
                    "users.last_Name",
                    "users.image",
                )
                ->when(!empty($request->per_page), function ($query) use ($request) {
                    return $query->paginate($request->per_page);
                }, function ($query) {
                    return $query->get();
                });




            $startDate = Carbon::parse(($start_date . ' 00:00:00'));
            $endDate = Carbon::parse(($end_date . ' 23:59:59'));


            $dateArray = [];
            for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                $dateArray[] = $date->format('Y-m-d');
            }


            $employee_ids = $employees->pluck("id");

            $leave_records = LeaveRecord::whereHas('leave',    function ($query) use ($employee_ids) {
                $query->whereIn("leaves.user_id",  $employee_ids)
                    ->where("leaves.status", "approved");
            })
                ->where('date', '>=', $start_date . ' 00:00:00')
                ->where('date', '<=', ($end_date . ' 23:59:59'))
                ->get();


            $attendances = Attendance::where("attendances.status", "approved")
                ->whereIn('attendances.user_id', $employee_ids)
                ->where('attendances.in_date', '>=', $start_date . ' 00:00:00')
                ->where('attendances.in_date', '<=', ($end_date . ' 23:59:59'))

                ->get();



            $employees =   $employees->map(function ($employee) use ($dateArray, $attendances, $leave_records) {



                $all_parent_department_ids = $this->all_parent_departments_of_user($employee->id);



                $total_paid_hours = 0;
                $total_paid_leave_hours = 0;
                $total_paid_holiday_hours = 0;
                $total_leave_hours = 0;
                $total_capacity_hours = 0;
                $total_balance_hours = 0;


                $employee->datewise_attendanes = collect($dateArray)->map(
                    function ($date) use ($attendances, $leave_records, &$total_balance_hours, &$total_paid_hours, &$total_capacity_hours, &$total_leave_hours, &$total_paid_leave_hours, &$total_paid_holiday_hours, $employee, $all_parent_department_ids) {

                        $holiday = $this->get_holiday_details($date, $employee->id, $all_parent_department_ids);


                        $attendance = $attendances->first(function ($attendance) use ($date, $employee) {
                            $in_date = Carbon::parse($attendance->in_date)->format("Y-m-d");
                            return (($in_date == $date) && ($attendance->user_id == $employee->id));
                        });

                        $leave_record = $leave_records->first(function ($leave_record) use ($date, $employee, &$total_leave_hours) {
                            $leave_date = Carbon::parse($leave_record->date)->format("Y-m-d");
                            if (($leave_record->user_id != $employee->id) || ($date != $leave_date)) {
                                return false;
                            }
                            $total_leave_hours += $leave_record->leave_hours;
                            return true;
                        });

                        $result_is_present = 0;
                        $result_paid_hours = 0;
                        $result_balance_hours = 0;


                        if ($leave_record) {
                            if ($leave_record->leave->leave_type->type == "paid") {
                                $paid_leave_hours =  $leave_record->leave_hours;
                                $total_paid_leave_hours += $paid_leave_hours;
                                $result_paid_hours += $paid_leave_hours;
                                $total_paid_hours +=  $paid_leave_hours;
                            }
                        }

                        if ($holiday) {
                            if (!$employee->weekly_contractual_hours || !$employee->minimum_working_days_per_week) {
                                $holiday_hours = 0;
                            } else {
                                $holiday_hours = $employee->weekly_contractual_hours / $employee->minimum_working_days_per_week;
                            }

                            $total_paid_holiday_hours += $holiday_hours;
                            $result_paid_hours += $holiday_hours;
                            $total_paid_hours += $holiday_hours;
                        }


                        if ($attendance) {
                            $total_capacity_hours += $attendance->capacity_hours;
                            if ($attendance->total_paid_hours > 0) {
                                $result_is_present = 1;

                                $result_balance_hours = $attendance->overtime_hours;
                                $total_paid_hours += $attendance->total_paid_hours;
                                $total_balance_hours += $attendance->overtime_hours;
                                $result_paid_hours += $attendance->total_paid_hours;
                            }
                        }

                        if ($leave_record || $attendance || $holiday) {
                            return [
                                'date' => Carbon::parse($date)->format("d-m-Y"),
                                'is_present' => $result_is_present,
                                'paid_hours' => $result_paid_hours,
                                "result_balance_hours" => $result_balance_hours,
                                'capacity_hours' => $attendance ? $attendance->capacity_hours : 0,
                                "paid_leave_hours"   => $leave_record ? (($leave_record->leave->leave_type->type == "paid") ? $leave_record->leave_hours : 0) : 0
                            ];
                        }

                        return  null;
                    }
                )
                    ->filter()
                    ->values();


                $employee->total_balance_hours = $total_balance_hours;
                $employee->total_leave_hours = $total_leave_hours;
                $employee->total_paid_leave_hours = $total_paid_leave_hours;
                $employee->total_paid_holiday_hours = $total_paid_holiday_hours;
                $employee->total_paid_hours = $total_paid_hours;
                $employee->total_capacity_hours = $total_capacity_hours;
                return $employee;
            });




            return response()->json($employees, 200);
        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }


    public function getAttendanceArrears(Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('attendance_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $all_manager_department_ids = $this->get_all_departments_of_manager();

            $business_id =  $request->user()->business_id;

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
            ])


                ->where(
                    [
                        "attendances.business_id" => $business_id
                    ]
                )

                ->when(
                    !empty($request->arrear_status),
                    function ($query) use ($request) {
                        $query->whereHas("arrear", function ($query) use ($request) {
                            $query
                                ->where(
                                    "attendance_arrears.status",
                                    $request->arrear_status
                                );
                        });
                    },
                    function ($query) use ($request) {
                        $query->whereHas("arrear", function ($query) use ($request) {
                            $query
                                ->whereNotNull(
                                    "attendance_arrears.status"
                                );
                        });
                    }

                );

            $attendancesQuery = $this->attendanceComponent->updateAttendanceQuery($all_manager_department_ids, $attendancesQuery);

            $attendances = $this->retrieveData($attendancesQuery, "attendances.id");






            if (!empty($request->response_type) && in_array(strtoupper($request->response_type), ['PDF', 'CSV'])) {
                if (strtoupper($request->response_type) == 'PDF') {
                    $pdf = PDF::loadView('pdf.attendances', ["attendances" => $attendances]);
                    return $pdf->download(((!empty($request->file_name) ? $request->file_name : 'attendance') . '.pdf'));
                } elseif (strtoupper($request->response_type) === 'CSV') {

                    return Excel::download(new AttendancesExport($attendances), ((!empty($request->file_name) ? $request->file_name : 'attendance') . '.csv'));
                }
            } else {
                return response()->json($attendances, 200);
            }

            return response()->json($attendances, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }




    public function getAttendanceById($id, Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('attendance_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            $all_manager_department_ids = $this->get_all_departments_of_manager();
            $business_id =  $request->user()->business_id;

            $attendance =  Attendance::with(

                [
                    "employee",
                    "projects" => function ($query) {
                        $query->select(
                            'projects.id',

                        );
                    },


                ]




            )->where([
                "id" => $id,
                "business_id" => $business_id
            ])
                ->whereHas("employee.department_user.department", function ($query) use ($all_manager_department_ids) {
                    $query->whereIn("departments.id", $all_manager_department_ids);
                })

                ->first();
            if (!$attendance) {

                return response()->json([
                    "message" => "no data found"
                ], 404);
            }

            return response()->json($attendance, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }


    public function getCurrentAttendance(Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            $all_manager_department_ids = $this->get_all_departments_of_manager();


            $attendance =  Attendance::with(
                [
                    "employee" => function ($query) {
                        $query->select(
                            'users.id',
                        );
                    },
                    "projects" => function ($query) {
                        $query->select(
                            'projects.id',
                            'projects.name',
                        );
                    },
                    "work_location"



                ]


            )->where([
                "business_id" => auth()->user()->business_id
            ])
                ->where("created_by", auth()->user()->id)


                ->orderByDesc("attendances.id")
                ->first();

            if (empty($attendance)) {
                return response()->json([], 200);
            }

            $isCheckedIn = collect($attendance->records)->contains(function ($item) {
                return ($item->out_time == $item->in_time);
            });


            $attendance_in_date = Carbon::parse($attendance->in_date);
            $isToday = $attendance_in_date->isToday();



            if (!$isCheckedIn && !$isToday) {
                return response()->json([], 200);
            }

            return response()->json($attendance, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }


    public function deleteAttendancesByIds(Request $request, $ids)
    {


        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('attendance_delete')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            $all_manager_department_ids = $this->get_all_departments_of_manager();
            $business_id =  $request->user()->business_id;
            $idsArray = explode(',', $ids);
            $existingIds = Attendance::where([
                "business_id" => $business_id
            ])
                ->whereHas("employee.department_user.department", function ($query) use ($all_manager_department_ids) {
                    $query->whereIn("departments.id", $all_manager_department_ids);
                })
                ->whereHas("employee", function ($query) {
                    $query->whereNotIn("users.id", [auth()->user()->id]);
                })

                ->whereIn('id', $idsArray)
                ->select('id')
                ->get()
                ->pluck('id')
                ->toArray();
            $nonExistingIds = array_diff($idsArray, $existingIds);

            if (!empty($nonExistingIds)) {
                return response()->json([
                    "message" => "Some or all of the specified data do not exist."
                ], 404);
            }

            $attendances =  Attendance::whereIn("id", $existingIds)->get();



            $payrolls = Payroll::whereHas("payroll_attendances", function ($query) use ($existingIds) {
                $query->whereIn("payroll_attendances.attendance_id", $existingIds);
            })->get();

            PayrollAttendance::whereIn("attendance_id", $existingIds)
                ->delete();



            Attendance::whereIn('id', $existingIds)->delete();








            $this->send_notification($attendances, $attendances->first()->employee, "Attendance deleted", "delete", "attendance");

            return response()->json(["message" => "data deleted sussfully", "deleted_ids" => $existingIds], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }




    public function createMultipleBypassAttendanceV1(AttendanceBypassMultipleCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            if (!$request->user()->hasRole('business_owner')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $request_data = $request->validated();




            if (empty($request_data["user_ids"])) {
                $users  =  User::where([
                    "business_id" => auth()->user()->business_id
                ])
                    ->select(
                        "id",
                        "joining_date",
                        'first_Name',
                        'last_Name',
                        'middle_Name',
                    )
                    ->get();
            } else {
                $users  =  User::where([
                    "business_id" => auth()->user()->business_id
                ])
                    ->whereIn("id", $request_data["user_ids"])
                    ->select(
                        "id",
                        "joining_date",
                        'first_Name',
                        'last_Name',
                        'middle_Name',
                    )
                    ->get();
            }

            $attendanceNotCreatedForUsers = collect();

            $allAttendanceData = collect();



            foreach ($users as $user) {


                $start_date = Carbon::parse($request_data["start_date"]);
                $end_date = Carbon::parse($request_data["end_date"]);

                $joining_date = Carbon::parse($user->joining_date);

                if ($joining_date->gt($end_date)) {
                    $attendanceNotCreatedForUsers->push($user);
                    continue;
                }

                if ($joining_date->gt($start_date)) {
                    $start_date = $joining_date;
                }



                $all_parent_department_ids = $this->all_parent_departments_of_user($user->id);



                $existingAttendanceDates = $this->get_existing_attendanceDates($start_date, $end_date, $user->id);
                $holiday_dates =  $this->holidayComponent->get_holiday_dates($start_date, $end_date, $user->id, $all_parent_department_ids);
                $leave_dates =  $this->leaveComponent->get_already_taken_leave_records($start_date, $end_date, $user->id, $all_parent_department_ids);





                $uniqueRestrictedDates = collect($existingAttendanceDates)
                    ->merge($holiday_dates)
                    ->merge($leave_dates)
                    ->unique()
                    ->toArray();



                $date_range = $start_date->daysUntil($end_date);

                $attendance_details = [];

                foreach ($date_range as $date) {

                    $dateString = $date->format('Y-m-d');


                    if (in_array($dateString, $uniqueRestrictedDates)) {
                        continue;
                    }

                    $temp_data["in_date"] = $date;
                    $temp_data["does_break_taken"] = 1;

                    $temp_data["is_present"] = 1;

                    $temp_data["work_location_id"] = $request_data["work_location_id"];
                    $temp_data["user_id"] = $user->id;

                    array_push($attendance_details, $temp_data);
                }




                $workShiftHistories =  $this->get_work_shift_histories($start_date, $end_date, $user->id, ["flexible"]);


                $salaryHistories = $this->get_salary_infos($user->id, $start_date, $end_date);







                $attendances_data =  collect($attendance_details)->map(function ($item) use ($user, $workShiftHistories, $salaryHistories, &$allAttendanceData) {

                    $itemInDate = Carbon::parse($item["in_date"]);

                    $work_shift_history = $workShiftHistories->first(function ($history) use ($itemInDate) {
                        $fromDate = Carbon::parse($history->from_date);
                        $toDate = $history->to_date ? Carbon::parse($history->to_date) : null;

                        return $itemInDate->greaterThanOrEqualTo($fromDate)
                            && ($toDate === null || $itemInDate->lessThan($toDate));
                    });



                    if (empty($work_shift_history)) {
                        return false;
                    }


                    $work_shift_details =  $this->get_work_shift_detailsV3($work_shift_history, $item["in_date"]);


                    if (empty($work_shift_details)) {
                        return false;
                    }




                    $item["attendance_records"][0]["in_time"] = $work_shift_details->start_at;
                    $item["attendance_records"][0]["out_time"] = $work_shift_details->end_at;


                    $item["attendance_records"] =     json_encode($item["attendance_records"]);


                    $attendance_data = $this->prepare_data_on_attendance_create($item, $user->id);
                    $attendance_data["status"] = "approved";



                    $user_salary_info = $salaryHistories->first(function ($history) use ($itemInDate) {
                        $fromDate = Carbon::parse($history["from_date"]);
                        $toDate = $history["to_date"] ? Carbon::parse($history["to_date"]) : null;

                        return $itemInDate->greaterThanOrEqualTo($fromDate)
                            && ($toDate === null || $itemInDate->lessThan($toDate));
                    });






                    $capacity_hours = $this->calculate_capacity_hours($work_shift_details);


                    $total_present_hours = $this->calculate_total_present_hours(json_decode($attendance_data["attendance_records"], true));

                    $total_paid_hours = $this->adjust_paid_hours($attendance_data["does_break_taken"], $total_present_hours, $work_shift_history);



                    $attendance_data["break_type"] = $work_shift_history->break_type;
                    $attendance_data["break_hours"] = $work_shift_history->break_hours;
                    $attendance_data["behavior"] = "regular";
                    $attendance_data["capacity_hours"] = $capacity_hours;
                    $attendance_data["work_hours_delta"] = 0;
                    $attendance_data["total_paid_hours"] = $total_paid_hours;
                    $attendance_data["regular_work_hours"] = $total_paid_hours;
                    $attendance_data["work_shift_start_at"] = $work_shift_details->start_at;
                    $attendance_data["work_shift_end_at"] =  $work_shift_details->end_at;
                    $attendance_data["work_shift_history_id"] = $work_shift_history->id;

                    $attendance_data["is_weekend"] = $work_shift_details->is_weekend;
                    $attendance_data["overtime_hours"] = 0;

                    $attendance_data["regular_hours_salary"] =   $total_paid_hours * $user_salary_info["hourly_salary"];
                    $attendance_data["overtime_hours_salary"] =   0;


                    $attendance_data["created_at"] =   now();
                    $attendance_data["updated_at"] =   now();



                    $attendance = collect($attendance_data)->only([
                        'note',
                        "in_geolocation",
                        "out_geolocation",
                        'user_id',
                        'in_date',
                        'does_break_taken',
                        "behavior",
                        "capacity_hours",
                        "work_hours_delta",
                        "break_type",
                        "break_hours",
                        "total_paid_hours",
                        "regular_work_hours",
                        "work_shift_start_at",
                        "work_shift_end_at",
                        "work_shift_history_id",
                        "holiday_id",
                        "leave_record_id",
                        "is_weekend",
                        "overtime_hours",

                        "status",
                        'work_location_id',

                        "is_active",
                        "business_id",
                        "created_by",
                        "regular_hours_salary",
                        "overtime_hours_salary",
                        "attendance_records",
                        "is_present",
                        "created_at",
                        "updated_at"
                    ])
                        ->toArray();



                 $allAttendanceData->push($attendance);
                    return  $attendance;
                })->filter()->values();

                if(!$attendances_data->count()){
                    $attendanceNotCreatedForUsers->push($user);
                }


            }



            Log::info("........................................................ attendances data");
            Log::info(json_encode($allAttendanceData->toArray()));
            Log::info("........................................................ attendances data ");

            Log::info("........................................................ attendances data count");
            Log::info($allAttendanceData->count());
            Log::info("........................................................ attendances data count");



            foreach ($allAttendanceData->chunk(1000) as $chunkedAttendances) {
                Log::info("Chunk callback invoked.");
                Log::info("Chunk size: " . count($chunkedAttendances));
                Log::info("Chunk data: " . json_encode($chunkedAttendances));
                Attendance::insert($chunkedAttendances->toArray());
            }



            $latest_attendances = Attendance::whereIn(
                "user_id",
                $users->pluck("id")
            )
            ->orderByDesc("attendances.id")
                ->take($attendances_data->count())
                ->get();

            Log::info("........................................................ uploaded attendances");
            Log::info(json_encode($latest_attendances));
            Log::info("........................................................ uploaded attendances");

            Log::info("........................................................ uploaded attendances count");
            Log::info($latest_attendances->count());
            Log::info("........................................................ uploaded attendances count");



            $attendance_history_data = [];


            foreach ($latest_attendances as $attendance) {


                $attendance_history = $attendance->toArray();

                $attendance_history = array_merge($attendance_history,  [
                    'attendance_id' => $attendance->id,
                    'actor_id' => auth()->user()->id,
                    'action' => 'create',
                    'attendance_created_at' => $attendance->created_at,
                    'attendance_updated_at' => $attendance->updated_at,
                    "attendance_records" => json_encode($attendance->attendance_records)

                ]);

                $attendance_history_data[] = collect($attendance_history)->only([
                    "attendance_id",
                    "actor_id",
                    "action",

                    "attendance_created_at",
                    "attendance_updated_at",

                    'note',
                    "in_geolocation",
                    "out_geolocation",
                    'user_id',

                    'in_date',
                    'does_break_taken',

                    "behavior",
                    "capacity_hours",
                    "work_hours_delta",
                    "break_type",
                    "break_hours",
                    "total_paid_hours",
                    "regular_work_hours",
                    "work_shift_start_at",
                    "work_shift_end_at",
                    "work_shift_history_id",
                    "holiday_id",
                    "leave_record_id",
                    "is_weekend",

                    "overtime_hours",

                    "status",
                    'work_location_id',

                    "is_active",
                    "business_id",
                    "created_by",
                    "regular_hours_salary",
                    "overtime_hours_salary",
                    "attendance_records",
                ])
                    ->toArray();
            }




            collect($attendance_history_data)
                ->chunk(1000, function ($chunkedHistoryData) {
                    foreach ($chunkedHistoryData as $historyData) {
                        AttendanceHistory::insert($historyData->toArray());
                    }
                });



            $this->send_notification($latest_attendances, $user, "Attendance Taken", "create", "attendance", $all_parent_department_ids);


            DB::commit();

            return response()->json(["ok" => true,"attendance_not_createdFor_users" => $attendanceNotCreatedForUsers->toArray()], 201);
        } catch (Exception $e) {
            DB::rollBack();
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }



    public function createMultipleBypassAttendanceV2(AttendanceBypassMultipleCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            if (!$request->user()->hasRole('business_owner')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $request_data = $request->validated();



            $setting_attendance = $this->get_attendance_setting();


            if (empty($request_data["user_ids"])) {
                $users  =  User::where([
                    "business_id" => auth()->user()->business_id
                ])
                    ->get();
            } else {
                $users  =  User::where([
                    "business_id" => auth()->user()->business_id
                ])
                    ->whereIn("id", $request_data["user_ids"])
                    ->get();
            }


            foreach ($users as $user) {


                $start_date = Carbon::parse($request_data["start_date"]);
                $end_date = Carbon::parse($request_data["end_date"]);

                $joining_date = Carbon::parse($user->joining_date);

                if ($joining_date->gt($end_date)) {
                    continue;
                }

                if ($joining_date->gt($start_date)) {
                    $start_date = $joining_date;
                }




                $all_parent_department_ids = $this->all_parent_departments_of_user($user->id);



                $existingAttendanceDates = $this->get_existing_attendanceDates($start_date, $end_date, $user->id);
                $holiday_dates =  $this->holidayComponent->get_holiday_dates($start_date, $end_date, $user->id, $all_parent_department_ids);
                $leave_dates =  $this->leaveComponent->get_already_taken_leave_records($start_date, $end_date, $user->id, $all_parent_department_ids);





                $uniqueRestrictedDates = collect($existingAttendanceDates)
                    ->merge($holiday_dates)
                    ->merge($leave_dates)
                    ->unique()
                    ->toArray();



                $date_range = $start_date->daysUntil($end_date);

                $attendance_details = [];

                foreach ($date_range as $date) {

                    $dateString = $date->format('Y-m-d');


                    if (in_array($dateString, $uniqueRestrictedDates)) {
                        continue;
                    }

                    $temp_data["in_date"] = $date;
                    $temp_data["does_break_taken"] = 1;

                    $temp_data["is_present"] = 1;

                    $temp_data["project_ids"] = [UserProject::where([
                        "user_id" => $user->id
                    ])
                        ->first()->project_id];

                    $temp_data["work_location_id"] = $request_data["work_location_id"];
                    $temp_data["user_id"] = $user->id;

                    array_push($attendance_details, $temp_data);
                }




                $workShiftHistories =  $this->get_work_shift_histories($start_date, $end_date, $user->id, ["flexible"]);


                $salaryHistories = $this->get_salary_infos($user->id, $start_date, $end_date);


                $attendances_data =  collect($attendance_details)->map(function ($item) use ($setting_attendance, $user, $workShiftHistories, $salaryHistories) {

                    $itemInDate = Carbon::parse($item["in_date"]);
                    $work_shift_history = $workShiftHistories->first(function ($history) use ($itemInDate) {
                        $fromDate = Carbon::parse($history->from_date);
                        $toDate = $history->to_date ? Carbon::parse($history->to_date) : null;

                        return $itemInDate->greaterThanOrEqualTo($fromDate)
                            && ($toDate === null || $itemInDate->lessThan($toDate));
                    });





                    if (empty($work_shift_history)) {
                        return false;
                    }



                    $work_shift_details =  $this->get_work_shift_detailsV3($work_shift_history, $item["in_date"]);



                    if (empty($work_shift_details)) {
                        return false;
                    }




                    $item["attendance_records"][0]["in_time"] = $work_shift_details->start_at;
                    $item["attendance_records"][0]["out_time"] = $work_shift_details->end_at;


                    $item["attendance_records"] =     json_encode($item["attendance_records"]);


                    $attendance_data = $this->prepare_data_on_attendance_create($item, $user->id);
                    $attendance_data["status"] = "approved";



                    $user_salary_info = $salaryHistories->first(function ($history) use ($itemInDate) {
                        $fromDate = Carbon::parse($history["from_date"]);
                        $toDate = $history["to_date"] ? Carbon::parse($history["to_date"]) : null;

                        return $itemInDate->greaterThanOrEqualTo($fromDate)
                            && ($toDate === null || $itemInDate->lessThan($toDate));
                    });





                    $capacity_hours = $this->calculate_capacity_hours($work_shift_details);


                    $total_present_hours = $this->calculate_total_present_hours(json_decode($attendance_data["attendance_records"], true));


                    $total_paid_hours = $this->adjust_paid_hours($attendance_data["does_break_taken"], $total_present_hours, $work_shift_history);



                    $attendance_data["break_type"] = $work_shift_history->break_type;
                    $attendance_data["break_hours"] = $work_shift_history->break_hours;
                    $attendance_data["behavior"] = "regular";
                    $attendance_data["capacity_hours"] = $capacity_hours;
                    $attendance_data["work_hours_delta"] = 0;
                    $attendance_data["total_paid_hours"] = $total_paid_hours;
                    $attendance_data["regular_work_hours"] = $total_paid_hours;
                    $attendance_data["work_shift_start_at"] = $work_shift_details->start_at;
                    $attendance_data["work_shift_end_at"] =  $work_shift_details->end_at;
                    $attendance_data["work_shift_history_id"] = $work_shift_history->id;

                    $attendance_data["is_weekend"] = $work_shift_details->is_weekend;
                    $attendance_data["overtime_hours"] = 0;

                    $attendance_data["regular_hours_salary"] =   $total_paid_hours * $user_salary_info["hourly_salary"];
                    $attendance_data["overtime_hours_salary"] =   0;


                    $attendance_data["created_at"] =   now();
                    $attendance_data["updated_at"] =   now();
                    return collect($attendance_data)->only([
                        'note',
                        "in_geolocation",
                        "out_geolocation",
                        'user_id',
                        'in_date',
                        'does_break_taken',
                        "behavior",
                        "capacity_hours",
                        "work_hours_delta",
                        "break_type",
                        "break_hours",
                        "total_paid_hours",
                        "regular_work_hours",
                        "work_shift_start_at",
                        "work_shift_end_at",
                        "work_shift_history_id",
                        "holiday_id",
                        "leave_record_id",
                        "is_weekend",
                        "overtime_hours",

                        "status",
                        'work_location_id',

                        "is_active",
                        "business_id",
                        "created_by",
                        "regular_hours_salary",
                        "overtime_hours_salary",
                        "attendance_records",
                        "is_present",
                        "created_at",
                        "updated_at"
                    ])->toArray();
                })->filter()->values();






                $created_attendances = Attendance::insert($attendances_data->toArray());


                if ($created_attendances) {

                    $latest_attendances = $user->attendances()
                    ->orderByDesc("attendances.id")
                    ->take(count($attendances_data->toArray()))->get();

                    Log::info(json_encode($latest_attendances));
                    Log::info("........................................................ attendances");




                    $attendance_history_data = [];
                    $attendance_project_data = [];

                    foreach ($latest_attendances as $attendance) {


                        $attendance_history = $attendance->toArray();

                        $attendance_history = array_merge($attendance_history,  [
                            'attendance_id' => $attendance->id,
                            'actor_id' => auth()->user()->id,
                            'action' => 'create',
                            'attendance_created_at' => $attendance->created_at,
                            'attendance_updated_at' => $attendance->updated_at,
                            "attendance_records" => json_encode($attendance->attendance_records)

                        ]);

                        $attendance_history_data[] = collect($attendance_history)->only([
                            "attendance_id",
                            "actor_id",
                            "action",

                            "attendance_created_at",
                            "attendance_updated_at",

                            'note',
                            "in_geolocation",
                            "out_geolocation",
                            'user_id',

                            'in_date',
                            'does_break_taken',

                            "behavior",
                            "capacity_hours",
                            "work_hours_delta",
                            "break_type",
                            "break_hours",
                            "total_paid_hours",
                            "regular_work_hours",
                            "work_shift_start_at",
                            "work_shift_end_at",
                            "work_shift_history_id",
                            "holiday_id",
                            "leave_record_id",
                            "is_weekend",

                            "overtime_hours",

                            "status",
                            'work_location_id',

                            "is_active",
                            "business_id",
                            "created_by",
                            "regular_hours_salary",
                            "overtime_hours_salary",
                            "attendance_records",
                        ])
                            ->toArray();
                    }




                    AttendanceHistory::insert($attendance_history_data);







                    $this->send_notification($latest_attendances, $user, "Attendance Taken", "create", "attendance", $all_parent_department_ids);
                }





















             



            }
            DB::commit();

            return response()->json(["ok" => true], 201);
        } catch (Exception $e) {
            DB::rollBack();
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }
}
