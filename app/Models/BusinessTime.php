<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessTime extends Model
{
    use HasFactory;

    protected $fillable = [
        "day",
        "start_at",
        "end_at",
        "is_weekend",
        "business_id",
    ];

   

}
