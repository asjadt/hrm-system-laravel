<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisabledJobType extends Model
{
    use HasFactory;
    protected $fillable = [
        'job_type_id',
        'business_id',
        'created_by',
   
    ];
  





}
