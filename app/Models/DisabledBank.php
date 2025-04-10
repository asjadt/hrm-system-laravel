<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisabledBank extends Model
{
    use HasFactory;
    protected $fillable = [
        'bank_id',
        'business_id',
        'created_by',
  
    ];

}
