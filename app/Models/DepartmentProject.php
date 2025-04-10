<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentProject extends Model
{
    use HasFactory;
    protected $fillable = [
        'project_id', 'department_id'
    ];

}
