<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSocialSite extends Model
{
    use HasFactory;

    protected $fillable = [
        'social_site_id',
        'user_id',
        'profile_link',
        "created_by"
    ];

    public function social_site()
    {
        return $this->belongsTo(SocialSite::class, 'social_site_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
   
}
