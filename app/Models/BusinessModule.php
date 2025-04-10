<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessModule extends Model
{
    use HasFactory;

    protected $fillable = [
        "is_enabled",
        "business_id",
        "module_id",
        'created_by'
    ];


    public function business_tier(){
        return $this->belongsTo(Business::class,'business_id', 'id');
    }

}
