<?php

namespace App\Http\Components;

use App\Models\Project;
use App\Models\WorkLocation;
use App\Models\WorkShift;
use App\Models\WorkShiftHistory;
use Carbon\Carbon;
use Exception;

class ProjectComponent
{

public function getProjects () {
    $projects = Project::with("departments","users")
    ->where(
        [
            "business_id" => auth()->user()->business_id
        ]
    )
     ->when(!empty(request()->user_id), function ($query)  {
        return $query->whereHas('users', function($query)  {
                $query->where("users.id",request()->user_id);
        });
    })
    ->when(!empty(request()->assigned_user_id_not), function ($query)  {
        return $query->whereDoesntHave('users', function($query)  {
                $query->where("users.id",request()->assigned_user_id_not);
        });
    })


        ->when(!empty(request()->search_key), function ($query)  {
            return $query->where(function ($query)  {
                $term = request()->search_key;
                $query->where("name", "like", "%" . $term . "%")
                    ->orWhere("description", "like", "%" . $term . "%");
            });
        })
        ->when(!empty(request()->name), function ($query)  {
            return $query->where(function ($query)  {
                $term = request()->name;
                $query->where("name", "like", "%" . $term . "%");
            });
        })
        ->when(!empty(request()->status), function ($query)  {
            return $query->where(function ($query)  {
                $term = request()->status;
                $query->where("status",  $term);
            });
        })





        ->when(!empty(request()->start_date), function ($query)  {
            return $query->where('start_date', ">=", request()->start_date);
        })
        ->when(!empty(request()->end_date), function ($query)  {
            return $query->where('end_date', "<=", (request()->end_date . ' 23:59:59'));
        })
        ->when(!empty(request()->in_date), function ($query)  {
            return $query->where('start_date', "<=", (request()->in_date . ' 00:00:00'))
            ->where('end_date', "<=", (request()->in_date . ' 23:59:59'));
        })




        ->when(!empty(request()->order_by) && in_array(strtoupper(request()->order_by), ['ASC', 'DESC']), function ($query)  {
            return $query->orderBy("projects.id", request()->order_by);
        }, function ($query) {
            return $query->orderBy("projects.id", "DESC");
        })

        ->select('projects.*')

        ->when(!empty(request()->per_page), function ($query)  {
            return $query->paginate(request()->per_page);
        }, function ($query) {
            return $query->get();
        });



        return $projects;


}

}
