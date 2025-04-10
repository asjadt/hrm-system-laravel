<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class BusinessPensionHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        "pension_scheme_registered",
        "pension_scheme_name",
        "pension_scheme_letters",
        "business_id",
        "created_by"
    ];

    protected $casts = [
        'pension_scheme_letters' => 'array',
    ];

    public function business(){
        return $this->belongsTo(Business::class,'business_id', 'id');
    }





        protected static function boot()
        {
            parent::boot();


            static::deleting(function($item) {

                $item->deleteFiles();
            });
        }

        /**
         * Delete associated files.
         *
         * @return void
         */



        public function deleteFiles()
        {

            $filePaths = $this->pension_scheme_letters;

          
            foreach ($filePaths as $filePath) {
                if (File::exists(public_path($filePath->file))) {
                    File::delete(public_path($filePath->file));
                }
            }
        }



}
