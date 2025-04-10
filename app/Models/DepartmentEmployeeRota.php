<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentEmployeeRota extends Model
{
    use HasFactory;



    protected $fillable = [
        'employee_rota_id', 'department_id'
    ];



}
