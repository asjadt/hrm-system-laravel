<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrunDepartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payrun_id', 'department_id'
    ];
   
}
