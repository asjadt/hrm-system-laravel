<?php

namespace App\Http\Utils;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\ErrorLog;
use App\Models\Notification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

trait BasicNotificationUtil
{
    use BasicUtil;

    public function send_notification($data, $user, $title, $type, $entity_name,$all_parent_department_ids=[])
    {

            if ($data instanceof \Illuminate\Support\Collection) {

                if ($data->isNotEmpty()) {

                    $entity_ids = $data->pluck('id')->toArray();

                    $entity = $data->first();
                    $notification_link = ($entity_name) . "/" . implode('_', $entity_ids);

                } else {

                    return;
                }
            } else {

                $entity_ids = [];
                $entity = $data;
                $notification_link = ($entity_name) . "/" . ($entity->id);
            }


            $departments = Department::whereHas("users", function ($query) use ($entity) {
                $query->where("users.id", $entity->user_id);
            })
                ->get();



            $notification_description = '';


            if ($type == "create") {
                $notification_description = (explode('_', $entity_name)[0]) . " taken for the user " . ($user->first_Name . " " . $user->middle_Name . " " . $user->last_Name);

            }
            if ($type == "update") {
                $notification_description = (explode('_', $entity_name)[0]) . " updated for the user " . ($user->first_Name . " " . $user->middle_Name . " " . $user->last_Name);

            }
            if ($type == "approve") {
                $notification_description = (explode('_', $entity_name)[0]) . " approved for the user " . ($user->first_Name . " " . $user->middle_Name . " " . $user->last_Name);

            }
            if ($type == "reject") {
                $notification_description = (explode('_', $entity_name)[0]) . " rejected for the user " . ($user->first_Name . " " . $user->middle_Name . " " . $user->last_Name);

            }
            if ($type == "delete") {
                $notification_description = (explode('_', $entity_name)[0]) . " deleted for the user " . ($user->first_Name . " " . $user->middle_Name . " " . $user->last_Name);

            }





          if(!empty($all_parent_department_ids)) {
            $unique_all_parent_department_manager_ids = $this->get_all_parent_department_manager_ids($all_parent_department_ids);
          } else {
            $all_parent_department_manager_ids = collect([]);
            foreach ($departments as $department) {
                $all_parent_department_manager_ids->push($department->manager_id);
                $all_parent_department_manager_ids = $all_parent_department_manager_ids->merge($department->getAllParentManagerIds());
            }
            $unique_all_parent_department_manager_ids = $all_parent_department_manager_ids
                ->filter()
                ->unique()
                ->values();
          }







$notifications = [];

foreach ($unique_all_parent_department_manager_ids->all() as $manager_id) {

    $notifications[] = [
        "entity_id" => $entity->id,
        "entity_ids" => json_encode($entity_ids),
        "entity_name" => $entity_name,
        'notification_title' => $title,
        'notification_description' => $notification_description,
        'notification_link' => $notification_link,
        "sender_id" => 1,
        "receiver_id" => $manager_id,
        "business_id" => auth()->user()->business_id,
        "is_system_generated" => 1,
        "status" => "unread",
        "created_at" => now(),
        "updated_at" => now(),
    ];
}


Notification::insert(collect($notifications)
->only([
    "sender_id",
    "receiver_id",
    "business_id",
    "entity_name",
    "entity_id",
    "entity_ids",


    'notification_title',
    'notification_description',
    'notification_link',
    "is_system_generated",
    "notification_template_id",
    "status",
])
->toArray()

);


    }



}
