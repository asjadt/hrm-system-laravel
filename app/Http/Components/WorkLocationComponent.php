<?php

namespace App\Http\Components;

use App\Models\WorkLocation;
use App\Models\WorkShift;
use App\Models\WorkShiftHistory;
use Carbon\Carbon;
use Exception;

class WorkLocationComponent
{

public function getWorkLocations () {
    $created_by  = NULL;
    if(auth()->user()->business) {
        $created_by = auth()->user()->business->created_by;
    }
    $work_locations = WorkLocation::when(empty(request()->user()->business_id), function ($query) use ($created_by) {
        if (auth()->user()->hasRole('superadmin')) {
            return $query->where('work_locations.business_id', NULL)
                ->where('work_locations.is_default', 1)
                ->when(isset(request()->is_active), function ($query)  {
                    return $query->where('work_locations.is_active', intval(request()->is_active));
                });
        } else {
            return $query

            ->where(function($query) {
                $query->where('work_locations.business_id', NULL)
                ->where('work_locations.is_default', 1)
                ->where('work_locations.is_active', 1)
                ->when(isset(request()->is_active), function ($query)  {
                    if(intval(request()->is_active)) {
                        return $query->whereDoesntHave("disabled", function($q) {
                            $q->whereIn("disabled_work_locations.created_by", [auth()->user()->id]);
                        });
                    }

                })
                ->orWhere(function ($query)  {
                    $query->where('work_locations.business_id', NULL)
                        ->where('work_locations.is_default', 0)
                        ->where('work_locations.created_by', auth()->user()->id)
                        ->when(isset(request()->is_active), function ($query)  {
                            return $query->where('work_locations.is_active', intval(request()->is_active));
                        });
                });

            });
        }
    })
        ->when(!empty(request()->user()->business_id), function ($query) use ($created_by) {
            return $query
            ->where(function($query) use( $created_by) {


                $query->where('work_locations.business_id', NULL)
                ->where('work_locations.is_default', 1)
                ->where('work_locations.is_active', 1)
                ->whereDoesntHave("disabled", function($q) use($created_by) {
                    $q->whereIn("disabled_work_locations.created_by", [$created_by]);
                })
                ->when(isset(request()->is_active), function ($query) use ($created_by)  {
                    if(intval(request()->is_active)) {
                        return $query->whereDoesntHave("disabled", function($q) use($created_by) {
                            $q->whereIn("disabled_work_locations.business_id",[auth()->user()->business_id]);
                        });
                    }

                })


                ->orWhere(function ($query) use ( $created_by){
                    $query->where('work_locations.business_id', NULL)
                        ->where('work_locations.is_default', 0)
                        ->where('work_locations.created_by', $created_by)
                        ->where('work_locations.is_active', 1)

                        ->when(isset(request()->is_active), function ($query)  {
                            if(intval(request()->is_active)) {
                                return $query->whereDoesntHave("disabled", function($q) {
                                    $q->whereIn("disabled_work_locations.business_id",[auth()->user()->business_id]);
                                });
                            }

                        })


                        ;
                })
                ->orWhere(function ($query) {
                    $query->where('work_locations.business_id', auth()->user()->business_id)

                        ->when(isset(request()->is_active), function ($query)  {
                            return $query->where('work_locations.is_active', intval(request()->is_active));
                        });;
                });
            });


        })
        ->when(!empty(request()->search_key), function ($query)  {
            return $query->where(function ($query)  {
                $term = request()->search_key;
                $query->where("work_locations.name", "like", "%" . $term . "%")
                    ->orWhere("work_locations.description", "like", "%" . $term . "%");
            });
        })
     
        ->when(!empty(request()->start_date), function ($query)  {
            return $query->where('work_locations.created_at', ">=", request()->start_date);
        })
        ->when(!empty(request()->end_date), function ($query)  {
            return $query->where('work_locations.created_at', "<=", (request()->end_date . ' 23:59:59'));
        })
        ->when(!empty(request()->order_by) && in_array(strtoupper(request()->order_by), ['ASC', 'DESC']), function ($query)  {
            return $query->orderBy("work_locations.id", request()->order_by);
        }, function ($query) {
            return $query->orderBy("work_locations.id", "DESC");
        })
        ->when(!empty(request()->per_page), function ($query)  {
            return $query->paginate(request()->per_page);
        }, function ($query) {
            return $query->get();
        });

        return $work_locations;


}

}
