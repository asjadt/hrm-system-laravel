<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateRecruitmentProcess extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id',
        'recruitment_process_id',
        'description',
        'attachments',
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id','id');
    }

    protected $casts = [
        'attachments' => 'array',

    ];

    public function recruitment_process()
    {
        return $this->hasOne(RecruitmentProcess::class, 'id','recruitment_process_id');
    }


}
