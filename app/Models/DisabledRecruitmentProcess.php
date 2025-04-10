<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisabledRecruitmentProcess extends Model
{
    use HasFactory;
    protected $fillable = [
        'recruitment_process_id',
        'business_id',
        'created_by',
      
    ];


}
