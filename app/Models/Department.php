<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Department extends Model
{
    use HasFactory;
    protected $appends = ['total_users_count'];
    protected $fillable = [
        "name",
        "work_location_id",
        "description",
        "is_active",
        "manager_id",
        "parent_id",
        "business_id",
        "created_by"
    ];






    public function parent(){
        return $this->belongsTo(Department::class,'parent_id', 'id');
    }

    public function children()
    {
        return $this->hasMany(Department::class, 'parent_id', 'id');
    }


    public function getAllDescendantIds()
    {
        $descendantIds = [];
        $this->getDescendantIdsRecursive($this, $descendantIds);
        return $descendantIds;
    }

    protected function getDescendantIdsRecursive($department, &$descendantIds)
    {
        foreach ($department->children as $child) {
            $descendantIds[] = $child->id;


            $this->getDescendantIdsRecursive($child, $descendantIds);
        }
    }

    public function getAllParentIds()
    {
        $parentIds = [];
        $this->getParentIdsRecursive($this, $parentIds,Department::where([
            "business_id" => auth()->user()->business_id
        ])
    ->count()


    );

        return $parentIds;
    }
    public function getAllParentDepartmentManagerIds($business_id)
    {
        $parentDepartmentManagerIds = [];
        $this->getParentDepartmentManagerIdsRecursive($this, $parentDepartmentManagerIds,
        Department::where([
            "business_id" => $business_id
        ]
        )
    ->count()


    );

        return $parentDepartmentManagerIds;
    }


    protected function getParentIdsRecursive($department, &$parentIds, $depth = 0)
    {

        if ($depth >= 10) {
            return;
        }

        if ($department->parent) {

            $parentIds[] = $department->parent->id;


            $this->getParentIdsRecursive($department->parent, $parentIds, $depth + 1);
        }
    }

    protected function getParentDepartmentManagerIdsRecursive($department, &$parentDepartmentManagerIds, $depth = 0)
    {

        if ($depth >= 10) {
            return;
        }

        if ($department->parent) {

            $parentDepartmentManagerIds[] = $department->parent->manager_id;


            $this->getParentDepartmentManagerIdsRecursive($department->parent, $parentDepartmentManagerIds, $depth + 1);
        }
    }

    public function getAllParentManagerIds()
    {
        $parentManagerIds = [];
        $this->getParentManagerIdsRecursive($this, $parentManagerIds);

        return $parentManagerIds;
    }
    protected function getParentManagerIdsRecursive($department, &$parentManagerIds)
    {
        if ($department->parent) {

            $parentManagerIds[] = $department->parent->manager_id;


            $this->getParentManagerIdsRecursive($department->parent, $parentManagerIds);
        }
    }




    public function recursiveChildren()
    {
        return $this->children()->with('recursiveChildren','manager');
    }

    public function getTotalUsersCountAttribute()
    {
        return DepartmentUser::where('department_id', $this->id)->count();
    }










    public function children_recursive()
    {
        return $this->hasMany(Department::class, 'parent_id', 'id')->with(
            [
                "children_recursive" => function ($query) {
                    $query->select('departments.id', 'departments.name');
                },
                "manager" => function ($query) {
                    $query->select('users.id', 'users.first_Name','users.middle_Name',
                    'users.last_Name');
                }

            ]


        )

        ->addSelect([
            'total_users_count' => DepartmentUser::selectRaw('COUNT(*)')
                ->whereColumn('departments.id', 'department_id')
        ]);
      ;
    }



    public function payrun_department()
    {
        return $this->hasOne(PayrunDepartment::class, "department_id" ,'id');
    }




    public function work_location()
    {
        return $this->belongsTo(WorkLocation::class, "work_location_id" ,'id');
    }

    public function manager(){
        return $this->belongsTo(User::class,'manager_id', 'id');
    }
    public function holidays() {
        return $this->belongsToMany(Holiday::class, 'department_holidays', 'department_id', 'holiday_id');
    }





    public function employee_rota()
    {
        return $this->hasOne(EmployeeRota::class, "department_id" ,'id');
    }




    public function users() {
        return $this->belongsToMany(User::class, 'department_users', 'department_id', 'user_id');
    }


    public function announcements() {
        return $this->belongsToMany(Announcement::class, 'department_announcements', 'department_id', 'announcement_id');
    }
    public function work_shifts() {
        return $this->belongsToMany(WorkShift::class, 'department_work_shifts', 'department_id', 'work_shift_id');
    }



    public function scopeWhereHasRecursiveHolidays($query, $today, $depth = 5)
    {
        if ($depth <= 0) {
            return;
        }

        $query->whereHas('holidays', function ($subQuery) use ($today) {
            $subQuery->where('start_date', '<=', $today->startOfDay())
                     ->where('end_date', '>=', $today->endOfDay());
        })->orWhere(function ($query) use ($today, $depth) {
            $query->whereHas('parent', function ($subQuery) use ($today, $depth) {
                $subQuery->whereNotNull('parent_id');
                $subQuery->whereHasRecursiveHolidays($today, $depth - 1); 
            });
        });
    }





}
