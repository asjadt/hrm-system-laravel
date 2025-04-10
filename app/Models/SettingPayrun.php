<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingPayrun extends Model
{
    use HasFactory;
    protected $fillable = [
        'payrun_period',
        'consider_type',
        'consider_overtime',

        "business_id",
        "is_active",
        "is_default",
        "created_by"
    ];



    public function restricted_users() {
        return $this->belongsToMany(User::class, 'setting_payrun_restricted_users', 'setting_payrun_id', 'user_id');
    }
    public function restricted_departments() {
        return $this->belongsToMany(Department::class, 'setting_payrun_restricted_departments', 'setting_payrun_id', 'department_id');
    }




   
}
