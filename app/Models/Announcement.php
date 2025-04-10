<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'description',
        "is_active",
        "business_id",
        "created_by"
    ];
    protected $appends = ['status'];


    public function getStatusAttribute($value) {


    $user_announcement = UserAnnouncement::where([
        "user_id" => auth()->user()->id,
        "announcement_id" =>$this->id
    ])
    ->first();
    if(!$user_announcement) {
return "invalid";
    }else {
        return $user_announcement->status;
    }



        }



    public function creator() {
        return $this->belongsTo(User::class, "created_by","id");
    }

    public function departments() {
        return $this->belongsToMany(Department::class, 'department_announcements', 'announcement_id', 'department_id');
    }

    public function users() {
        return $this->belongsToMany(User::class, 'user_announcements', 'announcement_id', 'user_id')->withPivot('status');
    }


}
