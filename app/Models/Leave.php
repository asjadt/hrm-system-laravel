<?php

namespace App\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasFactory;
    protected $appends = ['is_in_arrears'];
    protected $fillable = [
        'leave_duration',
        'day_type',
        'leave_type_id',
        'user_id',
        'date',
        'note',
        'start_date',
        'end_date',

        'attachments',
        "hourly_rate",
        "status",
        "is_active",
        "business_id",
        "created_by",
    ];
    public function getIsInArrearsAttribute($value)
    {
$is_in_arrears = false;


     $leave_records =LeaveRecord::where([
        "leave_id" => $this->id
    ])->get();
        $leave_record_ids = $leave_records->pluck("leave_records.id");


        if ($this->status == "approved" || (!empty($this->leave_type) && $this->leave_type->type == "paid")) {


            foreach ($leave_records as $leave_record) {

                $leave_record_arrear =   LeaveRecordArrear::where(["leave_record_id" => $leave_record->id])->first();

                $payroll = Payroll::whereHas("payroll_leave_records", function ($query) use ($leave_record) {
                    $query->where("payroll_leave_records.leave_record_id", $leave_record->id);
                })->first();


                if (empty($payroll)) {


                    if (empty($leave_record_arrear)) {


                        $last_payroll_exists = Payroll::where([
                            "user_id" => $this->user_id,
                        ])
                            ->where("end_date", ">=", $leave_record->date)
                            ->exists();

                        if (!empty($last_payroll_exists)) {
                            LeaveRecordArrear::create([
                                "leave_record_id" => $leave_record->id,
                                "status" => "pending_approval",
                            ]);
                            $is_in_arrears = true;
                            break;
                        }
                    } else if ($leave_record_arrear->status == "pending_approval") {

                        $is_in_arrears = true;
                        break;
                    }

                }

            }


        }


        LeaveRecordArrear::whereIn("leave_record_id", $leave_record_ids)

            ->delete();

        return  $is_in_arrears;
    }




    public function records()
    {
        return $this->hasMany(LeaveRecord::class, 'leave_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, "user_id", "id");
    }
    public function leave_type()
    {
        return $this->belongsTo(SettingLeaveType::class, "leave_type_id", "id");
    }
    protected $casts = [
        'attachments' => 'array',

    ];












}
