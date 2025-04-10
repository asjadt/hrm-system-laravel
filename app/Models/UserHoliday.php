<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHoliday extends Model
{
    use HasFactory;
    protected $fillable = [
        'holiday_id', 'user_id'
    ];
}
