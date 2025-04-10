<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{

    use HasFactory;
    protected $appends = ['can_update','can_delete'];

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
        "is_active",
        "is_default",
        "business_id",
        "created_by",
    ];

    public function creator() {
        return $this->belongsTo(User::class, "created_by","id");
    }


    public function getCanDeleteAttribute($value) {
        $request = request();



        if(!auth()->user()->hasRole("business_owner") && auth()->user()->id != $this->created_by) {
                return 0;
        }
        return 1;



        }

    public function getCanUpdateAttribute($value) {
        $request = request();


        if(!auth()->user()->hasRole("business_owner") && auth()->user()->id != $this->created_by) {
                return 0;
        }
        return 1;

        }


    public function departments() {
        return $this->belongsToMany(Department::class, 'department_projects', 'project_id', 'department_id');
    }

    public function users() {
        return $this->belongsToMany(User::class, 'user_projects', 'project_id', 'user_id');
    }





















}
