<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserPassportHistoryCreateRequest;
use App\Http\Requests\UserPassportHistoryUpdateRequest;
use App\Http\Utils\BasicUtil;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Department;
use App\Models\EmployeePassportDetail;
use App\Models\EmployeePassportDetailHistory;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserPassportHistoryController extends Controller
{
    use ErrorUtil, UserActivityUtil, BusinessUtil, BasicUtil;







    public function createUserPassportHistory(UserPassportHistoryCreateRequest $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            return DB::transaction(function () use ($request) {
                if (!$request->user()->hasPermissionTo('employee_passport_history_create')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                $request_data = $request->validated();

                $request_data["created_by"] = $request->user()->id;
                $request_data["business_id"] = auth()->user()->business_id;
                $request_data["is_manual"] = 1;






                $user_passport_history =  EmployeePassportDetailHistory::create($request_data);



                return response($user_passport_history, 201);



            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }



    public function updateUserPassportHistory(UserPassportHistoryUpdateRequest $request)
    {

        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

                if (!$request->user()->hasPermissionTo('employee_passport_history_update')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                $request_data = $request->validated();
                $request_data["created_by"] = auth()->user()->id;
                $request_data["is_manual"] = 1;
                $request_data["business_id"] = auth()->user()->business_id;

              $all_manager_department_ids = $this->get_all_departments_of_manager();


                $current_user_id =  $request_data["user_id"];
                $issue_date_column = 'passport_issue_date';
                $expiry_date_column = 'passport_expiry_date';



                $current_passport = $this->getCurrentHistory(EmployeePassportDetailHistory::class, 'current_passport_id', $current_user_id, $issue_date_column, $expiry_date_column);


                $user_passport_history_query_params = [
                    "id" => $request_data["id"],

                ];

                if ($current_passport && $current_passport->id == $request_data["id"]) {
                    $request_data["is_manual"] = 0;
                    $user_passport_history =   EmployeePassportDetailHistory::create($request_data);

                } else {
                    $user_passport_history  =  tap(EmployeePassportDetailHistory::where($user_passport_history_query_params))->update(
                        collect($request_data)->only([
                  'passport_number',
                  "passport_issue_date",
                  "passport_expiry_date",
                  "place_of_issue",
                  "from_date",
            "to_date",
        "user_id",
        "is_manual",


                        ])->toArray()
                    )
                        ->first();
                }



                if (!$user_passport_history) {
                    return response()->json([
                        "message" => "something went wrong."
                    ], 500);
                }



                DB::commit();
                return response($user_passport_history, 201);






        } catch (Exception $e) {
            DB::rollBack();
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }


    public function getUserPassportHistories(Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('employee_passport_history_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            $business_id =  $request->user()->business_id;

            $all_manager_department_ids = $this->get_all_departments_of_manager();





            $current_user_id = request()->user_id;
            $issue_date_column = 'passport_issue_date';
            $expiry_date_column = 'passport_expiry_date';
            $current_passport = $this->getCurrentHistory(EmployeePassportDetailHistory::class, 'current_passport_id', $current_user_id, $issue_date_column, $expiry_date_column);



            $user_passport_histories = EmployeePassportDetailHistory::with([
                "creator" => function ($query) {
                    $query->select(
                        'users.id',
                        'users.first_Name',
                        'users.middle_Name',
                        'users.last_Name'
                    );
                },

            ])

                ->whereHas("employee.department_user.department", function ($query) use ($all_manager_department_ids) {
                    $query->whereIn("departments.id", $all_manager_department_ids);
                })
                ->when(!empty($request->search_key), function ($query) use ($request) {
                    return $query->where(function ($query) use ($request) {
                        $term = $request->search_key;
                        $query->where("employee_passport_detail_histories.name", "like", "%" . $term . "%");

                    });
                })


                ->when(!empty($request->user_id), function ($query) use ($request) {
                    return $query->where('employee_passport_detail_histories.user_id', $request->user_id);
                })
                ->when(empty($request->user_id), function ($query) use ($request) {
                    return $query->where('employee_passport_detail_histories.user_id', $request->user()->id);
                })
                ->when(!empty($request->start_date), function ($query) use ($request) {
                    return $query->where('employee_passport_detail_histories.created_at', ">=", $request->start_date);
                })
                ->when(!empty($request->end_date), function ($query) use ($request) {
                    return $query->where('employee_passport_detail_histories.created_at', "<=", ($request->end_date . ' 23:59:59'));
                })
                ->when(!empty($request->order_by) && in_array(strtoupper($request->order_by), ['ASC', 'DESC']), function ($query) use ($request) {
                    return $query->orderBy("employee_passport_detail_histories.passport_expiry_date", $request->order_by);
                }, function ($query) {
                    return $query->orderBy("employee_passport_detail_histories.passport_expiry_date", "DESC");
                })
                ->when(!empty($request->per_page), function ($query) use ($request) {
                    return $query->paginate($request->per_page);
                }, function ($query) {
                    return $query->get();
                });


            return response()->json($user_passport_histories, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }



    public function getUserPassportHistoryById($id, Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('employee_passport_history_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            $business_id =  $request->user()->business_id;
            $all_manager_department_ids = $this->get_all_departments_of_manager();
            $user_passport_history =  EmployeePassportDetailHistory::where([
                "id" => $id,

            ])

                ->whereHas("employee.department_user.department", function ($query) use ($all_manager_department_ids) {
                    $query->whereIn("departments.id", $all_manager_department_ids);
                })
                ->first();
            if (!$user_passport_history) {

                return response()->json([
                    "message" => "no data found"
                ], 404);
            }

            return response()->json($user_passport_history, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }



    public function deleteUserPassportHistoriesByIds(Request $request, $ids)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('employee_passport_history_delete')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            $business_id =  $request->user()->business_id;
            $all_manager_department_ids = $this->get_all_departments_of_manager();
            $idsArray = explode(',', $ids);
            $existingIds = EmployeePassportDetailHistory::whereIn('id', $idsArray)
    
                ->whereHas("employee.department_user.department", function ($query) use ($all_manager_department_ids) {
                    $query->whereIn("departments.id", $all_manager_department_ids);
                })
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
            EmployeePassportDetailHistory::destroy($existingIds);


            return response()->json(["message" => "data deleted sussfully", "deleted_ids" => $existingIds], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
}
