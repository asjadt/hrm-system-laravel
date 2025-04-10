<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisabledWorkLocation extends Model
{
    use HasFactory;
    protected $fillable = [
        'work_location_id',
        'business_id',
        'created_by',
       
    ];



}
