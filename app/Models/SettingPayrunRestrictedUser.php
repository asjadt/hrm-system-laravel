<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingPayrunRestrictedUser extends Model
{
    use HasFactory;
    protected $fillable = [
        'setting_payrun_id', 'user_id'
    ];
  
}
