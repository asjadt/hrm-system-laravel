<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisabledEmploymentStatus extends Model
{
    use HasFactory;
    protected $fillable = [
        'employment_status_id',
        'business_id',
        'created_by',
    
    ];

}
