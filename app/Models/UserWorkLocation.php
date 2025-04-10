<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWorkLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_location_id',
        'user_id',
    ];
}
