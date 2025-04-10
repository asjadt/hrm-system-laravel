<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAssetHistory extends Model
{
    use HasFactory;
    protected $fillable = [

        'user_id',
        "user_asset_id",

        'name',
        'code',
        'serial_number',
        'type',
        "is_working",
        "status",
        'image',
        'date',
        'note',
        "business_id",

        "from_date",
        "to_date",
        'created_by'
    ];




    public function user(){
        return $this->hasOne(User::class,'id', 'user_id');
    }

    public function user_asset(){
        return $this->hasOne(UserAsset::class,'id', 'user_asset_id');
    }





}
