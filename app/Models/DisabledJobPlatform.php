<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisabledJobPlatform extends Model
{
    use HasFactory;
    protected $fillable = [
        'job_platform_id',
        'business_id',
        'created_by',
       
    ];


}
