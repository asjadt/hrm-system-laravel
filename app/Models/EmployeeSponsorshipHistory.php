<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class EmployeeSponsorshipHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        "business_id",
        'date_assigned',
        'expiry_date',
    
        'note',
        "certificate_number",
        "current_certificate_status",
        "is_sponsorship_withdrawn",

        "is_manual",
        'user_id',
        "from_date",
        "to_date",
        'created_by'
    ];

    protected $appends = ['is_current'];
    public function getIsCurrentAttribute() {
        $current_sponsorship_id = Session::get('current_sponsorship_id');

        return $current_sponsorship_id === $this->id;
    }


    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by',"id");
    }


    public function employee(){
        return $this->hasOne(User::class,'id', 'user_id');
    }














}
