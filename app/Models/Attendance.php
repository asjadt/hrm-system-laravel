<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $appends = ['is_in_arrears'];

    protected $fillable = [
        'note',
        "in_geolocation",
        "out_geolocation",
        'user_id',
        'in_date',
        'does_break_taken',
        "behavior",
        "capacity_hours",
        "work_hours_delta",
        "break_type",
        "break_hours",
        "total_paid_hours",
        "regular_work_hours",
        "work_shift_start_at",
        "work_shift_end_at",
        "work_shift_history_id",
        "holiday_id",
        "leave_record_id",
        "is_weekend",
        "overtime_hours",
        "punch_in_time_tolerance",
        "status",
        'work_location_id',

        "is_active",
        "business_id",
        "created_by",
        "regular_hours_salary",
        "overtime_hours_salary",
        "attendance_records",
        "is_present"

    ];


      protected $casts = [
        'attendance_records' => 'array',
    ];



    public function getIsInArrearsAttribute($value) {
        if($this->status == "approved" || $this->total_paid_hours > 0){
            $attendance_arrear =   AttendanceArrear::where(["attendance_id" => $this->id])->first();
            $payroll = Payroll::whereHas("payroll_attendances", function ($query)  {
                $query->where("payroll_attendances.attendance_id", $this->id);
            })->first();


            if (!$payroll) {
                if (!$attendance_arrear) {
                        $last_payroll_exists = Payroll::where([
                            "user_id" => $this->user_id,
                        ])
                            ->where("end_date", ">=", $this->in_date)
                            ->exists();

                        if ($last_payroll_exists) {
                            AttendanceArrear::create([
                                "attendance_id" => $this->id,
                                "status" =>  "pending_approval",

                            ]);
                            return true;
                        }

                }else if($attendance_arrear->status == "pending_approval") {
                return true;
                }

            }
            return false;
        }

        AttendanceArrear::where([
            "attendance_id" => $this->id,
        ])
        ->delete();

        return false;


        }


    public function arrear(){
        return $this->hasOne(AttendanceArrear::class,'attendance_id', 'id');
    }

    public function payroll_attendance()
    {
        return $this->hasOne(PayrollAttendance::class, "attendance_id" ,'id');
    }

    public function employee(){
        return $this->hasOne(User::class,'id', 'user_id');
    }











    public function work_location()
    {
        return $this->belongsTo(WorkLocation::class, "work_location_id" ,'id');
    }








    public function projects() {
        return $this->belongsToMany(Project::class, 'attendance_projects', 'attendance_id', 'project_id');
    }














}
