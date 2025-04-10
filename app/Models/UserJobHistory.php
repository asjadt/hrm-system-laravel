<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserJobHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'company_name',
        'country',
        'job_title',
        'employment_start_date',
        'employment_end_date',
        'responsibilities',
        'supervisor_name',
        'contact_information',
        'work_location',
        'achievements',
        'created_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by','id');
    }







}
