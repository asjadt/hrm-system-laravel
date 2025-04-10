<?php

namespace App\Http\Utils;

use App\Models\Business;
use App\Models\BusinessModule;
use App\Models\BusinessTierModule;
use App\Models\Module;
use Exception;

trait ModuleUtil
{

    public function isModuleEnabled($module_name)
    {
        $user = auth()->user();
        if (empty($user->business_id)) {
            return true;
        }


        $query_params = [
            'name' => $module_name,
        ];
        $module = Module::where($query_params)->first();
        if (empty($module)) {
            return false;
        }
        if (empty($module->is_enabled)) {

            return false;
        }

        $business = Business::find($user->business_id);
        if (empty($business)) {

            return false;
        }

        $business_tier_id = $business->service_plan ? $business->service_plan->business_tier->id : 1;



        $is_enabled = false;



        $businessTierModule =    BusinessTierModule::where([
            "business_tier_id" => $business_tier_id,
            "module_id" => $module->id
        ])
            ->first();

        if (!empty($businessTierModule)) {
            $is_enabled = $businessTierModule->is_enabled;
        }



        $businessModule =    BusinessModule::where([
            "business_id" => $business->id,
            "module_id" => $module->id
        ])
            ->first();


        if (!empty($businessModule)) {
            $is_enabled = $businessModule->is_enabled;
        }


        if (!$is_enabled) {
            throw new Exception('Module is not enabled', 401);
        }


        return $is_enabled;
    }
}
