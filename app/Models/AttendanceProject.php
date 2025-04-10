<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        "project_id",
    ];




}
