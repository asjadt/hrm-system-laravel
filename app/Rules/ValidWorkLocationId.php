<?php

namespace App\Rules;

use App\Models\WorkLocation;
use Illuminate\Contracts\Validation\Rule;

class ValidWorkLocationId implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {

            $created_by  = NULL;
            if(auth()->user()->business) {
                $created_by = auth()->user()->business->created_by;
            }

            $exists = WorkLocation::where("work_locations.id",$value)
            ->when(empty(auth()->user()->business_id), function ($query)  {

                $query->where(function($query) {
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

                            ->orWhere(function ($query)   {
                                $query->where('work_locations.business_id', NULL)
                                    ->where('work_locations.is_default', 0)
                                    ->where('work_locations.created_by', auth()->user()->id)
                                    ->where('work_locations.is_active', 1);


                            });
                    }
                });

            })
                ->when(!empty(auth()->user()->business_id), function ($query) use ($created_by) {
                     $query->where(function($query) use ($created_by) {
                        $query  ->where('work_locations.business_id', NULL)
                        ->where('work_locations.is_default', 1)
                        ->where('work_locations.is_active', 1)
                        ->whereDoesntHave("disabled", function($q) use($created_by) {
                            $q->whereIn("disabled_work_locations.created_by", [$created_by]);
                        })
                        ->whereDoesntHave("disabled", function($q)  {
                            $q->whereIn("disabled_work_locations.business_id",[auth()->user()->business_id]);
                        })

                        ->orWhere(function ($query) use( $created_by){
                            $query->where('work_locations.business_id', NULL)
                                ->where('work_locations.is_default', 0)
                                ->where('work_locations.created_by', $created_by)
                                ->where('work_locations.is_active', 1)
                                ->whereDoesntHave("disabled", function($q) {
                                    $q->whereIn("disabled_work_locations.business_id",[auth()->user()->business_id]);
                                });
                        })
                        ->orWhere(function ($query)   {
                            $query->where('work_locations.business_id', auth()->user()->business_id)

                                ->where('work_locations.is_active', 1);

                        });
                });
                     })


            ->exists();

            return $exists;


    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute is invalid.';
    }
}
