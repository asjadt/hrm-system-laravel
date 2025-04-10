<?php

namespace App\Http\Utils;

use App\Models\Coupon;
use App\Models\ServicePlanDiscountCode;
use Carbon\Carbon;
use Exception;

trait DiscountUtil
{
    
    public function getDiscountAmount($request_data)
    {

        if (!empty($request_data["service_plan_id"]) && !empty($request_data["service_plan_discount_code"])) {
            $discount =  ServicePlanDiscountCode::where([
                "code" => $request_data["service_plan_discount_code"],
                "service_plan_id" => $request_data["service_plan_id"],
            ])

                ->first();
                if(!$discount){
                    throw new Exception("Invalid discount code",403);

                }


            return $discount->discount_amount;
        }

        return 0;


    }





}
