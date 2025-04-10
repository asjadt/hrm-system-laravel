<?php

namespace App\Http\Utils;

use App\Models\Announcement;
use App\Models\Business;
use App\Models\Department;
use App\Models\EmployeePensionHistory;
use App\Models\UploadedFile;
use App\Models\User;
use App\Models\UserAnnouncement;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;

trait BasicUtil
{

    function getLast12MonthsDates($year) {
        $dates = [];


        for ($month = 1; $month <= 12; $month++) {

            $date = Carbon::createFromDate($year, $month, 1);

            $startOfMonth = $date->copy()->startOfMonth()->toDateString();
            $endOfMonth = $date->copy()->endOfMonth()->toDateString();
            $monthName = $date->copy()->format('F');

            $dates[] = [
                'month' => substr($monthName, 0, 3),
                'start_date' => $startOfMonth,
                'end_date' => $endOfMonth,
            ];
        }

        return $dates;
    }
    public function addAnnouncementIfMissing($all_parent_departments_of_user) {


        $announcements_to_show = Announcement::where(function($query) use($all_parent_departments_of_user) {
            $query->whereHas("departments",function($query) use($all_parent_departments_of_user) {
              $query->whereIn("departments.id",$all_parent_departments_of_user);
            })

            ->orWhereDoesntHave("departments");
        })
        ->pluck("id");

        foreach($announcements_to_show as $announcement_id){
       $userAnnouncement =  UserAnnouncement::where([
                "announcement_id" => $announcement_id,
                "user_id" => auth()->user()->id
            ])
            ->first();

           if(empty($userAnnouncement)) {
            UserAnnouncement::create([
                "announcement_id" => $announcement_id,
                "user_id" => auth()->user()->id,
                "status" => "unread"
            ]);
           }

        }
    }



    public function getMainRoleId() {

    $user = auth()->user();


    $roles = $user->roles;


    $roleIds = $roles->pluck('id');


    $minRoleId = $roleIds->min();
    return $minRoleId;
    }


      public function resolveClassName($className)
      {
          return "App\\Models\\" . $className;
      }



    public function fieldsHaveChanged($fields_to_check, $entity1, $entity2, $date_fields) {
        foreach ($fields_to_check as $field) {
            $value1 = $entity1->$field;
            $value2 = $entity2[$field];


            if (in_array($field, $date_fields)) {
                $value1 = (new Carbon($value1))->format('Y-m-d');
                $value2 = (new Carbon($value2))->format('Y-m-d');
            }

            if ($value1 !== $value2) {
                return true;
            }
        }
        return false;
    }

    public function getCurrentPensionHistory(string $modelClass,$session_name ,$current_user_id, $issue_date_column, $expiry_date_column)
    {
        $model = new $modelClass;

        $user = User::where([
            "id" => $current_user_id
        ])
        ->first();
        if(!$user) {
            return NULL;
          }
          $current_data = NULL;
        if(!$user->pension_eligible) {
            $current_data = $model::where('user_id', $current_user_id)
            ->where("pension_eligible",0)
            ->orderByDesc("id")
            ->first();
        } else {
            $current_data = $model::where('user_id', $current_user_id)
            ->where("pension_eligible", 1)
            ->where($issue_date_column, '<', now())
                ->orderByDesc("id")
                ->first();
        }

        Session::put($session_name, $current_data?$current_data->id:NULL);
        return $current_data;


    }

    public function getCurrentHistory(string $modelClass,$session_name ,$current_user_id, $issue_date_column, $expiry_date_column)
    {

        $model = new $modelClass;

        $user = User::where([
            "id" => $current_user_id
        ])
        ->first();

        if(!$user) {
            return NULL;
          }

        $current_data = NULL;

           $latest_expired_record = $model::where('user_id', $current_user_id)
            ->where($issue_date_column, '<', now())
            ->orderBy($expiry_date_column, 'DESC')
            ->first();


            if($latest_expired_record) {
                $current_data = $model::where('user_id', $current_user_id)
                ->where($issue_date_column, '<', now())
                ->where($expiry_date_column, $latest_expired_record[$expiry_date_column])

                ->orderByDesc("id")
                ->first();
            }


        Session::put($session_name, $current_data?$current_data->id:NULL);
        return $current_data;


    }



    public function get_all_departments_of_manager() {

        $all_manager_department_ids = [];
        $manager_departments = Department::where("manager_id", auth()->user()->id)->get();
        foreach ($manager_departments as $manager_department) {
            $all_manager_department_ids[] = $manager_department->id;
            $all_manager_department_ids = array_merge($all_manager_department_ids, $manager_department->getAllDescendantIds());
        }
        return $all_manager_department_ids;
    }


public function validateUserQuery($user_id,$all_manager_department_ids) {

    $user = User::where([
        "id" => $user_id
    ])
    ->where(function($query) use($all_manager_department_ids) {
        $query->whereHas("departments", function($query) use($all_manager_department_ids){
            $query->whereIn("departments.id",$all_manager_department_ids);
        })
        ->orWhere([
            "id" => auth()->user()->id
        ]);
    })
    ->first();

    if(empty($user)){
        throw new Exception("You don't have access to this user.",401);
    }
    return $user;

}


public function get_all_user_of_manager($all_manager_department_ids) {
    $all_manager_user_ids = User::whereHas("department_user.department", function($query) use($all_manager_department_ids){
        $query->whereIn("departments.id",$all_manager_department_ids);
    })
    ->pluck("users.id");

    return $all_manager_user_ids->toArray();
}

    public function all_parent_departments_of_user($user_id) {
        $all_parent_department_ids = [];
        $assigned_departments = Department::whereHas("users", function ($query) use ($user_id) {
            $query->where("users.id", $user_id);
        })->limit(1)->get();


        foreach ($assigned_departments as $assigned_department) {
            array_push($all_parent_department_ids, $assigned_department->id);
            $all_parent_department_ids = array_merge($all_parent_department_ids, $assigned_department->getAllParentIds());
        }

        return array_unique($all_parent_department_ids);
    }




    public function all_parent_departments_manager_of_user($user_id,$business_id) {
        $all_parent_department_manager_ids = [];
        $assigned_departments = Department::whereHas("users", function ($query) use ($user_id) {
            $query->where("users.id", $user_id);
        })->limit(1)->get();


        foreach ($assigned_departments as $assigned_department) {
            array_push($all_parent_department_manager_ids, $assigned_department->manager_id);
            $all_parent_department_manager_ids = array_merge($all_parent_department_manager_ids, $assigned_department->getAllParentDepartmentManagerIds($business_id));
        }


    $all_parent_department_manager_ids = array_unique(array_filter($all_parent_department_manager_ids, function($value) {
        return !is_null($value);
    }));

    return $all_parent_department_manager_ids;
    }






public function log($data) {
   Log::info(json_encode($data));
}

public function getUserByIdUtil($id,$all_manager_department_ids) {
  $user =  User::with("roles")
    ->where([
        "id" => $id
    ])

    ->when(!auth()->user()->hasRole('superadmin'), function ($query) use($all_manager_department_ids)  {
        return $query->where(function ($query) use($all_manager_department_ids){
            return  $query->where('created_by', auth()->user()->id)
                ->orWhere('id', auth()->user()->id)
                ->orWhere('business_id', auth()->user()->business_id)
                ->orWhereHas("department_user.department", function ($query) use ($all_manager_department_ids) {
                    $query->whereIn("departments.id", $all_manager_department_ids);
                });
        });
    })
    ->first();
    if (!$user) {
        throw new Exception("no user found",404);

    }
    return $user;
}


public function retrieveData($query,$orderByField){
  $data =  $query->when(!empty(request()->order_by) && in_array(strtoupper(request()->order_by), ['ASC', 'DESC']), function ($query) use ($orderByField)  {
        return $query->orderBy($orderByField, request()->order_by);
    }, function ($query) use ($orderByField) {
        return $query->orderBy($orderByField, "DESC");
    })
    ->when(!empty(request()->per_page), function ($query)  {
        return $query->paginate(request()->per_page);
    }, function ($query) {
        return $query->get();
    });
    return $data;
}



public function generateUniqueId($relationModel,$relationModelId,$mainModel,$unique_identifier_column = ""){

    $relation = $this->resolveClassName($relationModel)::where(["id" => $relationModelId])->first();


    $prefix = "";
    if ($relation) {
        preg_match_all('/\b\w/', $relation->name, $matches);

        $prefix = implode('', array_map(function ($match) {
            return strtoupper($match[0]);
        }, $matches[0]));


        $prefix = substr($prefix, 0, 2 * count($matches[0]));
    }

    $current_number = 1;

    do {
        $unique_identifier = $prefix . "-" . str_pad($current_number, 4, '0', STR_PAD_LEFT);
        $current_number++;
    } while (
        $this->resolveClassName($mainModel)::where([
            ($unique_identifier_column?$unique_identifier_column:"unique_identifier") => $unique_identifier,
            "business_id" => auth()->user()->business_id
        ])->exists()
    );
return $unique_identifier;


}










public function moveUploadedFiles($files, $location) {
    $temporary_files_location = config("setup-config.temporary_files_location");

    foreach ($files as $temp_file_path) {
        $full_temp_path = public_path($temp_file_path);
        $new_location_path = public_path(str_replace($temporary_files_location, $location, $temp_file_path));

        if (File::exists($full_temp_path)) {
            try {

                $new_directory_path = dirname($new_location_path);
                if (!File::exists($new_directory_path)) {
                    File::makeDirectory($new_directory_path, 0755, true);
                }


                File::move($full_temp_path, $new_location_path);
                Log::info("File moved successfully from {$full_temp_path} to {$new_location_path}");
            } catch (\Exception $e) {

                Log::error("Failed to move file from {$full_temp_path} to {$new_location_path}: " . $e->getMessage());
            }
        } else {

            Log::error("File does not exist: {$full_temp_path}");
        }
    }
}











public function storeUploadedFiles($filePaths, $fileKey, $location, $arrayOfString = NULL,$businessId=NULL)
    {



        if(empty($businessId)){
            $businessId = auth()->user()->business_id;
        }



        $location = "{$businessId}/{$location}";


        if (is_array($arrayOfString)) {
            return collect($filePaths)->map(function ($filePathItem) use ($fileKey, $location) {
                $filePathItem[$fileKey] = $this->storeUploadedFiles($filePathItem[$fileKey], "", $location);
                return $filePathItem;
            });
        }



        $temporaryFilesLocation = config("setup-config.temporary_files_location");




        return collect($filePaths)->map(function ($filePathItem) use ($temporaryFilesLocation, $fileKey, $location) {



            $file = !empty($fileKey) ? $filePathItem[$fileKey] : $filePathItem;



            $fullTemporaryPath = public_path($file);

            $newLocation = str_replace($temporaryFilesLocation, $location, $file);
            $newLocationPath = public_path($newLocation);


            if (File::exists($fullTemporaryPath)) {
                try {

                    $newDirectoryPath = dirname($newLocationPath);
                    if (!File::exists($newDirectoryPath)) {
                        File::makeDirectory($newDirectoryPath, 0755, true);
                    }


                    File::move($fullTemporaryPath, $newLocationPath);
                    Log::info("File moved successfully from {$fullTemporaryPath} to {$newLocationPath}");
                } catch (Exception $e) {
                    throw new Exception(("Failed to move file from {$fullTemporaryPath} to {$newLocationPath}: " . $e->getMessage()), 500);
                }
            }


            if (!empty($fileKey)) {
                $filePathItem[$fileKey] = $newLocation;
            } else {

                $filePathItem = $newLocation;
            }

            return $filePathItem;
        })->toArray();
    }


    public function makeFilePermanent($filePaths, $fileKey, $arrayOfString = NULL)
    {





    }




public function moveUploadedFilesBack($filePaths, $fileKey, $location, $arrayOfString= NULL) {


  if(is_array($arrayOfString)) {
        return collect($filePaths)->map(function($filePathItem) use ($fileKey, $location) {
            $filePathItem[$fileKey] = $this->storeUploadedFiles($filePathItem[$fileKey], "", $location);
            return $filePathItem;
        });

    }



    $temporaryFilesLocation = config("setup-config.temporary_files_location");


    collect($filePaths)->each(function($filePathItem) use ($temporaryFilesLocation, $fileKey, $location) {

        $file = (!empty($fileKey)) ? $filePathItem[$fileKey] : $filePathItem;


        $destinationPath = public_path($file);
        $temporaryLocation = str_replace($location, $temporaryFilesLocation, $file);


        if (File::exists($destinationPath)) {
            try {

                $temporaryDirectoryPath = dirname($temporaryLocation);
                if (!File::exists($temporaryDirectoryPath)) {
                    File::makeDirectory($temporaryDirectoryPath, 0755, true);
                }


                File::move($destinationPath, public_path($temporaryLocation));
                Log::info("File moved back successfully from {$destinationPath} to {$temporaryLocation}");
            } catch (\Exception $e) {

                Log::error("Failed to move file back from {$destinationPath} to {$temporaryLocation}: " . $e->getMessage());
            }
        } else {

            Log::error("File does not exist at destination: {$destinationPath}");
        }
    });

}





public function get_all_parent_department_manager_ids($all_parent_department_ids) {
    $manager_ids = Department::whereIn("id",$all_parent_department_ids)->pluck("manager_id")
    ->filter() 
    ->unique()
    ->values();
    return $manager_ids;
}





}
