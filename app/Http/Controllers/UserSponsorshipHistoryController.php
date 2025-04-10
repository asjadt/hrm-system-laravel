<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserSponsorshipHistoryCreateRequest;
use App\Http\Requests\UserSponsorshipHistoryUpdateRequest;
use App\Http\Utils\BasicUtil;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Department;
use App\Models\EmloyeeSponsorshipHistory;
use App\Models\EmployeeSponsorship;
use App\Models\EmployeeSponsorshipHistory;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserSponsorshipHistoryController extends Controller
{
    use ErrorUtil, UserActivityUtil, BusinessUtil, BasicUtil;







    public function createUserSponsorshipHistory(UserSponsorshipHistoryCreateRequest $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity","DUMMY description");
            return DB::transaction(function () use ($request) {
                if (!$request->user()->hasPermissionTo('employee_sponsorship_history_create')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                $request_data = $request->validated();

                $request_data["created_by"] = $request->user()->id;
                $request_data["business_id"] = auth()->user()->business_id;
                $request_data["is_manual"] = 1;








                $user_sponsorship_history =  EmployeeSponsorshipHistory::create($request_data);



                return response($user_sponsorship_history, 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }


    public function updateUserSponsorshipHistory(UserSponsorshipHistoryUpdateRequest $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity","DUMMY description");
            return DB::transaction(function () use ($request) {
                if (!$request->user()->hasPermissionTo('employee_sponsorship_history_update')) {
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
                $issue_date_column = 'date_assigned';
                $expiry_date_column = 'expiry_date';

                $current_sponsorship = $this->getCurrentHistory(EmployeeSponsorshipHistory::class, 'current_sponsorship_id', $current_user_id, $issue_date_column, $expiry_date_column);


                $user_sponsorship_history_query_params = [
                    "id" => $request_data["id"],

                ];

                if ($current_sponsorship && $current_sponsorship->id == $request_data["id"]) {
                    $request_data["is_manual"] = 0;
                    $user_sponsorship_history =   EmployeeSponsorshipHistory::create($request_data);

                } else {
                    $user_sponsorship_history  =  tap(EmployeeSponsorshipHistory::where($user_sponsorship_history_query_params))->update(
                        collect($request_data)->only([
        "business_id",
        'date_assigned',
        'expiry_date',

        'note',
        "certificate_number",
        "current_certificate_status",
        "is_sponsorship_withdrawn",

        "is_manual",
        'user_id',
        "from_date",
        "to_date",

                        ])->toArray()
                    )
                        ->first();
                }





                if (!$user_sponsorship_history) {
                    return response()->json([
                        "message" => "something went wrong."
                    ], 500);
                }

                return response($user_sponsorship_history, 201);




            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }




    public function getUserSponsorshipHistories(Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity","DUMMY description");
            if (!$request->user()->hasPermissionTo('employee_sponsorship_history_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            $business_id =  $request->user()->business_id;
            $all_manager_department_ids = $this->get_all_departments_of_manager();



            $current_user_id = request()->user_id;
            $issue_date_column = 'date_assigned';
            $expiry_date_column = 'expiry_date';
            $current_sponsorship = $this->getCurrentHistory(EmployeeSponsorshipHistory::class, 'current_sponsorship_id', $current_user_id, $issue_date_column, $expiry_date_column);



            $user_sponsorship_histories = EmployeeSponsorshipHistory::with([
                "creator" => function ($query) {
                    $query->select('users.id', 'users.first_Name','users.middle_Name',
                    'users.last_Name');
                },

            ])

            ->whereHas("employee.department_user.department", function($query) use($all_manager_department_ids) {
              $query->whereIn("departments.id",$all_manager_department_ids);
           })
            ->when(!empty($request->search_key), function ($query) use ($request) {
                    return $query->where(function ($query) use ($request) {
                        $term = $request->search_key;
                        $query->where("employee_sponsorship_histories.name", "like", "%" . $term . "%");

                    });
                })


                ->when(!empty($request->user_id), function ($query) use ($request) {
                    return $query->where('employee_sponsorship_histories.user_id', $request->user_id);
                })
                ->when(empty($request->user_id), function ($query) use ($request) {
                    return $query->where('employee_sponsorship_histories.user_id', $request->user()->id);
                })
                ->when(!empty($request->start_date), function ($query) use ($request) {
                    return $query->where('employee_sponsorship_histories.created_at', ">=", $request->start_date);
                })
                ->when(!empty($request->end_date), function ($query) use ($request) {
                    return $query->where('employee_sponsorship_histories.created_at', "<=", ($request->end_date . ' 23:59:59'));
                })

                ->when(!empty($request->order_by) && in_array(strtoupper($request->order_by), ['ASC', 'DESC']), function ($query) use ($request) {
                    return $query->orderBy("employee_sponsorship_histories.expiry_date", $request->order_by);
                }, function ($query) {
                    return $query->orderBy("employee_sponsorship_histories.expiry_date", "DESC");
                })

                ->when(!empty($request->per_page), function ($query) use ($request) {
                    return $query->paginate($request->per_page);
                }, function ($query) {
                    return $query->get();
                });;



            return response()->json($user_sponsorship_histories, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }




    public function getUserSponsorshipHistoryById($id, Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity","DUMMY description");
            if (!$request->user()->hasPermissionTo('employee_sponsorship_history_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            $business_id =  $request->user()->business_id;
            $all_manager_department_ids = $this->get_all_departments_of_manager();
            $user_sponsorship_history =  EmployeeSponsorshipHistory::where([
                "id" => $id,

            ])

            ->whereHas("employee.department_user.department", function($query) use($all_manager_department_ids) {
              $query->whereIn("departments.id",$all_manager_department_ids);
           })
                ->first();
            if (!$user_sponsorship_history) {

                return response()->json([
                    "message" => "no data found"
                ], 404);
            }

            return response()->json($user_sponsorship_history, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }




    public function deleteUserSponsorshipHistoriesByIds(Request $request, $ids)
    {

        try {
            $this->storeActivity($request, "DUMMY activity","DUMMY description");
            if (!$request->user()->hasPermissionTo('employee_sponsorship_history_delete')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            $business_id =  $request->user()->business_id;
            $all_manager_department_ids = $this->get_all_departments_of_manager();
            $idsArray = explode(',', $ids);
            $existingIds = EmployeeSponsorshipHistory::whereIn('id', $idsArray)
      
            ->whereHas("employee.department_user.department", function($query) use($all_manager_department_ids) {
              $query->whereIn("departments.id",$all_manager_department_ids);
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
            EmployeeSponsorshipHistory::destroy($existingIds);


            return response()->json(["message" => "data deleted sussfully","deleted_ids" => $existingIds], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
}
