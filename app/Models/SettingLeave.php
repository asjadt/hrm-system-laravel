<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingLeave extends Model
{
    use HasFactory;
    protected $fillable = [
        'start_month',
        'approval_level',
        'allow_bypass',
      "business_id",
      "is_active",
      "is_default",
      "created_by",


    ];

    public function special_users() {
        return $this->belongsToMany(User::class, 'setting_leave_special_users', 'setting_leave_id', 'user_id');
    }
    public function special_roles() {
        return $this->belongsToMany(Role::class, 'setting_leave_special_roles', 'setting_leave_id', 'role_id');
    }
    public function paid_leave_employment_statuses() {
        return $this->belongsToMany(EmploymentStatus::class, 'paid_leave_employment_statuses', 'setting_leave_id', 'employment_status_id');
    }
    public function unpaid_leave_employment_statuses() {
        return $this->belongsToMany(EmploymentStatus::class, 'unpaid_leave_employment_statuses', 'setting_leave_id', 'employment_status_id');
    }




   
}
