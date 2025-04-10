<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceHistoryRecord extends Model
{
    use HasFactory;
    protected $fillable = [
        'in_time',
        'out_time',
        "attendance_id",
        
    ];
}
