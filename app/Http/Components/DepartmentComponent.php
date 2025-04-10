<?php
namespace App\Http\Components;

use App\Models\Department;

class DepartmentComponent {
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

    public function get_all_departments_of_manager() {
        $all_manager_department_ids = [];
        $manager_departments = Department::where("manager_id", auth()->user()->id)->get();
        foreach ($manager_departments as $manager_department) {
            $all_manager_department_ids[] = $manager_department->id;
            $all_manager_department_ids = array_merge($all_manager_department_ids, $manager_department->getAllDescendantIds());
        }
        return $all_manager_department_ids;
    }

    

}
