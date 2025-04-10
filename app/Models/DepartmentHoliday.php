<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentHoliday extends Model
{
    use HasFactory;
    protected $fillable = [
        'department_id', 'holiday_id'
    ];

}
