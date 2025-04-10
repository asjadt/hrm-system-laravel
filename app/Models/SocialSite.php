<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialSite extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'icon',
        'link',
        "is_active",
        "is_default",
        "business_id",
        "created_by"
    ];
    public function user_social_site() {
        return $this->hasMany(UserSocialSite::class, 'social_site_id', 'id');
    }

   
}
