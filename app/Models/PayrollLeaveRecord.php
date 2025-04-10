<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollLeaveRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_id',
        'leave_record_id',
 
    ];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class, 'payroll_id');
    }

    public function leave_record()
    {
        return $this->belongsTo(LeaveRecord::class, 'leave_record_id');
    }









}
