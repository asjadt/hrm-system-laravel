<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSystemSettingRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\SystemSetting;
use Exception;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    use ErrorUtil,UserActivityUtil;


     public function updateSystemSetting(UpdateSystemSettingRequest $request)
     {

         try {
             $this->storeActivity($request, "DUMMY activity","DUMMY description");
             if (!$request->user()->hasPermissionTo('system_setting_update')) {
                 return response()->json([
                     "message" => "You can not perform this action"
                 ], 401);
             }
             $request_data = $request->validated();






             if (!empty($request_data['self_registration_enabled'])) {



                 try {

                     $stripe = new \Stripe\StripeClient($request_data['STRIPE_SECRET']);


                     $balance = $stripe->balance->retrieve();

                    

                 } catch (\Stripe\Exception\AuthenticationException $e) {

                     return response()->json([
                         "message" => "Something went wrong with the payment setup. It looks like the Stripe key you provided is invalid. Please double-check the key and try again. If you continue to experience issues, contact support."
                     ], 401);
                 } catch (\Stripe\Exception\ApiConnectionException $e) {
                     return response()->json([
                         "message" => "Something went wrong with the payment setup. There was a network error while connecting to Stripe. Please try again later or contact support."
                     ], 502);
                 } catch (\Stripe\Exception\InvalidRequestException $e) {
                     return response()->json([
                         "message" => "Something went wrong with the payment setup. The request to Stripe was invalid. Please check the details and try again."
                     ], 400);
                 } catch (\Exception $e) {
                     return response()->json([
                         "message" => "Something went wrong with the payment setup. An unexpected error occurred while verifying Stripe credentials. Please try again later or contact support."
                     ], 500);
                 }
             }


             $systemSetting = SystemSetting::first();

             if ($systemSetting) {
                 $systemSetting->fill(collect($request_data)->only([
                     'self_registration_enabled',
                     'STRIPE_KEY',
                     "STRIPE_SECRET",
                     "is_frontend_setup"
                 ])->toArray());
                 $systemSetting->save();


             } else {
               $systemSetting =  SystemSetting::create($request_data);
             }
             $systemSettingArray = $systemSetting->toArray();

             $systemSettingArray["STRIPE_KEY"] = $systemSetting->STRIPE_KEY;
             $systemSettingArray["STRIPE_SECRET"] = $systemSetting->STRIPE_SECRET;

             return response()->json($systemSettingArray, 200);
         } catch (Exception $e) {
             error_log($e->getMessage());
             return $this->sendError($e, 500, $request);
         }
     }


     public function getSystemSetting(Request $request)
     {
         try {
             $this->storeActivity($request, "DUMMY activity","DUMMY description");
             if (!$request->user()->hasPermissionTo('system_setting_view')) {
                 return response()->json([
                     "message" => "You can not perform this action"
                 ], 401);
             }


             $systemSetting = SystemSetting::first();

             $systemSettingArray = NULL;

             if(!empty($systemSetting)) {
                $systemSettingArray = $systemSetting->toArray();

                $systemSettingArray["STRIPE_KEY"] = $systemSetting->STRIPE_KEY;
                $systemSettingArray["STRIPE_SECRET"] = $systemSetting->STRIPE_SECRET;
             }


             return response()->json($systemSettingArray, 200);
         } catch (Exception $e) {

             return $this->sendError($e, 500, $request);
         }
     }

     public function getSystemSettingSettingClient(Request $request)
     {
         try {
             $this->storeActivity($request, "DUMMY activity","DUMMY description");

             $systemSetting = SystemSetting::first();

             return response()->json($systemSetting, 200);
         } catch (Exception $e) {

             return $this->sendError($e, 500, $request);
         }
     }








}
