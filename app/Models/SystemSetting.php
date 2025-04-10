<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'self_registration_enabled',
        'STRIPE_KEY',
        "STRIPE_SECRET"
    ];

    protected $hidden = [
        'STRIPE_KEY',
        "STRIPE_SECRET"
    ];
}
