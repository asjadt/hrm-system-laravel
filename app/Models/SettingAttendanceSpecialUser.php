<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingAttendanceSpecialUser extends Model
{
    use HasFactory;
    protected $fillable = [
        'setting_attendance_id', 'user_id'
    ];
  
}
