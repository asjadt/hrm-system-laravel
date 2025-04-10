<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeProjectHistory extends Model
{
    use HasFactory;

    protected $fillable = [

        'name',
        'description',
        'start_date',
        'end_date',
        'status',
        "is_active",
        "business_id",
        "created_by",
        "project_id",
        "from_date",
        "to_date",
        "user_id",


    ];

  

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }







}
