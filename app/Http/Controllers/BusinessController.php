<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRegisterBusinessRequest;
use App\Http\Requests\BusinessCreateRequest;
use App\Http\Requests\BusinessTakeOverRequest;
use App\Http\Requests\BusinessUpdatePart1Request;
use App\Http\Requests\BusinessUpdatePart2Request;
use App\Http\Requests\BusinessUpdatePart2RequestV2;
use App\Http\Requests\BusinessUpdatePart3Request;
use App\Http\Requests\BusinessUpdatePensionRequest;
use App\Http\Requests\BusinessUpdateRequest;
use App\Http\Requests\BusinessUpdateRequestPart4;
use App\Http\Requests\BusinessUpdateSeparateRequest;
use App\Http\Requests\CheckScheduleConflictRequest;
use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\MultipleImageUploadRequest;
use App\Http\Requests\GetIdRequest;
use App\Http\Utils\BasicUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\DiscountUtil;
use App\Http\Utils\EmailLogUtil;
use App\Http\Utils\UserActivityUtil;
use App\Mail\SendPassword;

use App\Models\Business;
use App\Models\BusinessPensionHistory;
use App\Models\BusinessSubscription;
use App\Models\BusinessTime;
use App\Models\ServicePlan;
use App\Models\SystemSetting;
use App\Models\User;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Invoice;

class BusinessController extends Controller
{
    use ErrorUtil, BusinessUtil, UserActivityUtil, DiscountUtil, BasicUtil, EmailLogUtil;




    public function createBusiness(BusinessCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            if (!$request->user()->hasPermissionTo('business_create')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            $request_data = $request->validated();



            $request_data["business"] = $this->businessImageStore($request_data["business"]);

            $user = User::where([
                "id" =>  $request_data['business']['owner_id']
            ])
                ->first();

            if (!$user) {
                $error =  [
                    "message" => "The given data was invalid.",
                    "errors" => ["owner_id" => ["No User Found"]]
                ];
                throw new Exception(json_encode($error), 422);
            }

            if (!$user->hasRole('business_owner')) {
                $error =  [
                    "message" => "The given data was invalid.",
                    "errors" => ["owner_id" => ["The user is not a businesses Owner"]]
                ];
                throw new Exception(json_encode($error), 422);
            }


            $request_data['business']['status'] = "pending";
            $request_data['business']['created_by'] = $request->user()->id;
            $request_data['business']['reseller_id'] = $request->user()->id;
            $request_data['business']['is_active'] = true;
            $request_data['business']['is_self_registered_businesses'] = false;
            $request_data['business']["pension_scheme_letters"] = [];
            $business =  Business::create($request_data['business']);

            $this->storeDefaultsToBusiness($business);


            DB::commit();

            return response([
                "business" => $business
            ], 201);
        } catch (Exception $e) {
            $this->businessImageRollBack($request_data);

            DB::rollBack();
            return $this->sendError($e, 500, $request);
        }
    }




    public function checkScheduleConflict(CheckScheduleConflictRequest $request)
    {


        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            return  DB::transaction(function () use (&$request) {


                $request_data = $request->validated();



                throw new Exception("this feature is not available now", 404);
            });
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }






    public function registerUserWithBusiness(AuthRegisterBusinessRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");


            if (!$request->user()->hasPermissionTo('business_create')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            $request_data = $request->validated();



            $data = $this->createUserWithBusiness($request_data);


            DB::commit();

            return response(
                [
                    "user" => $data["user"],
                    "business" => $data["business"]
                ],
                201
            );
        } catch (Exception $e) {
            $this->businessImageRollBack($request_data);
            DB::rollBack();
            return $this->sendError($e, 500, $request);
        }
    }



    public function registerUserWithBusinessClient(AuthRegisterBusinessRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");


            $request_data = $request->validated();






            $data = $this->createUserWithBusiness($request_data);


            DB::commit();

            return response(
                [
                    "user" => $data["user"],
                    "business" => $data["business"]
                ],
                201
            );
        } catch (Exception $e) {




            DB::rollBack();
            return $this->sendError($e, 500, $request);
        }
    }


    public function updateBusiness(BusinessUpdateRequest $request)
    {

        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            if (!$request->user()->hasPermissionTo('business_update')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $request_data = $request->validated();

            $business = $this->businessOwnerCheck($request_data['business']["id"], FALSE);

            $request_data["business"] = $this->businessImageStore($request_data["business"], $business->id);





            $userPrev = User::where([
                "id" => $request_data["user"]["id"]
            ]);

            $userPrev = $userPrev->first();

            if (!$userPrev) {
                throw new Exception("no user found with this id", 404);
            }




            if (!empty($request_data['user']['password'])) {
                $request_data['user']['password'] = Hash::make($request_data['user']['password']);
            } else {
                unset($request_data['user']['password']);
            }
            $request_data['user']['is_active'] = true;
            $request_data['user']['remember_token'] = Str::random(10);
            $request_data['user']['address_line_1'] = $request_data['business']['address_line_1'];
            $request_data['user']['address_line_2'] = $request_data['business']['address_line_2'];
            $request_data['user']['country'] = $request_data['business']['country'];
            $request_data['user']['city'] = $request_data['business']['city'];
            $request_data['user']['postcode'] = $request_data['business']['postcode'];
            $request_data['user']['lat'] = $request_data['business']['lat'];
            $request_data['user']['long'] = $request_data['business']['long'];

            $user  =  tap(User::where([
                "id" => $request_data['user']["id"]
            ]))->update(
                collect($request_data['user'])->only([
                    'first_Name',
                    'middle_Name',
                    'last_Name',
                    'phone',
                    'image',
                    'address_line_1',
                    'address_line_2',
                    'country',
                    'city',
                    'postcode',
                    'email',
                    'password',
                    "lat",
                    "long",
                    "gender"
                ])->toArray()
            )


                ->first();
            if (!$user) {
                throw new Exception("something went wrong updating user.", 500);
            }




            if (!empty($request_data["business"]["is_self_registered_businesses"])) {
                $request_data['business']['service_plan_discount_amount'] = $this->getDiscountAmount($request_data['business']);
            }

            $valid_stripe = false;
            $systemSetting = SystemSetting::first();

            if (!empty($systemSetting) && $systemSetting->self_registration_enabled) {
                $valid_stripe = true;
            }


            if ($valid_stripe) {
                Stripe::setApiKey($systemSetting->STRIPE_SECRET);
                Stripe::setClientId($systemSetting->STRIPE_KEY);

                if (isset($request_data["business"]["service_plan_id"]) && $business->service_plan_id !== $request_data["business"]["service_plan_id"]) {

                    if (!empty($user->stripe_id)) {

                        $subscriptions = \Stripe\Subscription::all([
                            'customer' => $user->stripe_id,
                            'status' => 'active',
                        ]);

                        foreach ($subscriptions->data as $subscription) {

                            \Stripe\Subscription::update($subscription->id, [
                                'cancel_at_period_end' => true,
                            ]);
                        }
                    }
                }
            }



            if (auth()->user()->id == $business->owner_id) {
                $request_data['business']["trail_end_date"] = $business->trail_end_date;
            }
            $business->fill(collect($request_data['business'])->only([
                "name",
                "start_date",
                "trail_end_date",
                "about",
                "web_page",
                "identifier_prefix",
                "pin_code",
                "phone",
                "email",
                "additional_information",
                "address_line_1",
                "address_line_2",
                "lat",
                "long",
                "country",
                "city",
                "currency",
                "postcode",
                "logo",
                "image",
                "background_image",
                "status",
                "is_active",

                "is_self_registered_businesses",
                "service_plan_id",
                "service_plan_discount_code",
                "service_plan_discount_amount",

                "pension_scheme_registered",
                "pension_scheme_name",
                "pension_scheme_letters",
                "number_of_employees_allowed",
                "owner_id",



            ])->toArray());

            $business->save();


            if (empty($business)) {
                return response()->json([
                    "massage" => "something went wrong"
                ], 500);
            }




            if (!empty($request_data["times"])) {

                $timesArray = collect($request_data["times"])->unique("day");








                BusinessTime::where([
                    "business_id" => $business->id
                ])
                    ->delete();

                $timesArray = collect($request_data["times"])->unique("day");
                foreach ($timesArray as $business_time) {
                    BusinessTime::create([
                        "business_id" => $business->id,
                        "day" => $business_time["day"],
                        "start_at" => $business_time["start_at"],
                        "end_at" => $business_time["end_at"],
                        "is_weekend" => $business_time["is_weekend"],
                    ]);
                }
            }

            $business->service_plan = $business->service_plan;

            DB::commit();

            return response([
                "user" => $user,
                "business" => $business
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            return $this->sendError($e, 500, $request);
        }
    }



    public function takeOverBusiness(BusinessTakeOverRequest $request)
    {

        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            if (!$request->user()->hasRole('superadmin')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $request_data = $request->validated();


            $business = $this->businessOwnerCheck($request_data["id"], FALSE);


            $business->reseller_id = auth()->user()->id;


            $business->save();


            if (empty($business)) {
                return response()->json([
                    "massage" => "something went wrong"
                ], 500);
            }




            DB::commit();

            return response([

                "business" => $business
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            return $this->sendError($e, 500, $request);
        }
    }



    public function updateBusinessPart1(BusinessUpdatePart1Request $request)
    {

        DB::beginTransaction();
        try {

            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            if (!$request->user()->hasPermissionTo('business_update')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $business = $this->businessOwnerCheck(auth()->user()->business_id, FALSE);

            $request_data = $request->validated();

            $userPrev = User::where([
                "id" => $request_data["user"]["id"]
            ]);

            $userPrev = $userPrev->first();

            if (!$userPrev) {
                throw new Exception("no user found with this id", 404);
            }







            if (!empty($request_data['user']['password'])) {
                $request_data['user']['password'] = Hash::make($request_data['user']['password']);
            } else {
                unset($request_data['user']['password']);
            }
            $request_data['user']['is_active'] = true;
            $request_data['user']['remember_token'] = Str::random(10);
            $request_data['user']['address_line_1'] = $request_data['business']['address_line_1'];
            $request_data['user']['address_line_2'] = $request_data['business']['address_line_2'];
            $request_data['user']['country'] = $request_data['business']['country'];
            $request_data['user']['city'] = $request_data['business']['city'];
            $request_data['user']['postcode'] = $request_data['business']['postcode'];
            $request_data['user']['lat'] = $request_data['business']['lat'];
            $request_data['user']['long'] = $request_data['business']['long'];
            $user  =  tap(User::where([
                "id" => $request_data['user']["id"]
            ]))->update(
                collect($request_data['user'])->only([
                    'first_Name',
                    'middle_Name',
                    'last_Name',
                    'phone',
                    'image',
                    'address_line_1',
                    'address_line_2',
                    'country',
                    'city',
                    'postcode',
                    'email',
                    'password',
                    "lat",
                    "long",
                    "gender"
                ])->toArray()
            )


                ->first();
            if (!$user) {
                throw new Exception("something went wrong updating user.", 500);
            }


            DB::commit();
            return response([
                "user" => $user,

            ], 201);
        } catch (Exception $e) {

            DB::rollBack();

            return $this->sendError($e, 500, $request);
        }
    }

    public function updateBusinessPart2(BusinessUpdatePart2Request $request)
    {

        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            if (!$request->user()->hasPermissionTo('business_update')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }



            $request_data = $request->validated();

            $business = $this->businessOwnerCheck($request_data['business']["id"], FALSE);


            if (!empty($request_data["business"]["images"])) {
                $request_data["business"]["images"] = $this->storeUploadedFiles($request_data["business"]["images"], "", "business_images");
                $this->makeFilePermanent($request_data["business"]["images"], "");
            }
            if (!empty($request_data["business"]["image"])) {
                $request_data["business"]["image"] = $this->storeUploadedFiles([$request_data["business"]["image"]], "", "business_images")[0];
                $this->makeFilePermanent([$request_data["business"]["image"]], "");
            }
            if (!empty($request_data["business"]["logo"])) {
                $request_data["business"]["logo"] = $this->storeUploadedFiles([$request_data["business"]["logo"]], "", "business_images")[0];
                $this->makeFilePermanent([$request_data["business"]["logo"]], "");
            }
            if (!empty($request_data["business"]["background_image"])) {
                $request_data["business"]["background_image"] = $this->storeUploadedFiles([$request_data["business"]["background_image"]], "", "business_images")[0];
                $this->makeFilePermanent([$request_data["business"]["background_image"]], "");
            }





            $business->fill(collect($request_data['business'])->only([
                "name",
                "start_date",
                "about",
                "web_page",
                "identifier_prefix",
                "pin_code",
                "phone",
                "email",
                "additional_information",
                "address_line_1",
                "address_line_2",
                "lat",
                "long",
                "country",
                "city",
                "postcode",
                "logo",
                "image",
                "status",
                "background_image",
                "currency",
                "number_of_employees_allowed"
            ])->toArray());

            $business->save();




            if (empty($business)) {
                return response()->json([
                    "massage" => "something went wrong"
                ], 500);
            }




            DB::commit();
            return response([
                "business" => $business
            ], 201);
        } catch (Exception $e) {

            DB::rollBack();

            return $this->sendError($e, 500, $request);
        }
    }

    public function updateBusinessPart2V2(BusinessUpdatePart2RequestV2 $request)
    {

        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            if (!$request->user()->hasPermissionTo('business_update')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }



            $request_data = $request->validated();

            $business = $this->businessOwnerCheck($request_data['business']["id"], FALSE);

            if (!empty($request_data["business"]["images"])) {
                $request_data["business"]["images"] = $this->storeUploadedFiles($request_data["business"]["images"], "", "business_images");
                $this->makeFilePermanent($request_data["business"]["images"], "");
            }
            if (!empty($request_data["business"]["image"])) {
                $request_data["business"]["image"] = $this->storeUploadedFiles([$request_data["business"]["image"]], "", "business_images")[0];
                $this->makeFilePermanent([$request_data["business"]["image"]], "");
            }
            if (!empty($request_data["business"]["logo"])) {
                $request_data["business"]["logo"] = $this->storeUploadedFiles([$request_data["business"]["logo"]], "", "business_images")[0];
                $this->makeFilePermanent([$request_data["business"]["logo"]], "");
            }
            if (!empty($request_data["business"]["background_image"])) {
                $request_data["business"]["background_image"] = $this->storeUploadedFiles([$request_data["business"]["background_image"]], "", "business_images")[0];
                $this->makeFilePermanent([$request_data["business"]["background_image"]], "");
            }





            $business->fill(collect($request_data['business'])->only([
                "name",
                "email",
                "phone",
                "address_line_1",
                "city",
                "country",
                "postcode",
                "start_date",
                "web_page",
                "identifier_prefix",
                "pin_code"


            ])->toArray());

            $business->save();




            if (empty($business)) {
                return response()->json([
                    "massage" => "something went wrong"
                ], 500);
            }




            DB::commit();
            return response([
                "business" => $business
            ], 201);
        } catch (Exception $e) {

            DB::rollBack();

            return $this->sendError($e, 500, $request);
        }
    }

    public function updateBusinessPart3(BusinessUpdatePart3Request $request)
    {

        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            if (!$request->user()->hasPermissionTo('business_update')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $business = $this->businessOwnerCheck(auth()->user()->business_id, FALSE);




            $request_data = $request->validated();



            if (!empty($request_data["times"])) {

                $timesArray = collect($request_data["times"])->unique("day");








                BusinessTime::where([
                    "business_id" => $business->id
                ])
                    ->delete();

                $timesArray = collect($request_data["times"])->unique("day");
                foreach ($timesArray as $business_time) {
                    BusinessTime::create([
                        "business_id" => $business->id,
                        "day" => $business_time["day"],
                        "start_at" => $business_time["start_at"],
                        "end_at" => $business_time["end_at"],
                        "is_weekend" => $business_time["is_weekend"],
                    ]);
                }
            }




            DB::commit();
            return response([

                "business" => $business
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            return $this->sendError($e, 500, $request);
        }
    }


    public function updateBusinessPensionInformation(BusinessUpdatePensionRequest $request)
    {


        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            if (!$request->user()->hasPermissionTo('business_update')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }


            $request_data = $request->validated();
            $business = $this->businessOwnerCheck($request_data['business']["id"], FALSE);

            $request_data["business"]["pension_scheme_letters"] = $this->storeUploadedFiles($request_data["business"]["pension_scheme_letters"], "", "pension_scheme_letters");

            $this->makeFilePermanent($request_data["business"]["pension_scheme_letters"], "");



            $pension_scheme_data =  collect($request_data['business'])->only([
                "pension_scheme_registered",
                "pension_scheme_name",
                "pension_scheme_letters",

            ])->toArray();


            $fields_to_check = [
                "pension_scheme_registered",
                "pension_scheme_name",
                "pension_scheme_letters",
            ];
            $date_fields = [];


            $fields_changed = $this->fieldsHaveChanged($fields_to_check, $business, $pension_scheme_data, $date_fields);

            if (
                $fields_changed
            ) {
                BusinessPensionHistory::create(array_merge(["created_by" => auth()->user()->id, "business_id" => $request_data['business']["id"]], $pension_scheme_data));
            }





            $business
                ->fill($pension_scheme_data)
                ->save();









            if (empty($business)) {
                return response()->json([
                    "massage" => "something went wrong"
                ], 500);
            }









            DB::commit();
            return response([
                "business" => $business
            ], 201);
        } catch (Exception $e) {

            DB::rollBack();

            return $this->sendError($e, 500, $request);
        }
    }



    public function toggleActiveBusiness(GetIdRequest $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('business_update')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            $request_data = $request->validated();

            $business = $this->businessOwnerCheck($request_data["id"], FALSE);


            if (empty($business)) {
                throw new Exception("no business found", 404);
            }


            $business->update([
                'is_active' => !$business->is_active
            ]);

            return response()->json(['message' => 'business status updated successfully'], 200);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }






    public function updateBusinessSeparate(BusinessUpdateSeparateRequest $request)
    {

        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            if (!$request->user()->hasPermissionTo('business_update')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }



            $request_data = $request->validated();
            $business = $this->businessOwnerCheck($request_data['business']["id"], FALSE);


            $business->fill(collect($request_data['business'])->only([
                "name",
                "start_date",
                "about",
                "web_page",
                "identifier_prefix",
                "pin_code",

                "phone",
                "email",
                "additional_information",
                "address_line_1",
                "address_line_2",
                "lat",
                "long",
                "country",
                "city",
                "postcode",
                "logo",
                "image",
                "status",
                "background_image",
                "currency",

                "number_of_employees_allowed"
            ])->toArray());

            $business->save();


            if (empty($business)) {
                return response()->json([
                    "massage" => "no business found"
                ], 404);
            }

            DB::commit();
            return response([
                "business" => $business
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            return $this->sendError($e, 500, $request);
        }
    }




    public function getBusinesses(Request $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('business_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $businesses = Business::with([
                "owner" => function ($query) {
                    $query->select(
                        'users.id',
                        'users.first_Name',
                        'users.middle_Name',
                        'users.last_Name'
                    );
                },
                "creator" => function ($query) {
                    $query->select(
                        'users.id',
                        'users.first_Name',
                        'users.middle_Name',
                        'users.last_Name',
                        "users.email"
                    );
                },

            ])
                ->withCount('users')
                ->when(
                    !$request->user()->hasRole('superadmin'),
                    function ($query) use ($request) {
                        $query->where(function ($query) {
                            $query

                                ->orWhere('owner_id', auth()->user()->id)
                                ->orWhere('reseller_id', auth()->user()->id)
                            ;
                        });
                    },
                )
                ->when(!empty($request->search_key), function ($query) use ($request) {
                    $term = $request->search_key;
                    return $query->where(function ($query) use ($term) {
                        $query->where("name", "like", "%" . $term . "%")
                            ->orWhere("phone", "like", "%" . $term . "%")
                            ->orWhere("email", "like", "%" . $term . "%")
                            ->orWhere("city", "like", "%" . $term . "%")
                            ->orWhere("postcode", "like", "%" . $term . "%");
                    });
                })
                ->when(!empty($request->start_date), function ($query) use ($request) {
                    return $query->where('created_at', ">=", $request->start_date);
                })
                ->when(!empty($request->end_date), function ($query) use ($request) {
                    return $query->where('created_at', "<=", ($request->end_date . ' 23:59:59'));
                })
                ->when(!empty($request->start_lat), function ($query) use ($request) {
                    return $query->where('lat', ">=", $request->start_lat);
                })
                ->when(!empty($request->end_lat), function ($query) use ($request) {
                    return $query->where('lat', "<=", $request->end_lat);
                })
                ->when(!empty($request->start_long), function ($query) use ($request) {
                    return $query->where('long', ">=", $request->start_long);
                })
                ->when(!empty($request->end_long), function ($query) use ($request) {
                    return $query->where('long', "<=", $request->end_long);
                })
                ->when(!empty($request->address), function ($query) use ($request) {
                    $term = $request->address;
                    return $query->where(function ($query) use ($term) {
                        $query->where("country", "like", "%" . $term . "%")
                            ->orWhere("city", "like", "%" . $term . "%");
                    });
                })
                ->when(!empty($request->country_code), function ($query) use ($request) {
                    return $query->orWhere("country", "like", "%" . $request->country_code . "%");
                })
                ->when(!empty($request->city), function ($query) use ($request) {
                    return $query->orWhere("city", "like", "%" . $request->city . "%");
                })



                ->when(!empty($request->created_by), function ($query) use ($request) {
                    return $query->where("created_by", $request->created_by);
                })


                ->when(!empty($request->order_by) && in_array(strtoupper($request->order_by), ['ASC', 'DESC']), function ($query) use ($request) {
                    return $query->orderBy("businesses.id", $request->order_by);
                }, function ($query) {
                    return $query->orderBy("businesses.id", "DESC");
                })
                ->when(!empty($request->per_page), function ($query) use ($request) {
                    return $query->paginate($request->per_page);
                }, function ($query) {
                    return $query->get();
                });

            return response()->json($businesses, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }



    public function getBusinessById($id, Request $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('business_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $business = $this->businessOwnerCheck($id, FALSE);

            $business->load('owner', 'times', 'service_plan');





            return response()->json($business, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }



    public function getSubscriptionsByBusinessId($id, Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            if (!$request->user()->hasPermissionTo('business_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $business = $this->businessOwnerCheck($id, false);
            $valid_stripe = false;
            $systemSetting = SystemSetting::first();

            if (!empty($systemSetting) && $systemSetting->self_registration_enabled) {
                $valid_stripe = true;
            }

            $business_subscriptions = [];
            $upcoming_business_subscriptions = [];
            $failed_attempts = [];

            if ($valid_stripe) {
                Stripe::setApiKey($systemSetting->STRIPE_SECRET);
                Stripe::setClientId($systemSetting->STRIPE_KEY);

                $stripeCustomerId = $business?->owner?->stripe_id ?? null;

                if (!empty($stripeCustomerId)) {

$stripeInvoices = \Stripe\Invoice::all([
    'customer' => $stripeCustomerId,
    'status' => 'paid',
    'limit' => 100,
]);

foreach ($stripeInvoices->data as $invoice) {
    $subscriptionId = $invoice->subscription;
    $subscriptionDetails = \Stripe\Subscription::retrieve($subscriptionId);

    $business_subscriptions[] = [
        'id' => $subscriptionId,
        'start_date' => Carbon::createFromTimestamp($subscriptionDetails->current_period_start)->toDateTimeString(),
        'end_date' => Carbon::createFromTimestamp($subscriptionDetails->current_period_end)->toDateTimeString(),
        'status' => $subscriptionDetails->status,
        'amount' => $invoice->amount_paid / 100,
        'service_plan_id' => $subscriptionDetails?->metadata?->service_plan_id ?? "",
        'service_plan_name' => $subscriptionDetails?->metadata?->service_plan_name ?? "",
        'url' => "https://dashboard.stripe.com/subscriptions/{$subscriptionId}",
    ];
}


                    $upcomingInvoice = null;
                    try {
                        $upcomingInvoice = \Stripe\Invoice::upcoming([
                            'customer' => $stripeCustomerId,
                        ]);
                    } catch (\Stripe\Exception\InvalidRequestException $e) {

                        $upcomingInvoice = null;
                    }

                    if (!empty($upcomingInvoice) && !empty($upcomingInvoice->lines->data)) {
                        foreach ($upcomingInvoice->lines->data as $subscriptionDetails) {
                            $upcoming_business_subscriptions[] = [
                                'service_plan_id' => $subscriptionDetails->price->id,
                                'start_date' => Carbon::createFromTimestamp($subscriptionDetails->period->start ?? $upcomingInvoice->period_start),
                                'end_date' => Carbon::createFromTimestamp($subscriptionDetails->period->end ?? $upcomingInvoice->period_end),
                                'amount' => $subscriptionDetails->amount / 100,
                                'service_plan_id' => $subscriptionDetails?->metadata?->service_plan_id ?? "",
                                'service_plan_name' => $subscriptionDetails?->metadata?->service_plan_name ?? "",
                                'url' => "https://dashboard.stripe.com/subscriptions/{$subscriptionDetails->subscription}",
                            ];
                        }
                    }



                    $events = \Stripe\Event::all([
                        'type' => 'invoice.payment_failed',
                        'created' => [
                            'gte' => Carbon::now()->subMonths(6)->timestamp,
                        ],
                    ]);

                    foreach ($events->data as $event) {
                        $invoice = $event->data->object;
                        $failed_attempts[] = [
                            'invoice_id' => $invoice->id,
                            'amount_due' => $invoice->amount_due / 100,
                            'attempt_count' => $invoice->attempt_count,
                            'failure_reason' => $invoice->failure_reason,
                            'failed_at' => Carbon::createFromTimestamp($invoice->created),
                            'url' => "https://dashboard.stripe.com/invoices/{$invoice->id}",
                        ];
                    }
                }
            }

            $responseData = [
                "subscriptions" => $business_subscriptions,
                "upcoming_subscriptions" => $upcoming_business_subscriptions,
                "failed_attempts" => $failed_attempts
            ];

            return response()->json($responseData, 200);
        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }




    public function getBusinessByIdV2($id, Request $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('business_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $business  = Business::where(["id" => $id])
                ->when(
                    !$request->user()->hasRole('superadmin'),
                    function ($query) use ($request) {
                        $query->where(function ($query) {
                            $query

                                ->orWhere('owner_id', auth()->user()->id)
                                ->orWhere('reseller_id', auth()->user()->id)
                            ;
                        });
                    },
                )
                ->select(
                    "id",
                    "name",
                    "email",
                    "phone",
                    "address_line_1",
                    "city",
                    "country",

                    "postcode",
                    "start_date",
                    "web_page",
                    'identifier_prefix',
                    "pin_code",
                    "reseller_id"
                )
                ->first();



            if (empty($business)) {
                throw new Exception("you are not the owner of the business or the requested business does not exist.", 401);
            }

            return response()->json($business, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }



    public function getBusinessIdByEmail($email, Request $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('business_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $business  = Business::where(["email" => $email])
                ->when(
                    !$request->user()->hasRole('superadmin'),
                    function ($query) use ($request) {
                        $query->where(function ($query) {
                            $query

                                ->orWhere('owner_id', auth()->user()->id)
                                ->orWhere('reseller_id', auth()->user()->id)
                            ;
                        });
                    },
                )
                ->select(
                    "id"
                )
                ->first();

            if (empty($business)) {
                throw new Exception("you are not the owner of the business or the requested business does not exist.", 401);
            }

            return response()->json($business, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }



    public function getBusinessPensionInformationById($id, Request $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('business_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            $business = $this->businessOwnerCheck($id, FALSE);

            if (!is_array($business->pension_scheme_letters) || empty($business->pension_scheme_letters)) {
                $business->pension_scheme_letters = [];
            } else {

                if (!is_string($business->pension_scheme_letters[0])) {
                    $business->pension_scheme_letters = [];
                }
            }

            $businessData = [
                'pension_scheme_registered' => $business->pension_scheme_registered,
                'pension_scheme_name' => $business->pension_scheme_name,
                'pension_scheme_letters' => $business->pension_scheme_letters,
            ];


            return response()->json($businessData, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }



    public function getBusinessPensionInformationHistoryByBusinessId($id, Request $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('business_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            $business = $this->businessOwnerCheck($id, FALSE);

            $businessPensionHistoriesQuery =  BusinessPensionHistory::where([
                "business_id" => $id
            ]);


            $businessPensionHistories = $this->retrieveData($businessPensionHistoriesQuery, "business_pension_histories.id");





            return response()->json($businessPensionHistories, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }



    public function deleteBusinessPensionInformationHistoryByIds(Request $request, $ids)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('business_delete')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $idsArray = explode(',', $ids);
            $existingIds = BusinessPensionHistory::whereIn('business_pension_histories.id', $idsArray)
                ->where(function ($query) {
                    $query

                        ->orWhere('owner_id', auth()->user()->id)
                        ->orWhere('reseller_id', auth()->user()->id)
                    ;
                })


                ->select('business_pension_histories.id')
                ->get()
                ->pluck('business_pension_histories.id')
                ->toArray();
            $nonExistingIds = array_diff($idsArray, $existingIds);


            if (!empty($nonExistingIds)) {
                return response()->json([
                    "message" => "Some or all of the specified data do not exist.",
                    "non_existing_ids" => $nonExistingIds
                ], 404);
            }


            BusinessPensionHistory::whereIn('id', $existingIds)->delete();

            return response()->json(["message" => "data deleted sussfully", "deleted_ids" => $existingIds], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }



    public function deleteBusinessesByIds(Request $request, $ids)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasPermissionTo('business_delete')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $idsArray = explode(',', $ids);
            $existingIds = Business::whereIn('id', $idsArray)
                ->when(
                    !request()->user()->hasRole('superadmin'),
                    function ($query) {
                        $query->where(function ($query) {
                            $query

                                ->orWhere('reseller_id', auth()->user()->id);
                        });
                    },
                )
                ->select('id')
                ->get()
                ->pluck('id')
                ->toArray();
            $nonExistingIds = array_diff($idsArray, $existingIds);


            if (!empty($nonExistingIds)) {
                return response()->json([
                    "message" => "Some or all of the specified data do not exist.",
                    "non_existing_ids" => $nonExistingIds
                ], 404);
            }


            Business::whereIn('id', $existingIds)->delete();

            return response()->json(["message" => "data deleted sussfully", "deleted_ids" => $existingIds], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }








    public function getAllBusinessesByBusinessOwner(Request $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            if (!$request->user()->hasRole('business_owner')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $businessesQuery = Business::where([
                "owner_id" => $request->user()->id
            ]);



            $businesses = $businessesQuery->orderByDesc("id")->get();
            return response()->json($businesses, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
}
