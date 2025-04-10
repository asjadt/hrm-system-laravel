<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class ProductVariation extends Model
{
    use HasFactory;

    protected $fillable = [
        "sub_sku",
        "quantity",
        "price",
        "automobile_make_id",
        "product_id",


    ];

    public function product(){
        return $this->belongsTo(Product::class,'product_id', 'id');
    }
  
}
