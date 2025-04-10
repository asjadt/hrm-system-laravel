<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class EmployeeRightToWorkHistory extends Model
{
    use HasFactory;

    protected $fillable = [
         "business_id",
        'right_to_work_code',
        'right_to_work_check_date',
        'right_to_work_expiry_date',
        'right_to_work_docs',

        "is_manual",
        'user_id',
        "from_date",
        "to_date",
        'created_by',

    ];

    protected $casts = [

        'right_to_work_docs' => 'array',
    ];

    protected $appends = ['is_current'];
    public function getIsCurrentAttribute() {
        $current_right_to_work_id = Session::get('current_right_to_work_id');
        return $current_right_to_work_id === $this->id;
    }



    public function employee()
    {
        return $this->belongsTo(User::class,"user_id","id");
    }
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }



}
