<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkShiftHistory extends Model
{
    use HasFactory;
    protected $appends = ['is_current',"attendance_exists"];

    protected $fillable = [
        'name',
        "break_type",
        "break_hours",
        'type',
        "description",

        'is_business_default',
        'is_personal',



        "is_default",
        "is_active",
        "business_id",
        "created_by",



        "from_date",
        "to_date",
        "work_shift_id",
        "user_id"


    ];

    protected $dates = ['start_date',
    'end_date'];

    public function getAttendanceExistsAttribute()
    {
       return Attendance::where([
        "work_shift_history_id" => $this->id
       ])
       ->exists();

    }
    public function getIsCurrentAttribute()
    {

        $today = Carbon::today();
        $from_date = Carbon::parse($this->from_date);
        $to_date = Carbon::parse($this->to_date);

        return $today->between($from_date, $to_date);
    }
    public function attendances(){
        return $this->hasMany(Attendance::class,'work_shift_history_id', 'id');
    }

    public function details(){
        return $this->hasMany(WorkShiftDetailHistory::class,'work_shift_id', 'id');
    }

    public function departments() {
        return $this->belongsToMany(Department::class, 'employee_department_work_shift_histories', 'work_shift_id', 'department_id');
    }
    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


    public function users() {
        return $this->belongsToMany(User::class, 'employee_user_work_shift_histories', 'work_shift_id', 'user_id')->withPivot('from_date', 'to_date');
    }

    public function user_work_shift(){
        return $this->hasMany(EmployeeUserWorkShiftHistory::class,'work_shift_id', 'id');
    }




}
