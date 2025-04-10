<?php

namespace App\Models;

use App\Http\Utils\BasicUtil;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payrun extends Model
{
    use HasFactory, BasicUtil;

    protected $fillable = [
        "period_type",
        "start_date",
        "end_date",
        "generating_type",
        "consider_type",
        "consider_overtime",
        "notes",

        "is_active",
        "business_id",
        "created_by"
    ];
    protected $appends = ['available_users_for_payroll'];
    public function getAvailableUsersForPayrollAttribute($value) {
        $all_manager_department_ids = $this->get_all_departments_of_manager();

          $employee_count = User::where([
            "business_id" => $this->business_id,
            "is_active" => 1
        ])
            ->whereDoesntHave("payrolls", function ($q)  {
                $q->where("payrolls.start_date", $this->start_date)
                    ->where("payrolls.end_date", $this->end_date);
            })

            ->whereNotIn("id", [auth()->user()->id])
            ->whereHas("department_user.department", function ($query) use ($all_manager_department_ids) {
                $query->whereIn("departments.id", $all_manager_department_ids);
            })




            ->count();
            return $employee_count;


        }

    public function getStartDateAttribute($value)
    {
        $start_date = $value;

        switch ($this->period_type) {
            case 'weekly':
                if (!$start_date) {
                    $start_date = Carbon::now()->startOfWeek()->subWeek(1);
                }
                break;
            case 'monthly':
                if (!$start_date) {
                    $start_date = Carbon::now()->startOfMonth()->subMonth(1);
                }

                break;
            default:
                if (!$start_date) {
                    $start_date = $this->attributes['start_date'];
                }
                break;
        }

        return $start_date;
    }

    public function getEndDateAttribute($value)
    {
        $end_date = $value;

        switch ($this->period_type) {
            case 'weekly':
                if (!$end_date) {
                    $end_date = Carbon::now()->startOfWeek();
                }
                break;
            case 'monthly':

                if (!$end_date) {
                    $end_date = Carbon::now()->startOfMonth()->subDay(1);
                }
                break;
            default:
                if (!$end_date) {
                    $end_date = $this->attributes['end_date'];
                }
                break;
        }

        return $end_date;
    }




    public function departments() {
        return $this->belongsToMany(Department::class, 'payrun_departments', 'payrun_id', 'department_id');
    }
    public function users() {
        return $this->belongsToMany(User::class, 'payrun_users', 'payrun_id', 'user_id');
    }

    public function payrolls() {
        return $this->hasMany(Payroll::class, 'payrun_id', 'id');
    }

}
