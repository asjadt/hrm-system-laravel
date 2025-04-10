<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class UserEducationHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'degree',
        'major',
        'school_name',
        'graduation_date',
        'start_date',

        'achievements',
        'description',
        'address',
        'country',
        'city',
        'postcode',
        'is_current',
        'created_by',
        'attachments',
    ];

    protected $casts = [
        'attachments' => 'array',

    ];


    public function user()
    {
        return $this->belongsTo(User::class,"user_id","id");
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by',"id");
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

      $filePaths = $this->attachments;

     
      foreach ($filePaths as $filePath) {
          if (File::exists(public_path($filePath))) {
              File::delete(public_path($filePath));
          }
      }

  }


}
