<?php

namespace App\Http\Requests;

use App\Models\WorkLocation;
use Illuminate\Foundation\Http\FormRequest;

class WorkLocationCreateRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [

            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'is_location_enabled' => 'required|boolean',

            "is_geo_location_enabled" => 'required|boolean',
            "is_ip_enabled" => 'required|boolean',
            "max_radius" => "nullable|numeric",
            "ip_address" => "nullable|string",




            'latitude' => 'required_if:is_location_enabled,1|numeric',
            'longitude' => 'required_if:is_location_enabled,1|numeric',

            'name' => [
                "required",
                'string',
                function ($attribute, $value, $fail) {

                        $created_by  = NULL;
                        if(auth()->user()->business) {
                            $created_by = auth()->user()->business->created_by;
                        }

                        $exists = WorkLocation::where("work_locations.name",$value)

                        ->when(empty(auth()->user()->business_id), function ($query) use ( $created_by, $value) {
                            if (auth()->user()->hasRole('superadmin')) {
                                return $query->where('work_locations.business_id', NULL)
                                    ->where('work_locations.is_default', 1)
                                    ->where('work_locations.is_active', 1);

                            } else {
                                return $query->where('work_locations.business_id', NULL)
                                    ->where('work_locations.is_default', 1)
                                    ->where('work_locations.is_active', 1)
                                    ->whereDoesntHave("disabled", function($q) {
                                        $q->whereIn("disabled_work_locations.created_by", [auth()->user()->id]);
                                    })

                                    ->orWhere(function ($query) use($value)  {
                                        $query->where("work_locations.id",$value)->where('work_locations.business_id', NULL)
                                            ->where('work_locations.is_default', 0)
                                            ->where('work_locations.created_by', auth()->user()->id)
                                            ->where('work_locations.is_active', 1);


                                    });
                            }
                        })
                            ->when(!empty(auth()->user()->business_id), function ($query) use ($created_by, $value) {
                                return $query->where('work_locations.business_id', NULL)
                                    ->where('work_locations.is_default', 1)
                                    ->where('work_locations.is_active', 1)
                                    ->whereDoesntHave("disabled", function($q) use($created_by) {
                                        $q->whereIn("disabled_work_locations.created_by", [$created_by]);
                                    })
                                    ->whereDoesntHave("disabled", function($q)  {
                                        $q->whereIn("disabled_work_locations.business_id",[auth()->user()->business_id]);
                                    })

                                    ->orWhere(function ($query) use( $created_by, $value){
                                        $query->where("work_locations.id",$value)->where('work_locations.business_id', NULL)
                                            ->where('work_locations.is_default', 0)
                                            ->where('work_locations.created_by', $created_by)
                                            ->where('work_locations.is_active', 1)
                                            ->whereDoesntHave("disabled", function($q) {
                                                $q->whereIn("disabled_work_locations.business_id",[auth()->user()->business_id]);
                                            });
                                    })
                                    ->orWhere(function ($query) use($value)  {
                                        $query->where("work_locations.id",$value)->where('work_locations.business_id', auth()->user()->business_id)
                                            ->where('work_locations.is_default', 0)
                                            ->where('work_locations.is_active', 1);

                                    });
                            })
                        ->exists();

                    if ($exists) {
                        $fail($attribute . " is already exist.");
                    }


                },
            ],
        ];


return $rules;

    }
}
