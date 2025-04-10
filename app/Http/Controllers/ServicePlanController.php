<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckDiscountRequest;
use App\Http\Requests\ServicePlanCreateRequest;
use App\Http\Requests\ServicePlanUpdateRequest;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\DiscountUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\ServicePlan;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Business;

class ServicePlanController extends Controller
{
    use ErrorUtil, UserActivityUtil, BusinessUtil, DiscountUtil;


    public function createServicePlan(ServicePlanCreateRequest $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity","DUMMY description");
            return DB::transaction(function () use ($request) {
                if (!$request->user()->hasPermissionTo('business_create')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                $request_data = $request->validated();


                $request_data["is_active"] = 1;
                $request_data["created_by"] = $request->user()->id;


                $service_plan =  ServicePlan::create($request_data);

                $service_plan->discount_codes()->createMany($request_data['discount_codes']);

                return response($service_plan, 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }


    public function updateServicePlan(ServicePlanUpdateRequest $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity","DUMMY description");
            return DB::transaction(function () use ($request) {
                if (!$request->user()->hasPermissionTo('business_create')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                $request_data = $request->validated();





                $service_plan  =  tap(ServicePlan::where([
                    "id" => $request_data["id"],

                ]))->update(
                    collect($request_data)->only([
                        "name",
                        "description",
                        'set_up_amount',
                        'duration_months',
                        "price",
                        'business_tier_id',
                    ])->toArray()
                )


                    ->first();

                if (!$service_plan) {
                    return response()->json([
                        "message" => "something went wrong."
                    ], 500);
                }

                foreach ($request_data['discount_codes'] as $discountCode) {
                    $service_plan->discount_codes()->updateOrCreate(
                        ['id' => $discountCode['id']],
                        $discountCode
                    );
                }

                return response($service_plan, 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }



    public function getServicePlans(Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity","DUMMY description");
            if (!$request->user()->hasPermissionTo('business_create')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }


            $service_plans = ServicePlan::with("business_tier")
            ->when(!empty($request->search_key), function ($query) use ($request) {
                return $query->where(function ($query) use ($request) {
                    $term = $request->search_key;
                    $query->where("service_plans.name", "like", "%" . $term . "%");
                });
            })


                ->when(!empty($request->start_date), function ($query) use ($request) {
                    return $query->where('service_plans.created_at', ">=", $request->start_date);
                })
                ->when(!empty($request->end_date), function ($query) use ($request) {
                    return $query->where('service_plans.created_at', "<=", ($request->end_date . ' 23:59:59'));
                })
                ->when(!empty($request->order_by) && in_array(strtoupper($request->order_by), ['ASC', 'DESC']), function ($query) use ($request) {
                    return $query->orderBy("service_plans.id", $request->order_by);
                }, function ($query) {
                    return $query->orderBy("service_plans.id", "DESC");
                })
                ->when(!empty($request->per_page), function ($query) use ($request) {
                    return $query->paginate($request->per_page);
                }, function ($query) {
                    return $query->get();
                });;



            return response()->json($service_plans, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }


     public function getServicePlanClient(Request $request)
     {
         try {
             $this->storeActivity($request, "DUMMY activity","DUMMY description");



             $service_plans = ServicePlan::with("business_tier")
             ->when(!empty($request->search_key), function ($query) use ($request) {
                 return $query->where(function ($query) use ($request) {
                     $term = $request->search_key;

                 });
             })


                 ->when(!empty($request->start_date), function ($query) use ($request) {
                     return $query->where('service_plans.created_at', ">=", $request->start_date);
                 })
                 ->when(!empty($request->end_date), function ($query) use ($request) {
                     return $query->where('service_plans.created_at', "<=", ($request->end_date . ' 23:59:59'));
                 })
                 ->when(!empty($request->order_by) && in_array(strtoupper($request->order_by), ['ASC', 'DESC']), function ($query) use ($request) {
                     return $query->orderBy("service_plans.id", $request->order_by);
                 }, function ($query) {
                     return $query->orderBy("service_plans.id", "DESC");
                 })
                 ->when(!empty($request->per_page), function ($query) use ($request) {
                     return $query->paginate($request->per_page);
                 }, function ($query) {
                     return $query->get();
                 });;



             return response()->json($service_plans, 200);
         } catch (Exception $e) {

             return $this->sendError($e, 500, $request);
         }
     }


    public function getServicePlanById($id, Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity","DUMMY description");
            if (!$request->user()->hasPermissionTo('business_create')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $service_plan =  ServicePlan::with("discount_codes")->where([
                "id" => $id,
            ])

                ->first();
            if (!$service_plan) {

                return response()->json([
                    "message" => "no data found"
                ], 404);
            }

            return response()->json($service_plan, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }



      public function deleteServicePlansByIds(Request $request, $ids)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('business_create')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $idsArray = explode(',', $ids);
            $existingIds = ServicePlan::whereIn('id', $idsArray)

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

            $conflicts = [];


            $conflictingBusinessesExists = Business::whereIn("service_plan_id", $existingIds)->exists();
            if ($conflictingBusinessesExists) {
                $conflicts[] = "Businesses associated with the Service Plans";
            }


            if (!empty($conflicts)) {
                $conflictList = implode(', ', $conflicts);
                return response()->json([
                    "message" => "Cannot delete this data as there are records associated with it in the following areas: $conflictList. Please update these records before attempting to delete.",
                ], 409);
            }

      


            ServicePlan::destroy($existingIds);


            return response()->json(["message" => "data deleted sussfully", "deleted_ids" => $existingIds], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }



     public function checkDiscountClient(CheckDiscountRequest $request)
     {
         try {
             $this->storeActivity($request, "DUMMY activity","DUMMY description");
             return DB::transaction(function () use ($request) {


                 $request_data = $request->validated();

                 $response_data['service_plan_discount_amount'] = $this->getDiscountAmount($request_data);


                 return response($response_data, 201);
             });
         } catch (Exception $e) {
             error_log($e->getMessage());
             return $this->sendError($e, 500, $request);
         }
     }
}
