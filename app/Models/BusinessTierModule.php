<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessTierModule extends Model
{
    use HasFactory;

    protected $fillable = [
        "is_enabled",
        "business_tier_id",
        "module_id",
        'created_by'
    ];


    public function business_tier(){
        return $this->belongsTo(BusinessTier::class,'business_tier_id', 'id');
    }



}
