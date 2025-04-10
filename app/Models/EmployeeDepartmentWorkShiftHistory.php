<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDepartmentWorkShiftHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_shift_id', 'department_id'
    ];
  
}
