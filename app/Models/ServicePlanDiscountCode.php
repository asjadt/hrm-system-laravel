<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicePlanDiscountCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'discount_amount',
        'service_plan_id',
    ];

    public function service_plan()
    {
        return $this->belongsTo(ServicePlan::class,"service_plan_id","id");
    }
}
