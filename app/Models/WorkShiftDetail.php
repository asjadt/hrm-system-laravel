<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkShiftDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_shift_id',
        'day',
        "start_at",
        'end_at',
        'is_weekend',
    ];
   



}
