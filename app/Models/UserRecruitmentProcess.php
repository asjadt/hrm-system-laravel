<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRecruitmentProcess extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'recruitment_process_id',
        'description',
        'attachments',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }

    protected $casts = [
        'attachments' => 'array',

    ];

    public function recruitment_process()
    {
        return $this->hasOne(RecruitmentProcess::class, 'id','recruitment_process_id');
    }



   
}
