<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisabledSettingLeaveType extends Model
{
    use HasFactory;
    protected $fillable = [
        'setting_leave_type_id',
        'business_id',
        'created_by',
  
    ];
}
