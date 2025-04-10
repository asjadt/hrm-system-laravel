<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'model_name',
        "issue_date_column",
        'expiry_date_column',
        "user_eligible_field",
        "user_relationship",
        'duration',
        'duration_unit',
        'send_time',
        'frequency_after_first_reminder',
        'reminder_limit',
        'keep_sending_until_update',
        'entity_name',
        "business_id",
        "created_by"
    ];

    protected $casts = [
        'keep_sending_until_update' => 'boolean',
    ];
    protected $hidden = [
        'model_name',
        "issue_date_column",
        'expiry_date_column',
    ];
}
