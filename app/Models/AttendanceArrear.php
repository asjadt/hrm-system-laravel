<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceArrear extends Model
{
    use HasFactory;
    protected $fillable = [
        'attendance_id',
        "status",
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class, "attendance_id" ,'id');
    }




   

}
