<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNoteMention extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_note_id',
        'user_id',
    ];


    public function user_note()
    {
        return $this->belongsTo(UserNote::class);
    }

    public function mentioned_user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
