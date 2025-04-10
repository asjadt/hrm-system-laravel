<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailerLog extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "is_login_attempts" ,
    ];

    public function user(){
        return $this->hasOne(User::class,'id', 'user_id');
    }
}
