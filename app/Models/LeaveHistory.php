<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveHistory extends Model
{
    use HasFactory;

    protected $fillable = [

        "leave_id",
        "actor_id",
        "action",
        "is_approved",
        "leave_created_at",
        "leave_updated_at",


        'leave_duration',
        'day_type',
        'leave_type_id',
        'user_id',
        'date',
        'note',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'attachments',
        "hourly_rate",
        "status",
        "is_active",
        "business_id",
        "created_by"
    ];

    protected $casts = [
        'attachments' => 'array',

    ];

    public function records(){
        return $this->hasMany(LeaveRecordHistory::class,'leave_id', 'id');
    }

    public function employee() {
        return $this->belongsTo(User::class, "user_id","id");
    }


    public function actor() {
        return $this->belongsTo(User::class, "actor_id","id");
    }
    public function approved_by_users(){
        return $this->hasMany(LeaveHistory::class,'leave_id', 'leave_id')
        ->where([
            "action" => "approve"
        ]);
    }
    public function rejected_by_users(){
        return $this->hasMany(LeaveHistory::class,'leave_id', 'leave_id')
        ->where([
            "action" => "reject"
        ]);
    }

    public function leave_type() {
        return $this->belongsTo(SettingLeaveType::class, "leave_type_id","id");
    }






}
