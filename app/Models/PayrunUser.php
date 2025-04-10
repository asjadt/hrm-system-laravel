<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrunUser extends Model
{
    use HasFactory;
    protected $fillable = [
        'payrun_id', 'user_id'
    ];


    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }



}
