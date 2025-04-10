<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeRota extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',

        "description",




        "department_id",
        "user_id",


        "is_default",
        "is_active",
        "business_id",
        "created_by"
    ];


    protected $dates = [
    'start_date',
    'end_date'
];



    public function details(){
        return $this->hasMany(EmployeeRotaDetail::class,'employee_rota_id', 'id');
    }


    public function department() {
        return $this->belongsTo(Department::class,  'department_id', 'id');
    }

    public function user() {
        return $this->belongsTo(User::class,  'user_id', 'id');
    }






}
