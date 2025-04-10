<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;
    protected $fillable = [
        "payroll_name",

        'user_id',
        "payrun_id",

        'total_holiday_hours',
        'total_paid_leave_hours',
        'total_regular_attendance_hours',
        'total_overtime_attendance_hours',
        'regular_hours',
        'overtime_hours',
        'holiday_hours_salary',
        'leave_hours_salary',
        'regular_attendance_hours_salary',
        'overtime_attendance_hours_salary',


        'regular_hours_salary',
        'overtime_hours_salary',




        "start_date",
        "end_date",

        'status',
        'is_active',
        'business_id',
        'created_by',
    ];










    protected $casts = [
        'is_active' => 'boolean',
    ];



    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function payrun()
    {
        return $this->belongsTo(Payrun::class, 'payrun_id');
    }

    public function payroll_attendances()
    {
        return $this->hasMany(PayrollAttendance::class, "payroll_id" ,'id');
    }

    public function payroll_leave_records()
    {
        return $this->hasMany(PayrollLeaveRecord::class, "payroll_id" ,'id');
    }

     public function payroll_holidays()
    {
        return $this->hasMany(PayrollHoliday::class, "payroll_id" ,'id');
    }



    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }






}
