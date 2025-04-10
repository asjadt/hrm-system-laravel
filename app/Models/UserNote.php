<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNote extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'title',
        'description',

        'history',
        'created_by'
    ];


    public function mentions()
    {
        return $this->hasMany(UserNoteMention::class, 'user_note_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class,"user_id","id");
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by',"id");
    }
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by',"id");
    }




  public function getHistoryAttribute($value)
  {
      return $this->created_by == auth()->user()->id ? json_decode($value, true) : null;
  }


  public function setHistoryAttribute($value)
  {
      $this->attributes['history'] = json_encode($value);
  }


  public function updateHistory(array $changes)
  {


      $history = $this->history ?? [];
      $history[] = $changes;
      $this->update(['history' => $history]);
  }


}
