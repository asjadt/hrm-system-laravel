<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingLeaveSpecialUser extends Model
{
    use HasFactory;
    protected $fillable = [
        'setting_leave_id', 'user_id'
    ];
    
}
