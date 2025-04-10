<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentUser extends Model
{
    use HasFactory;
    protected $fillable = [
        'department_id', 'user_id'
    ];
    public function department() {
        return $this->hasOne(Department::class,  'id', 'department_id');
    }
}
