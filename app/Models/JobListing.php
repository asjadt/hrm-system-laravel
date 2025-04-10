<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobListing extends Model
{
    use HasFactory;
    protected $appends = ['total_candidates'];

    protected $fillable = [
        'title',
        'description',
        'required_skills',
        'application_deadline',
        'posted_on',
        'department_id',
        'minimum_salary',
        'maximum_salary',
        'experience_level',
        'job_type_id',
        'work_location_id',
        "is_active",
        "business_id",
        "created_by"

    ];

    public function candidates()
    {
        return $this->hasMany(Candidate::class, "job_listing_id",'id');
    }


    public function job_type()
    {
        return $this->belongsTo(JobType::class, "job_type_id",'id');
    }

    public function work_location()
    {
        return $this->belongsTo(WorkLocation::class, "work_location_id" ,'id');
    }




    public function job_platforms() {
        return $this->belongsToMany(JobPlatform::class, 'job_listing_job_platforms', 'job_listing_id', 'job_platform_id');
    }


    public function department()
    {
        return $this->belongsTo(Department::class, "department_id" , 'id');
    }

    public function getTotalCandidatesAttribute($value) {
           return $this->candidates()->count();
    }









   

}
