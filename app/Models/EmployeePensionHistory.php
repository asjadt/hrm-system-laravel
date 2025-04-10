<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class EmployeePensionHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'business_id',
        'pension_eligible',
        'pension_enrollment_issue_date',
        'pension_letters',
        'pension_scheme_status',
        'pension_scheme_opt_out_date',
        'pension_re_enrollment_due_date',
        "is_manual",
        'user_id',
      
        "from_date",
        "to_date",
        'created_by'
    ];
    protected $appends = ['is_current'];

    public function getIsCurrentAttribute() {
        $current_pension_id = Session::get('current_pension_id');
        return $current_pension_id === $this->id;
    }





    public function employee(){
        return $this->hasOne(User::class,'id', 'user_id');
    }



    protected $casts = [
        'pension_letters' => 'array',
    ];
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by',"id");
    }


}
