<?php

namespace App\Jobs;

use App\Models\Department;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $data;
    protected $title;
    protected $type;
    protected $entity_name;
    protected $user;

    public function __construct($data, $user, $title, $type, $entity_name)
    {
        $this->data = $data;
        $this->title = $title;
        $this->type = $type;
        $this->entity_name = $entity_name;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        if ($this->data instanceof \Illuminate\Support\Collection) {

            if ($this->data->isNotEmpty()) {

                $entity_ids = $this->data->pluck('id')->toArray();

                $entity = $this->data->first();
                $notification_link = ($this->entity_name) . "/" . implode('_', $entity_ids);

            } else {

                return;
            }
        } else {

            $entity_ids = [];
            $entity = $this->data;
            $notification_link = ($this->entity_name) . "/" . ($entity->id);
        }


        $departments = Department::whereHas("users", function ($query) use ($entity) {
            $query->where("users.id", $entity->user_id);
        })
            ->get();

        $all_parent_department_manager_ids = [];
        foreach ($departments as $department) {
            array_push($all_parent_department_manager_ids,$department->manager_id);
            $all_parent_department_manager_ids = array_merge($all_parent_department_manager_ids, $department->getAllParentManagerIds());
        }
        $unique_all_parent_department_manager_ids = array_unique($all_parent_department_manager_ids);

        $notification_description = '';


        if ($this->type == "create") {
            $notification_description = (explode('_', $this->entity_name)[0]) . " taken for the user " . ($this->user->first_Name . " " . $this->user->middle_Name . " " . $this->user->last_Name);

        }
        if ($this->type == "update") {
            $notification_description = (explode('_', $this->entity_name)[0]) . " updated for the user " . ($this->user->first_Name . " " . $this->user->middle_Name . " " . $this->user->last_Name);

        }
        if ($this->type == "approve") {
            $notification_description = (explode('_', $this->entity_name)[0]) . " approved for the user " . ($this->user->first_Name . " " . $this->user->middle_Name . " " . $this->user->last_Name);

        }
        if ($this->type == "reject") {
            $notification_description = (explode('_', $this->entity_name)[0]) . " rejected for the user " . ($this->user->first_Name . " " . $this->user->middle_Name . " " . $this->user->last_Name);

        }
        if ($this->type == "delete") {
            $notification_description = (explode('_', $this->entity_name)[0]) . " deleted for the user " . ($this->user->first_Name . " " . $this->user->middle_Name . " " . $this->user->last_Name);

        }



        foreach ($unique_all_parent_department_manager_ids as $manager_id) {
       
            $notification = [
                "entity_id" => $entity->id,
                "entity_ids" => $entity_ids,
                "entity_name" => $this->entity_name,
                'notification_title' => $this->title,
                'notification_description' => $notification_description,
                'notification_link' => $notification_link,
                "sender_id" => 1,
                "receiver_id" => $manager_id,
                "business_id" => auth()->user()->business_id,
                "is_system_generated" => 1,
                "status" => "unread",
            ];

            Notification::create($notification);
        }
    }
}
