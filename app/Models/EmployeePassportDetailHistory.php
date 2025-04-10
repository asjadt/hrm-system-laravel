<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class EmployeePassportDetailHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        "business_id",

        'passport_number',
        "passport_issue_date",
        "passport_expiry_date",
        "place_of_issue",


        "from_date",
        "to_date",
        "user_id",
        "is_manual",
        'created_by'
    ];

    protected $appends = ['is_current'];
    public function getIsCurrentAttribute() {
        $current_passport_id = Session::get('current_passport_id');
        return $current_passport_id === $this->id;
    }



    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by',"id");
    }

    public function employee(){
        return $this->hasOne(User::class,'id', 'user_id');
    }


}
